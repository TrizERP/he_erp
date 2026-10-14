<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementOffer;
use App\Models\placement\PlacementSeason;
use App\Models\placement\PlacementStudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Offers: Offered -> Accepted / Rejected / Revoked -> Joined / Not joined.
 * Policy is enforced when the offer is recorded and again when it is accepted; a TPO override
 * needs a reason and is audited. Keeps placement_student_profile.is_placed in sync.
 */
class OfferService
{
    public const TRANSITIONS = [
        'offered'    => ['accepted', 'rejected', 'revoked'],
        'accepted'   => ['joined', 'not_joined', 'revoked'],
        'rejected'   => [],
        'revoked'    => [],
        'joined'     => [],
        'not_joined' => [],
    ];

    protected PolicyService $policy;
    protected AuditService $audit;
    protected NotificationService $notifier;

    public function __construct(PolicyService $policy, AuditService $audit, NotificationService $notifier)
    {
        $this->policy = $policy;
        $this->audit = $audit;
        $this->notifier = $notifier;
    }

    /**
     * Records an offer. $data: role_title, ctc_fixed, ctc_variable, joining_bonus, location, offer_date,
     * joining_date, bond_details, offer_letter. Pass $applicationId for drive offers, or student_id +
     * company_id (+ internship_id, is_ppo) for a PPO conversion.
     */
    public function create(array $data, ?int $applicationId = null, ?string $overrideReason = null): PlacementOffer
    {
        $application = $applicationId ? PlacementApplication::with('drive', 'role')->findOrFail($applicationId) : null;

        if ($application) {
            if (! in_array($application->status, ['shortlisted', 'selected'], true)) {
                throw ValidationException::withMessages(['offer' => 'Offers can only be issued to shortlisted or selected applicants.']);
            }
            if (PlacementOffer::where('application_id', $application->id)->whereIn('status', PlacementOffer::HOLDING)->exists()) {
                throw ValidationException::withMessages(['offer' => 'This application already has an active offer.']);
            }
            $data['student_id'] = $application->student_id;
            $data['company_id'] = $application->drive->company_id;
            $data['drive_id'] = $application->drive_id;
            $data['role_id'] = $application->role_id;
            $data['role_title'] = $data['role_title'] ?? $application->role->title;
            $seasonId = $application->drive->season_id;
        } else {
            $seasonId = $data['season_id'] ?? null;
        }
        unset($data['season_id']);
        // The offer counts towards the drive's season, else the season given, else the current one.
        $data['season_id'] = $seasonId ?? PlacementSeason::where('is_current', 1)->value('id');

        $profile = PlacementStudentProfile::where('student_id', $data['student_id'])->first();
        $company = PlacementCompany::findOrFail($data['company_id']);
        if (! $profile) {
            throw ValidationException::withMessages(['offer' => 'The student has no placement profile.']);
        }

        $data['ctc_fixed'] = (float) ($data['ctc_fixed'] ?? 0);
        $data['ctc_variable'] = (float) ($data['ctc_variable'] ?? 0);
        $data['joining_bonus'] = (float) ($data['joining_bonus'] ?? 0);
        $data['ctc_total'] = $data['ctc_fixed'] + $data['ctc_variable'];
        if ($data['ctc_total'] <= 0 && empty($data['is_ppo'])) {
            throw ValidationException::withMessages(['ctc_fixed' => 'Enter the CTC (fixed and/or variable).']);
        }

        $violations = $this->policy->checkOffer($profile, $company, $data['ctc_total'], $seasonId);
        if ($violations && trim((string) $overrideReason) === '') {
            throw ValidationException::withMessages(['offer' => $violations]);
        }

        $offer = DB::transaction(function () use ($data, $application, $violations, $overrideReason) {
            $offer = PlacementOffer::create($data + ['application_id' => $application?->id, 'offer_date' => $data['offer_date'] ?? now()->toDateString()]);
            if ($violations) {
                $offer->override_reason = $overrideReason;
                $offer->override_by = session('user_id');
                $offer->save();
            }
            if ($application && $application->status !== 'selected') {
                $application->update(['status' => 'selected', 'status_changed_at' => now()]);
            }
            $this->audit->log('offer', $offer->id, $violations ? 'create_override' : 'create', null,
                ['student_id' => $offer->student_id, 'company_id' => $offer->company_id, 'ctc_total' => $offer->ctc_total],
                $violations ? $overrideReason.' | overridden: '.implode('; ', $violations) : null);

            return $offer;
        });

        $this->notify($offer, 'offer_issued');

        return $offer;
    }

