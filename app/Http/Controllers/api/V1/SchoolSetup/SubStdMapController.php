<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\SubStdMapRequest;
use App\Http\Resources\V1\SchoolSetup\SubStdMapResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Subject <-> Standard mapping, Blade sub_std_mapController. */
class SubStdMapController extends BaseApiController
{
    use PaginatesLists;

    private const DEPENDENCY = 'You cannot delete Mapping.Mapping is having dependencies in Other Module';

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = $this->base()->where('m.sub_institute_id', $c['institute_id']);
        if ($std = $request->input('filters.standard_id')) {
            $q->where('m.standard_id', (int) $std);
        }

        return $this->listPage(
            $request,
            $q,
            ['standard_id' => 'm.standard_id', 'subject_name' => 'sub.subject_name', 'standard' => 'st.name', 'display_name' => 'm.display_name', 'allow_grades' => 'm.allow_grades', 'elective_subject' => 'm.elective_subject', 'sort_order' => 'm.sort_order'],
            ['sub.subject_name', 'sub.subject_code', 'st.name', 'm.display_name'],
            'standard_id',
            fn ($row) => (new SubStdMapResource((object) (array) $row))->resolve(),
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $row = $this->findOrFail($request, $id);

        return $this->ok((new SubStdMapResource((object) (array) $row))->resolve());
    }

    /** Update-or-create on (standard, subject), like the Blade store. */
    public function store(SubStdMapRequest $request): JsonResponse
    {
        $inst = $request->attributes->get('api_context')['institute_id'];
        $d = $request->validated();
        $this->assertOwned($d, $inst);

        $existing = DB::table('sub_std_map')->where(['standard_id' => $d['standard_id'], 'subject_id' => $d['subject_id'], 'sub_institute_id' => $inst])->first();
        $values = $this->values($request, $d, $inst, $existing->display_image ?? null);

        if ($existing) {
            DB::table('sub_std_map')->where('id', $existing->id)->update($values + ['updated_at' => now()]);
            $id = $existing->id;
        } else {
            $id = DB::table('sub_std_map')->insertGetId($values + ['created_at' => now(), 'updated_at' => now()]);
        }

        return $this->ok((new SubStdMapResource((object) (array) $this->findOrFail($request, $id)))->resolve(), 'Subject-Standard Mapping Added Successfully', null, 201);
    }

    public function update(SubStdMapRequest $request, int $id): JsonResponse
    {
        $inst = $request->attributes->get('api_context')['institute_id'];
        $current = DB::table('sub_std_map')->where(['id' => $id, 'sub_institute_id' => $inst])->first();
        abort_if(! $current, 404);
        $d = $request->validated();
        $this->assertOwned($d, $inst);

        if (DB::table('sub_std_map')->where(['standard_id' => $d['standard_id'], 'subject_id' => $d['subject_id'], 'sub_institute_id' => $inst])->where('id', '!=', $id)->exists()) {
            throw ValidationException::withMessages(['subject_id' => ['This subject is already mapped to the selected Standard.']]);
        }

        DB::table('sub_std_map')->where('id', $id)->update($this->values($request, $d, $inst, $current->display_image) + ['updated_at' => now()]);

        return $this->ok((new SubStdMapResource((object) (array) $this->findOrFail($request, $id)))->resolve(), 'Subject-Standard Mapping Updated Successfully');
    }

    /** Refused while chapters exist for this subject + standard in the current year (the Blade dependency check). */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $row = DB::table('sub_std_map')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->first();
        abort_if(! $row, 404);

        $dependent = DB::table('chapter_master')->where([
            'sub_institute_id' => $c['institute_id'], 'syear' => $c['syear'], 'subject_id' => $row->subject_id, 'standard_id' => $row->standard_id,
        ])->count();
        if ($dependent > 0) {
            return $this->fail(self::DEPENDENCY, null, 409);
        }

        DB::table('sub_std_map')->where('id', $id)->delete();

        return $this->ok(null, 'Subject-Standard Mapping Deleted Successfully');
    }

    private function base()
    {
        return DB::table('sub_std_map as m')
            ->join('standard as st', 'st.id', '=', 'm.standard_id')
            ->join('subject as sub', 'sub.id', '=', 'm.subject_id')
            ->select('m.*', 'sub.subject_name', 'sub.subject_code', 'st.name as standard_name');
    }

    private function findOrFail(Request $request, int $id): object
    {
        $row = $this->base()->where('m.id', $id)->where('m.sub_institute_id', $request->attributes->get('api_context')['institute_id'])->first();
        abort_if(! $row, 404);

        return $row;
    }

    /** Standard and subject must belong to this institute. */
    private function assertOwned(array $d, int $inst): void
    {
        if (! DB::table('standard')->where(['id' => $d['standard_id'], 'sub_institute_id' => $inst])->exists()) {
            throw ValidationException::withMessages(['standard_id' => ['Please select a Standard.']]);
        }
        if (! DB::table('subject')->where(['id' => $d['subject_id'], 'sub_institute_id' => $inst])->exists()) {
            throw ValidationException::withMessages(['subject_id' => ['Please select a Subject.']]);
        }
    }

    /** Column values; a new image replaces the old one, no image keeps it. Flags are stored as Yes / empty like Blade. */
    private function values(Request $request, array $d, int $inst, ?string $currentImage): array
    {
        $image = $currentImage;
        if ($request->hasFile('display_image')) {
            $file = $request->file('display_image');
            $name = 'SubStdMap_'.date('Y-m-d_h-i-s').'_'.substr(md5(uniqid('', true)), 0, 6).'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/SubStdMapping', $name);
            $image = '/SubStdMapping/'.$name;
        }

        return [
            'standard_id' => $d['standard_id'],
            'subject_id' => $d['subject_id'],
            'display_name' => $d['display_name'],
            'allow_grades' => ! empty($d['allow_grades']) ? 'Yes' : '',
            'elective_subject' => ! empty($d['elective_subject']) ? 'Yes' : '',
            'allow_content' => ! empty($d['allow_content']) ? 'Yes' : '',
            'subject_category' => $d['subject_category'] ?? null,
            'sort_order' => $d['sort_order'] ?? null,
            'add_content' => $d['add_content'] ?? null,
            'display_image' => $image,
            'sub_institute_id' => $inst,
            'status' => '1',
        ];
    }
}
