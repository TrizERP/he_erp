<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionDocument;
use App\Models\admission\AdmissionDocumentRequirement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Required documents are configured per institute (admission_document_requirement), optionally narrowed by
 * standard, academic year and applicant category. Each document is tracked independently and files live on a
 * private disk, only reachable through an authorised controller action.
 */
class DocumentService
{
    /** Real content types accepted per extension (the client-sent mime type is never trusted on its own). */
    protected const MIME_BY_EXT = [
        'pdf'  => ['application/pdf'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'doc'  => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
    ];

    public function __construct(protected AuditService $audit)
    {
    }

    /** Requirements that apply to this applicant. */
    public function requirementsFor(int $instituteId, int $enquiryId): \Illuminate\Support\Collection
    {
        $enquiry = DB::table('admission_enquiry')->where('id', $enquiryId)->where('sub_institute_id', $instituteId)->first();
        if (! $enquiry) {
            throw (new ModelNotFoundException())->setModel('admission_enquiry', [$enquiryId]);
        }
        $form = DB::table('admission_form')->where('enquiry_id', (string) $enquiryId)->where('sub_institute_id', (string) $instituteId)->orderBy('id')->first();
        $standard = ($form->admission_standard ?? '') !== '' ? $form->admission_standard : $enquiry->admission_standard;

        return AdmissionDocumentRequirement::where('sub_institute_id', $instituteId)->where('status', true)
            ->where(fn ($q) => $q->whereNull('standard_id')->orWhere('standard_id', $standard))
            ->where(fn ($q) => $q->whereNull('syear')->orWhere('syear', $enquiry->syear))
            ->where(fn ($q) => $q->whereNull('category')->orWhereRaw('LOWER(category) = ?', [strtolower((string) $enquiry->category)]))
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    /** Make sure each applicable requirement has a (pending) document row, then return all documents. */
    public function documentsFor(int $instituteId, int $enquiryId): \Illuminate\Support\Collection
    {
        foreach ($this->requirementsFor($instituteId, $enquiryId) as $requirement) {
            AdmissionDocument::firstOrCreate(
                ['enquiry_id' => $enquiryId, 'requirement_id' => $requirement->id],
                ['sub_institute_id' => $instituteId, 'doc_name' => $requirement->name]
            );
        }

        return AdmissionDocument::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->orderBy('id')->get();
    }

    /** Names of mandatory requirements with no uploaded file yet. */
    public function missingMandatory(int $instituteId, int $enquiryId): array
    {
        $docs = AdmissionDocument::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->get()->keyBy('requirement_id');
        $missing = [];
        foreach ($this->requirementsFor($instituteId, $enquiryId)->where('is_mandatory', true) as $requirement) {
            $doc = $docs->get($requirement->id);
            if (! $doc || ! in_array($doc->status, ['uploaded', 'verified'], true)) {
                $missing[] = $requirement->name;
            }
        }

        return $missing;
    }

    /** Names of mandatory requirements that are not verified (used by the confirmation checklist). */
    public function unverifiedMandatory(int $instituteId, int $enquiryId): array
    {
        $docs = AdmissionDocument::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->get()->keyBy('requirement_id');
        $out = [];
        foreach ($this->requirementsFor($instituteId, $enquiryId)->where('is_mandatory', true) as $requirement) {
            if (($docs->get($requirement->id)->status ?? null) !== 'verified') {
                $out[] = $requirement->name;
            }
        }

        return $out;
    }

    /**
     * Store an uploaded file against a requirement (or as an ad-hoc document when $requirementId is null).
     * A document that was rejected / sent back for resubmission is replaced in place and its version bumped;
     * the previous file name is kept in the audit trail.
     */
    public function upload(int $instituteId, int $enquiryId, ?int $requirementId, ?string $adHocName, UploadedFile $file): AdmissionDocument
    {
        $requirement = null;
        if ($requirementId) {
            $requirement = AdmissionDocumentRequirement::where('sub_institute_id', $instituteId)->where('id', $requirementId)->first();
            if (! $requirement || ! $this->requirementsFor($instituteId, $enquiryId)->contains('id', $requirement->id)) {
                throw AdmissionException::because('That document type does not apply to this application.');
            }
        } elseif (trim((string) $adHocName) === '') {
            throw AdmissionException::because('Choose a document type or give the document a name.');
        }

        $this->assertFileAllowed($file, $requirement);

        return DB::transaction(function () use ($instituteId, $enquiryId, $requirement, $adHocName, $file) {
            $this->assertNotLocked($instituteId, $enquiryId);

            $document = $requirement
                ? AdmissionDocument::where('enquiry_id', $enquiryId)->where('requirement_id', $requirement->id)->lockForUpdate()->first()
                : null;
            $document ??= new AdmissionDocument([
                'sub_institute_id' => $instituteId, 'enquiry_id' => $enquiryId,
                'requirement_id' => $requirement?->id, 'doc_name' => $requirement?->name ?? Str::limit(trim($adHocName), 120, ''),
            ]);

            if ($document->exists && $document->status === 'verified') {
                throw AdmissionException::because('This document is already verified. Ask a verifier to send it back before uploading a replacement.');
            }

            $oldFile = $document->original_filename;
            $oldPath = $document->storage_path;
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs("admission/{$instituteId}/{$enquiryId}", Str::uuid().'.'.$ext, config('admission.disk'));

            $from = $document->status;
            $document->forceFill([
                'storage_path' => $path, 'original_filename' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(),
                'version' => ((int) $document->version) + 1, 'status' => 'uploaded', 'rejection_reason' => null,
                'uploaded_by' => session()->get('user_id'), 'verified_by' => null, 'verified_at' => null,
            ])->save();

            if ($oldPath) {
                DB::afterCommit(fn () => Storage::disk(config('admission.disk'))->delete($oldPath));
            }
            $this->audit->log($instituteId, $enquiryId, 'document', $document->id, $document->version > 1 ? 'document_resubmitted' : 'document_uploaded',
                $from, 'uploaded', null, false, $oldFile ? ['file' => $oldFile] : null, ['file' => $document->original_filename, 'version' => $document->version]);

            return $document;
        });
    }

    public function verify(int $instituteId, int $documentId): AdmissionDocument
    {
        return DB::transaction(function () use ($instituteId, $documentId) {
            $document = $this->lockDocument($instituteId, $documentId);
            if ($document->status === 'verified') {
                return $document; // idempotent
            }
            if ($document->status !== 'uploaded') {
                throw AdmissionException::because('Only an uploaded document can be verified.');
            }
            $document->forceFill(['status' => 'verified', 'verified_by' => session()->get('user_id'), 'verified_at' => now(), 'rejection_reason' => null])->save();
            $this->audit->log($instituteId, $document->enquiry_id, 'document', $document->id, 'document_verified', 'uploaded', 'verified');

            return $document;
        });
    }

    /** $resubmit = true asks the applicant to upload again ("resubmission_required"); false is a final "rejected". */
    public function reject(int $instituteId, int $documentId, string $reason, bool $resubmit = true): AdmissionDocument
    {
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required to reject a document.');
        }

        return DB::transaction(function () use ($instituteId, $documentId, $reason, $resubmit) {
            $document = $this->lockDocument($instituteId, $documentId);
            if (! in_array($document->status, ['uploaded', 'verified'], true)) {
                throw AdmissionException::because('Only an uploaded or verified document can be rejected.');
            }
            $to = $resubmit ? 'resubmission_required' : 'rejected';
            $from = $document->status;
            $document->forceFill(['status' => $to, 'rejection_reason' => Str::limit(trim($reason), 500, ''), 'verified_by' => session()->get('user_id'), 'verified_at' => now()])->save();
            $this->audit->log($instituteId, $document->enquiry_id, 'document', $document->id, 'document_'.$to, $from, $to, $reason);

            return $document;
        });
    }

    /** Authorised, scoped read access for the controller's download / preview action. */
    public function findForDownload(int $instituteId, int $documentId): AdmissionDocument
    {
        $document = AdmissionDocument::where('sub_institute_id', $instituteId)->where('id', $documentId)->firstOrFail();
        if (! $document->storage_path || ! Storage::disk(config('admission.disk'))->exists($document->storage_path)) {
            throw (new ModelNotFoundException())->setModel(AdmissionDocument::class, [$documentId]);
        }

        return $document;
    }

    protected function lockDocument(int $instituteId, int $documentId): AdmissionDocument
    {
        $document = AdmissionDocument::where('sub_institute_id', $instituteId)->where('id', $documentId)->lockForUpdate()->firstOrFail();
        $this->assertNotLocked($instituteId, (int) $document->enquiry_id);

        return $document;
    }

    protected function assertNotLocked(int $instituteId, int $enquiryId): void
    {
        $confirmed = DB::table('admission_workflow')->where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->where('confirmation_status', 'confirmed')->exists();
        if ($confirmed) {
            throw AdmissionException::because('The admission is already confirmed; documents can no longer change.');
        }
    }

    /** Extension allow-list + real content sniffing + size ceiling. */
    protected function assertFileAllowed(UploadedFile $file, ?AdmissionDocumentRequirement $requirement): void
    {
        if (! $file->isValid()) {
            throw AdmissionException::because('The file could not be uploaded. Please try again.');
        }

        $global = config('admission.upload.allowed_mimes');
        $configured = array_filter(array_map('trim', explode(',', strtolower($requirement?->allowed_mimes ?: config('admission.upload.default_mimes')))));
        $allowed = array_values(array_intersect($configured, $global));
        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, $allowed, true)) {
            throw AdmissionException::because('File type .'.($ext ?: '?').' is not allowed. Allowed: '.implode(', ', $allowed).'.');
        }

        $detected = $file->getMimeType();
        if (! in_array($detected, self::MIME_BY_EXT[$ext] ?? [], true)) {
            throw AdmissionException::because('The file content does not match its .'.$ext.' extension.');
        }

        $maxKb = min((int) ($requirement?->max_kb ?: config('admission.upload.default_max_kb')), (int) config('admission.upload.hard_max_kb'));
        if ($file->getSize() > $maxKb * 1024) {
            throw AdmissionException::because("The file is larger than the {$maxKb} KB limit.");
        }
    }
}
