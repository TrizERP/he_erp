<?php

namespace Tests\Feature\Admission;

use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionHistory;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\DocumentService;
use App\Services\Admission\WorkflowService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkflowTest extends AdmissionTestCase
{
    protected WorkflowService $workflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = app(WorkflowService::class);
    }

    protected function realPdf(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'adm');
        file_put_contents($path, "%PDF-1.4
%test
");

        return new UploadedFile($path, 'marks.pdf', null, null, true);
    }

    public function test_convert_creates_one_application_form_and_is_idempotent(): void
    {
        $id = $this->makeEnquiry();

        $first = $this->workflow->convert(self::INST, $id);
        $second = $this->workflow->convert(self::INST, $id);

        $this->assertTrue($first['created']);
        $this->assertFalse($second['created']);
        $this->assertSame($first['form_no'], $second['form_no']);
        $this->assertSame(1, DB::table('admission_form')->where('enquiry_id', (string) $id)->count());
        $this->assertSame('converted', $this->workflow->ensure(self::INST, $id)->inquiry_status);
        $this->assertSame('draft', $this->workflow->ensure(self::INST, $id)->application_status);
    }

    public function test_application_numbers_are_generated_by_the_backend_and_unique(): void
    {
        $a = $this->makeEnquiry();
        $b = $this->makeEnquiry();

        $formA = $this->workflow->convert(self::INST, $a)['form_no'];
        $formB = $this->workflow->convert(self::INST, $b)['form_no'];

        $this->assertNotSame($formA, $formB);
        $this->assertSame((string) ((int) $formA + 1), $formB);
    }

    public function test_submission_is_blocked_with_a_clear_checklist_when_fields_are_missing(): void
    {
        $id = $this->makeEnquiry(['mobile' => '', 'admission_standard' => null]);
        $this->workflow->convert(self::INST, $id);

        try {
            $this->workflow->submit(self::INST, $id);
            $this->fail('Submission should have been blocked.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('incomplete', $e->getMessage());
            $this->assertContains('Mobile number is missing', $e->details);
            $this->assertContains('Admission standard / programme is missing', $e->details);
        }

        $this->assertSame('draft', $this->workflow->ensure(self::INST, $id)->application_status);
    }

    public function test_submit_succeeds_once_and_a_double_click_adds_no_second_history_row(): void
    {
        $id = $this->makeEnquiry();
        $this->workflow->convert(self::INST, $id);

        $this->workflow->submit(self::INST, $id);
        $this->workflow->submit(self::INST, $id);

        $this->assertSame('submitted', $this->workflow->ensure(self::INST, $id)->application_status);
        $this->assertSame(1, AdmissionHistory::where('enquiry_id', $id)->where('action', 'submitted')->count());
    }

    public function test_a_missing_mandatory_document_blocks_submission_until_uploaded(): void
    {
        Storage::fake(config('admission.disk'));
        $req = AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true]);
        $id = $this->makeEnquiry();
        $this->workflow->convert(self::INST, $id);

        $this->assertContains('Required document not uploaded: Marksheet', $this->workflow->submissionGaps(self::INST, $id));

        app(DocumentService::class)->upload(self::INST, $id, $req->id, null, UploadedFile::fake()->createWithContent('marks.pdf', "%PDF-1.4\n%test\n"));

        $this->assertSame([], $this->workflow->submissionGaps(self::INST, $id));
        $this->assertSame('submitted', $this->workflow->submit(self::INST, $id)->application_status);
    }

    public function test_reopen_needs_a_reason_and_returns_the_application_to_an_editable_state(): void
    {
        $id = $this->makeEnquiry();
        $this->workflow->convert(self::INST, $id);
        $this->workflow->submit(self::INST, $id);

        try {
            $this->workflow->reopen(self::INST, $id, '  ');
            $this->fail('A blank reason must be rejected.');
        } catch (AdmissionException $e) {
            $this->assertSame('submitted', $this->workflow->ensure(self::INST, $id)->application_status);
        }

        $wf = $this->workflow->reopen(self::INST, $id, 'Wrong date of birth');
        $this->assertSame('reopened', $wf->application_status);
        $this->assertSame(1, AdmissionHistory::where('enquiry_id', $id)->where('action', 'reopened')->count());
    }

    public function test_records_of_another_institute_are_not_reachable(): void
    {
        $id = $this->makeEnquiry(['sub_institute_id' => self::OTHER_INST]);

        $this->assertNull($this->workflow->applicant(self::INST, $id));
        $this->expectException(ModelNotFoundException::class);
        $this->workflow->convert(self::INST, $id);
    }

    public function test_cross_institute_convert_leaves_no_form_or_workflow_behind(): void
    {
        $id = $this->makeEnquiry(['sub_institute_id' => self::OTHER_INST]);

        try {
            $this->workflow->convert(self::INST, $id);
        } catch (ModelNotFoundException $e) {
            // expected
        }

        $this->assertSame(0, DB::table('admission_form')->count());
        $this->assertSame(0, DB::table('admission_workflow')->count());
    }

    public function test_saving_a_form_on_the_legacy_screen_counts_as_registration_and_never_touches_a_confirmed_application(): void
    {
        $id = $this->makeEnquiry();
        $this->workflow->ensure(self::INST, $id); // draft: no form yet
        $this->makeForm($id);

        WorkflowService::markLegacyRegistered(self::INST, $id);
        $this->assertSame('submitted', $this->workflow->ensure(self::INST, $id)->application_status);

        DB::table('admission_workflow')->where('enquiry_id', $id)->update(['application_status' => 'reopened']);
        WorkflowService::markLegacyRegistered(self::INST, $id);
        $this->assertSame('reopened', $this->workflow->ensure(self::INST, $id)->application_status, 'only a draft is auto-submitted');
    }

    public function test_a_legacy_form_that_already_exists_is_treated_as_submitted_and_a_legacy_student_as_confirmed(): void
    {
        $id = $this->makeEnquiry();
        $this->makeForm($id);
        $studentId = DB::table('tblstudent')->insertGetId(['admission_id' => (string) $id, 'sub_institute_id' => self::INST, 'enrollment_no' => '77']);

        $wf = $this->workflow->ensure(self::INST, $id);

        $this->assertSame('submitted', $wf->application_status);
        $this->assertSame('confirmed', $wf->confirmation_status);
        $this->assertSame($studentId, (int) $wf->student_id);
    }
}
