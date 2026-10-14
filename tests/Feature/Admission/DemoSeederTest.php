<?php

namespace Tests\Feature\Admission;

use App\Services\Admission\ApplicationQuery;
use App\Services\Admission\DashboardService;
use Database\Seeders\AdmissionDemoCleanupSeeder;
use Database\Seeders\AdmissionDemoSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoSeederTest extends AdmissionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('school_setup', function (Blueprint $t) {
            $t->bigInteger('Id')->primary();
        });
        DB::table('school_setup')->insert(['Id' => self::INST]);
        DB::table('student_quota')->insert(['id' => 1, 'title' => 'General', 'sub_institute_id' => self::INST]);
        putenv('DEMO_SYEAR='.self::SYEAR);
        DB::table('tblstudent_enrollment')->insert(['syear' => self::SYEAR, 'student_id' => 999, 'term_id' => 9, 'sub_institute_id' => self::INST]);
    }

    public function test_the_demo_data_fills_every_dashboard_tile_and_is_not_duplicated_on_rerun(): void
    {
        (new AdmissionDemoSeeder())->run();
        (new AdmissionDemoSeeder())->run();

        $this->assertSame(16, DB::table('admission_enquiry')->where('remarks', 'DEMO DATA')->count());
        $metrics = app(DashboardService::class)->summary(self::INST, self::SYEAR)['metrics'];
        foreach (array_keys(ApplicationQuery::METRICS) as $key) {
            $this->assertGreaterThan(0, $metrics[$key]['count'], "tile {$key} should not be empty for the demo");
        }
        $this->assertSame(1, $metrics['confirmed']['count']);
        $this->assertSame(1, DB::table('tblstudent')->count());
        foreach (['eligible', 'ineligible', 'needs_review'] as $status) {
            $this->assertGreaterThan(0, DB::table('admission_workflow')->where('eligibility_status', $status)->count());
        }
        foreach (['issued', 'accepted', 'declined'] as $status) {
            $this->assertGreaterThan(0, DB::table('admission_offer')->where('status', $status)->count());
        }
    }

    public function test_the_cleanup_removes_only_the_demo_data(): void
    {
        $real = $this->makeEnquiry(['first_name' => 'RealApplicant']);
        $realStudent = DB::table('tblstudent')->insertGetId(['admission_id' => '555', 'sub_institute_id' => self::INST, 'enrollment_no' => '1']);

        (new AdmissionDemoSeeder())->run();
        (new AdmissionDemoCleanupSeeder())->run();

        $this->assertSame(0, DB::table('admission_enquiry')->where('remarks', 'DEMO DATA')->count());
        $this->assertSame(1, DB::table('admission_enquiry')->count(), 'the real enquiry survives');
        $this->assertSame(1, DB::table('tblstudent')->count());
        $this->assertSame($realStudent, (int) DB::table('tblstudent')->value('id'));
        foreach (['admission_workflow', 'admission_offer', 'admission_document', 'admission_history', 'admission_document_requirement', 'admission_eligibility_rule', 'admission_form', 'admission_registration'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), "{$table} should be empty");
        }
        $this->assertNotNull($real);
    }
}
