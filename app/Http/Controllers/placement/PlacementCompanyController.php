<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Http\Requests\placement\PlacementCommunicationRequest;
use App\Http\Requests\placement\PlacementCompanyRequest;
use App\Http\Requests\placement\PlacementCompanyStatusRequest;
use App\Http\Requests\placement\PlacementContactRequest;
use App\Http\Requests\placement\PlacementFeedbackRequest;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementCompanyCommunication;
use App\Models\placement\PlacementCompanyContact;
use App\Models\placement\PlacementMaster;
use App\Models\placement\PlacementSeason;
use App\Services\Placement\AuditService;
use App\Services\Placement\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use function App\Helpers\is_mobile;

/**
 * Section 3C: company onboarding, status workflow, contacts, communication log and feedback.
 * All reads/writes go through PlacementModel, which scopes to the session institute.
 */
class PlacementCompanyController extends Controller
{
    protected AuditService $audit;
    protected CompanyService $companies;

    public function __construct(AuditService $audit, CompanyService $companies)
    {
        $this->audit = $audit;
        $this->companies = $companies;
    }

    public function index(Request $request)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['statuses'] = PlacementCompany::STATUSES;
        $res['industries'] = $this->industries();
        $res['status_counts'] = PlacementCompany::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $res['follow_ups'] = PlacementCompanyCommunication::with('company:id,name')
            ->where('follow_up_done', 0)->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<=', now()->addDays(7))
            ->orderBy('follow_up_date')->limit(10)->get();

