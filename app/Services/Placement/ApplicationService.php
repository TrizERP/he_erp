<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveRole;
use App\Models\placement\PlacementStudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public const TRANSITIONS = [
        'applied'     => ['shortlisted', 'rejected', 'withdrawn'],
        'shortlisted' => ['rejected', 'withdrawn', 'applied'],
        'rejected'    => ['applied'],
        'withdrawn'   => [],
        'selected'    => [],
    ];

    protected PolicyService $policy;
    protected AuditService $audit;

    public function __construct(PolicyService $policy, AuditService $audit)
    {
        $this->policy = $policy;
        $this->audit = $audit;
    }

    /**
     * Student applies (or a TPO applies on their behalf with an override reason).
     * Without an override, every policy violation is reported back to the student.
     */
    public function apply(PlacementStudentProfile $profile, PlacementDrive $drive, PlacementDriveRole $role, ?string $overrideReason = null): PlacementApplication
    {
        if ($role->drive_id !== $drive->id) {
            throw ValidationException::withMessages(['role_id' => 'This role does not belong to the drive.']);
        }

        $violations = $this->policy->checkApply($profile, $drive, $role);

        if ($violations && trim((string) $overrideReason) === '') {
            throw ValidationException::withMessages(['application' => $violations]);
        }

        return DB::transaction(function () use ($profile, $drive, $role, $violations, $overrideReason) {
            // A previous withdrawn/rejected row is reused so the unique key stays valid.
            $application = PlacementApplication::firstOrNew([
                'drive_id' => $drive->id, 'role_id' => $role->id, 'student_id' => $profile->student_id,
            ]);
            $application->fill(['status' => 'applied', 'remarks' => null]);
            $application->applied_at = now();
            $application->status_changed_at = now();

            if ($violations) {
                $application->override_reason = $overrideReason;
                $application->override_by = session('user_id');
            } else {
                $application->override_reason = null;
                $application->override_by = null;
            }
            $application->save();

            $this->audit->log('application', $application->id, $violations ? 'apply_override' : 'apply', null,
                ['drive_id' => $drive->id, 'role_id' => $role->id, 'student_id' => $profile->student_id],
                $violations ? $overrideReason.' | overridden: '.implode('; ', $violations) : null);

            return $application;
        });
    }

    /**
     * Moves applications to a new status. Shortlisting re-checks the policy; a block needs an
     * override reason (audited). Returns [changed => n, blocked => [application_id => [violations]]].
     *
     * @param  int[]  $applicationIds
     */
    public function setStatus(array $applicationIds, string $to, ?string $remarks = null, ?string $overrideReason = null, ?array $allowedStandardIds = null): array
    {
        if (! array_key_exists($to, PlacementApplication::STATUSES)) {
            throw ValidationException::withMessages(['status' => 'Unknown status.']);
        }

        $applications = PlacementApplication::with('drive', 'role')->whereIn('id', $applicationIds)->get();
        $profiles = PlacementStudentProfile::whereIn('student_id', $applications->pluck('student_id'))->get()->keyBy('student_id');
        $students = $allowedStandardIds !== null
            ? app(StudentLookup::class)->students($applications->pluck('student_id')->all(), $allowedStandardIds)
            : null;

        $changed = 0;
        $blocked = [];

        DB::transaction(function () use ($applications, $profiles, $students, $to, $remarks, $overrideReason, &$changed, &$blocked) {
            foreach ($applications as $application) {
                // Coordinators may only act on their own courses.
                if ($students !== null && ! $students->has($application->student_id)) {
                    $blocked[$application->id] = ['Student is outside your assigned courses.'];
                    continue;
                }
                if ($application->status === $to) {
                    continue;
                }
                if (! in_array($to, self::TRANSITIONS[$application->status] ?? [], true)) {
                    $blocked[$application->id] = ["Cannot move from {$application->status} to {$to}."];
                    continue;
                }

                $violations = [];
                if ($to === 'shortlisted' && isset($profiles[$application->student_id])) {
                    $violations = $this->policy->checkShortlist($profiles[$application->student_id], $application->drive, $application->role);
                }
                if ($violations && trim((string) $overrideReason) === '') {
                    $blocked[$application->id] = $violations;
                    continue;
                }

                $from = $application->status;
                $application->status = $to;
                $application->status_changed_at = now();
                $application->remarks = $remarks ?? $application->remarks;
                if ($violations) {
                    $application->override_reason = $overrideReason;
                    $application->override_by = session('user_id');
                }
                $application->save();
                $changed++;

                $this->audit->log('application', $application->id, $violations ? 'shortlist_override' : 'status_change',
                    ['status' => $from], ['status' => $to],
                    $violations ? $overrideReason.' | overridden: '.implode('; ', $violations) : $remarks);
            }
        });

        return ['changed' => $changed, 'blocked' => $blocked];
    }

    public function withdraw(PlacementApplication $application): PlacementApplication
    {
        if (! in_array('withdrawn', self::TRANSITIONS[$application->status] ?? [], true)) {
            throw ValidationException::withMessages(['application' => 'This application can no longer be withdrawn.']);
        }
        if (in_array($application->drive->status, ['in_progress', 'completed', 'cancelled'], true)) {
            throw ValidationException::withMessages(['application' => 'The drive has already started; contact the placement cell.']);
        }

        $from = $application->status;
        $application->update(['status' => 'withdrawn', 'status_changed_at' => now()]);
        $this->audit->log('application', $application->id, 'withdraw', ['status' => $from], ['status' => 'withdrawn']);

        return $application;
    }
}
