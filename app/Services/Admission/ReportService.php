<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;

/**
 * Admission reports. Each returns ['title','headings','rows'] so the same data feeds the screen and the
 * Excel export, and both are scoped to the institute (and academic year) of the logged-in user.
 */
class ReportService
{
    public const TYPES = [
        'inquiry_funnel'      => 'Inquiry funnel by source',
        'applications_status' => 'Applications by status and programme',
        'pending_documents'   => 'Pending / rejected documents',
        'eligibility'         => 'Eligibility and selection',
        'offers'              => 'Offers issued, accepted, declined, expired',
        'fees'                => 'Admission fee due / paid / pending',
        'confirmed'           => 'Confirmed admissions and student IDs',
    ];

    public function __construct(protected ApplicationQuery $queries, protected WorkflowService $workflow, protected OfferService $offers, protected FeeService $fees)
    {
    }

    public function build(string $type, int $instituteId, ?int $syear, array $filters = []): array
    {
        if (! array_key_exists($type, self::TYPES)) {
            throw AdmissionException::because('Unknown report.');
        }
        $this->workflow->syncMissing($instituteId);
        $this->offers->expireDue($instituteId);

        $report = $this->{'report'.str_replace('_', '', ucwords($type, '_'))}($instituteId, $syear, $filters);
        $report['title'] = self::TYPES[$type];

        return $report;
    }

    /** Base scoped query + optional programme / date-range filters. */
    protected function scoped(int $instituteId, ?int $syear, array $f)
    {
        return $this->queries->base($instituteId, $syear)
            ->when(! empty($f['standard_id']), fn ($q) => $q->where('w.standard_id', (int) $f['standard_id']))
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('ae.created_on', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('ae.created_on', '<=', $f['to']));
    }

    protected function name($r): string
    {
        return trim(implode(' ', array_filter([$r->first_name ?? '', $r->middle_name ?? '', $r->last_name ?? ''])));
    }

    protected function reportInquiryFunnel($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)->groupBy('ae.source_of_enquiry')->selectRaw(
            "COALESCE(NULLIF(ae.source_of_enquiry, ''), 'Unknown') as source, COUNT(*) as inquiries,
             SUM(CASE WHEN EXISTS (SELECT 1 FROM admission_form af WHERE af.enquiry_id = ae.id) THEN 1 ELSE 0 END) as converted,
             SUM(CASE WHEN w.application_status = 'submitted' THEN 1 ELSE 0 END) as submitted,
             SUM(CASE WHEN w.selection_status = 'selected' THEN 1 ELSE 0 END) as selected,
             SUM(CASE WHEN w.confirmation_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"
        )->get();

        return ['headings' => ['Source', 'Inquiries', 'Application created', 'Submitted', 'Selected', 'Confirmed'],
            'rows' => $rows->map(fn ($r) => [$r->source, (int) $r->inquiries, (int) $r->converted, (int) $r->submitted, (int) $r->selected, (int) $r->confirmed])->all()];
    }

