<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\OfferIssueRequest;
use App\Http\Requests\admission\OfferRespondRequest;
use App\Http\Requests\admission\ReasonRequest;
use App\Models\admission\AdmissionOffer;
use App\Services\Admission\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionOfferController extends AdmissionController
{
    public function __construct(protected OfferService $offers)
    {
    }

    public function index(Request $request)
    {
        $inst = $this->instituteId();
        $this->offers->expireDue($inst);
        $status = $request->input('status');

        $rows = DB::table('admission_offer as o')
            ->join('admission_enquiry as ae', function ($j) {
                $j->on('ae.id', '=', 'o.enquiry_id')->on('ae.sub_institute_id', '=', 'o.sub_institute_id');
            })
            ->leftJoin('standard as s', function ($j) {
                $j->on('s.id', '=', 'o.standard_id')->on('s.sub_institute_id', '=', 'o.sub_institute_id');
            })
            ->where('o.sub_institute_id', $inst)
            ->when($this->syear(), fn ($q, $y) => $q->where('ae.syear', $y))
            ->when($status && array_key_exists($status, AdmissionOffer::STATUSES), fn ($q) => $q->where('o.status', $status))
            ->select('o.*', 'ae.first_name', 'ae.last_name', 'ae.enquiry_no', 's.name as standard_name')
            ->orderByDesc('o.id')->paginate(25)->withQueryString();

        $counts = DB::table('admission_offer')->where('sub_institute_id', $inst)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admission.process.offers', ['data' => ['rows' => $rows, 'status' => $status, 'statuses' => AdmissionOffer::STATUSES, 'counts' => $counts]]);
    }

    public function issue(OfferIssueRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->offers->issue($this->instituteId(), $enquiry, $request->validated()), 'Offer issued.');
    }

    public function respond(OfferRespondRequest $request, int $offer)
    {
        $accept = $request->input('decision') === 'accept';

        return $this->attempt(fn () => $this->offers->respond($this->instituteId(), $offer, $accept, $request->input('notes')), $accept ? 'Offer accepted.' : 'Offer declined.');
    }

    public function withdraw(ReasonRequest $request, int $offer)
    {
        return $this->attempt(fn () => $this->offers->withdraw($this->instituteId(), $offer, $request->input('reason')), 'Offer withdrawn.');
    }
}
