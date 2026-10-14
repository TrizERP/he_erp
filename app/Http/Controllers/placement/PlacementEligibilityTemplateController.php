<?php

namespace App\Http\Controllers\placement;

use App\Models\placement\PlacementEligibilityTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlacementEligibilityTemplateController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementEligibilityTemplate::class;
    }

    protected function title(): string
    {
        return 'Eligibility Criteria Templates';
    }

    protected function routePrefix(): string
    {
        return 'placement.eligibility';
    }

    protected function columns(): array
    {
        return [
            'name'                => 'Template',
            'min_cgpa'            => 'Min CGPA',
            'min_10th_percent'    => 'Min 10th %',
            'min_12th_percent'    => 'Min 12th %',
            'max_active_backlogs' => 'Max backlogs',
            'standard_ids'        => 'Courses',
            'passing_year'        => 'Passing year',
            'status'              => 'Status',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Template name', 'type' => 'text', 'required' => true],
            ['name' => 'min_cgpa', 'label' => 'Minimum CGPA', 'type' => 'number'],
            ['name' => 'min_10th_percent', 'label' => 'Minimum 10th %', 'type' => 'number'],
            ['name' => 'min_12th_percent', 'label' => 'Minimum 12th %', 'type' => 'number'],
            ['name' => 'max_active_backlogs', 'label' => 'Max active backlogs', 'type' => 'number'],
            ['name' => 'passing_year', 'label' => 'Passing year', 'type' => 'number'],
            ['name' => 'gender', 'label' => 'Gender (only if legally permitted)', 'type' => 'select', 'options' => ['male' => 'Male', 'female' => 'Female']],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
            ['name' => 'standard_ids', 'label' => 'Allowed courses', 'type' => 'multiselect', 'options' => $this->standardOptions(), 'help' => 'Hold Ctrl to select several; empty = all courses'],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
        ];
    }

    protected function decorate($datatable)
    {
        $standards = $this->standardOptions();

        return $datatable
            ->editColumn('standard_ids', fn ($r) => $r->standard_ids ? collect($r->standard_ids)->map(fn ($id) => $standards[$id] ?? $id)->implode(', ') : 'All')
            ->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'name'                => 'required|string|max:150',
            'min_cgpa'            => 'nullable|numeric|min:0|max:10',
            'min_10th_percent'    => 'nullable|numeric|min:0|max:100',
            'min_12th_percent'    => 'nullable|numeric|min:0|max:100',
            'max_active_backlogs' => 'nullable|integer|min:0',
            'passing_year'        => 'nullable|integer|min:2000|max:2100',
            'gender'              => 'nullable|in:male,female',
            'status'              => 'required|in:0,1',
            'standard_ids'        => 'nullable|array',
            'standard_ids.*'      => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', session('sub_institute_id'))],
            'notes'               => 'nullable|string|max:2000',
        ];
    }
}
