<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionWorkflow;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inquiry -> application life cycle on top of the legacy admission_enquiry / admission_form tables.
 * Every method takes the institute id explicitly and never touches a row outside that institute.
 */
class WorkflowService
{
    public function __construct(
        protected AuditService $audit,
        protected NumberService $numbers,
        protected DocumentService $documents
    ) {
    }

    /** Legacy rows for one enquiry (enquiry + first form + first registration), or null if not in this institute. */
    public function applicant(int $instituteId, int $enquiryId): ?array
    {
        $enquiry = DB::table('admission_enquiry')->where('id', $enquiryId)->where('sub_institute_id', $instituteId)->first();
        if (! $enquiry) {
            return null;
        }
        $form = DB::table('admission_form')->where('enquiry_id', (string) $enquiryId)->where('sub_institute_id', (string) $instituteId)->orderBy('id')->first();
        $registration = DB::table('admission_registration')->where('enquiry_id', $enquiryId)->where('sub_institute_id', $instituteId)->orderBy('id')->first();

        return [
            'enquiry'      => (array) $enquiry,
            'form'         => $form ? (array) $form : null,
            'registration' => $registration ? (array) $registration : null,
        ];
    }

    /** Applicant data or 404. Use this at the top of every record-level action (institute scoping). */
    public function applicantOrFail(int $instituteId, int $enquiryId): array
    {
        return $this->applicant($instituteId, $enquiryId) ?? throw (new ModelNotFoundException())->setModel('admission_enquiry', [$enquiryId]);
    }

    /** The workflow row for an enquiry, created on first touch. 404 for an enquiry outside the institute. */
    public function ensure(int $instituteId, int $enquiryId): AdmissionWorkflow
    {
        $existing = AdmissionWorkflow::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->first();
        if ($existing) {
            return $existing;
        }

        $applicant = $this->applicantOrFail($instituteId, $enquiryId);
        $standard = $applicant['form']['admission_standard'] ?? null;
        $standard = $standard !== null && $standard !== '' ? $standard : ($applicant['enquiry']['admission_standard'] ?? null);

        $workflow = new AdmissionWorkflow(['sub_institute_id' => $instituteId, 'enquiry_id' => $enquiryId, 'syear' => $applicant['enquiry']['syear'] ?? null, 'standard_id' => $standard]);
        // A form that already exists predates the draft/submit concept: treat it as submitted.
        $workflow->application_status = $applicant['form'] ? 'submitted' : 'draft';
        $workflow->inquiry_status = $applicant['form'] ? 'converted' : 'new';

        $student = DB::table('tblstudent')->where('sub_institute_id', $instituteId)->where('admission_id', (string) $enquiryId)->orderBy('id')->first();
        if ($student && ! AdmissionWorkflow::where('student_id', $student->id)->exists()) {
            $workflow->student_id = $student->id;
            $workflow->confirmation_status = 'confirmed';
        }

        try {
            $workflow->save();
        } catch (QueryException $e) {
            // lost a race with another request creating the same row: use theirs
            $workflow = AdmissionWorkflow::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->firstOrFail();
        }

        return $workflow;
    }

