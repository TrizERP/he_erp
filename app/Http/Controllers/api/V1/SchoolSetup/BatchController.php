<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Batches, Blade batchController. Batches belong to a standard + division for the current academic year; the list
 * and the edit / delete actions work on that group.
 */
class BatchController extends BaseApiController
{
    use PaginatesLists;

    private const DUPLICATE = 'Batch Already Exist';

    /** One row per standard + division with its batch titles. */
    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $q = DB::table('batch as b')
            ->join('standard as s', 's.id', '=', 'b.standard_id')
            ->join('division as d', 'd.id', '=', 'b.division_id')
            ->where('b.sub_institute_id', $c['institute_id'])->where('b.syear', $c['syear'])
            ->groupBy('b.standard_id', 'b.division_id', 's.name', 'd.name')
            ->select('b.standard_id', 'b.division_id', 's.name as standard_name', 'd.name as division_name', DB::raw('count(*) as batch_count'));

        $response = $this->listPage(
            $request,
            $q,
            ['standard_id' => 'b.standard_id', 'standard' => 's.name', 'division' => 'd.name', 'batch_count' => 'batch_count'],
            ['s.name', 'd.name', 'b.title'],
            'standard_id',
            fn ($r) => ['standard_id' => (int) $r->standard_id, 'division_id' => (int) $r->division_id, 'standard_name' => $r->standard_name, 'division_name' => $r->division_name, 'batch_count' => (int) $r->batch_count, 'titles' => []],
        );

        // fill in the titles for the groups on this page (kept out of the grouped SQL so commas in titles are safe)
        $payload = $response->getData(true);
        foreach ($payload['data'] as &$g) {
            $g['titles'] = DB::table('batch')->where($this->scope($c) + ['standard_id' => $g['standard_id'], 'division_id' => $g['division_id']])
                ->orderBy('id')->pluck('title')->all();
        }
        unset($g);

