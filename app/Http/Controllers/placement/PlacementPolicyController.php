<?php

namespace App\Http\Controllers\placement;

use App\Http\Requests\placement\PlacementCrudRequest;
use App\Models\placement\PlacementPolicy;
use App\Models\placement\PlacementSeason;
use App\Services\Placement\PlacementAccess;
use App\Services\Placement\PolicyService;
use Illuminate\Http\Request;

class PlacementPolicyController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementPolicy::class;
    }

    protected function title(): string
    {
        return 'Placement Policy & Rules';
    }

    protected function routePrefix(): string
    {
        return 'placement.policies';
    }

    protected function columns(): array
    {
        return [
            'name'                => 'Policy',
            'season_id'           => 'Season',
            'one_offer_rule'      => 'One-offer',
            'dream_offer_rule'    => 'Dream-offer',
            'dream_min_ctc'       => 'Dream min CTC',
            'max_applications'    => 'Max applications',
            'max_active_backlogs' => 'Max backlogs',
            'approved_at'         => 'Approved on',
            'status'              => 'Status',
        ];
    }

    protected function fields(): array
    {
        $yn = [1 => 'Yes', 0 => 'No'];

        return [
            ['name' => 'name', 'label' => 'Policy name', 'type' => 'text', 'required' => true],
            ['name' => 'season_id', 'label' => 'Season', 'type' => 'select', 'options' => PlacementSeason::orderByDesc('id')->pluck('name', 'id')->toArray(), 'help' => 'Leave empty for an institute-wide policy'],
            ['name' => 'one_offer_rule', 'label' => 'One-offer rule', 'type' => 'select', 'options' => $yn, 'required' => true],
            ['name' => 'dream_offer_rule', 'label' => 'Dream-offer rule', 'type' => 'select', 'options' => $yn, 'required' => true],
            ['name' => 'dream_min_ctc', 'label' => 'Dream-offer holder may apply only above CTC (LPA)', 'type' => 'number'],
            ['name' => 'max_applications', 'label' => 'Max applications per student', 'type' => 'number'],
            ['name' => 'max_active_backlogs', 'label' => 'Max active backlogs allowed', 'type' => 'number'],
            ['name' => 'block_blacklisted', 'label' => 'Block blacklisted companies', 'type' => 'select', 'options' => $yn, 'required' => true],
            ['name' => 'debar_days', 'label' => 'Debar days after offer drop-out', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
        ];
    }

    protected function decorate($datatable)
    {
        $seasons = PlacementSeason::pluck('name', 'id');

        return $datatable
            ->editColumn('season_id', fn ($r) => $seasons[$r->season_id] ?? 'All seasons')
            ->editColumn('one_offer_rule', fn ($r) => $r->one_offer_rule ? 'Yes' : 'No')
            ->editColumn('dream_offer_rule', fn ($r) => $r->dream_offer_rule ? 'Yes' : 'No')
            ->editColumn('approved_at', fn ($r) => $r->approved_at ? $r->approved_at->format('d-m-Y') : 'Pending')
            ->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'name'                => 'required|string|max:150',
            'season_id'           => 'nullable|integer|exists:placement_season,id,sub_institute_id,'.session('sub_institute_id').',deleted_at,NULL',
            'one_offer_rule'      => 'required|in:0,1',
            'dream_offer_rule'    => 'required|in:0,1',
            'dream_min_ctc'       => 'nullable|numeric|min:0|required_if:dream_offer_rule,1',
            'max_applications'    => 'nullable|integer|min:1',
            'max_active_backlogs' => 'nullable|integer|min:0',
            'block_blacklisted'   => 'required|in:0,1',
            'debar_days'          => 'nullable|integer|min:0',
            'status'              => 'required|in:0,1',
            'notes'               => 'nullable|string|max:2000',
        ];
    }

    protected function extraActions($row): string
    {
        if ($row->approved_at || ! PlacementAccess::allows([PlacementAccess::PRINCIPAL])) {
            return '';
        }

        return '<button class="btn btn-xs btn-success pl-act" data-confirm="Approve this policy?" data-url="'
            .route('placement.policies.approve', $row->id).'"><i class="fa fa-check"></i> Approve</button> ';
    }

    /** Principal / admin sign-off on the rules. */
    public function approve(Request $request, $id, PolicyService $service)
    {
        $service->approve(PlacementPolicy::findOrFail($id));

        return response()->json(['status' => 1, 'message' => 'Policy approved.']);
    }

    // Changing the rules voids an earlier approval.
    public function update(PlacementCrudRequest $request, $id)
    {
        PlacementPolicy::findOrFail($id)->update(['approved_by' => null, 'approved_at' => null]);

        return parent::update($request, $id);
    }
}
