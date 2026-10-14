<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionWorkflow;
use Illuminate\Support\Facades\DB;

/**
 * Admission confirmation and student-record creation.
 *
 *  - Runs in ONE transaction holding the per-institute number lock and the application's workflow row lock.
 *  - Idempotent: a retry / double click returns the existing student and creates nothing
 *    (workflow.student_id is unique; an existing tblstudent.admission_id match is linked, not duplicated).
 *  - Policy items (documents, eligibility, selection, offer, fee) can be bypassed only with an explicit,
 *    authorised override and a reason, which is stored on the application and in the audit trail.
 *    Structural items (form, registration, programme data) can never be overridden.
 *  - The tblstudent / tblstudent_enrollment field mapping is the one the legacy saveStudent() used, so the
 *    resulting student looks the same to the rest of the ERP (active = status 1).
 */
class ConfirmationService
{
    public function __construct(
        protected AuditService $audit,
        protected WorkflowService $workflow,
        protected SettingService $settings,
        protected DocumentService $documents,
        protected FeeService $fees,
        protected NumberService $numbers
    ) {
    }

    /** @return array{items: array<int,array>, blocking: array<int,array>, can_confirm: bool, can_override: bool} */
    public function checklist(int $instituteId, int $enquiryId, int $syear): array
    {
        $applicant = $this->workflow->applicantOrFail($instituteId, $enquiryId);
        $wf = $this->workflow->ensure($instituteId, $enquiryId);
        $s = fn (string $key) => $this->settings->flag($instituteId, $key);

        $standardId = (int) $this->standardOf($applicant);
        $standard = $standardId ? DB::table('standard')->where('id', $standardId)->where('sub_institute_id', $instituteId)->first() : null;
        $profile = DB::table('tbluserprofilemaster')->where('name', 'Student')->where('sub_institute_id', $instituteId)->value('id');

        $unverified = $this->documents->unverifiedMandatory($instituteId, $enquiryId);
        $acceptedOffer = DB::table('admission_offer')->where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->where('status', 'accepted')->exists();
        $fee = $this->fees->summary($instituteId, $enquiryId, $syear);

        $items = [
            $this->item('form', 'Application form created', true, (bool) $applicant['form'], false),
            $this->item('registration', 'Admission registration details completed', true, (bool) $applicant['registration'], false, $applicant['registration'] ? null : 'Complete the admission registration record first.'),
            // tblstudent_enrollment.student_quota is NOT NULL, so a registration without one can never become a student
            $this->item('quota', 'Seat category (student quota) selected on the registration', true,
                $applicant['registration'] === null || trim((string) ($applicant['registration']['student_quota'] ?? '')) !== '', false,
                'Choose the student quota on the admission registration screen.'),
            $this->item('submitted', 'Application submitted', true, $wf->application_status === 'submitted', false),
            $this->item('documents', 'Required documents verified', $s('require_documents_verified'), ! $unverified, true, $unverified ? 'Not verified: '.implode(', ', $unverified) : null),
            $this->item('eligibility', 'Eligibility: eligible', $s('require_eligibility') || $wf->eligibility_status === 'ineligible', $wf->eligibility_status === 'eligible', true,
                'Current: '.(AdmissionWorkflow::ELIGIBILITY[$wf->eligibility_status] ?? $wf->eligibility_status).($wf->eligibility_override ? ' (manual override)' : '')),
            $this->item('selection', 'Selection: selected', $s('require_selection') || $wf->selection_status === 'rejected', $wf->selection_status === 'selected', true,
                'Current: '.(AdmissionWorkflow::SELECTION[$wf->selection_status] ?? 'Not reviewed')),
            $this->item('offer', 'Offer accepted', $s('require_offer_accepted'), $acceptedOffer, true),
            $this->item('fee', 'Admission fee condition ('.$this->settings->get($instituteId, 'fee_condition').')', $this->settings->get($instituteId, 'fee_condition') !== 'none',
                $this->fees->conditionMet($instituteId, $enquiryId, $syear), true, 'Fee status: '.$fee['status']),
            $this->item('programme', 'Programme, academic year and student profile available', true, $standard && ($standard->grade_id ?? null) && $syear && $profile, false,
                ! $standard ? 'Admission standard not found.' : (! $profile ? 'No "Student" user profile is configured for this institute.' : null)),
        ];

        $blocking = array_values(array_filter($items, fn ($i) => $i['required'] && ! $i['ok']));

        return [
            'items'        => $items,
            'blocking'     => $blocking,
            'can_confirm'  => ! $blocking,
            'can_override' => $blocking && ! array_filter($blocking, fn ($i) => ! $i['overridable']) ? true : false,
        ];
    }

