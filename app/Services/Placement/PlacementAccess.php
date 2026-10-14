<?php

namespace App\Services\Placement;

/**
 * Resolves the placement role of the logged-in ERP user from the session profile name
 * (tbluserprofilemaster.name). Menu visibility is still driven by tblgroupwise_rights;
 * this adds the route-level authorization the menu middleware does not provide.
 */
class PlacementAccess
{
    public const ADMIN = 'admin';          // ERP admin / super admin: unrestricted
    public const PRINCIPAL = 'principal';  // read-everything + policy approval
    public const TPO = 'tpo';              // placement officer: full CRUD
    public const COORDINATOR = 'coordinator';
    public const STUDENT = 'student';

    public static function role(): ?string
    {
        $profile = strtolower(trim((string) session()->get('user_profile_name')));

        if ($profile === '') {
            return null;
        }
        if (str_contains($profile, 'coordinator') && str_contains($profile, 'placement')) {
            return self::COORDINATOR;
        }
        if (str_contains($profile, 'student')) {
            return self::STUDENT;
        }
        if ($profile === 'tpo' || str_contains($profile, 'placement')) {
            return self::TPO;
        }
        if (str_contains($profile, 'principal')) {
            return self::PRINCIPAL;
        }
        if (str_contains($profile, 'admin')) {
            return self::ADMIN;
        }

        return null;
    }

    public static function allows(array $roles): bool
    {
        $role = self::role();

        return $role !== null && ($role === self::ADMIN || in_array($role, $roles, true));
    }
}
