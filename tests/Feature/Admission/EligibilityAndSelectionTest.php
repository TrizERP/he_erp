<?php

namespace Tests\Feature\Admission;

use App\Models\admission\AdmissionEligibilityRule;
use App\Models\admission\AdmissionHistory;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\EligibilityService;
use App\Services\Admission\SelectionService;
use App\Services\Admission\WorkflowService;

class EligibilityAndSelectionTest extends AdmissionTestCase
{
    protected EligibilityService $eligibility;
    protected SelectionService $selection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eligibility = app(EligibilityService::class);
        $this->selection = app(SelectionService::class);
    }

    protected function rule(array $o = []): AdmissionEligibilityRule
    {
        return AdmissionEligibilityRule::create($o + [
            'sub_institute_id' => self::INST, 'label' => 'Minimum percentage', 'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'is_mandatory' => true, 'status' => true,
        ]);
    }

    public function test_with_no_approved_criteria_nobody_is_ever_marked_eligible(): void
    {
        $id = $this->makeReadyApplication(['percentage' => '99']);

        $result = $this->eligibility->evaluate(self::INST, $id);

        $this->assertSame('needs_review', $result['status']);
        $this->assertStringContainsString('No approved eligibility criteria', $result['reason']);
    }

    public function test_meeting_all_mandatory_criteria_is_eligible_and_shows_the_values_used(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => '72.5%']);

        $result = $this->eligibility->evaluate(self::INST, $id);

        $this->assertSame('eligible', $result['status']);
        $this->assertSame('72.5%', $result['values'][0]['actual']);
        $this->assertSame('pass', $result['values'][0]['outcome']);
    }

    public function test_a_failed_mandatory_criterion_makes_the_applicant_ineligible_with_the_reason(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => '40']);

        $result = $this->eligibility->evaluate(self::INST, $id);

        $this->assertSame('ineligible', $result['status']);
        $this->assertStringContainsString('Minimum percentage', $result['reason']);
    }

    public function test_a_missing_value_is_routed_to_needs_review_not_rejected(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => null]);

        $this->assertSame('needs_review', $this->eligibility->evaluate(self::INST, $id)['status']);
    }

    public function test_an_unreadable_value_is_routed_to_needs_review_not_rejected(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => 'CGPA 8.2']);

        $this->assertSame('needs_review', $this->eligibility->evaluate(self::INST, $id)['status']);
    }

    public function test_a_non_mandatory_criterion_never_changes_the_outcome(): void
    {
        $this->rule();
        $this->rule(['label' => 'Preferred category', 'field' => 'category', 'operator' => '=', 'value' => 'OBC', 'is_mandatory' => false]);
        $id = $this->makeReadyApplication(['percentage' => '60', 'category' => 'General']);

        $result = $this->eligibility->evaluate(self::INST, $id);

        $this->assertSame('eligible', $result['status']);
        $this->assertSame('fail', $result['values'][1]['outcome'], 'informational rule is still shown');
    }

    public function test_criteria_are_scoped_to_programme_year_and_institute(): void
    {
        $this->rule(['standard_id' => 2]);               // other programme
        $this->rule(['syear' => 2020]);                  // other year
        $this->rule(['sub_institute_id' => self::OTHER_INST]);
        $this->rule(['status' => false]);
        $id = $this->makeReadyApplication(['percentage' => '10']);

        $this->assertSame('needs_review', $this->eligibility->evaluate(self::INST, $id)['status'], 'none of those rules apply, so a person decides');
    }

    public function test_run_stores_the_result_and_audits_it(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => '40']);

        $this->eligibility->run(self::INST, $id);

        $wf = app(WorkflowService::class)->ensure(self::INST, $id);
        $this->assertSame('ineligible', $wf->eligibility_status);
        $this->assertNotEmpty($wf->eligibility_values);
        $this->assertSame(1, AdmissionHistory::where('action', 'eligibility_evaluated')->where('to_status', 'ineligible')->count());
    }

    public function test_a_manual_decision_needs_a_reason_is_audited_and_survives_re_evaluation(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => '40']);
        $this->eligibility->run(self::INST, $id);

        try {
            $this->eligibility->override(self::INST, $id, 'eligible', '   ');
            $this->fail('A reason is mandatory.');
        } catch (AdmissionException $e) {
            $this->assertSame('ineligible', app(WorkflowService::class)->ensure(self::INST, $id)->eligibility_status);
        }

        $wf = $this->eligibility->override(self::INST, $id, 'eligible', 'Board moderation marks applied');
        $this->assertSame('eligible', $wf->eligibility_status);
        $this->assertTrue($wf->eligibility_override);
        $this->assertTrue((bool) AdmissionHistory::where('action', 'eligibility_override')->first()->is_override);

        $again = $this->eligibility->run(self::INST, $id);
        $this->assertTrue($again['kept_override'] ?? false);
        $this->assertSame('eligible', app(WorkflowService::class)->ensure(self::INST, $id)->eligibility_status);

        $this->eligibility->run(self::INST, $id, true);
        $this->assertSame('ineligible', app(WorkflowService::class)->ensure(self::INST, $id)->eligibility_status, 'explicit reset re-evaluates');
    }

    public function test_an_ineligible_applicant_cannot_be_shortlisted_or_selected_without_an_override(): void
    {
        $this->rule();
        $id = $this->makeReadyApplication(['percentage' => '40']);
        $this->eligibility->run(self::INST, $id);

        foreach (['shortlisted', 'selected'] as $status) {
            try {
                $this->selection->decide(self::INST, $id, $status, null);
                $this->fail("{$status} must be blocked for an ineligible applicant");
            } catch (AdmissionException $e) {
                $this->assertStringContainsString('Ineligible', $e->getMessage());
            }
        }

        $this->eligibility->override(self::INST, $id, 'eligible', 'Approved by committee');
        $this->assertSame('selected', $this->selection->decide(self::INST, $id, 'selected', null)->selection_status);
    }

    public function test_selection_requires_remarks_for_reject_and_waitlist_and_follows_the_transition_map(): void
    {
        $id = $this->makeReadyApplication();

        foreach (['rejected', 'waitlisted'] as $status) {
            try {
                $this->selection->decide(self::INST, $id, $status, '');
                $this->fail("{$status} needs remarks");
            } catch (AdmissionException $e) {
                $this->assertStringContainsString('Remarks', $e->getMessage());
            }
        }

        $this->selection->decide(self::INST, $id, 'rejected', 'Incomplete application');
        try {
            $this->selection->decide(self::INST, $id, 'selected', null);
            $this->fail('rejected -> selected is not a valid move');
        } catch (AdmissionException $e) {
            $this->assertStringContainsString('cannot move', $e->getMessage());
        }
        $this->assertSame('under_review', $this->selection->decide(self::INST, $id, 'under_review', null)->selection_status);
    }

    public function test_repeating_the_same_selection_is_a_no_op(): void
    {
        $id = $this->makeReadyApplication();

        $this->selection->decide(self::INST, $id, 'shortlisted', null);
        $this->selection->decide(self::INST, $id, 'shortlisted', null);

        $this->assertSame(1, AdmissionHistory::where('action', 'selection_shortlisted')->count());
    }

    public function test_the_institute_can_require_eligibility_before_shortlisting(): void
    {
        $id = $this->makeReadyApplication();
        $this->setSetting('require_eligibility', '1');

        $this->expectException(AdmissionException::class);
        $this->selection->decide(self::INST, $id, 'shortlisted', null);
    }

    public function test_a_draft_application_cannot_be_reviewed(): void
    {
        $id = $this->makeEnquiry();
        app(WorkflowService::class)->convert(self::INST, $id); // draft

        $this->expectException(AdmissionException::class);
        $this->expectExceptionMessage('submitted');
        $this->selection->decide(self::INST, $id, 'shortlisted', null);
    }
}