    protected function reportApplicationsStatus($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)->groupBy('w.standard_id', 's.name')->selectRaw(
            "COALESCE(s.name, 'Not set') as standard,
             SUM(CASE WHEN w.application_status IN ('draft','reopened') AND EXISTS (SELECT 1 FROM admission_form af WHERE af.enquiry_id = ae.id) THEN 1 ELSE 0 END) as draft,
             SUM(CASE WHEN w.application_status = 'submitted' THEN 1 ELSE 0 END) as submitted,
             SUM(CASE WHEN w.eligibility_status = 'eligible' THEN 1 ELSE 0 END) as eligible,
             SUM(CASE WHEN w.eligibility_status = 'ineligible' THEN 1 ELSE 0 END) as ineligible,
             SUM(CASE WHEN w.eligibility_status = 'needs_review' THEN 1 ELSE 0 END) as needs_review,
             SUM(CASE WHEN w.selection_status = 'selected' THEN 1 ELSE 0 END) as selected,
             SUM(CASE WHEN w.selection_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
             SUM(CASE WHEN w.confirmation_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"
        )->get();

        return ['headings' => ['Programme', 'Draft', 'Submitted', 'Eligible', 'Ineligible', 'Needs review', 'Selected', 'Rejected', 'Confirmed'],
            'rows' => $rows->map(fn ($r) => [$r->standard, (int) $r->draft, (int) $r->submitted, (int) $r->eligible, (int) $r->ineligible, (int) $r->needs_review, (int) $r->selected, (int) $r->rejected, (int) $r->confirmed])->all()];
    }

    protected function reportPendingDocuments($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)
            ->join('admission_document as d', function ($j) {
                $j->on('d.enquiry_id', '=', 'ae.id')->on('d.sub_institute_id', '=', 'ae.sub_institute_id');
            })
            ->whereIn('d.status', ['uploaded', 'rejected', 'resubmission_required'])
            ->select('ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 'd.doc_name', 'd.status', 'd.rejection_reason', 'd.updated_at')
            ->orderBy('d.updated_at')->get();

        return ['headings' => ['Enquiry no.', 'Applicant', 'Document', 'Status', 'Reason', 'Last updated'],
            'rows' => $rows->map(fn ($r) => [$r->enquiry_no, $this->name($r), $r->doc_name, $r->status, $r->rejection_reason, $r->updated_at])->all()];
    }

    protected function reportEligibility($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)->where('w.application_status', 'submitted')
            ->select('ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 's.name as standard', 'w.eligibility_status', 'w.eligibility_reason', 'w.eligibility_override', 'w.selection_status')
            ->orderBy('ae.id')->get();

        return ['headings' => ['Enquiry no.', 'Applicant', 'Programme', 'Eligibility', 'Reason', 'Manual override', 'Selection'],
            'rows' => $rows->map(fn ($r) => [$r->enquiry_no, $this->name($r), $r->standard, $r->eligibility_status, $r->eligibility_reason, $r->eligibility_override ? 'Yes' : 'No', $r->selection_status])->all()];
    }

    protected function reportOffers($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)
            ->join('admission_offer as o', function ($j) {
                $j->on('o.enquiry_id', '=', 'ae.id')->on('o.sub_institute_id', '=', 'ae.sub_institute_id');
            })
            ->select('ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 's.name as standard', 'o.offer_date', 'o.expiry_date', 'o.student_quota', 'o.status', 'o.responded_at')
            ->orderByDesc('o.id')->get();

        return ['headings' => ['Enquiry no.', 'Applicant', 'Programme', 'Offer date', 'Respond by', 'Seat category', 'Status', 'Responded at'],
            'rows' => $rows->map(fn ($r) => [$r->enquiry_no, $this->name($r), $r->standard, $r->offer_date, $r->expiry_date, $r->student_quota, $r->status, $r->responded_at])->all()];
    }

    protected function reportFees($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)
            ->join('admission_registration as ar', function ($j) {
                $j->on('ar.enquiry_id', '=', 'ae.id')->on('ar.sub_institute_id', '=', 'ae.sub_institute_id');
            })
            ->select('ae.id', 'ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 'w.standard_id', 's.name as standard', 'w.fee_waived',
                'ar.amount', 'ar.payment_mode', 'ar.date_of_payment')
            ->orderBy('ae.id')->get();

        $demand = [];
        $out = $rows->map(function ($r) use ($i, $y, &$demand) {
            $demand[$r->standard_id] ??= $this->fees->demand($i, (int) $y, $r->standard_id ? (int) $r->standard_id : null);
            $recorded = ! empty($r->date_of_payment) || ! empty($r->payment_mode);
            $paid = $recorded ? (float) $r->amount : 0.0;
            $due = $demand[$r->standard_id];
            $status = $r->fee_waived ? 'waived' : ($paid <= 0 ? 'unpaid' : ($due > 0 && $paid < $due ? 'partially_paid' : 'paid'));

            return [$r->enquiry_no, $this->name($r), $r->standard, $due, $paid, max($due - $paid, 0), $status];
        })->all();

        return ['headings' => ['Enquiry no.', 'Applicant', 'Programme', 'Fee due', 'Paid', 'Balance', 'Status'], 'rows' => $out];
    }

    protected function reportConfirmed($i, $y, $f): array
    {
        $rows = $this->scoped($i, $y, $f)->where('w.confirmation_status', 'confirmed')
            ->leftJoin('tblstudent as ts', 'ts.id', '=', 'w.student_id')
            ->select('ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 's.name as standard', 'w.student_id', 'ts.enrollment_no', 'w.confirmed_at', 'w.confirmation_override')
            ->orderByDesc('w.confirmed_at')->get();

        return ['headings' => ['Enquiry no.', 'Applicant', 'Programme', 'Student ID', 'Enrollment no.', 'Confirmed at', 'Override used'],
            'rows' => $rows->map(fn ($r) => [$r->enquiry_no, $this->name($r), $r->standard, $r->student_id, $r->enrollment_no, $r->confirmed_at, $r->confirmation_override ? 'Yes' : 'No'])->all()];
    }
}
