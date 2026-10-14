<?php

namespace Tests\Feature\Admission;

use App\Exports\Admission\ReportExport;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\ApplicationQuery;
use App\Services\Admission\ConfirmationService;
use App\Services\Admission\DashboardService;
use App\Services\Admission\OfferService;
use App\Services\Admission\ReportService;
use App\Services\Admission\WorkflowService;
use Illuminate\Support\Facades\DB;

class DashboardAndReportTest extends AdmissionTestCase
{
    /** Builds a spread of applications and returns their ids by role in the story. */
    protected function seedScenario(): array
    {
        $ids = [];
        $ids['inquiry_only'] = $this->makeEnquiry();                                   // no form
        $ids['draft'] = $this->makeEnquiry();                                          // converted, not submitted
        app(WorkflowService::class)->convert(self::INST, $ids['draft']);
        $ids['submitted'] = $this->makeReadyApplication();                             // submitted, unpaid
        $ids['paid_with_doc'] = $this->makeReadyApplication([], ['amount' => 5000, 'payment_mode' => 'Cash', 'date_of_payment' => '2026-06-01']);
        DB::table('admission_document')->insert(['sub_institute_id' => self::INST, 'enquiry_id' => $ids['paid_with_doc'], 'doc_name' => 'Marksheet', 'status' => 'uploaded']);
        $ids['offered'] = $this->makeReadyApplication();
        app(OfferService::class)->issue(self::INST, $ids['offered'], ['offer_date' => now()->toDateString(), 'expiry_date' => now()->addWeek()->toDateString()]);
        $ids['confirmed'] = $this->makeReadyApplication();
        app(ConfirmationService::class)->confirm(self::INST, $ids['confirmed'], self::SYEAR, 1);

        // must never be counted: other institute, and another academic year
        $foreign = $this->makeEnquiry(['sub_institute_id' => self::OTHER_INST]);
        DB::table('admission_form')->insert(['enquiry_id' => (string) $foreign, 'form_no' => '1', 'sub_institute_id' => (string) self::OTHER_INST]);
        $this->makeEnquiry(['syear' => 2025]);

        return $ids;
    }

    public function test_dashboard_counts_are_the_expected_numbers(): void
    {
        $this->seedScenario();

        $m = app(DashboardService::class)->summary(self::INST, self::SYEAR)['metrics'];

        $this->assertSame(6, $m['total_inquiries']['count']);
        $this->assertSame(1, $m['draft_applications']['count']);
        $this->assertSame(4, $m['submitted_applications']['count']);
        $this->assertSame(1, $m['pending_documents']['count']);
        $this->assertSame(3, $m['pending_eligibility']['count'], 'submitted + not yet reviewed + not confirmed');
        $this->assertSame(1, $m['offers_awaiting']['count']);
        $this->assertSame(3, $m['pending_fees']['count'], 'registered, not confirmed, no payment recorded');
        $this->assertSame(1, $m['confirmed']['count']);
    }

    public function test_every_dashboard_tile_matches_the_rows_behind_it(): void
    {
        $this->seedScenario();
        $dashboard = app(DashboardService::class);
        $summary = $dashboard->summary(self::INST, self::SYEAR)['metrics'];

        foreach (array_keys(ApplicationQuery::METRICS) as $metric) {
            $list = $dashboard->drillDown(self::INST, self::SYEAR, $metric);
            $this->assertSame($summary[$metric]['count'], $list->total(), "tile and drill-down differ for {$metric}");
            $this->assertCount(min($list->total(), 25), $list->items());
        }
    }

    public function test_drill_down_lists_exactly_the_right_applicants(): void
    {
        $ids = $this->seedScenario();
        $dashboard = app(DashboardService::class);

        $pick = fn (string $metric) => collect($dashboard->drillDown(self::INST, self::SYEAR, $metric)->items())->pluck('id')->sort()->values()->all();

        $this->assertSame([$ids['draft']], $pick('draft_applications'));
        $this->assertSame([$ids['paid_with_doc']], $pick('pending_documents'));
        $this->assertSame([$ids['offered']], $pick('offers_awaiting'));
        $this->assertSame([$ids['confirmed']], $pick('confirmed'));
        $this->assertSame(collect([$ids['draft'], $ids['submitted'], $ids['offered']])->sort()->values()->all(), $pick('pending_fees'));
    }

    public function test_dashboard_never_leaks_another_institute_or_year(): void
    {
        $this->seedScenario();

        $this->assertSame(1, app(DashboardService::class)->summary(self::OTHER_INST, self::SYEAR)['metrics']['total_inquiries']['count']);
        $this->assertSame(1, app(DashboardService::class)->summary(self::INST, 2025)['metrics']['total_inquiries']['count']);
    }

    public function test_the_drill_down_search_filters_within_the_metric(): void
    {
        $this->makeReadyApplication(['first_name' => 'Zoya']);
        $this->makeReadyApplication(['first_name' => 'Aman']);

        $found = app(DashboardService::class)->drillDown(self::INST, self::SYEAR, 'submitted_applications', ['q' => 'Zoy']);

        $this->assertSame(1, $found->total());
        $this->assertSame('Zoya', $found->items()[0]->first_name);
    }

