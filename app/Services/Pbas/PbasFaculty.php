<?php

namespace App\Services\Pbas;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Faculty lists for the HOD / Principal screens. The original listed rows of its own `personal` table; here the
 * list is the institute's active staff (tbluser, not students/parents) whose department - the PBAS profile choice,
 * else their HRMS department - is the one asked for.
 */
class PbasFaculty
{
    /** @return Collection<int, object{id:int, code:string, name:string}> */
    public static function inDepartment(int $instituteId, int $departmentId): Collection
    {
        return DB::table('tbluser as u')
            ->leftJoin('pbas_profiles as p', function ($join) {
                $join->on('p.user_id', '=', 'u.id')->on('p.sub_institute_id', '=', 'u.sub_institute_id');
            })
            ->leftJoin('tbluserprofilemaster as m', 'm.id', '=', 'u.user_profile_id')
            ->where('u.sub_institute_id', $instituteId)
            ->where('u.status', 1)
            ->whereRaw('COALESCE(p.department_id, u.department_id) = ?', [$departmentId])
            ->whereRaw("LOWER(COALESCE(m.name, '')) NOT IN ('student', 'parent')")
            ->orderBy('u.first_name')->orderBy('u.last_name')
            ->get(['u.id', 'u.employee_id', 'u.user_name', 'u.first_name', 'u.middle_name', 'u.last_name', 'p.facultyname'])
            ->map(fn ($u) => (object) [
                'id' => (int) $u->id,
                'code' => (string) ($u->employee_id ?: $u->user_name),
                'name' => $u->facultyname ?: trim(implode(' ', array_filter([$u->first_name, $u->middle_name, $u->last_name]))),
            ]);
    }

    public static function name(int $instituteId, int $userId): string
    {
        $u = DB::table('tbluser')->where(['sub_institute_id' => $instituteId, 'id' => $userId])->first();
        if (! $u) {
            return '';
        }
        $saved = DB::table('pbas_profiles')->where(['sub_institute_id' => $instituteId, 'user_id' => $userId])->value('facultyname');

        return (string) ($saved ?: trim(implode(' ', array_filter([$u->first_name, $u->middle_name, $u->last_name]))));
    }
}
