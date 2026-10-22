<?php

namespace App\Http\Controllers\pbas;

use App\Http\Controllers\Controller;
use App\Services\Pbas\PbasAccess;
use App\Services\Pbas\PbasEntries;
use App\Services\Pbas\PbasLookups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * One controller for all PBAS data-entry screens (teaching, departmental, institute, society). Each route sets
 * `slug`; the screen definition comes from PbasEntries. Faculty only ever touch their own rows.
 */
class PbasEntryController extends Controller
{
    public function index(Request $request)
    {
        [$slug, $def] = $this->definition($request);
        $uid = PbasAccess::userId();

        $rows = DB::table($def['table'])
            ->where(['sub_institute_id' => PbasAccess::instituteId(), 'user_id' => $uid])
            ->orderBy('id')
            ->get();

        return view('pbas.entry', [
            'slug' => $slug,
            'def' => $def,
            'rows' => $rows,
            'years' => PbasLookups::years(),
            'currentYear' => PbasLookups::currentYear(),
            'locked' => PbasAccess::isLocked($uid),
        ]);
    }

    public function store(Request $request)
    {
        [$slug, $def] = $this->definition($request);
        $uid = PbasAccess::userId();

        if (PbasAccess::isLocked($uid)) {
            return $this->back($slug, 'pbas_error', 'Your data is locked by HOD. You cannot add records.');
        }

        // Same rule as the original: any posted field that is empty() rejects the whole form. A radio group the
        // user left untouched is not posted at all, so it passes and is stored as an empty string.
        foreach ($def['fields'] as $field) {
            if ($request->has($field['name']) && empty($request->input($field['name']))) {
                return $this->back($slug, 'pbas_error', 'All Fields are required', true);
            }
        }

        $values = [];
        foreach ($def['fields'] as $field) {
            $value = $request->input($field['name']);
            $value = is_scalar($value) ? (string) $value : '';
            if (! empty($field['int']) && ! is_numeric($value)) {
                // the original died with an SQL error on a non-numeric value in an integer column
                return $this->back($slug, 'pbas_error', 'Problem in insertion. Try Again!', true);
            }
            $values[$field['column'] ?? $field['name']] = $value;
        }

        DB::table($def['table'])->insert($values + [
            'sub_institute_id' => PbasAccess::instituteId(),
            'user_id' => $uid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->back($slug, 'pbas_success', 'successfully eneterd data!');
    }

    public function destroy(Request $request)
    {
        [$slug, $def] = $this->definition($request);
        $uid = PbasAccess::userId();

        if (PbasAccess::isLocked($uid)) {
            return $this->back($slug, 'pbas_error', 'Your data is locked by HOD. You cannot delete records.');
        }

        $ids = array_filter((array) $request->input($def['checkbox'], []), 'is_numeric');
        if ($ids) {
            DB::table($def['table'])
                ->where(['sub_institute_id' => PbasAccess::instituteId(), 'user_id' => $uid])
                ->whereIn('id', $ids)
                ->delete();
        }

        return redirect()->route("pbas.{$slug}.index");
    }

    /** @return array{0:string,1:array} */
    protected function definition(Request $request): array
    {
        $slug = (string) $request->route('slug');
        $def = PbasEntries::find($slug);
        abort_if($def === null, 404);

        return [$slug, $def];
    }

    protected function back(string $slug, string $flash, string $message, bool $withInput = false)
    {
        $redirect = redirect()->route("pbas.{$slug}.index")->with($flash, $message);

        return $withInput ? $redirect->withInput() : $redirect;
    }
}
