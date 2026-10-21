<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\PeriodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Periods (Blade periodController). A period has per-standard timings in period_details; the API exposes them as
 * "groups" (standards sharing a start / end time), which is how the Blade form edits them.
 */
class PeriodController extends BaseApiController
{
    use PaginatesLists;

    private const DUPLICATE = 'Period Already Exist';

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $response = $this->listPage(
            $request,
            DB::table('period')->where('sub_institute_id', $c['institute_id']),
            ['sort_order' => 'sort_order', 'title' => 'title', 'short_name' => 'short_name', 'used_for_attendance' => 'used_for_attendance'],
            ['title', 'short_name'],
            'sort_order',
            fn ($p) => $this->summary($p),
        );

        // distinct start / end times per listed period, from its timing rows (Blade: GROUP_CONCAT over period_details)
        $payload = $response->getData(true);
        $ids = array_column($payload['data'], 'id');
        $details = $ids ? DB::table('period_details')->whereIn('period_id', $ids)->where('sub_institute_id', $c['institute_id'])->orderBy('start_time')->get(['period_id', 'start_time', 'end_time']) : collect();
        foreach ($payload['data'] as &$row) {
            $mine = $details->where('period_id', $row['id']);
            $row['start_times'] = $mine->pluck('start_time')->unique()->values()->map(fn ($t) => $this->hm($t))->all();
            $row['end_times'] = $mine->pluck('end_time')->unique()->values()->map(fn ($t) => $this->hm($t))->all();
        }
        unset($row);

        return $this->ok($payload['data'], 'Success', $payload['meta']);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $p = DB::table('period')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->first();
        abort_if(! $p, 404);

        $groups = [];
        foreach (DB::table('period_details')->where(['period_id' => $id, 'sub_institute_id' => $c['institute_id']])->orderBy('start_time')->orderBy('standard_id')->get() as $d) {
            $key = $d->start_time.'|'.$d->end_time;
            $groups[$key] ??= ['standard_ids' => [], 'start_time' => $this->hm($d->start_time), 'end_time' => $this->hm($d->end_time)];
            $groups[$key]['standard_ids'][] = (int) $d->standard_id;
        }

