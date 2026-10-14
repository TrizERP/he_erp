<?php

namespace Tests\Feature\Admission;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Base for the admission tests. Everything runs on an in-memory SQLite database that is built from scratch in
 * setUp(): the minimal legacy tables the module reads/writes, then the real admission migrations. A hard guard
 * aborts if the connection is anything but in-memory SQLite, so these tests can never touch the real database.
 */
abstract class AdmissionTestCase extends TestCase
{
    protected const INST = 1;
    protected const OTHER_INST = 2;
    protected const SYEAR = 2026;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
            $this->fail('Refusing to run: admission tests require in-memory SQLite (got '.$connection->getDriverName().' / '.$connection->getDatabaseName().').');
        }

        $this->buildLegacySchema();
        Artisan::call('migrate', [
            '--path' => [
                'database/migrations/2026_10_18_100001_create_admission_core_tables.php',
                'database/migrations/2026_10_18_100002_create_admission_document_tables.php',
                'database/migrations/2026_10_18_100003_create_admission_eligibility_and_offer_tables.php',
            ],
            '--force' => true,
        ]);

        DB::table('standard')->insert(['id' => 1, 'name' => 'B.Sc Year 1', 'grade_id' => 10, 'sub_institute_id' => self::INST]);
        DB::table('standard')->insert(['id' => 2, 'name' => 'B.Com Year 1', 'grade_id' => 11, 'sub_institute_id' => self::INST]);
        DB::table('tbluserprofilemaster')->insert(['id' => 5, 'name' => 'Student', 'sub_institute_id' => self::INST]);
        DB::table('tbluserprofilemaster')->insert(['id' => 6, 'name' => 'Student', 'sub_institute_id' => self::OTHER_INST]);

        $this->actAs('Admission Officer');
    }

    /** Log in as a profile (the ERP keeps these in the session). */
    protected function actAs(string $profile, int $institute = self::INST, int $userId = 7): void
    {
        session()->put([
            'user_id' => $userId, 'sub_institute_id' => $institute, 'syear' => self::SYEAR, 'term_id' => 1,
            'user_profile_name' => $profile, 'user_profile_id' => 99,
        ]);
    }

    protected function sessionFor(string $profile, int $institute = self::INST): array
    {
        return [
            'user_id' => 7, 'sub_institute_id' => $institute, 'syear' => self::SYEAR, 'term_id' => 1,
            'user_profile_name' => $profile, 'user_profile_id' => 99,
        ];
    }

    // -- factories -----------------------------------------------------------------------------------

    protected function makeEnquiry(array $o = []): int
    {
        static $n = 0;
        $n++;

        return (int) DB::table('admission_enquiry')->insertGetId($o + [
            'enquiry_no' => 'ENQ'.str_pad((string) $n, 4, '0', STR_PAD_LEFT), 'first_name' => 'Asha', 'last_name' => 'Patel',
            'mobile' => '99999'.str_pad((string) $n, 5, '0', STR_PAD_LEFT), 'email' => "asha{$n}@example.com", 'gender' => 'F',
            'syear' => self::SYEAR, 'admission_standard' => 1, 'source_of_enquiry' => 'Website', 'category' => 'General',
            'father_name' => 'Ramesh Patel', 'date_of_birth' => '2007-05-01', 'sub_institute_id' => self::INST,
        ]);
    }

    protected function makeForm(int $enquiryId, array $o = []): int
    {
        return (int) DB::table('admission_form')->insertGetId($o + [
            'enquiry_id' => (string) $enquiryId, 'form_no' => (string) (100 + $enquiryId), 'admission_standard' => '1',
            'registration_no' => 'R'.$enquiryId, 'sub_institute_id' => (string) self::INST,
        ]);
    }

    protected function makeRegistration(int $enquiryId, array $o = []): int
    {
        return (int) DB::table('admission_registration')->insertGetId($o + [
            'enquiry_id' => $enquiryId, 'admission_date' => '2026-06-15', 'admission_division' => 3, 'student_quota' => 1,
            'sub_institute_id' => self::INST,
        ]);
    }

    /** An application that is ready to confirm: form + registration, submitted. */
    protected function makeReadyApplication(array $enquiry = [], array $registration = []): int
    {
        $id = $this->makeEnquiry($enquiry);
        $this->makeForm($id);
        $this->makeRegistration($id, $registration);
        app(\App\Services\Admission\WorkflowService::class)->ensure(self::INST, $id); // legacy form present => submitted

        return $id;
    }

    protected function setSetting(string $key, string $value, int $institute = self::INST): void
    {
        DB::table('admission_setting')->updateOrInsert(['sub_institute_id' => $institute, 'setting_key' => $key], ['setting_value' => $value]);
        app()->forgetInstance(\App\Services\Admission\SettingService::class);
        app()->forgetInstance(\App\Services\Admission\ConfirmationService::class);
        app()->forgetInstance(\App\Services\Admission\OfferService::class);
        app()->forgetInstance(\App\Services\Admission\SelectionService::class);
        app()->forgetInstance(\App\Services\Admission\FeeService::class);
    }

    // -- minimal legacy schema ---------------------------------------------------------------------------

    protected function buildLegacySchema(): void
    {
        Schema::create('admission_enquiry', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('enquiry_no')->nullable();
            foreach (['first_name', 'middle_name', 'last_name', 'gender', 'mobile', 'email', 'address', 'source_of_enquiry', 'category', 'percentage',
                         'father_name', 'mother_name', 'annual_income', 'aadharcard_number', 'place_of_birth', 'religion', 'nationality', 'previous_standard', 'remarks'] as $c) {
                $t->string($c)->nullable();
            }
            $t->date('date_of_birth')->nullable();
            $t->integer('age')->nullable();
            $t->integer('syear')->nullable();
            $t->integer('admission_standard')->nullable();
            $t->timestamp('created_on')->nullable()->useCurrent();
            $t->integer('created_by')->nullable();
            $t->integer('sub_institute_id')->nullable();
            $t->bigInteger('receipt_id')->nullable();
            $t->longText('receipt_html')->nullable();
        });
        Schema::create('admission_form', function (Blueprint $t) {
            $t->bigIncrements('id');
            foreach (['enquiry_id', 'enquiry_no', 'form_no', 'status', 'admission_standard', 'annual_income', 'admission_docket_no', 'registration_no',
                         'last_exam_percentage', 'admission_form_fee', 'receipt_id', 'created_by', 'sub_institute_id'] as $c) {
                $t->string($c)->nullable();
            }
            $t->longText('receipt_html')->nullable();
            $t->timestamp('created_on')->nullable()->useCurrent();
        });
        Schema::create('admission_registration', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('enquiry_id')->nullable();
            $t->integer('enquiry_no')->nullable();
            $t->integer('created_by')->default(0);
            $t->string('enrollment_no')->nullable();
            $t->integer('amount')->nullable();
            foreach (['payment_mode', 'blood_group', 'aadhar_number', 'mother_name', 'place_of_birth'] as $c) {
                $t->string($c)->nullable();
            }
            $t->string('mother_mobile_number')->nullable();
            $t->date('date_of_payment')->nullable();
            $t->date('admission_date')->nullable();
            $t->string('admission_division')->nullable();
            $t->bigInteger('student_quota')->nullable();
            $t->integer('sub_institute_id')->default(0);
        });
        Schema::create('tblstudent', function (Blueprint $t) {
            $t->bigIncrements('id');
            foreach (['enrollment_no', 'first_name', 'middle_name', 'last_name', 'gender', 'mobile', 'email', 'address', 'username', 'admission_id', 'place_of_birth',
                         'adharnumber', 'mother_name', 'mother_mobile', 'father_name', 'anuualincome', 'bloodgroup', 'admission_docket_no', 'registration_no'] as $c) {
                $t->string($c)->nullable();
            }
            $t->integer('admission_year')->nullable();
            $t->integer('since_when')->nullable();
            $t->date('admission_date')->nullable();
            $t->date('dob')->nullable();
            $t->bigInteger('user_profile_id')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
            $t->integer('status')->nullable();
        });
        Schema::create('tblstudent_enrollment', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('syear')->nullable();
            $t->bigInteger('student_id')->nullable();
            $t->bigInteger('grade_id')->nullable();
            $t->bigInteger('standard_id')->nullable();
            $t->bigInteger('section_id')->nullable();
            $t->string('student_quota')->nullable();
            $t->date('start_date')->nullable();
            $t->string('enrollment_code')->nullable();
            $t->integer('term_id')->nullable();
            $t->string('admission_fees')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
            $t->unique(['syear', 'student_id', 'term_id', 'sub_institute_id'], 'enr_key');
        });
        Schema::create('standard', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name')->nullable();
            $t->bigInteger('grade_id')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
        });
        Schema::create('tbluserprofilemaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
        });
        Schema::create('enrollment_prefix_master', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('sub_institute_id')->nullable();
            $t->string('prefix')->nullable();
            $t->string('standards')->nullable();
        });
        Schema::create('student_quota', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('title')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
        });
        Schema::create('fees_breackoff', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('standard_id')->nullable();
            $t->bigInteger('grade_id')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
            $t->bigInteger('quota')->nullable();
            $t->integer('amount')->nullable();
            $t->integer('syear')->nullable();
            $t->integer('admission_year')->nullable();
            $t->string('month_id')->nullable();
        });
        // used by the real check_permissions middleware in the HTTP tests (it expects an ERP-tour row per user)
        Schema::create('erptour', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('user_id')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
            foreach (['fees_collect', 'fees_title', 'fees_structure', 'student_quota', 'fees_map'] as $c) {
                $t->integer($c)->nullable();
            }
            $t->nullableTimestamps();
        });
        DB::table('erptour')->insert([['user_id' => 7, 'sub_institute_id' => 1], ['user_id' => 7, 'sub_institute_id' => 2]]);
        Schema::create('tblmenumaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->integer('status')->default(1);
            $t->string('link')->nullable();
        });
        Schema::create('tblindividual_rights', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('menu_id');
            $t->bigInteger('profile_id');
            $t->bigInteger('user_id');
            $t->bigInteger('sub_institute_id');
            $t->integer('can_view')->default(0);
            $t->integer('can_add')->default(0);
            $t->integer('can_edit')->default(0);
            $t->integer('can_delete')->default(0);
        });
        Schema::create('tblgroupwise_rights', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('menu_id');
            $t->bigInteger('profile_id');
            $t->bigInteger('sub_institute_id');
            $t->integer('can_view')->default(0);
            $t->integer('can_add')->default(0);
            $t->integer('can_edit')->default(0);
            $t->integer('can_delete')->default(0);
        });
    }
}
