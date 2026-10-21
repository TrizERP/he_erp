<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\StandardRequest;
use App\Http\Resources\V1\SchoolSetup\StandardResource;
use App\Models\school_setup\standardModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * "Standard" master (called Semester for colleges, see GET /labels).
 * List is limited to the current term, like the Blade dropdowns, unless filters[all_terms]=1 (used for the
 * "next standard" options, which the Blade collectsct helper does not limit by term).
 */
class StandardController extends BaseApiController
{
    use PaginatesLists;

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $query = DB::table('standard as s')
            ->leftJoin('academic_section as g', function ($j) {
                $j->on('g.id', '=', 's.grade_id')->on('g.sub_institute_id', '=', 's.sub_institute_id');
            })
            ->where('s.sub_institute_id', $c['institute_id'])
            ->select('s.*', 'g.title as grade_title', 'g.short_name as grade_short_name');

        if (! $request->boolean('filters.all_terms') && $c['term_id']) {
            $query->where('s.marking_period_id', $c['term_id']);
        }
        if ($grade = $request->input('filters.grade_id')) {
            $query->where('s.grade_id', (int) $grade);
        }

        return $this->listPage(
            $request,
            $query,
            ['name' => 's.name', 'short_name' => 's.short_name', 'sort_order' => 's.sort_order', 'medium' => 's.medium', 'grade' => 'g.title'],
            ['s.name', 's.short_name', 's.medium', 'g.title'],
            'sort_order',
            fn ($row) => (new StandardResource((object) (array) $row))->resolve(),
        );
    }

    public function store(StandardRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $request->validated();

        // same insert as the Blade controller: the standard belongs to the current term
        $id = standardModel::insertGetId([
            'grade_id' => $d['grade_id'] ?? null,
            'name' => $d['name'],
            'short_name' => $d['short_name'] ?? null,
            'sort_order' => $d['sort_order'] ?? null,
            'medium' => $d['medium'] ?? null,
            'sub_institute_id' => $c['institute_id'],
            'course_duration' => $d['course_duration'] ?? null,
            'next_grade_id' => $d['next_grade_id'] ?? null,
            'next_standard_id' => $d['next_standard_id'] ?? null,
            'marking_period_id' => $c['term_id'] ?? null,
        ]);

        return $this->ok((new StandardResource((object) DB::table('standard')->where('id', $id)->first()))->resolve(), 'Standard Added Successfully !', null, 201);
    }
}
