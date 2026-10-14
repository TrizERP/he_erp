<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveEligibility;
use App\Models\placement\PlacementDriveEligibleStudent;
use App\Models\placement\PlacementStudentProfile;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates eligibility criteria against a student's placement profile and ERP enrolment, and
 * materialises the result per drive/role in placement_drive_eligible_student.
 * Only registered AND verified students are in the candidate pool.
 */
class EligibilityService
{
    protected StudentLookup $students;

    public function __construct(StudentLookup $students)
    {
        $this->students = $students;
    }

    /**
     * @param  array  $student   row from StudentLookup (standard_id, gender)
     * @param  object $criteria  PlacementDriveEligibility (or null = no criteria)
     * @return string[] reasons the student fails; empty = eligible
     */
    public function evaluate(PlacementStudentProfile $profile, $student, $criteria): array
    {
        if (! $criteria) {
            return [];
        }
        $reasons = [];

        if ($criteria->min_cgpa !== null) {
            if ($profile->cgpa === null) {
                $reasons[] = 'CGPA not provided';
            } elseif ((float) $profile->cgpa < (float) $criteria->min_cgpa) {
                $reasons[] = "CGPA {$profile->cgpa} below required {$criteria->min_cgpa}";
            }
        }
        if ($criteria->min_10th_percent !== null) {
            if ($profile->tenth_percent === null) {
                $reasons[] = '10th % not provided';
            } elseif ((float) $profile->tenth_percent < (float) $criteria->min_10th_percent) {
                $reasons[] = "10th % {$profile->tenth_percent} below required {$criteria->min_10th_percent}";
            }
        }
        if ($criteria->min_12th_percent !== null) {
            if ($profile->twelfth_percent === null) {
                $reasons[] = '12th % not provided';
            } elseif ((float) $profile->twelfth_percent < (float) $criteria->min_12th_percent) {
                $reasons[] = "12th % {$profile->twelfth_percent} below required {$criteria->min_12th_percent}";
            }
        }
        if ($criteria->max_active_backlogs !== null && (int) $profile->active_backlogs > (int) $criteria->max_active_backlogs) {
            $reasons[] = "{$profile->active_backlogs} active backlog(s), maximum allowed {$criteria->max_active_backlogs}";
        }
        if ($criteria->passing_year !== null && (int) $profile->passing_year !== (int) $criteria->passing_year) {
            $reasons[] = "Passing year must be {$criteria->passing_year}";
        }
        if (! empty($criteria->standard_ids) && ! in_array((int) ($student->standard_id ?? 0), array_map('intval', $criteria->standard_ids), true)) {
            $reasons[] = 'Course not allowed for this drive';
        }
        if ($criteria->gender) {
            $want = strtolower($criteria->gender) === 'male' ? 'M' : (strtolower($criteria->gender) === 'female' ? 'F' : strtoupper($criteria->gender));
            if (strtoupper((string) ($student->gender ?? '')) !== $want) {
                $reasons[] = 'Drive is restricted by gender';
            }
        }

        return $reasons;
    }

    /** Criteria for one role: role-specific row first, otherwise the drive-wide row. */
    public function criteriaFor($eligibilities, $roleId)
    {
        return $eligibilities->firstWhere('role_id', $roleId) ?? $eligibilities->firstWhere('role_id', null);
    }

    /**
     * Rebuilds the eligible list for a drive. Returns [role_id => ['eligible' => n, 'not_eligible' => n]].
     */
    public function computeForDrive(PlacementDrive $drive): array
    {
        $instituteId = $drive->sub_institute_id;
        $roles = $drive->roles()->get();
        $eligibilities = PlacementDriveEligibility::where('drive_id', $drive->id)->get();

        $profiles = PlacementStudentProfile::where('registration_status', 'registered')
            ->where('verification_status', 'verified')->get();
        $students = $this->students->students($profiles->pluck('student_id')->all(), null, $instituteId, $this->syearFor($drive));

        $now = now();
        $rows = [];
        $summary = [];

        foreach ($roles as $role) {
            $criteria = $this->criteriaFor($eligibilities, $role->id);
            $summary[$role->id] = ['eligible' => 0, 'not_eligible' => 0];

            foreach ($profiles as $profile) {
                $student = $students->get($profile->student_id);
                if (! $student) {
                    continue; // not enrolled this academic year
                }
                $reasons = $this->evaluate($profile, $student, $criteria);
                $ok = empty($reasons);
                $summary[$role->id][$ok ? 'eligible' : 'not_eligible']++;
                $rows[] = [
                    'sub_institute_id' => $instituteId,
                    'drive_id'         => $drive->id,
                    'role_id'          => $role->id,
                    'student_id'       => $profile->student_id,
                    'is_eligible'      => $ok ? 1 : 0,
                    'reasons'          => $ok ? null : json_encode($reasons),
                    'computed_at'      => $now,
                ];
            }
        }

        DB::transaction(function () use ($drive, $rows) {
            PlacementDriveEligibleStudent::where('sub_institute_id', $drive->sub_institute_id)
                ->where('drive_id', $drive->id)->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('placement_drive_eligible_student')->insert($chunk);
            }
        });

        return $summary;
    }

    /** Academic year to read enrolment from: the drive's season, else the session year. */
    protected function syearFor(PlacementDrive $drive)
    {
        $syear = $drive->season_id
            ? DB::table('placement_season')->where('id', $drive->season_id)->value('syear')
            : null;

        return $syear ?: session('syear');
    }
}
