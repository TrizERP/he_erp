<?php

namespace App\Http\Controllers\pbas;

use App\Http\Controllers\Controller;
use App\Services\Pbas\PbasAccess;
use App\Services\Pbas\PbasFaculty;
use App\Services\Pbas\PbasLookups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Principal > View data and input ACR (original principal_acr.php / acr.php): pick a department, see its faculty
 * with HOD comments and lock status, open a member's API Form and enter the Annual Confidential Report marks
 * for the current academic year.
 */
class PbasPrincipalController extends Controller
{
    protected const MARKS = ['punctuality', 'innovative', 'dedication', 'inp', 'performance'];

    public function index(Request $request)
    {
        $institute = PbasAccess::instituteId();
        $departments = PbasLookups::departments($institute);
        $dept = $request->filled('e') && array_key_exists((int) $request->input('e'), $departments) ? (int) $request->input('e') : null;
        $year = PbasLookups::currentYear();

        $faculty = $dept ? PbasFaculty::inDepartment($institute, $dept) : collect();
        $ids = $faculty->pluck('id')->all();

        return view('pbas.principal', [
            'departments' => $departments,
            'dept' => $dept,
            'year' => $year,
            'faculty' => $faculty,
            'locks' => DB::table('pbas_locks')->where('sub_institute_id', $institute)->whereIn('user_id', $ids)->pluck('flag', 'user_id'),
            'comments' => DB::table('pbas_comments')->where('sub_institute_id', $institute)->whereIn('user_id', $ids)->orderBy('id')->get()->groupBy('user_id'),
            'acr' => DB::table('pbas_acr')->where(['sub_institute_id' => $institute, 'year' => $year])->whereIn('user_id', $ids)->get()->keyBy('user_id'),
        ]);
    }

    /** ACR entry form for one faculty member. */
    public function acr(Request $request)
    {
        $facultyId = (int) $request->input('facultyid');
        abort_unless(PbasAccess::canManage($facultyId), 403, 'You are not authorised to enter ACR for this faculty member.');

        return $this->renderAcr($facultyId, (string) $request->input('moveback'));
    }

    public function storeAcr(Request $request)
    {
        $facultyId = (int) $request->input('facultyid');
        abort_unless(PbasAccess::canManage($facultyId), 403, 'You are not authorised to enter ACR for this faculty member.');
        $institute = PbasAccess::instituteId();
        $year = PbasLookups::currentYear();
        $back = redirect()->route('pbas.principal.acr', ['facultyid' => $facultyId, 'moveback' => (string) $request->input('moveback')]);

        // required fields; unlike the original a mark of 0 is accepted (a blank is not)
        $marks = [];
        foreach (self::MARKS as $field) {
            $value = trim((string) $request->input($field));
            if ($value === '') {
                return $back->withInput()->with('pbas_error', 'All Fields are required');
            }
            $marks[$field] = $value;
        }

        $key = ['sub_institute_id' => $institute, 'user_id' => $facultyId, 'year' => $year];
        $name = PbasFaculty::name($institute, $facultyId);
        if (DB::table('pbas_acr')->where($key)->exists()) {
            DB::table('pbas_acr')->where($key)->update($marks + ['entered_by' => PbasAccess::userId(), 'updated_at' => now()]);
            $message = 'Data updated Succesfully for : '.$name.' for year '.$year;
        } else {
            DB::table('pbas_acr')->insert($key + $marks + ['entered_by' => PbasAccess::userId(), 'created_at' => now(), 'updated_at' => now()]);
            $message = 'Data inserted sucessfully for : '.$name.' for year '.$year;
        }

        return $back->with('pbas_success', $message);
    }

    protected function renderAcr(int $facultyId, string $moveback)
    {
        $institute = PbasAccess::instituteId();
        $year = PbasLookups::currentYear();

        return view('pbas.acr', [
            'facultyId' => $facultyId,
            'facultyName' => PbasFaculty::name($institute, $facultyId),
            'year' => $year,
            'moveback' => $moveback,
            'acr' => DB::table('pbas_acr')->where(['sub_institute_id' => $institute, 'user_id' => $facultyId, 'year' => $year])->first(),
            'comments' => DB::table('pbas_comments')
                ->where(['sub_institute_id' => $institute, 'user_id' => $facultyId, 'year' => $year])->orderBy('id')->get(),
        ]);
    }
}