        return $this->ok($this->summary($p) + ['academic_section_id' => $p->academic_section_id === null ? null : (int) $p->academic_section_id, 'academic_year_id' => $p->academic_year_id ? (int) $p->academic_year_id : null, 'groups' => array_values($groups)]);
    }

    public function store(PeriodRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $request->validated();
        $this->assertValid($d, $c['institute_id'], null);

        $id = DB::transaction(function () use ($d, $c) {
            $id = DB::table('period')->insertGetId($this->periodValues($d, $c) + ['created_at' => now(), 'updated_at' => now()]);
            $this->syncDetails($id, $d['groups'] ?? [], $c);

            return $id;
        });

        return $this->ok(['id' => $id], 'Period Added Successfully', null, 201);
    }

    public function update(PeriodRequest $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        abort_if(! DB::table('period')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->exists(), 404);
        $d = $request->validated();
        $this->assertValid($d, $c['institute_id'], $id);

        DB::transaction(function () use ($d, $c, $id) {
            DB::table('period')->where('id', $id)->update($this->periodValues($d, $c) + ['updated_at' => now()]);
            $this->syncDetails($id, $d['groups'] ?? [], $c);
        });

        return $this->ok(['id' => $id], 'Period Updated Successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        abort_if(! DB::table('period')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->exists(), 404);
        DB::transaction(function () use ($id, $c) {
            DB::table('period_details')->where(['period_id' => $id, 'sub_institute_id' => $c['institute_id']])->delete();
            DB::table('period')->where('id', $id)->delete();
        });

        return $this->ok(null, 'Period Deleted Successfully');
    }

    // ---------------------------------------------------------------- helpers

    private function summary($p): array
    {
        return [
            'id' => (int) $p->id,
            'title' => $p->title,
            'short_name' => $p->short_name,
            'sort_order' => $p->sort_order === null ? null : (int) $p->sort_order,
            'used_for_attendance' => $p->used_for_attendance === 'Yes',
            'start_times' => [],
            'end_times' => [],
        ];
    }

    private function hm(?string $t): ?string
    {
        return $t ? substr($t, 0, 5) : null;
    }

    private function seconds(string $t): int
    {
        $parts = array_map('intval', explode(':', $t));

        return ($parts[0] * 3600) + ($parts[1] * 60) + ($parts[2] ?? 0);
    }

    private function periodValues(array $d, array $c): array
    {
        $first = $d['groups'][0] ?? null;

        return [
            'title' => $d['title'],
            'short_name' => $d['short_name'],
            'sort_order' => $d['sort_order'],
            'used_for_attendance' => ! empty($d['used_for_attendance']) ? 'Yes' : '',
            'academic_section_id' => $d['academic_section_id'] ?? null,
            'academic_year_id' => $d['academic_year_id'] ?? 0,
            'start_time' => $first ? $this->full($first['start_time']) : '00:00:00',
            'end_time' => $first ? $this->full($first['end_time']) : '00:00:00',
            'length' => null,
            'sub_institute_id' => $c['institute_id'],
            'marking_period_id' => $c['term_id'] ?? null,
            'status' => '1',
        ];
    }

    private function full(string $t): string
    {
        return strlen($t) === 5 ? $t.':00' : $t;
    }

    /** Title unique per institute (case-insensitive, ignoring itself); a standard appears in one group only. */
    private function assertValid(array $d, int $inst, ?int $ignoreId): void
    {
        $dup = DB::table('period')->where('sub_institute_id', $inst)->whereRaw('UPPER(title) = ?', [strtoupper($d['title'])]);
        if ($ignoreId) {
            $dup->where('id', '!=', $ignoreId);
        }
        if ($dup->exists()) {
            throw ValidationException::withMessages(['title' => [self::DUPLICATE]]);
        }

        $seen = [];
        $owned = DB::table('standard')->where('sub_institute_id', $inst)->pluck('id')->map(fn ($v) => (int) $v)->all();
        foreach ($d['groups'] ?? [] as $i => $g) {
            foreach ($g['standard_ids'] as $std) {
                if (! in_array((int) $std, $owned, true)) {
                    throw ValidationException::withMessages(["groups.$i.standard_ids" => ['Please select a Standard.']]);
                }
                if (isset($seen[(int) $std])) {
                    throw ValidationException::withMessages(["groups.$i.standard_ids" => ['A standard can only be in one timing group.']]);
                }
                $seen[(int) $std] = true;
            }
        }
        if ($d['academic_section_id'] ?? null) {
            if (! DB::table('academic_section')->where(['id' => $d['academic_section_id'], 'sub_institute_id' => $inst])->exists()) {
                throw ValidationException::withMessages(['academic_section_id' => ['Please select an Academic Section.']]);
            }
        }
    }

    /** Make period_details match the groups: update changed rows, add new ones, drop standards that were removed. */
    private function syncDetails(int $periodId, array $groups, array $c): void
    {
        $inst = $c['institute_id'];
        $want = [];
        foreach ($groups as $g) {
            $start = $this->full($g['start_time']);
            $end = $this->full($g['end_time']);
            $length = abs($this->seconds($start) - $this->seconds($end)) / 60;
            foreach ($g['standard_ids'] as $std) {
                $want[(int) $std] = ['start_time' => $start, 'end_time' => $end, 'length' => $length];
            }
        }

        $have = DB::table('period_details')->where(['period_id' => $periodId, 'sub_institute_id' => $inst])->get()->keyBy(fn ($r) => (int) $r->standard_id);
        $removed = $have->keys()->diff(array_keys($want))->all();
        if ($removed) {
            DB::table('period_details')->where(['period_id' => $periodId, 'sub_institute_id' => $inst])->whereIn('standard_id', $removed)->delete();
        }
        foreach ($want as $std => $v) {
            if ($have->has($std)) {
                DB::table('period_details')->where('id', $have[$std]->id)->update($v + ['updated_by' => $c['user_id'], 'updated_at' => now()]);
            } else {
                DB::table('period_details')->insert($v + ['period_id' => $periodId, 'standard_id' => $std, 'sub_institute_id' => $inst, 'created_by' => $c['user_id'], 'created_at' => now()]);
            }
        }
    }
}