    /** @param string $to accepted|rejected|revoked|joined|not_joined */
    public function transition(PlacementOffer $offer, string $to, ?string $reason = null, bool $byStudent = false, ?string $overrideReason = null): PlacementOffer
    {
        $from = $offer->status;
        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw ValidationException::withMessages(['offer' => "An offer cannot move from {$from} to {$to}."]);
        }
        if ($byStudent && ! in_array($to, ['accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['offer' => 'Students can only accept or reject an offer.']);
        }
        if (in_array($to, ['revoked', 'rejected', 'not_joined'], true) && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'A reason is required.']);
        }

        $violations = [];
        if ($to === 'accepted') {
            $profile = PlacementStudentProfile::where('student_id', $offer->student_id)->first();
            $company = PlacementCompany::find($offer->company_id);
            $seasonId = $offer->season_id;
            // The offer itself is excluded so it does not count as "another" offer.
            $violations = $profile && $company ? $this->policy->checkOffer($profile, $company, (float) $offer->ctc_total, $seasonId, $offer->id) : [];
            if ($violations && ($byStudent || trim((string) $overrideReason) === '')) {
                throw ValidationException::withMessages(['offer' => $violations]);
            }
        }

        $offer = DB::transaction(function () use ($offer, $from, $to, $reason, $violations, $overrideReason) {
            $offer->status = $to;
            $offer->status_reason = $reason ?: $offer->status_reason;
            if (in_array($to, ['accepted', 'rejected'], true)) {
                $offer->responded_at = now();
            }
            if ($violations) {
                $offer->override_reason = $overrideReason;
                $offer->override_by = session('user_id');
            }
            $offer->save();

            $this->syncPlaced($offer->student_id);
            if ($to === 'not_joined') {
                $this->applyDebar($offer);
            }
            if (in_array($to, ['revoked', 'rejected'], true) && $offer->application_id) {
                // The seat goes back to "shortlisted" so another offer can follow.
                PlacementApplication::where('id', $offer->application_id)->where('status', 'selected')
                    ->update(['status' => 'shortlisted', 'status_changed_at' => now()]);
            }

            $this->audit->log('offer', $offer->id, $violations ? 'status_override' : 'status_change', ['status' => $from], ['status' => $to],
                $violations ? $overrideReason.' | overridden: '.implode('; ', $violations) : $reason);

            return $offer;
        });

        if (in_array($to, ['revoked', 'joined', 'not_joined'], true)) {
            $this->notify($offer, 'offer_status');
        }

        return $offer;
    }

    /** TPO confirms the student's acceptance (offer letter seen, details match). */
    public function verifyAcceptance(PlacementOffer $offer): PlacementOffer
    {
        if ($offer->status !== 'accepted') {
            throw ValidationException::withMessages(['offer' => 'Only an accepted offer can be verified.']);
        }
        $offer->update(['verified_by' => session('user_id'), 'verified_at' => now()]);
        $this->audit->log('offer', $offer->id, 'verify');
        $this->notify($offer, 'offer_status');

        return $offer;
    }

    /** True when the student holds an accepted / joined offer. */
    public function syncPlaced(int $studentId): void
    {
        $placed = PlacementOffer::where('student_id', $studentId)->whereIn('status', ['accepted', 'joined'])->exists();
        PlacementStudentProfile::where('student_id', $studentId)->update(['is_placed' => $placed ? 1 : 0]);
    }

    /** An accepted offer that the student never joins can debar them for the policy's period. */
    protected function applyDebar(PlacementOffer $offer): void
    {
        $seasonId = $offer->season_id;
        $policy = $this->policy->forSeason($seasonId);
        if ($policy && $policy->debar_days) {
            PlacementStudentProfile::where('student_id', $offer->student_id)->update([
                'debarred_until' => now()->addDays($policy->debar_days)->toDateString(),
                'debar_reason'   => 'Did not join accepted offer (offer #'.$offer->id.')',
            ]);
            $this->audit->log('student_profile', null, 'debar', null, ['student_id' => $offer->student_id, 'days' => $policy->debar_days], 'Did not join offer #'.$offer->id);
        }
    }

    protected function notify(PlacementOffer $offer, string $event): void
    {
        $company = PlacementCompany::find($offer->company_id);
        $this->notifier->notify($event, [$offer->student_id], [
            'company' => $company->name ?? '',
            'role'    => $offer->role_title,
            'ctc'     => rtrim(rtrim(number_format($offer->ctc_total, 2), '0'), '.').' LPA',
            'status'  => PlacementOffer::STATUSES[$offer->status] ?? $offer->status,
            'date'    => now()->format('d-m-Y'),
        ], 'offer', $offer->id);
    }
}
