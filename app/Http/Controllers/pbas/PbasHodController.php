<?php

namespace App\Http\Controllers\pbas;

use App\Http\Controllers\Controller;
use App\Services\Pbas\PbasAccess;
use App\Services\Pbas\PbasFaculty;
use App\Services\Pbas\PbasLookups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD Activities > Lock/UnLock Data (original hod_comp.php and comment.php): the HOD sees the faculty of their
 * own department, can lock/unlock a member's data and leave comments. Principal and admin use the same screens.
 */
class PbasHodController extends Controller
{
    public function index()
    {
        $institute = PbasAccess::instituteId();
        $dept = PbasAccess::departmentOf(PbasAccess::userId());
        $departments = PbasLookups::departments($institute);

        $faculty = $dept ? PbasFaculty::inDepartment($institute, $dept) : collect();
        $ids = $faculty->pluck('id')->all();

        $locks = DB::table('pbas_locks')->where('sub_institute_id', $institute)->whereIn('user_id', $ids)->pluck('flag', 'user_id');
        // the original showed the first comment ever written for the member
        $comments = DB::table('pbas_comments')->where('sub_institute_id', $institute)->whereIn('user_id', $ids)->orderBy('id')->get()
            ->groupBy('user_id')->map(fn ($rows) => $rows->first()->comment);

        return view('pbas.hod', [
            'departmentName' => $departments[$dept] ?? '',
            'faculty' => $faculty,
            'locks' => $locks,
            'comments' => $comments,
            'years' => PbasLookups::years(),
            'currentYear' => PbasLookups::currentYear(),
        ]);
    }

    public function lock(Request $request)
    {
        return $this->setLock($request, 1);
    }

    public function unlock(Request $request)
    {
        return $this->setLock($request, 0);
    }

    protected function setLock(Request $request, int $flag)
    {
        $facultyId = (int) $request->input('facultyid');
        abort_unless(PbasAccess::canManage($facultyId), 403, 'You are not authorised to lock or unlock this faculty member\'s data.');

        DB::table('pbas_locks')->updateOrInsert(
            ['sub_institute_id' => PbasAccess::instituteId(), 'user_id' => $facultyId],
            ['flag' => $flag, 'changed_by' => PbasAccess::userId(), 'updated_at' => now()]
        );

        $name = (string) ($request->input('facultyname') ?: PbasFaculty::name(PbasAccess::instituteId(), $facultyId));

        return redirect()->route('pbas.hod.index')->with(
            $flag ? 'pbas_locked' : 'pbas_unlocked',
            ($flag ? 'Data Locked Succesfully for : ' : 'Data Un-Locked Succesfully for : ').$name
        );
    }

    /** The comment form for one faculty member (opened in a new tab from the list). */
    public function comment(Request $request)
    {
        $facultyId = (int) $request->input('username');
        abort_unless(PbasAccess::canManage($facultyId), 403, 'You are not authorised to comment on this faculty member.');
        $year = (string) $request->input('year');

        return view('pbas.comment', [
            'facultyId' => $facultyId,
            'year' => $year,
            'comments' => DB::table('pbas_comments')
                ->where(['sub_institute_id' => PbasAccess::instituteId(), 'user_id' => $facultyId, 'year' => $year])
                ->orderBy('id')->get(),
        ]);
    }

    public function storeComment(Request $request)
    {
        $facultyId = (int) $request->input('username');
        abort_unless(PbasAccess::canManage($facultyId), 403, 'You are not authorised to comment on this faculty member.');
        $year = (string) $request->input('year');
        $back = redirect()->route('pbas.hod.comment', ['username' => $facultyId, 'year' => $year]);

        // original rule: every posted field must be non-empty
        foreach (['username', 'year', 'commentsta'] as $field) {
            if (empty($request->input($field))) {
                return $back->with('pbas_error', 'All Fields are required');
            }
        }

        DB::table('pbas_comments')->insert([
            'sub_institute_id' => PbasAccess::instituteId(),
            'user_id' => $facultyId,
            'year' => $year,
            'comment' => (string) $request->input('commentsta'),
            'commented_by' => PbasAccess::userId(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $back->with('pbas_success', 'successfully eneterd comment!');
    }
}
