<?php

namespace App\Http\Controllers\placement;

use App\Models\placement\PlacementMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Section 3A masters: one table, one screen per master_type (tabs). */
class PlacementMasterController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementMaster::class;
    }

    protected function title(): string
    {
        return 'Placement Masters';
    }

    protected function routePrefix(): string
    {
        return 'placement.masters';
    }

    protected function currentType(Request $request): string
    {
        $type = $request->input('type');

        return array_key_exists($type, PlacementMaster::TYPES) ? $type : 'industry';
    }

    protected function baseQuery(Request $request): Builder
    {
        return PlacementMaster::query()->where('master_type', $this->currentType($request));
    }

    protected function extraData(Request $request): array
    {
        return ['master_type' => $this->currentType($request)];
    }

    protected function indexData(Request $request): array
    {
        $type = $this->currentType($request);
        $isBand = in_array($type, ['salary_band', 'package_category'], true);

        return [
            'title'   => PlacementMaster::TYPES[$type],
            'tabs'    => PlacementMaster::TYPES,
            'query'   => ['type' => $type],
            'columns' => array_filter([
                'name'       => 'Name',
                'code'       => 'Code',
                'min_ctc'    => $isBand ? 'Min CTC (LPA)' : null,
                'max_ctc'    => $isBand ? 'Max CTC (LPA)' : null,
                'sort_order' => 'Order',
                'status'     => 'Status',
            ]),
            'fields'  => $this->fieldsFor($isBand),
        ];
    }

    protected function columns(): array
    {
        return ['name' => 'Name', 'code' => 'Code', 'min_ctc' => 'Min CTC', 'max_ctc' => 'Max CTC', 'sort_order' => 'Order', 'status' => 'Status'];
    }

    protected function fields(): array
    {
        return $this->fieldsFor(true);
    }

    protected function fieldsFor(bool $withBand): array
    {
        return array_values(array_filter([
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
            $withBand ? ['name' => 'min_ctc', 'label' => 'Min CTC (LPA)', 'type' => 'number'] : null,
            $withBand ? ['name' => 'max_ctc', 'label' => 'Max CTC (LPA)', 'type' => 'number'] : null,
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
        ]));
    }

    protected function decorate($datatable)
    {
        return $datatable->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        $type = $this->currentType($request);

        return [
            'name'       => ['required', 'string', 'max:150',
                Rule::unique('placement_master', 'name')->ignore($id)
                    ->where('sub_institute_id', session('sub_institute_id'))
                    ->where('master_type', $type)->whereNull('deleted_at')],
            'code'       => 'nullable|string|max:50',
            'min_ctc'    => 'nullable|numeric|min:0',
            'max_ctc'    => 'nullable|numeric|min:0|gte:min_ctc',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:0,1',
        ];
    }
}
