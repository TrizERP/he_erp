<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementOfferRequest;
use App\Http\Requests\placement\PlacementOfferTransitionRequest;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementOffer;
use App\Services\Placement\OfferService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/** Section 3G: offer recording, acceptance verification, revoke, joining, PPO conversion. */
class PlacementOfferController extends Controller
{
    protected OfferService $offers;
    protected StudentLookup $students;

    public function __construct(OfferService $offers, StudentLookup $students)
    {
        $this->offers = $offers;
        $this->students = $students;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['statuses'] = PlacementOffer::STATUSES;
        $res['status_counts'] = PlacementOffer::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return is_mobile($request->input('type'), 'placement/offer/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $scope = $this->students->scopeStandardIds();

        $query = DB::table('placement_offer as o')
            ->join('tblstudent as s', 's.id', '=', 'o.student_id')
            ->join('placement_company as c', 'c.id', '=', 'o.company_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->where('o.sub_institute_id', session('sub_institute_id'))->whereNull('o.deleted_at')
            ->when($request->filled('status'), fn ($q) => $q->where('o.status', $request->status))
            ->when($scope !== null, fn ($q) => $q->whereIn('e.standard_id', $scope ?: [0]))
            ->selectRaw("o.id, s.enrollment_no, CONCAT_WS(' ', s.first_name, s.last_name) as student, c.name as company,
                o.role_title, o.ctc_total, o.status, o.is_ppo, o.verified_at, o.offer_date");

        return DataTables::query($query)
            ->addIndexColumn()
            ->editColumn('status', fn ($r) => '<span class="label label-'.$this->badge($r->status).'">'.(PlacementOffer::STATUSES[$r->status] ?? $r->status).'</span>'
                .($r->is_ppo ? ' <span class="label label-info">PPO</span>' : '')
                .($r->status === 'accepted' && ! $r->verified_at ? ' <span class="label label-warning">verify</span>' : ''))
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.offers.show', $r->id).'"><i class="fa fa-eye"></i></a>')
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $scope = $this->students->scopeStandardIds();

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['ppo'] = $request->boolean('ppo');
        $res['applications'] = DB::table('placement_application as a')
            ->join('tblstudent as s', 's.id', '=', 'a.student_id')
            ->join('placement_drive as d', 'd.id', '=', 'a.drive_id')
            ->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->join('placement_drive_role as r', 'r.id', '=', 'a.role_id')
            ->where('a.sub_institute_id', session('sub_institute_id'))->whereNull('a.deleted_at')
            ->whereIn('a.status', ['shortlisted', 'selected'])
            ->orderBy('c.name')
            ->selectRaw("a.id, CONCAT(s.enrollment_no, ' - ', s.first_name, ' ', s.last_name, ' | ', c.name, ' | ', r.title) as label, r.title as role_title")
            ->limit(2000)->get();
        $res['companies'] = PlacementCompany::where('status', '!=', 'blacklisted')->orderBy('name')->pluck('name', 'id');
        $res['internships'] = Schema::hasTable('internship_student')
            ? DB::table('internship_student as i')->join('internships as n', 'n.id', '=', 'i.internship_id')
                ->where('i.status', 'completed')->select('i.student_id', 'i.internship_id', 'n.title')->get()
            : collect();

        return is_mobile($request->input('type'), 'placement/offer/create', $res, 'view');
    }

    public function store(PlacementOfferRequest $request)
    {
        $data = $request->safe()->only(['role_title', 'ctc_fixed', 'ctc_variable', 'joining_bonus', 'location', 'offer_date', 'joining_date', 'bond_details']);

        if ($request->boolean('is_ppo')) {
            $data += ['student_id' => $request->student_id, 'company_id' => $request->company_id, 'is_ppo' => 1, 'internship_id' => $request->internship_id];
        }
        if ($request->hasFile('offer_letter')) {
            $data['offer_letter'] = $request->file('offer_letter')->store('placement/'.session('sub_institute_id').'/offers', config('placement.disk'));
        }

        $offer = $this->offers->create($data, $request->boolean('is_ppo') ? null : (int) $request->application_id, $request->override_reason);

        return redirect()->route('placement.offers.show', $offer->id)->with('data', ['status_code' => 1, 'message' => 'Offer recorded and student notified.']);
    }

    public function show(Request $request, $offer)
    {
        $offer = PlacementOffer::with('company', 'drive')->findOrFail($offer);
        $this->assertInScope($offer);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['offer'] = $offer;
        $res['student'] = $this->students->find($offer->student_id);
        $res['next'] = OfferService::TRANSITIONS[$offer->status] ?? [];
        $res['statuses'] = PlacementOffer::STATUSES;
        $res['letter_url'] = $offer->offer_letter ? Storage::disk(config('placement.disk'))->url($offer->offer_letter) : null;

        return is_mobile($request->input('type'), 'placement/offer/show', $res, 'view');
    }

    public function transition(PlacementOfferTransitionRequest $request, $offer)
    {
        $offer = PlacementOffer::findOrFail($offer);
        $this->offers->transition($offer, $request->status, $request->reason, false, $request->override_reason);

        return redirect()->route('placement.offers.show', $offer->id)->with('data', ['status_code' => 1, 'message' => 'Offer marked '.PlacementOffer::STATUSES[$request->status].'.']);
    }

    public function verify($offer)
    {
        $offer = $this->offers->verifyAcceptance(PlacementOffer::findOrFail($offer));

        return redirect()->route('placement.offers.show', $offer->id)->with('data', ['status_code' => 1, 'message' => 'Acceptance verified.']);
    }

    protected function assertInScope(PlacementOffer $offer): void
    {
        $scope = $this->students->scopeStandardIds();
        abort_if($scope !== null && ! $this->students->students([$offer->student_id], $scope)->has($offer->student_id), 404);
    }

    protected function badge(string $status): string
    {
        return ['offered' => 'info', 'accepted' => 'primary', 'rejected' => 'default', 'revoked' => 'danger', 'joined' => 'success', 'not_joined' => 'warning'][$status] ?? 'default';
    }
}
