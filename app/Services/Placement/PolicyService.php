<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveEligibleStudent;
use App\Models\placement\PlacementDriveRole;
use App\Models\placement\PlacementOffer;
use App\Models\placement\PlacementPolicy;
use App\Models\placement\PlacementSeason;
use App\Models\placement\PlacementStudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * One place for placement rules. Enforcement points: applying (checkApply), shortlisting
 * (checkShortlist) and, from Phase 3, recording an offer.
 *
 * A check returns a list of human-readable violations; empty = allowed. A TPO may override
 * with a reason, which ApplicationService writes to the audit log.
 */
class PolicyService
{
    protected AuditService $audit;

    public function __construct(AuditService $audit)
    {
        $this->audit = $audit;
    }

    /** Active, approved policy for the season, falling back to an institute-wide one. */
    public function forSeason(?int $seasonId): ?PlacementPolicy
    {
        // PPO conversions have no drive, hence no season: use the current placement season.
        $seasonId = $seasonId ?? PlacementSeason::where('is_current', 1)->value('id');
        $q = PlacementPolicy::where('status', 1)->whereNotNull('approved_at');

        return (clone $q)->where('season_id', $seasonId)->latest('id')->first()
            ?? (clone $q)->whereNull('season_id')->latest('id')->first();
    }

    public function approve(PlacementPolicy $policy): PlacementPolicy
    {
        if ($policy->approved_at) {
            throw ValidationException::withMessages(['policy' => 'This policy is already approved.']);
        }

        return DB::transaction(function () use ($policy) {
            $policy->approved_by = session()->get('user_id');
            $policy->approved_at = now();
            $policy->save();
            $this->audit->log('policy', $policy->id, 'approve');

            return $policy;
        });
    }

    /** Rules that stop a student applying to a drive role. */
    public function checkApply(PlacementStudentProfile $profile, PlacementDrive $drive, PlacementDriveRole $role): array
    {
        $violations = [];

        if ($profile->registration_status !== 'registered') {
            $violations[] = 'You have not registered for placements.';
        }
        if ($profile->verification_status !== 'verified') {
            $violations[] = 'Your placement profile is not verified yet.';
        }
        if (! $drive->isRegistrationOpen()) {
            $violations[] = 'Registration is not open for this drive.';
        }

        $eligible = PlacementDriveEligibleStudent::where('sub_institute_id', $drive->sub_institute_id)
            ->where(['drive_id' => $drive->id, 'role_id' => $role->id, 'student_id' => $profile->student_id])->first();
        if (! $eligible || ! $eligible->is_eligible) {
            $violations[] = 'You are not eligible for this role'.($eligible && $eligible->reasons ? ': '.implode('; ', $eligible->reasons) : '.');
        }

        if (PlacementApplication::where(['drive_id' => $drive->id, 'role_id' => $role->id, 'student_id' => $profile->student_id])
            ->whereIn('status', PlacementApplication::ACTIVE)->exists()) {
            $violations[] = 'You have already applied for this role.';
        }

        return array_merge($violations, $this->policyViolations($profile, $drive, $role, true));
    }

    /** Re-checks the policy at shortlisting time (student state may have changed since applying). */
    public function checkShortlist(PlacementStudentProfile $profile, PlacementDrive $drive, PlacementDriveRole $role): array
    {
        return $this->policyViolations($profile, $drive, $role, false);
    }

    /**
     * Third enforcement point: recording an offer. $totalCtc is in LPA.
     * One-offer rule: a student already holding an offer gets no more. Dream-offer rule: a student
     * holding an offer may receive another only above the policy's dream CTC.
     */
    public function checkOffer(PlacementStudentProfile $profile, PlacementCompany $company, float $totalCtc, ?int $seasonId, ?int $ignoreOfferId = null): array
    {
        $violations = [];

        if ($profile->isDebarred()) {
            $violations[] = 'Student is debarred until '.$profile->debarred_until->format('d-m-Y').'.';
        }
        $policy = $this->forSeason($seasonId);
        if (! $policy) {
            return $violations;
        }
        if ($policy->block_blacklisted && $company->status === 'blacklisted') {
            $violations[] = 'The company is blacklisted.';
        }

        $holding = PlacementOffer::where('student_id', $profile->student_id)
            ->whereIn('status', PlacementOffer::HOLDING)
            ->when($ignoreOfferId, fn ($q) => $q->where('id', '!=', $ignoreOfferId))->exists();

        if ($holding) {
            if ($policy->one_offer_rule) {
                $violations[] = 'One-offer rule: the student already holds an offer.';
            } elseif ($policy->dream_offer_rule && $policy->dream_min_ctc !== null && $totalCtc <= (float) $policy->dream_min_ctc) {
                $violations[] = "Dream-offer rule: a further offer must be above {$policy->dream_min_ctc} LPA.";
            }
        }

        return $violations;
    }

    protected function policyViolations(PlacementStudentProfile $profile, PlacementDrive $drive, PlacementDriveRole $role, bool $countLimit): array
    {
        $violations = [];

        if ($profile->isDebarred()) {
            $violations[] = 'Debarred until '.$profile->debarred_until->format('d-m-Y').($profile->debar_reason ? " ({$profile->debar_reason})" : '').'.';
        }

        $policy = $this->forSeason($drive->season_id);
        if (! $policy) {
            return $violations; // no approved policy yet: only the hard checks above apply
        }

        if ($policy->block_blacklisted) {
            $company = PlacementCompany::find($drive->company_id);
            if ($company && $company->status === 'blacklisted') {
                $violations[] = 'This company is blacklisted.';
            }
        }

        if ($policy->max_active_backlogs !== null && (int) $profile->active_backlogs > (int) $policy->max_active_backlogs) {
            $violations[] = "Policy allows at most {$policy->max_active_backlogs} active backlog(s).";
        }

        if ($countLimit && $policy->max_applications) {
            $count = PlacementApplication::where('student_id', $profile->student_id)
                ->whereIn('status', PlacementApplication::ACTIVE)
                ->whereIn('drive_id', PlacementDrive::where('season_id', $drive->season_id)->select('id'))
                ->count();
            if ($count >= $policy->max_applications) {
                $violations[] = "Maximum of {$policy->max_applications} applications reached.";
            }
        }

        // is_placed is maintained by OfferService (Phase 3) when an offer is accepted.
        if ($profile->is_placed) {
            if ($policy->one_offer_rule) {
                $violations[] = 'One-offer rule: you already hold an offer.';
            } elseif ($policy->dream_offer_rule && $policy->dream_min_ctc !== null && $role->topCtc() <= (float) $policy->dream_min_ctc) {
                $violations[] = "Dream-offer rule: only roles above {$policy->dream_min_ctc} LPA are open to you.";
            }
        }

        return $violations;
    }
}
