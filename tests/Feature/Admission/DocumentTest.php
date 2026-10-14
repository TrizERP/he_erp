<?php

namespace Tests\Feature\Admission;

use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionHistory;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\DocumentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentTest extends AdmissionTestCase
{
    protected DocumentService $docs;
    protected int $enquiry;
    protected AdmissionDocumentRequirement $marksheet;
    protected array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('admission.disk'));
        $this->docs = app(DocumentService::class);
        $this->enquiry = $this->makeEnquiry();
        $this->marksheet = AdmissionDocumentRequirement::create([
            'sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true, 'max_kb' => 4,
        ]);
    }

    /**
     * A real uploaded file (not UploadedFile::fake(), whose getMimeType() only looks at the file NAME), so the
     * service's content sniffing runs exactly as it does for a browser upload.
     */
    protected function upload(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'adm');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }

    protected function pdf(string $name = 'm.pdf', string $extra = ''): UploadedFile
    {
        return $this->upload($name, "%PDF-1.4
%test
".$extra);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    public function test_requirements_are_configurable_per_programme_year_and_category(): void
    {
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Commerce certificate', 'standard_id' => 2, 'status' => true]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Old year doc', 'syear' => 2020, 'status' => true]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Caste certificate', 'category' => 'OBC', 'status' => true]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::OTHER_INST, 'name' => 'Other institute doc', 'status' => true]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Retired', 'status' => false]);

        $names = $this->docs->requirementsFor(self::INST, $this->enquiry)->pluck('name')->all();
        $this->assertSame(['Marksheet'], $names, 'General-category B.Sc applicant, 2026: only the unconditional requirement applies');

        $obc = $this->makeEnquiry(['category' => 'obc', 'admission_standard' => 2]);
        $this->assertEqualsCanonicalizing(['Marksheet', 'Commerce certificate', 'Caste certificate'], $this->docs->requirementsFor(self::INST, $obc)->pluck('name')->all());
    }

    public function test_a_valid_upload_is_stored_privately_with_metadata(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());

        $this->assertSame('uploaded', $doc->status);
        $this->assertSame('m.pdf', $doc->original_filename);
        $this->assertSame('application/pdf', $doc->mime_type);
        $this->assertSame(1, $doc->version);
        $this->assertStringStartsWith('admission/'.self::INST.'/'.$this->enquiry.'/', $doc->storage_path);
        Storage::disk(config('admission.disk'))->assertExists($doc->storage_path);
        $this->assertNotSame('public', config('admission.disk'), 'applicant documents must not live on the public disk');
    }

    public function test_disallowed_extension_is_rejected(): void
    {
        $this->expectException(AdmissionException::class);
        $this->expectExceptionMessage('not allowed');
        $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->upload('run.exe', 'MZ'));
    }

    public function test_content_that_does_not_match_the_extension_is_rejected(): void
    {
        $this->expectException(AdmissionException::class);
        $this->expectExceptionMessage('does not match');
        $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->upload('fake.pdf', '<?php echo 1;'));
    }

    public function test_oversized_file_is_rejected_and_nothing_is_stored(): void
    {
        try {
            $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf('big.pdf', str_repeat('x', 6 * 1024)));
            $this->fail('A 6 KB file must exceed the 4 KB limit.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('4 KB', $e->getMessage());
        }

        $this->assertSame([], Storage::disk(config('admission.disk'))->allFiles());
        $this->assertSame(0, DB::table('admission_document')->where('status', 'uploaded')->count());
    }

    public function test_a_document_type_from_another_institute_cannot_be_used(): void
    {
        $foreign = AdmissionDocumentRequirement::create(['sub_institute_id' => self::OTHER_INST, 'name' => 'X', 'status' => true]);

        $this->expectException(AdmissionException::class);
        $this->docs->upload(self::INST, $this->enquiry, $foreign->id, null, $this->pdf());
    }

    public function test_rejection_requires_a_reason_saves_it_and_records_history(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());

        try {
            $this->docs->reject(self::INST, $doc->id, '', true);
            $this->fail('A reason is mandatory.');
        } catch (AdmissionException $e) {
            $this->assertSame('uploaded', $doc->fresh()->status);
        }

        $rejected = $this->docs->reject(self::INST, $doc->id, 'Photo is blurred', true);

        $this->assertSame('resubmission_required', $rejected->status);
        $this->assertSame('Photo is blurred', $rejected->rejection_reason);
        $this->assertSame(7, (int) $rejected->verified_by, 'who acted is recorded');
        $this->assertNotNull($rejected->verified_at);
        $this->assertSame(1, AdmissionHistory::where('entity_type', 'document')->where('action', 'document_resubmission_required')->count());
    }

    public function test_a_final_rejection_is_distinct_from_a_resubmission_request(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());

        $this->assertSame('rejected', $this->docs->reject(self::INST, $doc->id, 'Not our form', false)->status);
    }

    public function test_resubmission_replaces_the_file_bumps_the_version_and_clears_the_reason(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf('first.pdf'));
        $oldPath = $doc->storage_path;
        $this->docs->reject(self::INST, $doc->id, 'Blurred', true);

        $again = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf('second.pdf'));

        $this->assertSame($doc->id, $again->id, 'one row per requirement');
        $this->assertSame(2, $again->version);
        $this->assertSame('uploaded', $again->status);
        $this->assertNull($again->rejection_reason);
        $this->assertSame('second.pdf', $again->original_filename);
        Storage::disk(config('admission.disk'))->assertMissing($oldPath);
        $resub = AdmissionHistory::where('action', 'document_resubmitted')->first();
        $this->assertSame('first.pdf', $resub->old_values['file'], 'the replaced file name stays in the trail');
    }

    public function test_a_verified_document_cannot_be_silently_replaced(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());
        $this->docs->verify(self::INST, $doc->id);

        $this->expectException(AdmissionException::class);
        $this->expectExceptionMessage('already verified');
        $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf('swap.pdf'));
    }

    public function test_verify_is_idempotent_and_only_works_on_uploaded_documents(): void
    {
        $row = $this->docs->documentsFor(self::INST, $this->enquiry)->first(); // pending
        try {
            $this->docs->verify(self::INST, $row->id);
            $this->fail('A pending document has nothing to verify.');
        } catch (AdmissionException $e) {
            $this->assertSame('pending', $row->fresh()->status);
        }

        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());
        $this->docs->verify(self::INST, $doc->id);
        $this->docs->verify(self::INST, $doc->id);
        $this->assertSame(1, AdmissionHistory::where('action', 'document_verified')->count());
        $this->assertSame([], $this->docs->unverifiedMandatory(self::INST, $this->enquiry));
    }

    public function test_download_lookup_is_scoped_to_the_institute(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());

        $this->assertSame($doc->id, $this->docs->findForDownload(self::INST, $doc->id)->id);
        $this->expectException(ModelNotFoundException::class);
        $this->docs->findForDownload(self::OTHER_INST, $doc->id);
    }

    public function test_documents_are_locked_once_the_admission_is_confirmed(): void
    {
        $doc = $this->docs->upload(self::INST, $this->enquiry, $this->marksheet->id, null, $this->pdf());
        app(\App\Services\Admission\WorkflowService::class)->ensure(self::INST, $this->enquiry);
        DB::table('admission_workflow')->where('enquiry_id', $this->enquiry)->update(['confirmation_status' => 'confirmed']);

        $this->expectException(AdmissionException::class);
        $this->docs->verify(self::INST, $doc->id);
    }
}
