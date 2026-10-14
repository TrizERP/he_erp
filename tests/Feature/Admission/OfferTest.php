<?php

namespace Tests\Feature\Admission;

use App\Models\admission\AdmissionHistory;
use App\Models\admission\AdmissionOffer;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\EligibilityService;
use App\Services\Admission\OfferService;
use App\Services\Admission\WorkflowService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class OfferTest extends AdmissionTestCase
{
    protected OfferService $offers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offers = app(OfferService::class);
    }

    protected function data(array $o = []): array
    {
        return $o + ['offer_date' => now()->toDateString(), 'expiry_date' => now()->addDays(7)->toDateString()];
    }

    public function test_an_offer_can_only_be_issued_for_a_submitted_application(): void
    {
        $id = $this->makeEnquiry();
        app(WorkflowService::class)->convert(self::INST, $id); // draft

        $this->expectException(AdmissionException::class);
        $this->expectExceptionMessage('submitted application');
        $this->offers->issue(self::INST, $id, $this->data());
    }

    public function test_issuing_records_dates_programme_seat_category_and_history(): void
    {
        $id = $this->makeReadyApplication();

        $offer = $this->offers->issue(self::INST, $id, $this->data(['student_quota' => 'General', 'notes' => 'Provisional']));

        $this->assertSame('issued', $offer->status);
        $this->assertSame(1, (int) $offer->standard_id, 'defaults to the applicant programme');
        $this->assertSame('General', $offer->student_quota);
        $this->assertSame(1, AdmissionHistory::where('action', 'offer_issued')->count());
    }

    public function test_the_response_deadline_must_not_be_in_the_past_or_before_the_offer_date(): void
    {
        $id = $this->makeReadyApplication();

        $this->expectException(AdmissionException::class);
        $this->offers->issue(self::INST, $id, $this->data(['expiry_date' => now()->subDay()->toDateString()]));
    }

    public function test_a_second_active_offer_is_refused(): void
    {
        $id = $this->makeReadyApplication();
        $this->offers->issue(self::INST, $id, $this->data());

        try {
            $this->offers->issue(self::INST, $id, $this->data());
            $this->fail('Only one active offer per application.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('active offer', $e->getMessage());
        }
        $this->assertSame(1, AdmissionOffer::count());
    }

    public function test_the_database_itself_refuses_two_active_offers(): void
    {
        $id = $this->makeReadyApplication();
        $this->offers->issue(self::INST, $id, $this->data());

        $this->expectException(QueryException::class);
        DB::table('admission_offer')->insert([
            'sub_institute_id' => self::INST, 'enquiry_id' => $id, 'offer_date' => '2026-07-01', 'expiry_date' => '2026-07-09', 'status' => 'issued', 'active' => 1,
        ]);
    }

    public function test_accept_and_decline_are_recorded_and_repeating_is_harmless(): void
    {
        $id = $this->makeReadyApplication();
        $offer = $this->offers->issue(self::INST, $id, $this->data());

        $this->offers->respond(self::INST, $offer->id, true);
        $this->offers->respond(self::INST, $offer->id, true);
        $this->assertSame('accepted', $offer->fresh()->status);
        $this->assertSame(1, AdmissionHistory::where('action', 'offer_accepted')->count());

        try {
            $this->offers->respond(self::INST, $offer->id, false);
            $this->fail('An accepted offer cannot be declined afterwards.');
        } catch (AdmissionException $e) {
            $this->assertSame('accepted', $offer->fresh()->status);
        }
    }

    public function test_a_declined_offer_frees_the_application_for_a_new_offer(): void
    {
        $id = $this->makeReadyApplication();
        $offer = $this->offers->issue(self::INST, $id, $this->data());
        $this->offers->respond(self::INST, $offer->id, false);

        $second = $this->offers->issue(self::INST, $id, $this->data());

        $this->assertNotSame($offer->id, $second->id);
        $this->assertSame(['declined', 'issued'], AdmissionOffer::orderBy('id')->pluck('status')->all());
    }

    public function test_an_expired_offer_cannot_be_accepted_and_is_marked_expired_with_history(): void
    {
        $id = $this->makeReadyApplication();
        $offer = $this->offers->issue(self::INST, $id, $this->data());
        DB::table('admission_offer')->where('id', $offer->id)->update(['expiry_date' => now()->subDays(2)->toDateString()]);

        try {
            $this->offers->respond(self::INST, $offer->id, true);
            $this->fail('Expired.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('expired', $e->getMessage());
        }

        $this->assertSame('expired', $offer->fresh()->status);
        $this->assertNull($offer->fresh()->active);
        $this->assertSame(1, AdmissionHistory::where('action', 'offer_expired')->count());
    }

    public function test_expire_due_expires_only_overdue_issued_offers_of_this_institute(): void
    {
        $a = $this->makeReadyApplication();
        $b = $this->makeReadyApplication();
        $offerA = $this->offers->issue(self::INST, $a, $this->data());
        $offerB = $this->offers->issue(self::INST, $b, $this->data());
        DB::table('admission_offer')->where('id', $offerA->id)->update(['expiry_date' => now()->subDay()->toDateString()]);

        $this->assertSame(1, $this->offers->expireDue(self::INST));
        $this->assertSame(0, $this->offers->expireDue(self::INST), 'second run does nothing');
        $this->assertSame('expired', $offerA->fresh()->status);
        $this->assertSame('issued', $offerB->fresh()->status);
        $this->assertSame(0, $this->offers->expireDue(self::OTHER_INST));
    }

    public function test_withdrawing_needs_a_reason_and_allows_a_replacement_offer(): void
    {
        $id = $this->makeReadyApplication();
        $offer = $this->offers->issue(self::INST, $id, $this->data());

        try {
            $this->offers->withdraw(self::INST, $offer->id, '');
            $this->fail('Reason required');
        } catch (AdmissionException $e) {
            $this->assertSame('issued', $offer->fresh()->status);
        }

        $this->offers->withdraw(self::INST, $offer->id, 'Seat reallocated');
        $this->assertSame('withdrawn', $offer->fresh()->status);
        $this->assertSame('issued', $this->offers->issue(self::INST, $id, $this->data())->status);
    }

    public function test_an_ineligible_applicant_gets_no_offer_without_an_audited_override(): void
    {
        $id = $this->makeReadyApplication(['percentage' => '10']);
        \App\Models\admission\AdmissionEligibilityRule::create(['sub_institute_id' => self::INST, 'label' => 'Min %', 'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'status' => true]);
        app(EligibilityService::class)->run(self::INST, $id);

        try {
            $this->offers->issue(self::INST, $id, $this->data());
            $this->fail('Ineligible applicants cannot receive an offer.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('override', $e->getMessage());
        }
        $this->assertSame(0, AdmissionOffer::count());

        app(EligibilityService::class)->override(self::INST, $id, 'eligible', 'Committee exception #12');
        $this->assertSame('issued', $this->offers->issue(self::INST, $id, $this->data())->status);
    }

    public function test_the_institute_can_require_eligibility_and_selection_before_an_offer(): void
    {
        $id = $this->makeReadyApplication();
        $this->setSetting('require_selection', '1');

        try {
            $this->offers->issue(self::INST, $id, $this->data());
            $this->fail('Selection is required.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('Selected', $e->getMessage());
        }

        app(\App\Services\Admission\SelectionService::class)->decide(self::INST, $id, 'selected', null);
        $this->assertSame('issued', $this->offers->issue(self::INST, $id, $this->data())->status);
    }

    public function test_a_rejected_applicant_cannot_receive_an_offer(): void
    {
        $id = $this->makeReadyApplication();
        app(\App\Services\Admission\SelectionService::class)->decide(self::INST, $id, 'rejected', 'Not suitable');

        $this->expectException(AdmissionException::class);
        $this->offers->issue(self::INST, $id, $this->data());
    }

    public function test_offers_of_another_institute_cannot_be_answered(): void
    {
        $id = $this->makeReadyApplication();
        $offer = $this->offers->issue(self::INST, $id, $this->data());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->offers->respond(self::OTHER_INST, $offer->id, true);
    }
}
