<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;

/**
 * Admission fee view over the EXISTING fee records. There is no admission ledger of our own:
 *  - demand  : fees_breackoff for the standard (same rule as admissionEnquiryController::ajax_getFeesBreakoff)
 *  - payment : admission_registration.amount / date_of_payment / payment_mode, recorded through the existing
 *              admission registration screens (and the admission_form receipt for the form fee)
 * None of the new screens can set a payment status; only the authorised, audited waiver below changes
 * "fee_waived", and it is only available when the institute allows waivers.
 */
class FeeService
{
    public function __construct(protected AuditService $audit, protected WorkflowService $workflow, protected SettingService $settings)
    {
    }

    /** Total breakoff amount for a standard in the given year (0 if none is configured). */
    public function demand(int $instituteId, int $syear, ?int $standardId): float
    {
        if (! $standardId) {
            return 0.0;
        }

        $row = DB::table('fees_breackoff as fb')
            ->join('standard as s', function ($join) {
                $join->whereRaw('s.id = fb.standard_id AND s.grade_id = fb.grade_id AND s.sub_institute_id = fb.sub_institute_id');
            })->join('student_quota as sq', function ($join) {
                $join->whereRaw('sq.id = fb.quota AND sq.sub_institute_id = fb.sub_institute_id');
            })
            ->selectRaw('SUM(fb.amount) as total_breakoff')
            ->where('fb.standard_id', $standardId)->where('fb.sub_institute_id', $instituteId)
            ->where('fb.syear', $syear)->where('fb.admission_year', $syear)
            ->where('fb.month_id', 'like', '%4202%')->where('sq.title', 'like', '%General%')
            ->groupBy('fb.quota')->first();

        return (float) ($row->total_breakoff ?? 0);
    }

    /** Fee summary for one application: demand, paid, status, receipt references. */
    public function summary(int $instituteId, int $enquiryId, int $syear): array
    {
        $applicant = $this->workflow->applicantOrFail($instituteId, $enquiryId);
        $workflow = $this->workflow->ensure($instituteId, $enquiryId);
        $registration = $applicant['registration'] ?? [];
        $form = $applicant['form'] ?? [];

        $standard = (int) ($workflow->standard_id ?: ($applicant['enquiry']['admission_standard'] ?? 0));
        $demand = $this->demand($instituteId, $syear, $standard ?: null);
        $recorded = ! empty($registration['date_of_payment']) || ! empty($registration['payment_mode']);
        $paid = $recorded ? (float) ($registration['amount'] ?? 0) : 0.0;

        if ($workflow->fee_waived) {
            $status = 'waived';
        } elseif ($paid <= 0) {
            $status = 'unpaid';
        } elseif ($demand > 0 && $paid < $demand) {
            $status = 'partially_paid';
        } else {
            $status = 'paid';
        }

        return [
            'demand' => $demand, 'paid' => $paid, 'balance' => max($demand - $paid, 0), 'status' => $status,
            'payment_mode' => $registration['payment_mode'] ?? null, 'date_of_payment' => $registration['date_of_payment'] ?? null,
            'form_fee' => $form['admission_form_fee'] ?? null, 'form_receipt_id' => $form['receipt_id'] ?? null,
            'waived' => (bool) $workflow->fee_waived, 'waiver_reason' => $workflow->fee_waiver_reason,
        ];
    }

    /** Does the institute's configured fee condition hold? */
    public function conditionMet(int $instituteId, int $enquiryId, int $syear): bool
    {
        $condition = $this->settings->get($instituteId, 'fee_condition');
        if ($condition === 'none') {
            return true;
        }
        $summary = $this->summary($instituteId, $enquiryId, $syear);
        if ($summary['status'] === 'waived') {
            return true;
        }

        return $condition === 'full' ? $summary['status'] === 'paid' : in_array($summary['status'], ['paid', 'partially_paid'], true);
    }

    /** Authorised waiver ('waive' capability): reason mandatory, institute must allow waivers. */
    public function waive(int $instituteId, int $enquiryId, string $reason): void
    {
        if (! $this->settings->flag($instituteId, 'allow_fee_waiver')) {
            throw AdmissionException::because('Fee waivers are not enabled for this institute (Admission Settings).');
        }
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required to waive the admission fee.');
        }

        DB::transaction(function () use ($instituteId, $enquiryId, $reason) {
            $workflow = $this->workflow->lockWorkflow($instituteId, $enquiryId);
            if ($workflow->confirmation_status === 'confirmed') {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            if ($workflow->fee_waived) {
                return;
            }
            $workflow->fee_waived = true;
            $workflow->fee_waiver_reason = mb_substr(trim($reason), 0, 500);
            $workflow->fee_waived_by = session()->get('user_id');
            $workflow->fee_waived_at = now();
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'fee', $enquiryId, 'fee_waived', null, 'waived', $reason, true);
        });
    }
}