        return is_mobile($request->input('type'), 'placement/company/index', $res, 'view');
    }

    public function data(Request $request)
    {
        $query = PlacementCompany::query()
            ->leftJoin('placement_master as ind', 'ind.id', '=', 'placement_company.industry_id')
            ->select('placement_company.*', 'ind.name as industry_name')
            ->when($request->filled('status'), fn ($q) => $q->where('placement_company.status', $request->status))
            ->when($request->filled('industry_id'), fn ($q) => $q->where('placement_company.industry_id', $request->industry_id));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filterColumn('industry_name', fn ($q, $kw) => $q->where('ind.name', 'like', "%{$kw}%"))
            ->editColumn('status', fn ($r) => '<span class="label label-'.$this->badge($r->status).'">'.(PlacementCompany::STATUSES[$r->status] ?? $r->status).'</span>')
            ->editColumn('mou_status', fn ($r) => PlacementCompany::MOU_STATUSES[$r->mou_status] ?? $r->mou_status)
            ->editColumn('rating', fn ($r) => $r->rating ? number_format($r->rating, 1).' / 5' : '-')
            ->addColumn('action', fn ($r) => '<a class="btn btn-xs btn-info" href="'.route('placement.companies.show', $r->id).'"><i class="fa fa-eye"></i></a>')
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        return $this->form($request, new PlacementCompany(['mou_status' => 'none']));
    }

    public function store(PlacementCompanyRequest $request)
    {
        $company = DB::transaction(function () use ($request) {
            $data = $request->safe()->except('logo');
            $company = PlacementCompany::create($data);
            $this->saveLogo($request, $company);
            $this->audit->log('company', $company->id, 'create', null, $data);

            return $company;
        });

        return redirect()->route('placement.companies.show', $company->id)
            ->with('data', ['status_code' => 1, 'message' => 'Company added.']);
    }

    public function show(Request $request, $company)
    {
        $company = PlacementCompany::with(['contacts', 'communications' => fn ($q) => $q->orderByDesc('communicated_at')])->findOrFail($company);

        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['company'] = $company;
        $res['industry'] = $company->industry_id ? PlacementMaster::find($company->industry_id)?->name : null;
        $res['next_statuses'] = $this->companies->allowedNext($company);
        $res['statuses'] = PlacementCompany::STATUSES;
        $res['logo_url'] = $company->logo ? Storage::disk(config('placement.disk'))->url($company->logo) : null;
        $res['gst_no'] = $this->mask($company->getAttributes()['gst_no'] ?? null);
        $res['pan_no'] = $this->mask($company->getAttributes()['pan_no'] ?? null);

        return is_mobile($request->input('type'), 'placement/company/show', $res, 'view');
    }

    public function edit(Request $request, $company)
    {
        return $this->form($request, PlacementCompany::findOrFail($company));
    }

    public function update(PlacementCompanyRequest $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);

        DB::transaction(function () use ($request, $company) {
            // A blank GST/PAN box means "keep the stored value" (the form never echoes them back).
            $data = collect($request->safe()->except('logo'))
                ->reject(fn ($v, $k) => in_array($k, ['gst_no', 'pan_no'], true) && blank($v))->all();
            $company->fill($data);
            [$old, $new] = $this->audit->diff($company->getOriginal(), $company->getDirty());
            $company->save();
            $this->saveLogo($request, $company);
            if ($new) {
                $this->audit->log('company', $company->id, 'update', $old, $new);
            }
        });

        return redirect()->route('placement.companies.show', $company->id)
            ->with('data', ['status_code' => 1, 'message' => 'Company updated.']);
    }

    public function destroy(Request $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);

        DB::transaction(function () use ($company) {
            $company->delete();
            $this->audit->log('company', $company->id, 'delete');
        });

        return redirect()->route('placement.companies.index')
            ->with('data', ['status_code' => 1, 'message' => 'Company deleted.']);
    }

    public function transition(PlacementCompanyStatusRequest $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);
        $this->companies->transition($company, $request->status, $request->reason);

        return $this->back($company, 'Status changed to '.PlacementCompany::STATUSES[$request->status].'.');
    }

    public function feedback(PlacementFeedbackRequest $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);
        $company->fill($request->validated());
        [$old, $new] = $this->audit->diff($company->getOriginal(), $company->getDirty());
        $company->save();
        $this->audit->log('company', $company->id, 'feedback', $old, $new);

        return $this->back($company, 'Feedback saved.');
    }

    public function storeContact(PlacementContactRequest $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);

        DB::transaction(function () use ($request, $company) {
            if ($request->boolean('is_primary')) {
                $company->contacts()->update(['is_primary' => 0]);
            }
            $company->contacts()->create(array_merge($request->validated(), ['is_primary' => $request->boolean('is_primary') ? 1 : 0]));
        });

        return $this->back($company, 'Contact added.');
    }

    public function destroyContact($company, $contact)
    {
        $company = PlacementCompany::findOrFail($company);
        $company->contacts()->findOrFail($contact)->delete();

        return $this->back($company, 'Contact removed.');
    }

    public function storeCommunication(PlacementCommunicationRequest $request, $company)
    {
        $company = PlacementCompany::findOrFail($company);
        $company->communications()->create($request->validated());

        return $this->back($company, 'Communication logged.');
    }

    public function completeFollowUp($company, $communication)
    {
        $company = PlacementCompany::findOrFail($company);
        $company->communications()->findOrFail($communication)->update(['follow_up_done' => 1]);

        return $this->back($company, 'Follow-up marked done.');
    }

    // ---------------------------------------------------------------- helpers

    protected function form(Request $request, PlacementCompany $company)
    {
        $res['status_code'] = 1;
        $res['message'] = 'Success';
        $res['company'] = $company;
        $res['industries'] = $this->industries();
        $res['seasons'] = PlacementSeason::orderByDesc('id')->pluck('name', 'id');
        $res['mou_statuses'] = PlacementCompany::MOU_STATUSES;
        // Sensitive IDs are never echoed back into the form; leaving them blank keeps the stored value.
        $res['has_gst'] = ! empty($company->getAttributes()['gst_no'] ?? null);
        $res['has_pan'] = ! empty($company->getAttributes()['pan_no'] ?? null);

        return is_mobile($request->input('type'), 'placement/company/form', $res, 'view');
    }

    protected function saveLogo(Request $request, PlacementCompany $company): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }
        $disk = config('placement.disk');
        $path = $request->file('logo')->store('placement/'.$company->sub_institute_id.'/logos', $disk);
        if ($company->logo) {
            Storage::disk($disk)->delete($company->logo);
        }
        $company->update(['logo' => $path]);
    }

    protected function industries()
    {
        return PlacementMaster::where('master_type', 'industry')->where('status', 1)->orderBy('name')->pluck('name', 'id');
    }

    protected function back(PlacementCompany $company, string $message)
    {
        return redirect()->route('placement.companies.show', $company->id)
            ->with('data', ['status_code' => 1, 'message' => $message]);
    }

    protected function mask(?string $value): ?string
    {
        return $value ? str_repeat('*', max(strlen($value) - 3, 0)).substr($value, -3) : null;
    }

    protected function badge(string $status): string
    {
        return ['prospect' => 'default', 'invited' => 'info', 'confirmed' => 'primary', 'visited' => 'success', 'blacklisted' => 'danger'][$status] ?? 'default';
    }
}
