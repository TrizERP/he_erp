<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementStudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    /** Academic facts a coordinator verifies; changing one sends the profile back to pending. */
    public const VERIFIED_FIELDS = ['cgpa', 'tenth_percent', 'twelfth_percent', 'active_backlogs', 'passing_year'];

    protected AuditService $audit;

    public function __construct(AuditService $audit)
    {
        $this->audit = $audit;
    }

    public function forStudent($studentId): PlacementStudentProfile
    {
        $profile = PlacementStudentProfile::firstOrNew(['student_id' => $studentId]);
        if (! $profile->exists) {
            $profile->student_id = $studentId;
            $profile->save();
        }

        return $profile;
    }

    /** Weighted checklist, 0-100. */
    public function completeness(PlacementStudentProfile $profile): int
    {
        $score = ($profile->cgpa !== null ? 15 : 0)
            + ($profile->tenth_percent !== null ? 10 : 0)
            + ($profile->twelfth_percent !== null ? 10 : 0)
            + ($profile->passing_year !== null ? 5 : 0)
            + (! empty($profile->resume_file) ? 20 : 0)
            + ($profile->skills()->count() >= 3 ? 15 : 0)
            + ((! empty($profile->projects) || ! empty($profile->certifications)) ? 10 : 0)
            + ((! empty($profile->linkedin_url) || ! empty($profile->github_url) || ! empty($profile->portfolio_url)) ? 10 : 0)
            + (! empty($profile->preferences) ? 5 : 0);

        return min(100, $score);
    }

    public function refreshCompleteness(PlacementStudentProfile $profile): PlacementStudentProfile
    {
        $profile->completeness = $this->completeness($profile);
        $profile->save();

        return $profile;
    }

    /** True when the update touched a verified academic field. */
    public function touchesVerifiedField(PlacementStudentProfile $profile): bool
    {
        return (bool) array_intersect(self::VERIFIED_FIELDS, array_keys($profile->getDirty()));
    }

    public function register(PlacementStudentProfile $profile): PlacementStudentProfile
    {
        if ($profile->isDebarred()) {
            throw ValidationException::withMessages(['registration' => 'You are currently debarred from placements.']);
        }

        return DB::transaction(function () use ($profile) {
            $from = $profile->registration_status;
            $profile->registration_status = 'registered';
            $profile->undertaking_accepted_at = now();
            $profile->save();
            $this->audit->log('student_profile', $profile->id, 'register', ['registration_status' => $from], ['registration_status' => 'registered']);

            return $profile;
        });
    }

    public function optOut(PlacementStudentProfile $profile): PlacementStudentProfile
    {
        return DB::transaction(function () use ($profile) {
            $from = $profile->registration_status;
            $profile->registration_status = 'opted_out';
            $profile->save();
            $this->audit->log('student_profile', $profile->id, 'opt_out', ['registration_status' => $from], ['registration_status' => 'opted_out']);

            return $profile;
        });
    }

    public function decide(PlacementStudentProfile $profile, string $status, ?string $remarks): PlacementStudentProfile
    {
        if (! in_array($status, ['verified', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid verification decision.']);
        }
        if ($status === 'rejected' && trim((string) $remarks) === '') {
            throw ValidationException::withMessages(['remarks' => 'Remarks are required when rejecting a profile.']);
        }

        return DB::transaction(function () use ($profile, $status, $remarks) {
            $from = $profile->verification_status;
            $profile->verification_status = $status;
            $profile->verification_remarks = $remarks;
            $profile->verified_by = session('user_id');
            $profile->verified_at = now();
            $profile->save();
            $this->audit->log('student_profile', $profile->id, 'verification', ['verification_status' => $from], ['verification_status' => $status], $remarks);

            return $profile;
        });
    }
}