        return $this->ok($payload['data'], 'Success', $payload['meta']);
    }

    /** GET batches/group?standard_id=&division_id= : the batches of one group (for the edit form). */
    public function group(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        [$std, $div] = $this->groupKey($request);
        $rows = DB::table('batch')->where($this->scope($c) + ['standard_id' => $std, 'division_id' => $div])->orderBy('id')->get(['id', 'title']);
        abort_if($rows->isEmpty(), 404);

        return $this->ok(['standard_id' => $std, 'division_id' => $div, 'batches' => $rows->map(fn ($r) => ['id' => (int) $r->id, 'title' => $r->title])->all()]);
    }

    /** POST { standard_id, division_id, titles: [..] }: titles that already exist in the group are skipped. */
    public function store(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $this->validated($request, true);
        $this->assertOwned($d['standard_id'], $d['division_id'], $c['institute_id']);

        $created = [];
        $skipped = [];
        foreach ($this->clean($d['titles']) as $title) {
            if ($this->exists($c, $d['standard_id'], $d['division_id'], $title)) {
                $skipped[] = $title;
                continue;
            }
            DB::table('batch')->insert($this->scope($c) + ['title' => $title, 'standard_id' => $d['standard_id'], 'division_id' => $d['division_id'], 'created_at' => now(), 'updated_at' => now()]);
            $created[] = $title;
        }
        if (! $created) {
            throw ValidationException::withMessages(['titles' => [self::DUPLICATE]]);
        }

        return $this->ok(['created' => $created, 'skipped' => $skipped], 'Batch Added Successfully', null, 201);
    }

    /** PUT { standard_id, division_id, new: [titles], edit: { "<batch id>": "<new title>" } } */
    public function updateGroup(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $d = $request->validate([
            'standard_id' => 'required|integer',
            'division_id' => 'required|integer',
            'new' => 'nullable|array',
            'new.*' => 'nullable|string|max:255',
            'edit' => 'nullable|array',
            'edit.*' => 'required|string|max:255',
        ]);
        $this->assertOwned((int) $d['standard_id'], (int) $d['division_id'], $c['institute_id']);
        $std = (int) $d['standard_id'];
        $div = (int) $d['division_id'];

        DB::transaction(function () use ($c, $d, $std, $div) {
            foreach ($d['edit'] ?? [] as $id => $title) {
                $title = trim($title);
                $row = DB::table('batch')->where($this->scope($c) + ['id' => (int) $id, 'standard_id' => $std, 'division_id' => $div])->first();
                if (! $row) {
                    throw ValidationException::withMessages(["edit.$id" => ['Batch not found.']]);
                }
                if ($title === '' || DB::table('batch')->where($this->scope($c) + ['standard_id' => $std, 'division_id' => $div])
                    ->whereRaw('UPPER(title) = ?', [strtoupper($title)])->where('id', '!=', $row->id)->exists()) {
                    throw ValidationException::withMessages(["edit.$id" => [$title === '' ? 'Title is required' : self::DUPLICATE]]);
                }
                DB::table('batch')->where('id', $row->id)->update(['title' => $title, 'updated_at' => now()]);
            }
            foreach ($this->clean($d['new'] ?? []) as $title) {
                if (! $this->exists($c, $std, $div, $title)) {
                    DB::table('batch')->insert($this->scope($c) + ['title' => $title, 'standard_id' => $std, 'division_id' => $div, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });

        return $this->ok(null, 'Batch Updated Successfully');
    }

    /** DELETE batches/group?standard_id=&division_id= : removes every batch of the group (Blade destroy). */
    public function destroyGroup(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        [$std, $div] = $this->groupKey($request);
        DB::table('batch')->where($this->scope($c) + ['standard_id' => $std, 'division_id' => $div])->delete();

        return $this->ok(null, 'Batch Deleted Successfully');
    }

    /** DELETE batches/{id} : one batch (Blade ajaxdestroy). */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        abort_if(! DB::table('batch')->where(['id' => $id, 'sub_institute_id' => $c['institute_id']])->delete(), 404);

        return $this->ok(null, 'Batch Deleted Successfully');
    }

    // ---------------------------------------------------------------- helpers

    private function scope(array $c): array
    {
        return ['sub_institute_id' => $c['institute_id'], 'syear' => $c['syear']];
    }

    private function groupKey(Request $request): array
    {
        $v = $request->validate(['standard_id' => 'required|integer', 'division_id' => 'required|integer']);

        return [(int) $v['standard_id'], (int) $v['division_id']];
    }

    private function validated(Request $request, bool $titlesRequired): array
    {
        return $request->validate([
            'standard_id' => 'required|integer',
            'division_id' => 'required|integer',
            'titles' => ($titlesRequired ? 'required' : 'nullable').'|array|min:1',
            'titles.*' => 'nullable|string|max:255',
        ]) + ['titles' => []];
    }

    /** Trim, drop blanks, and drop case-insensitive repeats inside the request. */
    private function clean(array $titles): array
    {
        $out = [];
        foreach ($titles as $t) {
            $t = trim((string) $t);
            if ($t !== '') {
                $out[strtoupper($t)] ??= $t;
            }
        }

        return array_values($out);
    }

    private function exists(array $c, int $std, int $div, string $title): bool
    {
        return DB::table('batch')->where($this->scope($c) + ['standard_id' => $std, 'division_id' => $div])
            ->whereRaw('UPPER(title) = ?', [strtoupper($title)])->exists();
    }

    private function assertOwned(int $std, int $div, int $inst): void
    {
        if (! DB::table('standard')->where(['id' => $std, 'sub_institute_id' => $inst])->exists()) {
            throw ValidationException::withMessages(['standard_id' => ['Please select a Standard.']]);
        }
        if (! DB::table('division')->where(['id' => $div, 'sub_institute_id' => $inst])->exists()) {
            throw ValidationException::withMessages(['division_id' => ['Please select a Division.']]);
        }
    }
}
