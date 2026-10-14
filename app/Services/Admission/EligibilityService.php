<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionEligibilityRule;
use App\Models\admission\AdmissionWorkflow;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates an applicant against the institute's configured, approved eligibility rules.
 *
 *  - No thresholds are built in. With no active rule the result is "needs_review", never "eligible".
 *  - A missing / non-numeric value never rejects: the applicant is routed to "needs_review".
 *  - Only an actual failed mandatory rule produces "ineligible".
 *  - A manual override (admin only, reason mandatory, audited) is sticky until explicitly reset.
 *  - No merit ranking / formula is implemented: none has been approved. Selection is a recorded decision
 *    (see SelectionService), not a computed score.
 */
class EligibilityService
{
    public function __construct(protected AuditService $audit, protected WorkflowService $workflow)
    {
    }

    public function rulesFor(int $instituteId, array $applicant)
    {
        $standard = ($applicant['form']['admission_standard'] ?? '') !== '' ? $applicant['form']['admission_standard'] : ($applicant['enquiry']['admission_standard'] ?? null);

        return AdmissionEligibilityRule::where('sub_institute_id', $instituteId)->where('status', true)
            ->where(fn ($q) => $q->whereNull('standard_id')->orWhere('standard_id', $standard))
            ->where(fn ($q) => $q->whereNull('syear')->orWhere('syear', $applicant['enquiry']['syear'] ?? null))
            ->orderBy('id')->get();
    }

    /** Pure evaluation (no writes): status, human reason and the values used. */
    public function evaluate(int $instituteId, int $enquiryId): array
    {
        $applicant = $this->workflow->applicantOrFail($instituteId, $enquiryId);
        $rules = $this->rulesFor($instituteId, $applicant);

        if ($rules->isEmpty()) {
            return ['status' => 'needs_review', 'reason' => 'No approved eligibility criteria are configured for this programme and year, so a person must decide.', 'values' => []];
        }

        $sources = $applicant['enquiry'];
        foreach (($applicant['form'] ?? []) as $key => $value) {
            if ($value !== null && $value !== '' && ! array_key_exists($key, $sources)) {
                $sources[$key] = $value;
            } elseif ($value !== null && $value !== '' && $key === 'last_exam_percentage') {
                $sources[$key] = $value;
            }
        }

        $rows = [];
        $failed = $missing = [];
        foreach ($rules as $rule) {
            $actual = $sources[$rule->field] ?? null;
            $outcome = $this->test($rule, $actual);
            $rows[] = [
                'label' => $rule->label, 'field' => $rule->field, 'operator' => $rule->operator, 'expected' => $rule->value,
                'actual' => $actual, 'mandatory' => (bool) $rule->is_mandatory, 'outcome' => $outcome,
            ];
            if ($rule->is_mandatory && $outcome === 'fail') {
                $failed[] = $rule->label;
            } elseif ($rule->is_mandatory && $outcome === 'missing') {
                $missing[] = $rule->label;
            }
        }

        if ($failed) {
            return ['status' => 'ineligible', 'reason' => 'Does not meet: '.implode('; ', $failed).($missing ? '. Also could not check: '.implode('; ', $missing) : ''), 'values' => $rows];
        }
        if ($missing) {
            return ['status' => 'needs_review', 'reason' => 'Value missing or unreadable for: '.implode('; ', $missing).'. Not rejected automatically.', 'values' => $rows];
        }

        return ['status' => 'eligible', 'reason' => 'All mandatory criteria met.', 'values' => $rows];
    }

