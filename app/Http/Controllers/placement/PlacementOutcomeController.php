<?php

namespace App\Http\Controllers\placement;

use App\Http\Requests\placement\PlacementCrudRequest;
use App\Models\placement\PlacementOutcome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Section 3J: outcomes other than campus placement (higher studies, entrepreneurship, off-campus
 * placement). Needed for the NAAC progression figures. The student is entered by enrollment number.
 */
class PlacementOutcomeController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementOutcome::class;
    }

    protected function title(): string
    {
        return 'Higher Studies, Entrepreneurship & Other Outcomes';
    }

    protected function routePrefix(): string
    {
        return 'placement.outcomes';
    }

    protected function columns(): array
    {
        return ['enrollment_no' => 'Enrollment', 'student' => 'Student', 'outcome_type' => 'Outcome', 'organization' => 'Institute / venture / employer',
            'program_role' => 'Programme / role', 'outcome_date' => 'Date', 'verified' => 'Verified'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'enrollment_no', 'label' => 'Student enrollment no.', 'type' => 'text', 'required' => true],
            ['name' => 'outcome_type', 'label' => 'Outcome', 'type' => 'select', 'options' => PlacementOutcome::TYPES, 'required' => true],
            ['name' => 'organization', 'label' => 'Institute / venture / employer', 'type' => 'text'],
            ['name' => 'program_role', 'label' => 'Programme / role', 'type' => 'text'],
            ['name' => 'country', 'label' => 'Country', 'type' => 'text'],
            ['name' => 'outcome_date', 'label' => 'Date', 'type' => 'date'],
            ['name' => 'verified', 'label' => 'Verified', 'type' => 'select', 'options' => [0 => 'No', 1 => 'Yes'], 'required' => true],
            ['name' => 'details', 'label' => 'Details', 'type' => 'textarea'],
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return PlacementOutcome::query()
            ->leftJoin('tblstudent as s', 's.id', '=', 'placement_outcome_other.student_id')
            ->select('placement_outcome_other.*', 's.enrollment_no', DB::raw("CONCAT_WS(' ', s.first_name, s.last_name) as student"));
    }

    protected function decorate($datatable)
    {
        return $datatable
            ->editColumn('outcome_type', fn ($r) => PlacementOutcome::TYPES[$r->outcome_type] ?? $r->outcome_type)
            ->editColumn('outcome_date', fn ($r) => $r->outcome_date?->format('d-m-Y'))
            ->editColumn('verified', fn ($r) => $r->verified ? 'Yes' : 'No')
            ->filterColumn('student', fn ($q, $kw) => $q->whereRaw("CONCAT_WS(' ', s.first_name, s.last_name) LIKE ?", ["%{$kw}%"]))
            ->filterColumn('enrollment_no', fn ($q, $kw) => $q->where('s.enrollment_no', 'like', "%{$kw}%"));
    }

    public function edit(Request $request, $id)
    {
        $row = PlacementOutcome::findOrFail($id);
        $row->setAttribute('enrollment_no', DB::table('tblstudent')->where('id', $row->student_id)->value('enrollment_no'));

        return response()->json($row);
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'enrollment_no' => ['required', 'string', 'max:50', Rule::exists('tblstudent', 'enrollment_no')->where('sub_institute_id', session('sub_institute_id'))],
            'outcome_type'  => ['required', Rule::in(array_keys(PlacementOutcome::TYPES))],
            'organization'  => 'nullable|string|max:200',
            'program_role'  => 'nullable|string|max:200',
            'country'       => 'nullable|string|max:80',
            'outcome_date'  => 'nullable|date',
            'verified'      => 'required|in:0,1',
            'details'       => 'nullable|string|max:3000',
        ];
    }

    // enrollment_no is a lookup key, not a column: resolve it to student_id before saving.
    public function store(PlacementCrudRequest $request)
    {
        $row = PlacementOutcome::create($this->payload($request));
        app(\App\Services\Placement\AuditService::class)->log('outcomes', $row->id, 'create', null, ['student_id' => $row->student_id, 'outcome_type' => $row->outcome_type]);

        return response()->json(['status' => 1, 'message' => 'Saved successfully.', 'id' => $row->id]);
    }

    public function update(PlacementCrudRequest $request, $id)
    {
        $row = PlacementOutcome::findOrFail($id);
        $row->update($this->payload($request));
        app(\App\Services\Placement\AuditService::class)->log('outcomes', $row->id, 'update');

        return response()->json(['status' => 1, 'message' => 'Updated successfully.']);
    }

    protected function payload(PlacementCrudRequest $request): array
    {
        $data = $request->validated();
        $data['student_id'] = DB::table('tblstudent')->where('sub_institute_id', session('sub_institute_id'))->where('enrollment_no', $data['enrollment_no'])->value('id');
        unset($data['enrollment_no']);

        return $data;
    }
}
