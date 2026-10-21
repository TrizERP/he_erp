<?php

namespace App\Http\Controllers\api\V1\Meta;

use App\Http\Controllers\api\V1\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Small dropdown sources, institute scoped, login only. They replace the Blade AJAX helpers
 * (collectsct, ajax_StandardwiseDivision, ajax_getSubjectsByStandard, ...), which were not rights-gated either.
 */
class LookupController extends BaseApiController
{
    /** GET /lookups/standards?all_terms=1&grade_id= */
    public function standards(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = DB::table('standard')->where('sub_institute_id', $c['institute_id'])->orderBy('sort_order')->orderBy('id');
        if (! $request->boolean('all_terms') && $c['term_id']) {
            $q->where('marking_period_id', $c['term_id']);
        }
        if ($grade = $request->query('grade_id')) {
            $q->where('grade_id', (int) $grade);
        }

        return $this->ok($q->get(['id', 'name', 'short_name', 'grade_id'])->map(fn ($r) => [
            'id' => (int) $r->id, 'name' => $r->name, 'short_name' => $r->short_name, 'grade_id' => $r->grade_id === null ? null : (int) $r->grade_id,
        ])->all());
    }

    /** GET /lookups/divisions?standard_id= : with a standard, only the divisions mapped to it (std_div_map). */
    public function divisions(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = DB::table('division as d')->where('d.sub_institute_id', $c['institute_id'])->orderBy('d.name');
        if ($std = $request->query('standard_id')) {
            $q->join('std_div_map as m', function ($j) use ($std, $c) {
                $j->on('m.division_id', '=', 'd.id')->where('m.standard_id', (int) $std)->where('m.sub_institute_id', $c['institute_id']);
            });
        }

        return $this->ok($q->distinct()->get(['d.id', 'd.name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all());
    }

    /** GET /lookups/subjects?standard_id=&all_terms=1 */
    public function subjects(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = DB::table('subject')->where('sub_institute_id', $c['institute_id'])->orderBy('subject_name');
        if (! $request->boolean('all_terms') && $c['term_id']) {
            $q->where('marking_period_id', $c['term_id']);
        }
        if ($std = $request->query('standard_id')) {
            $q->where('standard_id', (int) $std);
        }

        return $this->ok($q->get(['id', 'subject_name', 'subject_code', 'standard_id'])->map(fn ($r) => [
            'id' => (int) $r->id, 'subject_name' => $r->subject_name, 'subject_code' => $r->subject_code, 'standard_id' => $r->standard_id === null ? null : (int) $r->standard_id,
        ])->all());
    }

    /** GET /lookups/academic-sections (grades) */
    public function academicSections(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        return $this->ok(DB::table('academic_section')->where('sub_institute_id', $c['institute_id'])->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'title', 'short_name'])->map(fn ($r) => ['id' => (int) $r->id, 'title' => $r->title, 'short_name' => $r->short_name])->all());
    }

    /** GET /lookups/academic-years : the academic_year rows (terms) of the current year; id is what period.academic_year_id stores. */
    public function academicYears(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        return $this->ok(DB::table('academic_year')->where('sub_institute_id', $c['institute_id'])->where('syear', $c['syear'])->orderBy('sort_order')
            ->get(['id', 'title', 'term_id'])->map(fn ($r) => ['id' => (int) $r->id, 'title' => $r->title, 'term_id' => $r->term_id === null ? null : (int) $r->term_id])->all());
    }

    /** GET /lookups/content-categories : LMS subject categories (institute's own plus the shared ones). */
    public function contentCategories(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        return $this->ok(DB::table('lms_content_category')->where('status', '1')
            ->where(fn ($q) => $q->where('sub_institute_id', $c['institute_id'])->orWhere('sub_institute_id', 0))
            ->orderBy('category_name')->get(['id', 'category_name'])->map(fn ($r) => ['id' => (int) $r->id, 'category_name' => $r->category_name])->all());
    }
}
