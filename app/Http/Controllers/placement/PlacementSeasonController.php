<?php

namespace App\Http\Controllers\placement;

use App\Http\Requests\placement\PlacementCrudRequest;
use App\Models\placement\PlacementSeason;
use Illuminate\Http\Request;

class PlacementSeasonController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementSeason::class;
    }

    protected function title(): string
    {
        return 'Placement Seasons';
    }

    protected function routePrefix(): string
    {
        return 'placement.seasons';
    }

    protected function columns(): array
    {
        return ['name' => 'Season', 'syear' => 'Academic Year', 'start_date' => 'Start', 'end_date' => 'End', 'is_current' => 'Current', 'status' => 'Status'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Season name', 'type' => 'text', 'required' => true, 'help' => 'e.g. Placement 2026-27'],
            ['name' => 'syear', 'label' => 'Academic year (syear)', 'type' => 'number', 'help' => 'Matches the ERP academic year'],
            ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'required' => true],
            ['name' => 'end_date', 'label' => 'End date', 'type' => 'date', 'required' => true],
            ['name' => 'is_current', 'label' => 'Current season', 'type' => 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
        ];
    }

    protected function decorate($datatable)
    {
        return $datatable
            ->editColumn('start_date', fn ($r) => $r->start_date->format('d-m-Y'))
            ->editColumn('end_date', fn ($r) => $r->end_date->format('d-m-Y'))
            ->editColumn('is_current', fn ($r) => $r->is_current ? 'Yes' : 'No')
            ->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'name'       => 'required|string|max:100',
            'syear'      => 'nullable|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_current' => 'required|in:0,1',
            'status'     => 'required|in:0,1',
        ];
    }

    // Only one season can be current per institute.
    public function store(PlacementCrudRequest $request)
    {
        $this->clearCurrent($request);

        return parent::store($request);
    }

    public function update(PlacementCrudRequest $request, $id)
    {
        $this->clearCurrent($request, (int) $id);

        return parent::update($request, $id);
    }

    protected function clearCurrent(Request $request, ?int $exceptId = null): void
    {
        if ((int) $request->input('is_current') === 1) {
            PlacementSeason::when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->update(['is_current' => 0]);
        }
    }
}
