<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionWorkflow;
use Illuminate\Support\Facades\DB;

/**
 * Records the selection / merit-review decision. This is a human decision with an audit trail; no merit
 * formula or tie-break is implemented because none has been formally approved (see EligibilityService).
 */
class SelectionService
{
    /** from => allowed targets. A missing (null) selection may go anywhere except back to nothing. */
    protected const TRANSITIONS = [
        ''             => ['under_review', 'shortlisted', 'waitlisted', 'rejected', 'selected'],
        'under_review' => ['shortlisted', 'waitlisted', 'rejected', 'selected'],
        'shortlisted'  => ['under_review', 'waitlisted', 'rejected', 'selected'],
        'waitlisted'   => ['under_review', 'shortlisted', 'rejected', 'selected'],
        'selected'     => ['under_review', 'waitlisted', 'rejected'],
        'rejected'     => ['under_review'],
    ];

    public function __construct(protected AuditService $audit, protected WorkflowService $workflow, protected SettingService $settings)
    {
    }

    public function decide(int $instituteId, int $enquiryId, string $status, ?string $remarks): AdmissionWorkflow
    {
        if (! array_key_exists($status, AdmissionWorkflow::SELECTION)) {
            throw AdmissionException::because('Unknown selection status.');
        }
        if (in_array($status, ['rejected', 'waitlisted'], true) && trim((string) $remarks) === '') {
            throw AdmissionException::because('Remarks are required when rejecting or waitlisting an applicant.');
        }

        return DB::transaction(function () use ($instituteId, $enquiryId, $status, $remarks) {
            $workflow = $this->workflow->lockWorkflow($instituteId, $enquiryId);

            if ($workflow->confirmation_status === 'confirmed') {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            if ($workflow->selection_status === $status) {
                return $workflow; // idempotent
            }
            if ($workflow->application_status !== 'submitted') {
                throw AdmissionException::because('Only a submitted application can be reviewed.');
            }

            $from = (string) $workflow->selection_status;
            if (! in_array($status, self::TRANSITIONS[$from] ?? [], true)) {
                throw AdmissionException::because("A {$this->label($from)} applicant cannot move to {$this->label($status)}.");
            }

            if (in_array($status, ['shortlisted', 'selected'], true)) {
                if ($workflow->eligibility_status === 'ineligible') {
                    throw AdmissionException::because('The applicant is marked Ineligible. An authorised eligibility override is required first.');
                }
                if ($this->settings->flag($instituteId, 'require_eligibility') && $workflow->eligibility_status !== 'eligible') {
                    throw AdmissionException::because('Eligibility must be "Eligible" (or authorised override) before shortlisting or selection.');
                }
            }
            if ($status === 'rejected' || $status === 'under_review') {
                $liveOffer = DB::table('admission_offer')->where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->where('active', 1)->exists();
                if ($liveOffer) {
                    throw AdmissionException::because('An offer is active for this applicant. Withdraw it first.');
                }
            }

            $workflow->selection_status = $status;
            $workflow->selection_remarks = $remarks !== null ? mb_substr(trim($remarks), 0, 500) : null;
            $workflow->selection_by = session()->get('user_id');
            $workflow->selection_at = now();
            $workflow->save();

            $this->audit->log($instituteId, $enquiryId, 'selection', $enquiryId, 'selection_'.$status, $from ?: null, $status, $remarks);

            return $workflow;
        });
    }

    protected function label(string $status): string
    {
        return AdmissionWorkflow::SELECTION[$status] ?? 'unreviewed';
    }
}