    /**
     * Confirm the admission and create (or link) exactly one student record.
     *
     * @return array{status:string, created:bool, student_id:int, enrollment_no:?string}
     */
    public function confirm(int $instituteId, int $enquiryId, int $syear, $termId, ?string $overrideReason = null, bool $mayOverride = false): array
    {
        return DB::transaction(function () use ($instituteId, $enquiryId, $syear, $termId, $overrideReason, $mayOverride) {
            $this->numbers->lock($instituteId);
            $wf = $this->workflow->lockWorkflow($instituteId, $enquiryId);

            // Idempotent paths -----------------------------------------------------------------
            if ($wf->confirmation_status === 'confirmed' && $wf->student_id) {
                return $this->result('already_confirmed', false, (int) $wf->student_id);
            }
            $existing = DB::table('tblstudent')->where('sub_institute_id', $instituteId)->where('admission_id', (string) $enquiryId)->orderBy('id')->first();
            if ($existing) {
                $before = $wf->confirmation_status;
                $this->markConfirmed($wf, (int) $existing->id, null);
                $this->audit->log($instituteId, $enquiryId, 'confirmation', $enquiryId, 'student_linked', $before, 'confirmed', 'Linked to existing student record');

                return $this->result('linked', false, (int) $existing->id, $existing->enrollment_no);
            }
            if ($wf->confirmation_status === 'cancelled') {
                throw AdmissionException::because('This admission was cancelled. Resume it before confirming.');
            }

            // Checklist ------------------------------------------------------------------------
            $check = $this->checklist($instituteId, $enquiryId, $syear);
            $overridden = false;
            if (! $check['can_confirm']) {
                if (! $check['can_override']) {
                    throw AdmissionException::because('Confirmation is blocked by the checklist.', array_column($check['blocking'], 'label'));
                }
                if (! $mayOverride) {
                    throw AdmissionException::because('Confirmation is blocked by the checklist. Only an authorised administrator can override it.', array_column($check['blocking'], 'label'));
                }
                if (trim((string) $overrideReason) === '') {
                    throw AdmissionException::because('A reason is required to override the checklist.', array_column($check['blocking'], 'label'));
                }
                $overridden = true;
            }

            // Create ---------------------------------------------------------------------------
            $applicant = $this->workflow->applicantOrFail($instituteId, $enquiryId);
            $standardId = (int) $this->standardOf($applicant);
            $enrollmentNo = $this->enrollmentNumber($instituteId, $standardId, $applicant);
            $studentId = $this->createStudent($instituteId, $syear, $applicant, $enrollmentNo);
            $this->createEnrollment($instituteId, $syear, $termId, $studentId, $standardId, $applicant);

            $from = $wf->confirmation_status;
            $this->markConfirmed($wf, $studentId, $overridden ? trim($overrideReason) : null);
            $this->audit->log($instituteId, $enquiryId, 'confirmation', $enquiryId, $overridden ? 'confirmed_with_override' : 'confirmed', $from, 'confirmed',
                $overridden ? $overrideReason : null, $overridden, $overridden ? ['bypassed' => array_column($check['blocking'], 'key')] : null, ['student_id' => $studentId, 'enrollment_no' => $enrollmentNo]);

            return $this->result('confirmed', true, $studentId, $enrollmentNo);
        });
    }

    /** on_hold | cancelled. A reason is mandatory; a confirmed admission cannot be held or cancelled here. */
    public function setStatus(int $instituteId, int $enquiryId, string $status, string $reason, bool $mayOverride = false): AdmissionWorkflow
    {
        if (! in_array($status, ['on_hold', 'cancelled', 'pending'], true)) {
            throw AdmissionException::because('Unsupported confirmation status.');
        }
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required.');
        }

        return DB::transaction(function () use ($instituteId, $enquiryId, $status, $reason, $mayOverride) {
            $wf = $this->workflow->lockWorkflow($instituteId, $enquiryId);
            if ($wf->confirmation_status === 'confirmed') {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            if ($wf->confirmation_status === $status) {
                return $wf;
            }
            if ($status === 'pending' && $wf->confirmation_status === 'cancelled' && ! $mayOverride) {
                throw AdmissionException::because('Only an administrator can resume a cancelled admission.');
            }
            $from = $wf->confirmation_status;
            $wf->confirmation_status = $status;
            $wf->confirmation_note = mb_substr(trim($reason), 0, 500);
            $wf->save();
            $this->audit->log($instituteId, $enquiryId, 'confirmation', $enquiryId, 'admission_'.$status, $from, $status, $reason);

            return $wf;
        });
    }

    // -- internals (protected so a test can inject a failure into the middle of the transaction) --------

