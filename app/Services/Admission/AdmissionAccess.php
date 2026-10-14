<?php

namespace App\Services\Admission;

/**
 * Maps the logged-in ERP profile (tbluserprofilemaster.name in session) to an admission role and
 * answers capability questions. This is the route-level authorisation layer for the admission
 * actions; menu rights (tblgroupwise_rights) still drive menu visibility and the menu-linked screens.
 * Keywords per role come from config('admission.role_keywords').
 */
class AdmissionAccess
{
    public const ADMIN = 'admin';
    public const OFFICER = 'officer';
    public const ACCOUNTS = 'accounts';
    public const APPROVER = 'approver';

    /** capability => roles allowed (ADMIN is always allowed). */
    public const CAPABILITIES = [
        'view'      => [self::OFFICER, self::ACCOUNTS, self::APPROVER],
        'manage'    => [self::OFFICER],                  // convert, submit, reopen, upload, evaluate eligibility
        'documents' => [self::OFFICER],                  // verify / reject documents
        'decide'    => [self::OFFICER, self::APPROVER],  // selection decisions
        'offer'     => [self::OFFICER],
        'fees'      => [self::OFFICER, self::ACCOUNTS],
        'confirm'   => [self::OFFICER],
        'override'  => [],                               // eligibility / confirmation overrides: admin only
        'waive'     => [],
        'settings'  => [],
    ];

    public static function role(): ?string
    {
        $profile = strtolower(trim((string) session()->get('user_profile_name')));
        if ($profile === '') {
            return null;
        }

        // Most specific first, so "Admission Approver" is an approver and not an officer.
        foreach (['admin', 'approver', 'accounts', 'officer'] as $role) {
            foreach (config('admission.role_keywords.'.$role, []) as $keyword) {
                if ($keyword !== '' && str_contains($profile, $keyword)) {
                    return $role;
                }
            }
        }

        return null;
    }

    public static function can(string $capability): bool
    {
        $role = self::role();
        if ($role === null) {
            return false;
        }

        return $role === self::ADMIN || in_array($role, self::CAPABILITIES[$capability] ?? [], true);
    }

    /** True if the user holds at least one of the capabilities. */
    public static function canAny(array $capabilities): bool
    {
        foreach ($capabilities as $capability) {
            if (self::can($capability)) {
                return true;
            }
        }

        return false;
    }
}
