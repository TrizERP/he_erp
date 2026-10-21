<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\DivisionPlannerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Division Planner (division_capacity_master), Blade divisionCapacityMasterController. Scoped to the academic year. */
class DivisionPlannerController extends BaseApiController
{
    use PaginatesLists;

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = $this->base()->where('dc.sub_institute_id', $c['institute_id'])->where('dc.syear', $c['syear']);

        return $this->listPage(
            $request,
            $q,
            ['id' => 'dc.id', 'academic_section' => 'a.title', 'standard' => 's.name', 'division' => 'd.name', 'sem_start_date' => 'dc.sem_start_date', 'sem_end_date' => 'dc.sem_end_date', 'capacity' => 'dc.capacity'],
            ['a.title', 's.name', 'd.name'],
            'id',
            fn ($r) => $this->shape($r),
        );
    }

    public function store(DivisionPlannerRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $request->validated();
        $this->assertOwned($d, $c['institute_id']);

        $id = DB::table('division_capacity_master')->insertGetId([
            'sub_institute_id' => $c['institute_id'],
            'syear' => $c['syear'],
            'grade_id' => $d['grade_id'],
            'standard_id' => $d['standard_id'],
            'division_id' => $d['division_id'],
            'capacity' => $d['capacity'],
            'sem_start_date' => $d['sem_start_date'],
            'sem_end_date' => $d['sem_end_date'],
            'created_on' => now(),
            'created_by' => $c['user_id'],
            'created_ip' => $request->ip(),
        ]);

        return $this->ok($this->shape($this->base()->where('dc.id', $id)->first()), 'Division Planner Added Successfully', null, 201);
    }

    public function update(DivisionPlannerRequest $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        abort_if(! DB::table('division_capacity_master')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->exists(), 404);
        $d = $request->validated();
        $this->assertOwned($d, $c['institute_id']);

        DB::table('division_capacity_master')->where('id', $id)->update([
            'grade_id' => $d['grade_id'],
            'standard_id' => $d['standard_id'],
            'division_id' => $d['division_id'],
            'capacity' => $d['capacity'],
            'sem_start_date' => $d['sem_start_date'],
            'sem_end_date' => $d['sem_end_date'],
            'updated_on' => now(),
            'updated_by' => $c['user_id'],
        ]);

        return $this->ok($this->shape($this->base()->where('dc.id', $id)->first()), 'Division Planner Updated Successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        abort_if(! DB::table('division_capacity_master')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->delete(), 404);

        return $this->ok(null, 'Division Planner Deleted Successfully');
    }

    private function base()
    {
        return DB::table('division_capacity_master as dc')
            ->join('academic_section as a', 'a.id', '=', 'dc.grade_id')
            ->join('standard as s', 's.id', '=', 'dc.standard_id')
            ->join('division as d', 'd.id', '=', 'dc.division_id')
            ->select('dc.*', 'a.title as academic_section_name', 's.name as standard_name', 'd.name as division_name');
    }

    private function shape($r): array
    {
        return [
            'id' => (int) $r->id,
            'grade_id' => (int) $r->grade_id,
            'standard_id' => (int) $r->standard_id,
            'division_id' => (int) $r->division_id,
            'academic_section_name' => $r->academic_section_name,
            'standard_name' => $r->standard_name,
            'division_name' => $r->division_name,
            'capacity' => (int) $r->capacity,
            'sem_start_date' => $r->sem_start_date,
            'sem_end_date' => $r->sem_end_date,
        ];
    }

    private function assertOwned(array $d, int $inst): void
    {
        foreach ([['academic_section', 'grade_id', 'Grade'], ['standard', 'standard_id', 'Standard'], ['division', 'division_id', 'Division']] as [$table, $field, $label]) {
            if (! DB::table($table)->where(['id' => $d[$field], 'sub_institute_id' => $inst])->exists()) {
                throw ValidationException::withMessages([$field => ["Please select a $label."]]);
            }
        }
    }
}
