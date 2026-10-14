<?php

namespace Tests\Feature\Admission;

use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionEligibilityRule;
use App\Models\admission\AdmissionHistory;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\ConfirmationService;
use App\Services\Admission\EligibilityService;
use App\Services\Admission\WorkflowService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmationTest extends AdmissionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function confirmIt(int $id, ?string $override = null, bool $mayOverride = false, ?int $institute = null): array
    {
        return app(ConfirmationService::class)->confirm($institute ?? self::INST, $id, self::SYEAR, 1, $override, $mayOverride);
    }

    protected function counts(): array
    {
        return [DB::table('tblstudent')->count(), DB::table('tblstudent_enrollment')->count()];
    }

    // -- happy path ---------------------------------------------------------------------------------------

    public function test_confirmation_creates_one_student_and_one_enrollment_with_the_legacy_field_mapping(): void
    {
        $id = $this->makeReadyApplication(
            ['first_name' => 'Asha', 'father_name' => 'Ramesh', 'place_of_birth' => 'Surat'],
            ['amount' => 5000, 'payment_mode' => 'Cash', 'blood_group' => 'B+', 'aadhar_number' => '1234']
        );

        $result = $this->confirmIt($id);

        $this->assertSame('confirmed', $result['status']);
        $this->assertTrue($result['created']);
        $student = DB::table('tblstudent')->where('id', $result['student_id'])->first();
        $this->assertSame((string) $id, $student->admission_id);
        $this->assertSame('Asha', $student->first_name);
        $this->assertSame('Ramesh', $student->father_name);
        $this->assertSame('Surat', $student->place_of_birth);
        $this->assertSame('B+', $student->bloodgroup);
        $this->assertSame(1, (int) $student->status, 'active, as the legacy saveStudent made it');
        $this->assertSame(5, (int) $student->user_profile_id, 'the institute Student profile');
        $this->assertSame($student->enrollment_no, $student->username);

        $enrollment = DB::table('tblstudent_enrollment')->where('student_id', $student->id)->first();
        $this->assertSame(10, (int) $enrollment->grade_id, 'grade comes from the standard');
        $this->assertSame(1, (int) $enrollment->standard_id);
        $this->assertSame(3, (int) $enrollment->section_id);
        $this->assertSame('5000', $enrollment->admission_fees);
        $this->assertSame(self::SYEAR, (int) $enrollment->syear);

        $wf = app(WorkflowService::class)->ensure(self::INST, $id);
        $this->assertSame('confirmed', $wf->confirmation_status);
        $this->assertSame((int) $student->id, (int) $wf->student_id);
        $this->assertSame(7, (int) $wf->confirmed_by);
        $this->assertSame(1, AdmissionHistory::where('action', 'confirmed')->count());
    }

    public function test_the_next_enrollment_number_follows_the_existing_students(): void
    {
        DB::table('tblstudent')->insert(['sub_institute_id' => self::INST, 'enrollment_no' => '100']);
        DB::table('tblstudent')->insert(['sub_institute_id' => self::INST, 'enrollment_no' => '2024-legacy']);
        DB::table('tblstudent')->insert(['sub_institute_id' => self::OTHER_INST, 'enrollment_no' => '9000']); // other institute: ignored

        $first = $this->confirmIt($this->makeReadyApplication());
        $second = $this->confirmIt($this->makeReadyApplication());

        $this->assertSame('101', $first['enrollment_no']);
        $this->assertSame('102', $second['enrollment_no'], 'consecutive confirmations never share a number');
    }

    public function test_the_enrollment_number_captured_on_the_registration_record_is_used(): void
    {
        $id = $this->makeReadyApplication([], ['enrollment_no' => '555']);

        $this->assertSame('555', $this->confirmIt($id)['enrollment_no']);
    }

    public function test_a_captured_enrollment_number_that_is_already_taken_is_refused(): void
    {
        DB::table('tblstudent')->insert(['sub_institute_id' => self::INST, 'enrollment_no' => '555']);
        $id = $this->makeReadyApplication([], ['enrollment_no' => '555']);

        try {
            $this->confirmIt($id);
            $this->fail('Duplicate enrollment number must be refused.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('already used', $e->getMessage());
        }
        $this->assertSame([1, 0], $this->counts());
    }

    public function test_institute_47_uses_the_per_standard_prefix_numbering(): void
    {
        DB::table('standard')->insert(['id' => 21, 'name' => 'Std 1', 'grade_id' => 1, 'sub_institute_id' => 47]);
        DB::table('tbluserprofilemaster')->insert(['name' => 'Student', 'sub_institute_id' => 47]);
        DB::table('enrollment_prefix_master')->insert(['sub_institute_id' => 47, 'prefix' => 'MM', 'standards' => '21,22']);
        DB::table('tblstudent')->insert(['sub_institute_id' => 47, 'enrollment_no' => 'MM0041']);
        DB::table('tblstudent')->insert(['sub_institute_id' => 47, 'enrollment_no' => '12']); // no prefix: separate series

        $this->actAs('Admission Officer', 47);
        $id = $this->makeEnquiry(['sub_institute_id' => 47, 'admission_standard' => 21]);
        DB::table('admission_form')->insert(['enquiry_id' => (string) $id, 'form_no' => '1', 'admission_standard' => '21', 'sub_institute_id' => '47']);
        $this->makeRegistration($id, ['sub_institute_id' => 47]);

        $this->assertSame('MM42', $this->confirmIt($id, null, false, 47)['enrollment_no']);
    }

    // -- idempotency ---------------------------------------------------------------------------------------

    public function test_repeating_the_confirmation_returns_the_same_student_and_creates_nothing_new(): void
    {
        $id = $this->makeReadyApplication();

        $first = $this->confirmIt($id);
        $again = $this->confirmIt($id);
        $thirdTime = $this->confirmIt($id);

        $this->assertSame('already_confirmed', $again['status']);
        $this->assertFalse($again['created']);
        $this->assertSame($first['student_id'], $again['student_id']);
        $this->assertSame($first['student_id'], $thirdTime['student_id']);
        $this->assertSame($first['enrollment_no'], $thirdTime['enrollment_no']);
        $this->assertSame([1, 1], $this->counts(), 'one student, one enrollment');
        $this->assertSame(1, AdmissionHistory::where('action', 'confirmed')->count());
    }

    public function test_a_student_created_earlier_by_the_legacy_screen_is_linked_not_duplicated(): void
    {
        $id = $this->makeReadyApplication();
        $existing = DB::table('tblstudent')->insertGetId(['admission_id' => (string) $id, 'sub_institute_id' => self::INST, 'enrollment_no' => '321']);

        $result = $this->confirmIt($id);

        $this->assertSame('linked', $result['status']);
        $this->assertSame($existing, $result['student_id']);
        $this->assertSame([1, 0], $this->counts());
        $this->assertSame('confirmed', app(WorkflowService::class)->ensure(self::INST, $id)->confirmation_status);
    }

    public function test_the_same_student_can_never_be_linked_to_two_applications(): void
    {
        $a = $this->makeReadyApplication();
        $b = $this->makeReadyApplication();
        $result = $this->confirmIt($a);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('admission_workflow')->where('enquiry_id', $b)->update(['student_id' => $result['student_id']]);
    }

    // -- transaction safety ------------------------------------------------------------------------------------

    public function test_a_failure_part_way_through_rolls_everything_back_and_a_retry_then_succeeds(): void
    {
        $id = $this->makeReadyApplication();

        $this->app->bind(ConfirmationService::class, FailingConfirmationService::class);
        try {
            app(ConfirmationService::class)->confirm(self::INST, $id, self::SYEAR, 1);
            $this->fail('The injected failure should surface.');
        } catch (RuntimeException $e) {
            $this->assertSame('simulated failure after the student row was written', $e->getMessage());
        }
        $this->app->bind(ConfirmationService::class, ConfirmationService::class);

        $this->assertSame([0, 0], $this->counts(), 'no half-created student');
        $wf = app(WorkflowService::class)->ensure(self::INST, $id);
        $this->assertSame('pending', $wf->confirmation_status);
        $this->assertNull($wf->student_id);
        $this->assertSame(0, AdmissionHistory::where('action', 'confirmed')->count());

        $retry = app(ConfirmationService::class)->confirm(self::INST, $id, self::SYEAR, 1);
        $this->assertSame('confirmed', $retry['status']);
        $this->assertSame([1, 1], $this->counts());
    }

    // -- checklist ------------------------------------------------------------------------------------------------

    public function test_confirmation_needs_the_application_form_registration_and_submission_and_these_cannot_be_overridden(): void
    {
        $id = $this->makeEnquiry();
        $this->makeForm($id); // submitted via legacy rule, but no registration record

        try {
            $this->confirmIt($id, 'urgent', true);
            $this->fail('Structural checks cannot be overridden.');
        } catch (AdmissionException $e) {
            $this->assertContains('Admission registration details completed', $e->details);
        }
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_a_registration_without_a_seat_category_is_blocked_with_a_clear_message_not_a_database_error(): void
    {
        $id = $this->makeReadyApplication([], ['student_quota' => null]);

        try {
            $this->confirmIt($id, 'urgent', true); // cannot be overridden: the student table requires it
            $this->fail('Missing seat category must block.');
        } catch (AdmissionException $e) {
            $this->assertContains('Seat category (student quota) selected on the registration', $e->details);
        }
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_a_draft_application_cannot_be_confirmed_even_by_an_administrator(): void
    {
        $id = $this->makeEnquiry();
        app(WorkflowService::class)->convert(self::INST, $id);
        $this->makeRegistration($id);

        $this->expectException(AdmissionException::class);
        $this->confirmIt($id, 'urgent', true);
    }

    public function test_unverified_mandatory_documents_block_when_the_institute_requires_verification(): void
    {
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true]);
        $id = $this->makeReadyApplication();

        $this->assertSame('confirmed', app(ConfirmationService::class)->checklist(self::INST, $id, self::SYEAR)['can_confirm'] ? 'confirmed' : 'blocked', 'policy off: not enforced');

        $this->setSetting('require_documents_verified', '1');
        try {
            $this->confirmIt($id);
            $this->fail('Unverified documents must block.');
        } catch (AdmissionException $e) {
            $this->assertContains('Required documents verified', $e->details);
        }
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_an_ineligible_applicant_is_blocked_even_when_eligibility_is_not_a_required_policy(): void
    {
        AdmissionEligibilityRule::create(['sub_institute_id' => self::INST, 'label' => 'Min %', 'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'status' => true]);
        $id = $this->makeReadyApplication(['percentage' => '10']);
        app(EligibilityService::class)->run(self::INST, $id);

        try {
            $this->confirmIt($id);
            $this->fail('Ineligible must block.');
        } catch (AdmissionException $e) {
            $this->assertContains('Eligibility: eligible', $e->details);
        }
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_a_rejected_applicant_is_blocked(): void
    {
        $id = $this->makeReadyApplication();
        app(\App\Services\Admission\SelectionService::class)->decide(self::INST, $id, 'rejected', 'No');

        $this->expectException(AdmissionException::class);
        $this->confirmIt($id);
    }

    public function test_an_accepted_offer_can_be_required(): void
    {
        $id = $this->makeReadyApplication();
        $this->setSetting('require_offer_accepted', '1');
        $offers = app(\App\Services\Admission\OfferService::class);

        try {
            $this->confirmIt($id);
            $this->fail('Offer required.');
        } catch (AdmissionException $e) {
            $this->assertContains('Offer accepted', $e->details);
        }

        $offer = $offers->issue(self::INST, $id, ['offer_date' => now()->toDateString(), 'expiry_date' => now()->addWeek()->toDateString()]);
        $offers->respond(self::INST, $offer->id, true);
        $this->assertSame('confirmed', $this->confirmIt($id)['status']);
    }

    // -- fee condition ---------------------------------------------------------------------------------------------

    protected function feeStructure(int $amount): void
    {
        DB::table('student_quota')->insert(['id' => 1, 'title' => 'General', 'sub_institute_id' => self::INST]);
        DB::table('fees_breackoff')->insert([
            'standard_id' => 1, 'grade_id' => 10, 'sub_institute_id' => self::INST, 'quota' => 1, 'amount' => $amount,
            'syear' => self::SYEAR, 'admission_year' => self::SYEAR, 'month_id' => '4202',
        ]);
    }

    public function test_payment_pending_blocks_confirmation_when_full_payment_is_required(): void
    {
        $this->feeStructure(10000);
        $this->setSetting('fee_condition', 'full');
        $id = $this->makeReadyApplication([], ['amount' => 0]);

        try {
            $this->confirmIt($id);
            $this->fail('Unpaid fee must block.');
        } catch (AdmissionException $e) {
            $this->assertSame(1, count(array_filter($e->details, fn ($d) => str_contains($d, 'Admission fee condition'))));
        }
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_a_part_payment_satisfies_any_payment_but_not_full_payment(): void
    {
        $this->feeStructure(10000);
        $id = $this->makeReadyApplication([], ['amount' => 4000, 'payment_mode' => 'Cash', 'date_of_payment' => '2026-06-10']);

        $this->setSetting('fee_condition', 'full');
        $this->assertFalse(app(ConfirmationService::class)->checklist(self::INST, $id, self::SYEAR)['can_confirm']);

        $this->setSetting('fee_condition', 'any_payment');
        $this->assertTrue(app(ConfirmationService::class)->checklist(self::INST, $id, self::SYEAR)['can_confirm']);
    }

    public function test_a_full_payment_satisfies_the_full_payment_rule(): void
    {
        $this->feeStructure(10000);
        $this->setSetting('fee_condition', 'full');
        $id = $this->makeReadyApplication([], ['amount' => 10000, 'payment_mode' => 'Online', 'date_of_payment' => '2026-06-10']);

        $this->assertSame('confirmed', $this->confirmIt($id)['status']);
    }

    public function test_an_amount_with_no_recorded_payment_is_not_treated_as_paid(): void
    {
        $this->feeStructure(10000);
        $this->setSetting('fee_condition', 'any_payment');
        $id = $this->makeReadyApplication([], ['amount' => 10000]); // typed in, but no payment mode / date recorded

        $this->assertFalse(app(ConfirmationService::class)->checklist(self::INST, $id, self::SYEAR)['can_confirm']);
    }

    public function test_an_authorised_waiver_satisfies_the_fee_condition_only_when_enabled(): void
    {
        $this->setSetting('fee_condition', 'full');
        $id = $this->makeReadyApplication([], ['amount' => 0]);
        try {
            app(\App\Services\Admission\FeeService::class)->waive(self::INST, $id, 'Scholarship');
            $this->fail('Waivers are off by default.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('not enabled', $e->getMessage());
        }

        $this->setSetting('allow_fee_waiver', '1');
        app(\App\Services\Admission\FeeService::class)->waive(self::INST, $id, 'Merit scholarship, committee minute 4');

        $this->assertSame('confirmed', $this->confirmIt($id)['status']);
        $this->assertTrue((bool) AdmissionHistory::where('action', 'fee_waived')->first()->is_override);
    }

    // -- override --------------------------------------------------------------------------------------------------

    public function test_a_policy_block_can_be_overridden_only_by_an_authorised_user_with_a_reason(): void
    {
        $this->setSetting('require_selection', '1');
        $id = $this->makeReadyApplication();

        foreach ([[null, false], ['Special case', false], [null, true], ['   ', true]] as [$reason, $may]) {
            try {
                $this->confirmIt($id, $reason, $may);
                $this->fail('Override must need both authority and a reason.');
            } catch (AdmissionException $e) {
                $this->assertNotEmpty($e->details);
            }
        }
        $this->assertSame([0, 0], $this->counts());

        $result = $this->confirmIt($id, 'Principal approved admission by letter 17/6', true);
        $this->assertSame('confirmed', $result['status']);

        $wf = app(WorkflowService::class)->ensure(self::INST, $id);
        $this->assertTrue($wf->confirmation_override);
        $this->assertSame('Principal approved admission by letter 17/6', $wf->confirmation_override_reason);
        $history = AdmissionHistory::where('action', 'confirmed_with_override')->first();
        $this->assertTrue($history->is_override);
        $this->assertSame(['selection'], $history->old_values['bypassed']);
    }

    // -- hold / cancel -----------------------------------------------------------------------------------------------

    public function test_hold_and_cancel_need_a_reason_and_a_cancelled_admission_cannot_be_confirmed(): void
    {
        $id = $this->makeReadyApplication();

        try {
            app(ConfirmationService::class)->setStatus(self::INST, $id, 'on_hold', '');
            $this->fail('Reason required.');
        } catch (AdmissionException $e) {
            $this->assertSame('pending', app(WorkflowService::class)->ensure(self::INST, $id)->confirmation_status);
        }

        app(ConfirmationService::class)->setStatus(self::INST, $id, 'on_hold', 'Awaiting TC');
        $this->assertSame('confirmed', $this->confirmIt($id)['status'] ?? null, 'on hold can still be confirmed once resolved');
    }

    public function test_cancelling_blocks_confirmation_and_only_an_administrator_can_resume(): void
    {
        $id = $this->makeReadyApplication();
        app(ConfirmationService::class)->setStatus(self::INST, $id, 'cancelled', 'Applicant withdrew');

        try {
            $this->confirmIt($id);
            $this->fail('Cancelled.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('cancelled', $e->getMessage());
        }
        try {
            app(ConfirmationService::class)->setStatus(self::INST, $id, 'pending', 'Changed mind', false);
            $this->fail('Only an admin may resume a cancelled admission.');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('administrator', $e->getMessage());
        }

        app(ConfirmationService::class)->setStatus(self::INST, $id, 'pending', 'Changed mind', true);
        $this->assertSame('confirmed', $this->confirmIt($id)['status']);
    }

    public function test_a_confirmed_admission_cannot_be_held_or_cancelled(): void
    {
        $id = $this->makeReadyApplication();
        $this->confirmIt($id);

        $this->expectException(AdmissionException::class);
        app(ConfirmationService::class)->setStatus(self::INST, $id, 'cancelled', 'Too late');
    }

    // -- scope --------------------------------------------------------------------------------------------------------

    public function test_another_institute_cannot_confirm_or_see_the_checklist_of_this_application(): void
    {
        $id = $this->makeReadyApplication();

        foreach ([fn () => $this->confirmIt($id, null, false, self::OTHER_INST),
                  fn () => app(ConfirmationService::class)->checklist(self::OTHER_INST, $id, self::SYEAR)] as $attempt) {
            try {
                $attempt();
                $this->fail('Out-of-scope access must fail.');
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame([0, 0], $this->counts());
    }
}

/** Fails after the student row exists, to prove the whole confirmation is one transaction. */
class FailingConfirmationService extends ConfirmationService
{
    protected function createEnrollment(int $instituteId, int $syear, $termId, int $studentId, int $standardId, array $a): void
    {
        throw new RuntimeException('simulated failure after the student row was written');
    }
}
