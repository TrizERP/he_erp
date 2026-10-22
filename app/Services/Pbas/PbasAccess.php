<?php

namespace App\Services\Pbas;

use Illuminate\Support\Facades\DB;

/**
 * Maps the logged-in ERP user to the three roles of the original PBAS login ("Login As": faculty / hod /
 * principal) from the session profile name (tbluserprofilemaster.name). Menu visibility still comes from
 * tblgroupwise_rights; this adds the route-level check and the department scoping the original did with
 * `personal.department`. ERP admins pass every check.
 */
class PbasAccess
{
    public const ADMIN = 'admin';
    public const PRINCIPAL = 'principal';
    public const HOD = 'hod';
    public const FACULTY = 'faculty';

    public static function role(): ?string
    {
        $profile = strtolower(trim((string) session()->get('user_profile_name')));
        if ($profile === '') {
            return null;
        }
        if (in_array($profile, ['super admin', 'admin'], true)) {
            return self::ADMIN;
        }
        if (str_contains($profile, 'principal')) {
            return self::PRINCIPAL;
        }
        if ($profile === 'hod' || str_contains($profile, 'head of')) {
            return self::HOD;
        }
        if (str_contains($profile, 'student') || str_contains($profile, 'parent')) {
            return null;
        }

        return self::FACULTY;
    }

    /** Every logged-in staff member has the faculty screens; HOD/Principal also see their own. */
    public static function allows(array $roles): bool
    {
        $role = self::role();
        if ($role === null) {
            return false;
        }
        if ($role === self::ADMIN) {
            return true;
        }
        // HOD and Principal are faculty too (the original sidebar showed them every faculty menu).
        if (in_array(self::FACULTY, $roles, true) && in_array($role, [self::HOD, self::PRINCIPAL], true)) {
            return true;
        }

        return in_array($role, $roles, true);
    }

    public static function instituteId(): int
    {
        return (int) session()->get('sub_institute_id');
    }

    public static function userId(): int
    {
        return (int) session()->get('user_id');
    }

    /** Department (hrms_departments.id) of a user: the PBAS profile choice, else the HRMS assignment. */
    public static function departmentOf(int $userId, ?int $instituteId = null): ?int
    {
        $instituteId ??= self::instituteId();
        $fromProfile = DB::table('pbas_profiles')->where(['sub_institute_id' => $instituteId, 'user_id' => $userId])->value('department_id');
        if ($fromProfile) {
            return (int) $fromProfile;
        }
        $fromUser = DB::table('tbluser')->where(['sub_institute_id' => $instituteId, 'id' => $userId])->value('department_id');

        return $fromUser ? (int) $fromUser : null;
    }

    /**
     * May the current user look at / act on this faculty member's data?
     * Own data always; HOD within their own department; Principal and admin for everyone in the institute.
     */
    public static function canManage(int $facultyUserId): bool
    {
        $role = self::role();
        $exists = DB::table('tbluser')->where(['sub_institute_id' => self::instituteId(), 'id' => $facultyUserId])->exists();
        if (! $exists) {
            return false;
        }
        if (in_array($role, [self::ADMIN, self::PRINCIPAL], true)) {
            return true;
        }
        if ($facultyUserId === self::userId()) {
            return true;
        }
        if ($role === self::HOD) {
            $dept = self::departmentOf(self::userId());

            return $dept !== null && $dept === self::departmentOf($facultyUserId);
        }

        return false;
    }

    /** Is this faculty member's data locked by the HOD / Principal? (the original `flag` table) */
    public static function isLocked(int $userId): bool
    {
        return (int) DB::table('pbas_locks')->where(['sub_institute_id' => self::instituteId(), 'user_id' => $userId])->value('flag') === 1;
    }
}