    public function test_an_unknown_metric_is_rejected(): void
    {
        $this->expectException(AdmissionException::class);
        app(ApplicationQuery::class)->metric('nope', self::INST);
    }

    public function test_syncing_missing_workflow_rows_is_idempotent_and_scoped(): void
    {
        $this->makeEnquiry();
        $this->makeEnquiry(['sub_institute_id' => self::OTHER_INST]);
        $workflow = app(WorkflowService::class);

        $workflow->syncMissing(self::INST);
        $workflow->syncMissing(self::INST);

        $this->assertSame(1, DB::table('admission_workflow')->where('sub_institute_id', self::INST)->count());
        $this->assertSame(0, DB::table('admission_workflow')->where('sub_institute_id', self::OTHER_INST)->count());
    }

    // -- reports -------------------------------------------------------------------------------------------------

    public function test_every_report_runs_and_is_scoped(): void
    {
        $this->seedScenario();
        $reports = app(ReportService::class);

        foreach (array_keys(ReportService::TYPES) as $type) {
            $report = $reports->build($type, self::INST, self::SYEAR);
            $this->assertSame(ReportService::TYPES[$type], $report['title']);
            foreach ($report['rows'] as $row) {
                $this->assertCount(count($report['headings']), $row, "{$type}: row width matches headings");
            }
        }
    }

    public function test_the_funnel_and_status_reports_carry_the_right_numbers(): void
    {
        $this->seedScenario();
        $reports = app(ReportService::class);

        $funnel = $reports->build('inquiry_funnel', self::INST, self::SYEAR);
        $website = collect($funnel['rows'])->firstWhere(0, 'Website');
        $this->assertSame([6, 5, 4, 0, 1], array_slice($website, 1), 'inquiries, application created, submitted, selected, confirmed');

        $status = $reports->build('applications_status', self::INST, self::SYEAR);
        $row = collect($status['rows'])->firstWhere(0, 'B.Sc Year 1');
        $this->assertSame(1, $row[1], 'draft');
        $this->assertSame(4, $row[2], 'submitted');
        $this->assertSame(1, $row[8], 'confirmed');
    }

    public function test_the_confirmed_report_lists_student_ids_and_the_fee_report_uses_the_existing_fee_records(): void
    {
        $ids = $this->seedScenario();
        DB::table('student_quota')->insert(['id' => 1, 'title' => 'General', 'sub_institute_id' => self::INST]);
        DB::table('fees_breackoff')->insert(['standard_id' => 1, 'grade_id' => 10, 'sub_institute_id' => self::INST, 'quota' => 1, 'amount' => 8000,
            'syear' => self::SYEAR, 'admission_year' => self::SYEAR, 'month_id' => '4202']);
        $reports = app(ReportService::class);

        $confirmed = $reports->build('confirmed', self::INST, self::SYEAR);
        $this->assertCount(1, $confirmed['rows']);
        $studentId = DB::table('admission_workflow')->where('enquiry_id', $ids['confirmed'])->value('student_id');
        $this->assertSame((int) $studentId, (int) $confirmed['rows'][0][3]);

        $fees = $reports->build('fees', self::INST, self::SYEAR);
        $paid = collect($fees['rows'])->first(fn ($r) => $r[4] == 5000);
        $this->assertSame([8000, 5000, 3000, 'partially_paid'], [(int) $paid[3], (int) $paid[4], (int) $paid[5], $paid[6]]);
    }

    public function test_report_filters_narrow_by_programme(): void
    {
        $this->makeReadyApplication();
        $commerce = $this->makeEnquiry(['admission_standard' => 2]);
        DB::table('admission_form')->insert(['enquiry_id' => (string) $commerce, 'form_no' => '9', 'admission_standard' => '2', 'sub_institute_id' => (string) self::INST]);
        $this->makeRegistration($commerce);

        $all = app(ReportService::class)->build('eligibility', self::INST, self::SYEAR);
        $only = app(ReportService::class)->build('eligibility', self::INST, self::SYEAR, ['standard_id' => 2]);

        $this->assertCount(2, $all['rows']);
        $this->assertCount(1, $only['rows']);
        $this->assertSame('B.Com Year 1', $only['rows'][0][2]);
    }

    public function test_the_excel_export_carries_the_same_headings_and_rows(): void
    {
        $this->seedScenario();
        $report = app(ReportService::class)->build('confirmed', self::INST, self::SYEAR);

        $export = new ReportExport($report['title'], $report['headings'], $report['rows']);

        $this->assertSame($report['headings'], $export->headings());
        $this->assertSame($report['rows'], $export->array());
        $this->assertLessThanOrEqual(31, mb_strlen($export->title()));
    }

    public function test_an_unknown_report_is_rejected(): void
    {
        $this->expectException(AdmissionException::class);
        app(ReportService::class)->build('nonsense', self::INST, self::SYEAR);
    }
}