    /** Evaluate and store the result on the workflow row. A manual override is kept unless $resetOverride. */
    public function run(int $instituteId, int $enquiryId, bool $resetOverride = false): array
    {
        return DB::transaction(function () use ($instituteId, $enquiryId, $resetOverride) {
            $workflow = $this->workflow->lockWorkflow($instituteId, $enquiryId);
            $this->assertOpen($workflow);
            $result = $this->evaluate($instituteId, $enquiryId);

            if ($workflow->eligibility_override && ! $resetOverride) {
                $result['kept_override'] = true;

                return $result;
            }

            $from = $workflow->eligibility_status;
            $workflow->eligibility_status = $result['status'];
            $workflow->eligibility_reason = $result['reason'];
            $workflow->eligibility_values = $result['values'];
            $workflow->eligibility_override = false;
            $workflow->eligibility_override_reason = null;
            $workflow->eligibility_by = session()->get('user_id');
            $workflow->eligibility_at = now();
            $workflow->save();

            $this->audit->log($instituteId, $enquiryId, 'eligibility', $enquiryId, $resetOverride ? 'eligibility_override_reset' : 'eligibility_evaluated', $from, $result['status'], $result['reason']);

            return $result;
        });
    }

    /** Manual decision outside the rules. Caller must hold the 'override' capability. */
    public function override(int $instituteId, int $enquiryId, string $status, string $reason): AdmissionWorkflow
    {
        if (! array_key_exists($status, AdmissionWorkflow::ELIGIBILITY) || $status === 'pending_review') {
            throw AdmissionException::because('Choose Eligible, Ineligible or Needs Review.');
        }
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required for a manual eligibility decision.');
        }

        return DB::transaction(function () use ($instituteId, $enquiryId, $status, $reason) {
            $workflow = $this->workflow->lockWorkflow($instituteId, $enquiryId);
            $this->assertOpen($workflow);
            $from = $workflow->eligibility_status;
            $computed = $this->evaluate($instituteId, $enquiryId);

            $workflow->eligibility_status = $status;
            $workflow->eligibility_override = true;
            $workflow->eligibility_override_reason = mb_substr(trim($reason), 0, 500);
            $workflow->eligibility_reason = 'Manual decision: '.$workflow->eligibility_override_reason;
            $workflow->eligibility_values = $computed['values'];
            $workflow->eligibility_by = session()->get('user_id');
            $workflow->eligibility_at = now();
            $workflow->save();

            $this->audit->log($instituteId, $enquiryId, 'eligibility', $enquiryId, 'eligibility_override', $from, $status, $reason, true, ['computed' => $computed['status']], ['status' => $status]);

            return $workflow;
        });
    }

    protected function assertOpen(AdmissionWorkflow $workflow): void
    {
        if ($workflow->confirmation_status === 'confirmed') {
            throw AdmissionException::because('The admission is already confirmed.');
        }
    }

    /** pass | fail | missing */
    protected function test(AdmissionEligibilityRule $rule, $actual): string
    {
        $actual = is_string($actual) ? trim($actual) : $actual;
        $empty = $actual === null || $actual === '';

        if ($rule->operator === 'filled') {
            return $empty ? 'fail' : 'pass';
        }
        if ($empty) {
            return 'missing';
        }

        if (in_array($rule->operator, ['>=', '>', '<=', '<'], true)) {
            $a = $this->number($actual);
            $e = $this->number($rule->value);
            if ($a === null || $e === null) {
                return 'missing'; // ambiguous value or misconfigured rule: needs a person, not a rejection
            }

            return match ($rule->operator) {
                '>=' => $a >= $e ? 'pass' : 'fail',
                '>'  => $a > $e ? 'pass' : 'fail',
                '<=' => $a <= $e ? 'pass' : 'fail',
                '<'  => $a < $e ? 'pass' : 'fail',
            };
        }

        $a = strtolower((string) $actual);
        if ($rule->operator === 'in') {
            $list = array_map(fn ($v) => strtolower(trim($v)), explode(',', (string) $rule->value));

            return in_array($a, $list, true) ? 'pass' : 'fail';
        }
        $matches = $a === strtolower(trim((string) $rule->value));

        return ($rule->operator === '=') === $matches ? 'pass' : 'fail';
    }

    protected function number($value): ?float
    {
        $clean = str_replace(['%', ',', ' '], '', (string) $value);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
