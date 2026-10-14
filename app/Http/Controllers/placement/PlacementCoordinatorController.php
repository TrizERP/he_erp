<?php

namespace App\Http\Controllers\placement;

use App\Models\placement\PlacementCoordinator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Assigns faculty / student coordinators and the courses (standards) each one manages. */
class PlacementCoordinatorController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementCoordinator::class;
    }

    protected function title(): string
    {
        return 'Placement Coordinators';
    }

    protected function routePrefix(): string
    {
        return 'placement.coordinators';
    }

    protected function columns(): array
    {
        return ['user_id' => 'Coordinator', 'role' => 'Type', 'standard_ids' => 'Courses', 'status' => 'Status'];
    }

    protected function userOptions(): array
    {
        return DB::table('tbluser')
            ->where('sub_institute_id', session('sub_institute_id'))
            ->where('status', 1)
            ->orderBy('first_name')
            ->selectRaw("id, CONCAT_WS(' ', first_name, last_name) AS name")
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'user_id', 'label' => 'User', 'type' => 'select', 'options' => $this->userOptions(), 'required' => true],
            ['name' => 'role', 'label' => 'Type', 'type' => 'select', 'options' => ['faculty' => 'Faculty / Department coordinator', 'student' => 'Student coordinator'], 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
            ['name' => 'standard_ids', 'label' => 'Courses managed', 'type' => 'multiselect', 'options' => $this->standardOptions(), 'required' => true],
        ];
    }

    protected function decorate($datatable)
    {
        $users = $this->userOptions();
        $standards = $this->standardOptions();

        return $datatable
            ->editColumn('user_id', fn ($r) => $users[$r->user_id] ?? ('User #'.$r->user_id))
            ->editColumn('standard_ids', fn ($r) => collect($r->standard_ids ?? [])->map(fn ($id) => $standards[$id] ?? $id)->implode(', '))
            ->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        $instituteId = session('sub_institute_id');

        return [
            'user_id'        => ['required', 'integer',
                Rule::exists('tbluser', 'id')->where('sub_institute_id', $instituteId),
                Rule::unique('placement_coordinator', 'user_id')->ignore($id)
                    ->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'role'           => 'required|in:faculty,student',
            'status'         => 'required|in:0,1',
            'standard_ids'   => 'required|array|min:1',
            'standard_ids.*' => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', $instituteId)],
        ];
    }
}
