<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionOffer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Offer / seat allocation. At most one live (issued or accepted) offer per application is enforced by the
 * unique index admission_offer(sub_institute_id, enquiry_id, active) as well as by a pre-check here.
 */
class OfferService
{
    public function __construct(protected AuditService $audit, protected WorkflowService $workflow, protected SettingService $settings)
    {
    }

    public function issue(int $instituteId, int $enquiryId, array $data): AdmissionOffer
    {
        $offerDate = Carbon::parse($data['offer_date'])->startOfDay();
        $expiry = Carbon::parse($data['expiry_date'])->startOfDay();
        if ($expiry->lt($offerDate) || $expiry->lt(Carbon::today())) {
            throw AdmissionException::because('The response deadline must be today or later, and not before the offer date.');
        }

        try {
            return DB::transaction(function () use ($instituteId, $enquiryId, $data, $offerDate, $expiry) {
                $workflow = $this->workflow->lockWorkflow($instituteId, $enquiryId);

                if ($workflow->confirmation_status === 'confirmed') {
                    throw AdmissionException::because('The admission is already confirmed.');
                }
                if ($workflow->application_status !== 'submitted') {
                    throw AdmissionException::because('An offer can only be issued for a submitted application.');
                }
                if ($workflow->eligibility_status === 'ineligible') {
                    throw AdmissionException::because('The applicant is Ineligible. An authorised, audited eligibility override is required before an offer.');
                }
                if ($workflow->selection_status === 'rejected') {
                    throw AdmissionException::because('The applicant has been rejected.');
                }
                if ($this->settings->flag($instituteId, 'require_eligibility') && $workflow->eligibility_status !== 'eligible') {
                    throw AdmissionException::because('Eligibility must be "Eligible" before an offer can be issued.');
                }
                if ($this->settings->flag($instituteId, 'require_selection') && $workflow->selection_status !== 'selected') {
                    throw AdmissionException::because('The applicant must be Selected before an offer can be issued.');
                }

                $this->expireDue($instituteId, $enquiryId);
                if (AdmissionOffer::where('sub_institute_id', $instituteId)->where('enquiry_id', $enquiryId)->where('active', 1)->exists()) {
                    throw AdmissionException::because('This application already has an active offer. Withdraw it before issuing another.');
                }

                $offer = new AdmissionOffer([
                    'sub_institute_id' => $instituteId, 'enquiry_id' => $enquiryId,
                    'offer_date' => $offerDate->toDateString(), 'expiry_date' => $expiry->toDateString(),
                    'standard_id' => $data['standard_id'] ?? $workflow->standard_id,
                    'student_quota' => $data['student_quota'] ?? null,
                    'notes' => isset($data['notes']) ? mb_substr($data['notes'], 0, 500) : null,
                ]);
                $offer->forceFill(['status' => 'issued', 'active' => 1, 'issued_by' => session()->get('user_id')])->save();

                $this->audit->log($instituteId, $enquiryId, 'offer', $offer->id, 'offer_issued', null, 'issued', "Respond by {$offer->expiry_date}");

                return $offer;
            });
        } catch (QueryException $e) {
            if ((string) $e->getCode() !== '23000') {
                throw $e; // only a unique-index violation means "someone else issued first"
            }
            // two simultaneous issues: the unique index let exactly one through
            throw AdmissionException::because('This application already has an active offer.');
        }
    }

    /** $accept true = accepted, false = declined. */
    public function respond(int $instituteId, int $offerId, bool $accept, ?string $notes = null): AdmissionOffer
    {
        $expired = false;
        $result = DB::transaction(function () use ($instituteId, $offerId, $accept, $notes, &$expired) {
            $offer = AdmissionOffer::where('sub_institute_id', $instituteId)->where('id', $offerId)->lockForUpdate()->firstOrFail();
            $target = $accept ? 'accepted' : 'declined';

            if ($offer->status === $target) {
                return $offer; // idempotent
            }
            if ($offer->status === 'issued' && Carbon::parse($offer->expiry_date)->lt(Carbon::today())) {
                // Record the expiry, but report it only after commit: throwing here would roll the expiry back.
                $this->markExpired($offer);
                $expired = true;

                return $offer;
            }
            if ($offer->status !== 'issued') {
                throw AdmissionException::because('Only an offer that is still awaiting a response can be answered.');
            }

            $offer->forceFill(['status' => $target, 'active' => $accept ? 1 : null, 'responded_at' => now()]);
            if ($notes !== null && $notes !== '') {
                $offer->notes = mb_substr($notes, 0, 500);
            }
            $offer->save();
            $this->audit->log($instituteId, $offer->enquiry_id, 'offer', $offer->id, 'offer_'.$target, 'issued', $target, $notes);

            return $offer;
        });

        if ($expired) {
            throw AdmissionException::because('This offer has expired.');
        }

        return $result;
    }

    public function withdraw(int $instituteId, int $offerId, string $reason): AdmissionOffer
    {
        if (trim($reason) === '') {
            throw AdmissionException::because('A reason is required to withdraw an offer.');
        }

        return DB::transaction(function () use ($instituteId, $offerId, $reason) {
            $offer = AdmissionOffer::where('sub_institute_id', $instituteId)->where('id', $offerId)->lockForUpdate()->firstOrFail();
            if ($offer->status === 'withdrawn') {
                return $offer;
            }
            if (! in_array($offer->status, ['issued', 'accepted'], true)) {
                throw AdmissionException::because('Only a live offer can be withdrawn.');
            }
            if (DB::table('admission_workflow')->where('sub_institute_id', $instituteId)->where('enquiry_id', $offer->enquiry_id)->where('confirmation_status', 'confirmed')->exists()) {
                throw AdmissionException::because('The admission is already confirmed.');
            }
            $from = $offer->status;
            $offer->forceFill(['status' => 'withdrawn', 'active' => null])->save();
            $this->audit->log($instituteId, $offer->enquiry_id, 'offer', $offer->id, 'offer_withdrawn', $from, 'withdrawn', $reason);

            return $offer;
        });
    }

    /** Lazily expire overdue offers (optionally for one application). Returns how many were expired. */
    public function expireDue(int $instituteId, ?int $enquiryId = null): int
    {
        $due = AdmissionOffer::where('sub_institute_id', $instituteId)->where('status', 'issued')
            ->whereDate('expiry_date', '<', Carbon::today()->toDateString())
            ->when($enquiryId, fn ($q) => $q->where('enquiry_id', $enquiryId))->get();

        foreach ($due as $offer) {
            $this->markExpired($offer);
        }

        return $due->count();
    }

    protected function markExpired(AdmissionOffer $offer): void
    {
        $updated = AdmissionOffer::where('id', $offer->id)->where('status', 'issued')->update(['status' => 'expired', 'active' => null, 'updated_at' => now()]);
        if ($updated) {
            $this->audit->log((int) $offer->sub_institute_id, (int) $offer->enquiry_id, 'offer', $offer->id, 'offer_expired', 'issued', 'expired');
        }
    }
}
