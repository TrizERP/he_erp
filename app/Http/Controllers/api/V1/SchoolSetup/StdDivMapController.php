<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semester (standard) <-> Division mapping, Blade std_divController. The screen is one grid; saving replaces the
 * institute's whole mapping, now in a single transaction.
 */
class StdDivMapController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->ok($this->payload($request->attributes->get('api_context')['institute_id']));
    }

    /** POST body: { mapping: { "<standard_id>": [division_id, ...], ... } } */
    public function save(Request $request): JsonResponse
    {
        $inst = $request->attributes->get('api_context')['institute_id'];
        $data = $request->validate([
            'mapping' => 'present|array',
            'mapping.*' => 'array',
            'mapping.*.*' => 'integer',
        ]);

        $standards = DB::table('standard')->where('sub_institute_id', $inst)->pluck('id')->map(fn ($v) => (int) $v)->all();
        $divisions = DB::table('division')->where('sub_institute_id', $inst)->pluck('id')->map(fn ($v) => (int) $v)->all();

        $rows = [];
        foreach ($data['mapping'] as $std => $divs) {
            if (! in_array((int) $std, $standards, true)) {
                throw ValidationException::withMessages(['mapping' => ['Unknown standard.']]);
            }
            foreach (array_unique($divs) as $div) {
                if (! in_array((int) $div, $divisions, true)) {
                    throw ValidationException::withMessages(['mapping' => ['Unknown division.']]);
                }
                $rows[] = ['standard_id' => (int) $std, 'division_id' => (int) $div, 'sub_institute_id' => $inst, 'created_at' => now(), 'updated_at' => now()];
            }
        }

        DB::transaction(function () use ($inst, $rows) {
            DB::table('std_div_map')->where('sub_institute_id', $inst)->delete();
            if ($rows) {
                DB::table('std_div_map')->insert($rows);
            }
        });

        return $this->ok($this->payload($inst), 'Division Mapped Successfully.');
    }

    private function payload(int $inst): array
    {
        $mapping = [];
        foreach (DB::table('std_div_map')->where('sub_institute_id', $inst)->orderBy('id')->get(['standard_id', 'division_id']) as $r) {
            $mapping[(string) $r->standard_id][] = (int) $r->division_id;
        }

        return [
            'standards' => DB::table('standard')->where('sub_institute_id', $inst)->orderBy('id')->get(['id', 'name'])
                ->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'divisions' => DB::table('division')->where('sub_institute_id', $inst)->orderBy('id')->get(['id', 'name'])
                ->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'mapping' => (object) $mapping,
        ];
    }
}
