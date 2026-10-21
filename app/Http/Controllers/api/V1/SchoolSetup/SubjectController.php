<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\SubjectRequest;
use App\Http\Resources\V1\SchoolSetup\SubjectResource;
use App\Models\school_setup\subjectModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Subjects (Blade subject1Controller: store / update / destroy + the Add Masters "Subject" form). */
class SubjectController extends BaseApiController
{
    use PaginatesLists;

    private const DUPLICATE = 'This subject already exists for the selected Standard.';

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $query = DB::table('subject as sub')
            ->leftJoin('standard as st', 'st.id', '=', 'sub.standard_id')
            ->where('sub.sub_institute_id', $c['institute_id'])
            ->select('sub.*', 'st.name as standard_name');

        if ($c['term_id']) {
            $query->where('sub.marking_period_id', $c['term_id']);
        }
        if ($std = $request->input('filters.standard_id')) {
            $query->where('sub.standard_id', (int) $std);
        }

        return $this->listPage(
            $request,
            $query,
            ['subject_name' => 'sub.subject_name', 'standard' => 'st.name', 'subject_code' => 'sub.subject_code', 'subject_type' => 'sub.subject_type', 'short_name' => 'sub.short_name'],
            ['sub.subject_name', 'sub.subject_code', 'sub.short_name', 'st.name'],
            'subject_name',
            fn ($row) => (new SubjectResource((object) (array) $row))->resolve(),
        );
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $request->validated();
        $this->assertStandardIsOurs((int) $d['standard_id'], $c['institute_id']);
        $this->assertUnique($d['subject_name'], $c['institute_id'], (int) $d['standard_id']);

        $row = subjectModel::create([
            'subject_name' => $d['subject_name'],
            'subject_type' => ! empty($d['major']) ? 'Major' : '',
            'subject_code' => $d['subject_code'] ?? null,
            'short_name' => $d['short_name'] ?? null,
            'sub_institute_id' => $c['institute_id'],
            'standard_id' => $d['standard_id'],
            'marking_period_id' => $c['term_id'] ?? null,
            'status' => '1',
        ]);

        return $this->ok($this->one($row->id), 'Subject Added Successfully', null, 201);
    }

    public function update(SubjectRequest $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $row = subjectModel::where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->firstOrFail();
        $d = $request->validated();
        $this->assertStandardIsOurs((int) $d['standard_id'], $c['institute_id']);
        $this->assertUnique($d['subject_name'], $c['institute_id'], (int) $d['standard_id'], $row->id);

        $row->update([
            'subject_name' => $d['subject_name'],
            'subject_type' => ! empty($d['major']) ? 'Major' : '',
            'subject_code' => $d['subject_code'] ?? null,
            'short_name' => $d['short_name'] ?? null,
            'standard_id' => $d['standard_id'],
            'marking_period_id' => $c['term_id'] ?? null,
        ]);

        return $this->ok($this->one($row->id), 'Subject Updated Successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        subjectModel::where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->firstOrFail()->delete();

        return $this->ok(null, 'Subject Deleted Successfully');
    }

    /** Case-insensitive name + standard (+ institute) uniqueness, same as check_exist / check_exist_update. */
    private function assertUnique(string $name, int $institute, int $standardId, ?int $ignoreId = null): void
    {
        $q = DB::table('subject')->where('sub_institute_id', $institute)->where('standard_id', $standardId)
            ->whereRaw('UPPER(subject_name) = ?', [strtoupper($name)]);
        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }
        if ($q->exists()) {
            throw ValidationException::withMessages(['subject_name' => [self::DUPLICATE]]);
        }
    }

    /** The Blade form only offers this institute's standards; reject anything else. */
    private function assertStandardIsOurs(int $standardId, int $institute): void
    {
        if (! DB::table('standard')->where(['id' => $standardId, 'sub_institute_id' => $institute])->exists()) {
            throw ValidationException::withMessages(['standard_id' => ['Please select a Standard.']]);
        }
    }

    private function one(int $id): array
    {
        $row = DB::table('subject as sub')->leftJoin('standard as st', 'st.id', '=', 'sub.standard_id')
            ->where('sub.id', $id)->select('sub.*', 'st.name as standard_name')->first();

        return (new SubjectResource((object) $row))->resolve();
    }
}
