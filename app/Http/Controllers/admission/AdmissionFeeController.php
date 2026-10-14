<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\ReasonRequest;
use App\Services\Admission\ApplicationQuery;
use App\Services\Admission\FeeService;
use App\Services\Admission\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admission fee view. Amounts and payment status are read from the existing fee records
 * (see FeeService); this controller exposes no way to mark anything paid.
 */
class AdmissionFeeController extends AdmissionController
{
    public function __construct(protected FeeService $fees, protected WorkflowService $workflow, protected ApplicationQuery $queries)
    {
    }

    public function index(Request $request)
    {
        $inst = $this->instituteId();
        $syear = (int) ($this->syear() ?? 0);
        $this->workflow->syncMissing($inst);

        $page = $this->queries->listColumns($this->queries->base($inst, $this->syear()))
            ->selectRaw("(case when ae.receipt_html is not null and ae.receipt_html <> '' then 1 else 0 end) as has_enquiry_receipt")
            ->selectRaw("(select count(*) from admission_form afr where afr.enquiry_id = ae.id and afr.receipt_html is not null and afr.receipt_html <> '') as has_form_receipt")
            ->whereExists(function ($f) {
                $f->selectRaw('1')->from('admission_form as af')->whereColumn('af.enquiry_id', 'ae.id');
            })
            ->when($request->input('q'), function ($q, $term) {
                $like = '%'.trim($term).'%';
                $q->where(fn ($w) => $w->where('ae.first_name', 'like', $like)->orWhere('ae.last_name', 'like', $like)->orWhere('ae.enquiry_no', 'like', $like));
            })
            ->orderByDesc('ae.id')->paginate(25)->withQueryString();

        $summaries = [];
        foreach ($page as $row) {
            $summaries[$row->id] = $this->fees->summary($inst, (int) $row->id, $syear);
        }

        return view('admission.process.fees', ['data' => ['rows' => $page, 'fees' => $summaries, 'q' => $request->input('q')]]);
    }

    /**
     * Reprint a fee receipt that the existing admission screens already generated and stored
     * (admission_enquiry.receipt_html = inquiry fee, admission_form.receipt_html = application form fee).
     * Scoped to the institute; no new receipt is created here.
     */
    public function receipt(int $enquiry, string $kind)
    {
        abort_unless(in_array($kind, ['enquiry', 'form', 'registration'], true), 404);
        $inst = $this->instituteId();
        $applicant = $this->workflow->applicantOrFail($inst, $enquiry);
        $name = trim(($applicant['enquiry']['first_name'] ?? '').' '.($applicant['enquiry']['last_name'] ?? ''));

        if ($kind === 'registration') {
            // Printable copy of the payment recorded on the admission registration record. It does not consume the
            // fee-book receipt numbers: its reference is derived from the registration record id.
            $fee = $this->fees->summary($inst, $enquiry, (int) ($this->syear() ?? 0));
            abort_if($fee['paid'] <= 0, 404, 'No admission fee payment has been recorded for this applicant.');
            $html = view('admission.process.receipt_registration', [
                'a' => $applicant, 'fee' => $fee, 'header' => $this->receiptHeader($inst), 'name' => $name,
                'standard' => DB::table('standard')->where('id', $this->workflow->ensure($inst, $enquiry)->standard_id)->where('sub_institute_id', $inst)->value('name'),
            ])->render();
            $title = 'Admission fee receipt - '.$name;
        } else {
            $html = $kind === 'form' ? ($applicant['form']['receipt_html'] ?? null) : ($applicant['enquiry']['receipt_html'] ?? null);
            abort_if(trim((string) $html) === '', 404, 'No receipt has been generated for this fee.');
            $title = ($kind === 'form' ? 'Application form fee' : 'Inquiry fee').' receipt - '.$name;
        }

        $nonce = \Illuminate\Support\Str::random(24);

        // Stored legacy HTML is rendered but never allowed to run scripts.
        return response()->view('admission.process.receipt', [
            'html' => $html, 'nonce' => $nonce, 'title' => $title,
        ])->withHeaders([
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src * data:; font-src * data:; script-src 'nonce-{$nonce}'",
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, no-store',
        ]);
    }

    /** Letterhead lines / logo from the institute's existing fee receipt book (same source the legacy receipts use). */
    protected function receiptHeader(int $inst): array
    {
        $header = ['lines' => [], 'logo' => null];
        try {
            if (Schema::hasTable('fees_receipt_book_master')) {
                $book = DB::table('fees_receipt_book_master')->where('sub_institute_id', $inst)
                    ->when($this->syear(), fn ($q, $y) => $q->where('syear', $y))->first()
                    ?? DB::table('fees_receipt_book_master')->where('sub_institute_id', $inst)->first();
                if ($book) {
                    foreach (['receipt_line_1', 'receipt_line_2', 'receipt_line_3', 'receipt_line_4'] as $line) {
                        if (! empty($book->$line)) {
                            $header['lines'][] = $book->$line;
                        }
                    }
                    $header['logo'] = ! empty($book->receipt_logo) ? '/storage/fees/'.$book->receipt_logo : null;
                }
            }
        } catch (\Throwable $e) {
            report($e); // a missing letterhead must never block printing a receipt
        }

        return $header;
    }

    public function waive(ReasonRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->fees->waive($this->instituteId(), $enquiry, $request->input('reason')), 'Admission fee waived and audited.');
    }
}