    /**
     * Materialise workflow rows for every enquiry of the institute that has none, so dashboard counts and
     * drill-down lists run over one table. Idempotent; the unique index makes concurrent runs safe.
     */
    public function syncMissing(int $instituteId): void
    {
        $missing = DB::table('admission_enquiry as ae')
            ->where('ae.sub_institute_id', $instituteId)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('admission_workflow as w')
                    ->whereColumn('w.enquiry_id', 'ae.id')->whereColumn('w.sub_institute_id', 'ae.sub_institute_id');
            })->pluck('ae.id');

        foreach ($missing as $enquiryId) {
            $this->ensure($instituteId, (int) $enquiryId);
        }
    }

    /** Create the application form for an enquiry. Idempotent: a second call returns the existing form. */
    public function convert(int $instituteId, int $enquiryId): array
    {
        return DB::transaction(function () use ($instituteId, $enquiryId) {
            $this->applicantOrFail($instituteId, $enquiryId);
            $this->numbers->lock($instituteId);
            $workflow = AdmissionWorkflow::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->lockForUpdate()->first() ?? $this->ensure($instituteId, $enquiryId);
            $applicant = $this->applicantOrFail($instituteId, $enquiryId);

            if ($applicant['form']) {
                return ['created' => false, 'form_no' => $applicant['form']['form_no']];
            }
            if ($workflow->inquiry_status === 'closed') {
                throw AdmissionException::because('This inquiry is closed. Reopen it before converting.');
            }

            $formNo = $this->numbers->nextFormNo($instituteId);
            DB::table('admission_form')->insert([
                'enquiry_id'       => (string) $enquiryId,
                'enquiry_no'       => (string) ($applicant['enquiry']['enquiry_no'] ?? ''),
                'form_no'          => $formNo,
                'admission_standard' => (string) ($applicant['enquiry']['admission_standard'] ?? ''),
                'sub_institute_id' => (string) $instituteId,
                'created_by'       => (string) session()->get('user_id'),
            ]);

            $from = $workflow->inquiry_status;
            $workflow->inquiry_status = 'converted';
            $workflow->application_status = 'draft';
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'inquiry', $enquiryId, 'converted_to_application', $from, 'converted', "Application form no. {$formNo}");

            return ['created' => true, 'form_no' => $formNo];
        });
    }

    public function setInquiryStatus(int $instituteId, int $enquiryId, string $status, ?string $note = null): AdmissionWorkflow
    {
        if (! in_array($status, ['contacted', 'follow_up', 'closed', 'new'], true)) {
            throw AdmissionException::because('That inquiry status cannot be set manually.');
        }

        return DB::transaction(function () use ($instituteId, $enquiryId, $status, $note) {
            $workflow = $this->lockWorkflow($instituteId, $enquiryId);
            if ($workflow->confirmation_status === 'confirmed') {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            if ($workflow->inquiry_status === $status) {
                return $workflow; // idempotent
            }
            $from = $workflow->inquiry_status;
            $workflow->inquiry_status = $status;
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'inquiry', $enquiryId, 'inquiry_status', $from, $status, $note);

            return $workflow;
        });
    }

    /**
     * Fields that must be complete before submission, as a list of human-readable gaps (empty = ready).
     * Minimum identity data only; document gaps come from the institute's configured requirements.
     */
    public function submissionGaps(int $instituteId, int $enquiryId): array
    {
        $applicant = $this->applicantOrFail($instituteId, $enquiryId);
        $gaps = [];

        if (! $applicant['form']) {
            $gaps[] = 'Application form has not been created (convert the inquiry first)';
        }
        $e = $applicant['enquiry'];
        foreach (['first_name' => 'Applicant first name', 'mobile' => 'Mobile number'] as $field => $label) {
            if (trim((string) ($e[$field] ?? '')) === '') {
                $gaps[] = $label.' is missing';
            }
        }
        $standard = $applicant['form']['admission_standard'] ?? null;
        if (trim((string) ($standard ?: ($e['admission_standard'] ?? ''))) === '') {
            $gaps[] = 'Admission standard / programme is missing';
        }
        foreach ($this->documents->missingMandatory($instituteId, $enquiryId) as $name) {
            $gaps[] = "Required document not uploaded: {$name}";
        }

        return $gaps;
    }

    public function submit(int $instituteId, int $enquiryId): AdmissionWorkflow
    {
        return DB::transaction(function () use ($instituteId, $enquiryId) {
            $workflow = $this->lockWorkflow($instituteId, $enquiryId);

            if ($workflow->application_status === 'submitted') {
                return $workflow; // double click / retry: no second transition, no second history row
            }
            if ($workflow->confirmation_status === 'confirmed') {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            $gaps = $this->submissionGaps($instituteId, $enquiryId);
            if ($gaps) {
                throw AdmissionException::because('The application is incomplete.', $gaps);
            }

            $from = $workflow->application_status;
            $workflow->application_status = 'submitted';
            $workflow->submitted_at = now();
            $workflow->submitted_by = session()->get('user_id');
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'application', $enquiryId, 'submitted', $from, 'submitted');

            return $workflow;
        });
    }

    /** Authorised correction path: a submitted application goes back to editable, with a mandatory reason. */
    public function reopen(int $instituteId, int $enquiryId, string $reason): AdmissionWorkflow
    {
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required to reopen an application.');
        }

        return DB::transaction(function () use ($instituteId, $enquiryId, $reason) {
            $workflow = $this->lockWorkflow($instituteId, $enquiryId);
            if ($workflow->confirmation_status === 'confirmed') {
                throw AdmissionException::because('A confirmed admission cannot be reopened.');
            }
            if ($workflow->application_status !== 'submitted') {
                return $workflow;
            }
            $workflow->application_status = 'reopened';
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'application', $enquiryId, 'reopened', 'submitted', 'reopened', $reason);

            return $workflow;
        });
    }

    /**
     * Called by the legacy application-form screen after it saves a form. On that screen "form saved" has always
     * meant "application registered", so a draft becomes submitted. Failures are reported but never allowed to
     * break the legacy save (e.g. when the workflow migrations have not been run yet).
     */
    public static function markLegacyRegistered(int $instituteId, int $enquiryId): void
    {
        try {
            if (! Schema::hasTable('admission_workflow')) {
                return;
            }
            app(self::class)->registerLegacy($instituteId, $enquiryId);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function registerLegacy(int $instituteId, int $enquiryId): void
    {
        if (! $this->applicant($instituteId, $enquiryId)) {
            return;
        }

        DB::transaction(function () use ($instituteId, $enquiryId) {
            $workflow = $this->lockWorkflow($instituteId, $enquiryId);
            if ($workflow->application_status !== 'draft') {
                return; // already submitted / reopened / confirmed: leave the workflow alone
            }
            $workflow->application_status = 'submitted';
            $workflow->inquiry_status = 'converted';
            $workflow->submitted_at = now();
            $workflow->submitted_by = session()->get('user_id');
            $workflow->save();
            $this->audit->log($instituteId, $enquiryId, 'application', $enquiryId, 'registered_via_form', 'draft', 'submitted', 'Saved on the application form screen');
        });
    }

    /** Locked workflow row; must be called inside a transaction. */
    public function lockWorkflow(int $instituteId, int $enquiryId): AdmissionWorkflow
    {
        $this->ensure($instituteId, $enquiryId);

        return AdmissionWorkflow::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->lockForUpdate()->firstOrFail();
    }

    public function isLocked(AdmissionWorkflow $workflow): bool
    {
        return $workflow->confirmation_status === 'confirmed';
    }
}
