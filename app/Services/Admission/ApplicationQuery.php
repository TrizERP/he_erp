<?php

namespace App\Services\Admission;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The ONE place that defines which applications belong to a dashboard metric. The dashboard counts
 * `metric(...)->count()` and the drill-down page lists `metric(...)->paginate()`, so the number on a
 * tile and the rows behind it cannot disagree. Every query is scoped to the institute (and year).
 */
class ApplicationQuery
{
    public const METRICS = [
        'total_inquiries'       => 'Total inquiries',
        'draft_applications'    => 'Draft applications',
        'submitted_applications' => 'Submitted applications',
        'pending_documents'     => 'Pending document verification',
        'pending_eligibility'   => 'Pending eligibility review',
        'offers_awaiting'       => 'Offers awaiting response',
        'pending_fees'          => 'Pending admission fees',
        'confirmed'             => 'Confirmed admissions',
    ];

    /** All enquiries of the institute with their workflow row (call WorkflowService::syncMissing first). */
    public function base(int $instituteId, ?int $syear = null): Builder
    {
        return DB::table('admission_enquiry as ae')
            ->join('admission_workflow as w', function ($join) {
                $join->on('w.enquiry_id', '=', 'ae.id')->on('w.sub_institute_id', '=', 'ae.sub_institute_id');
            })
            ->leftJoin('standard as s', function ($join) {
                $join->on('s.id', '=', 'w.standard_id')->on('s.sub_institute_id', '=', 'w.sub_institute_id');
            })
            ->where('ae.sub_institute_id', $instituteId)
            ->when($syear, fn ($q) => $q->where('ae.syear', $syear));
    }

    public function metric(string $key, int $instituteId, ?int $syear = null): Builder
    {
        $q = $this->base($instituteId, $syear);

        switch ($key) {
            case 'total_inquiries':
                return $q;
            case 'draft_applications':
                return $this->hasForm($q)->whereIn('w.application_status', ['draft', 'reopened']);
            case 'submitted_applications':
                return $this->hasForm($q)->where('w.application_status', 'submitted');
            case 'pending_documents':
                return $q->whereExists(function ($d) use ($instituteId) {
                    $d->select(DB::raw(1))->from('admission_document as d')
                        ->whereColumn('d.enquiry_id', 'ae.id')->where('d.sub_institute_id', $instituteId)->where('d.status', 'uploaded');
                });
            case 'pending_eligibility':
                return $q->where('w.application_status', 'submitted')
                    ->whereIn('w.eligibility_status', ['pending_review', 'needs_review'])
                    ->whereIn('w.confirmation_status', ['pending', 'on_hold']);
            case 'offers_awaiting':
                return $q->whereExists(function ($o) use ($instituteId) {
                    $o->select(DB::raw(1))->from('admission_offer as o')
                        ->whereColumn('o.enquiry_id', 'ae.id')->where('o.sub_institute_id', $instituteId)->where('o.status', 'issued');
                });
            case 'pending_fees':
                // application registered, not yet confirmed/cancelled, not waived, and no payment recorded
                // on the registration record (the same test FeeService uses for "paid > 0")
                return $this->hasForm($q)->whereIn('w.confirmation_status', ['pending', 'on_hold'])->where('w.fee_waived', false)
                    ->whereNotExists(function ($r) use ($instituteId) {
                        $r->select(DB::raw(1))->from('admission_registration as ar')
                            ->whereColumn('ar.enquiry_id', 'ae.id')->where('ar.sub_institute_id', $instituteId)->where('ar.amount', '>', 0)
                            ->where(function ($p) {
                                $p->whereNotNull('ar.date_of_payment')->orWhere(function ($m) {
                                    $m->whereNotNull('ar.payment_mode')->where('ar.payment_mode', '<>', '');
                                });
                            });
                    });
            case 'confirmed':
                return $q->where('w.confirmation_status', 'confirmed');
        }

        throw AdmissionException::because('Unknown dashboard metric.');
    }

    /** Columns shown on every application list. */
    public function listColumns(Builder $q): Builder
    {
        return $q->select([
            'ae.id', 'ae.enquiry_no', 'ae.first_name', 'ae.middle_name', 'ae.last_name', 'ae.mobile', 'ae.syear', 'ae.source_of_enquiry',
            'w.inquiry_status', 'w.application_status', 'w.eligibility_status', 'w.selection_status', 'w.confirmation_status',
            'w.fee_waived', 'w.student_id', 's.name as standard_name',
        ])->selectRaw('(select af.form_no from admission_form af where af.enquiry_id = ae.id order by af.id limit 1) as form_no')
            // extra facts the StageService needs to say "where is it now / what next"
            ->selectRaw('(select count(*) from admission_form af2 where af2.enquiry_id = ae.id) as form_count')
            ->selectRaw("(select count(*) from admission_document d where d.enquiry_id = ae.id and d.status = 'uploaded') as docs_to_verify")
            ->selectRaw('(select o.status from admission_offer o where o.enquiry_id = ae.id order by o.id desc limit 1) as last_offer');
    }

    protected function hasForm(Builder $q): Builder
    {
        return $q->whereExists(function ($f) {
            $f->select(DB::raw(1))->from('admission_form as af')->whereColumn('af.enquiry_id', 'ae.id');
        });
    }
}
