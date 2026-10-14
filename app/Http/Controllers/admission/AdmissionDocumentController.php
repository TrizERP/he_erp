<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\DocumentRejectRequest;
use App\Http\Requests\admission\DocumentUploadRequest;
use App\Models\admission\AdmissionDocument;
use App\Services\Admission\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Document verification queue, upload, verify / reject and the secure (never public) download. */
class AdmissionDocumentController extends AdmissionController
{
    public function __construct(protected DocumentService $documents)
    {
    }

    public function index(Request $request)
    {
        $inst = $this->instituteId();
        $status = $request->input('status', 'uploaded');

        $rows = DB::table('admission_document as d')
            ->join('admission_enquiry as ae', function ($j) {
                $j->on('ae.id', '=', 'd.enquiry_id')->on('ae.sub_institute_id', '=', 'd.sub_institute_id');
            })
            ->where('d.sub_institute_id', $inst)
            ->when($this->syear(), fn ($q, $y) => $q->where('ae.syear', $y))
            ->when($status !== 'all' && array_key_exists($status, AdmissionDocument::STATUSES), fn ($q) => $q->where('d.status', $status))
            ->select('d.*', 'ae.first_name', 'ae.last_name', 'ae.enquiry_no')
            ->orderBy('d.updated_at')->paginate(25)->withQueryString();

        return view('admission.process.documents', ['data' => ['rows' => $rows, 'status' => $status, 'statuses' => AdmissionDocument::STATUSES]]);
    }

    public function upload(DocumentUploadRequest $request, int $enquiry)
    {
        return $this->attempt(
            fn () => $this->documents->upload($this->instituteId(), $enquiry, $request->integer('requirement_id') ?: null, $request->input('doc_name'), $request->file('file')),
            'Document uploaded.'
        );
    }

    public function verify(int $document)
    {
        return $this->attempt(fn () => $this->documents->verify($this->instituteId(), $document), 'Document verified.');
    }

    public function reject(DocumentRejectRequest $request, int $document)
    {
        return $this->attempt(
            fn () => $this->documents->reject($this->instituteId(), $document, $request->input('reason'), $request->input('mode') === 'resubmit'),
            $request->input('mode') === 'resubmit' ? 'Resubmission requested.' : 'Document rejected.'
        );
    }

    /** Streams the private file after the institute scope check. nosniff + attachment for non-images. */
    public function download(int $document)
    {
        $doc = $this->documents->findForDownload($this->instituteId(), $document);
        $inline = in_array($doc->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true);
        $name = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) $doc->original_filename);

        return Storage::disk(config('admission.disk'))->response($doc->storage_path, $name, [
            'Content-Type'           => $doc->mime_type,
            'Content-Disposition'    => ($inline ? 'inline' : 'attachment').'; filename="'.$name.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }
}