    protected function createStudent(int $instituteId, int $syear, array $a, string $enrollmentNo): int
    {
        $e = $a['enquiry'];
        $f = $a['form'] ?? [];
        $r = $a['registration'] ?? [];
        $pick = fn (...$values) => collect($values)->first(fn ($v) => $v !== null && $v !== '');
        $profile = DB::table('tbluserprofilemaster')->where('name', 'Student')->where('sub_institute_id', $instituteId)->value('id');

        return (int) DB::table('tblstudent')->insertGetId([
            'admission_id'        => (string) $e['id'],
            'first_name'          => $e['first_name'] ?? null,
            'middle_name'         => $e['middle_name'] ?? null,
            'last_name'           => $e['last_name'] ?? null,
            'gender'              => $e['gender'] ?? null,
            'mobile'              => $e['mobile'] ?? null,
            'email'               => $e['email'] ?? null,
            'address'             => $e['address'] ?? null,
            'username'            => $enrollmentNo,
            'user_profile_id'     => $profile,
            'admission_year'      => $syear,
            'since_when'          => $syear,
            'admission_date'      => $r['admission_date'] ?? null,
            'sub_institute_id'    => $instituteId,
            'status'              => '1',
            'place_of_birth'      => $pick($r['place_of_birth'] ?? null, $e['place_of_birth'] ?? null),
            'adharnumber'         => $pick($r['aadhar_number'] ?? null, $e['aadharcard_number'] ?? null),
            'mother_name'         => $pick($r['mother_name'] ?? null, $e['mother_name'] ?? null),
            'mother_mobile'       => $r['mother_mobile_number'] ?? null,
            'father_name'         => $e['father_name'] ?? null,
            'dob'                 => $e['date_of_birth'] ?? null,
            'anuualincome'        => $pick($f['annual_income'] ?? null, $e['annual_income'] ?? null),
            'bloodgroup'          => $r['blood_group'] ?? null,
            'admission_docket_no' => $f['admission_docket_no'] ?? null,
            'registration_no'     => $f['registration_no'] ?? null,
            'enrollment_no'       => $enrollmentNo,
        ]);
    }

    protected function createEnrollment(int $instituteId, int $syear, $termId, int $studentId, int $standardId, array $a): void
    {
        $r = $a['registration'] ?? [];
        $standard = DB::table('standard')->where('id', $standardId)->where('sub_institute_id', $instituteId)->first();

        DB::table('tblstudent_enrollment')->insert([
            'syear'            => $syear,
            'student_id'       => $studentId,
            'grade_id'         => $standard->grade_id ?? null,
            'standard_id'      => $standardId,
            'section_id'       => $r['admission_division'] ?? null,
            'student_quota'    => $r['student_quota'] ?? null,
            'start_date'       => date('Y-m-d'),
            'enrollment_code'  => '1',
            'term_id'          => $termId,
            'admission_fees'   => $r['amount'] ?? null,
            'sub_institute_id' => $instituteId,
        ]);
    }

    /** The number captured on the registration record wins (as before) but must not collide; otherwise generate. */
    protected function enrollmentNumber(int $instituteId, int $standardId, array $applicant): string
    {
        $given = trim((string) ($applicant['registration']['enrollment_no'] ?? ''));
        if ($given !== '') {
            if (DB::table('tblstudent')->where('sub_institute_id', $instituteId)->where('enrollment_no', $given)->exists()) {
                throw AdmissionException::because("Enrollment number {$given} is already used by another student.");
            }

            return $given;
        }

        return $this->numbers->nextEnrollmentNo($instituteId, $standardId ?: null);
    }

    protected function markConfirmed(AdmissionWorkflow $wf, int $studentId, ?string $overrideReason): void
    {
        $wf->confirmation_status = 'confirmed';
        $wf->student_id = $studentId;
        $wf->confirmed_at = now();
        $wf->confirmed_by = session()->get('user_id');
        $wf->confirmation_override = $overrideReason !== null;
        $wf->confirmation_override_reason = $overrideReason !== null ? mb_substr($overrideReason, 0, 500) : null;
        $wf->save();
    }

    protected function standardOf(array $applicant)
    {
        $fromForm = $applicant['form']['admission_standard'] ?? null;

        return $fromForm !== null && $fromForm !== '' ? $fromForm : ($applicant['enquiry']['admission_standard'] ?? null);
    }

    protected function item(string $key, string $label, bool $required, bool $ok, bool $overridable, ?string $detail = null): array
    {
        return ['key' => $key, 'label' => $label, 'required' => $required, 'ok' => (bool) $ok, 'overridable' => $overridable, 'detail' => $detail];
    }

    protected function result(string $status, bool $created, int $studentId, ?string $enrollmentNo = null): array
    {
        $enrollmentNo ??= DB::table('tblstudent')->where('id', $studentId)->value('enrollment_no');

        return ['status' => $status, 'created' => $created, 'student_id' => $studentId, 'enrollment_no' => $enrollmentNo];
    }
}
