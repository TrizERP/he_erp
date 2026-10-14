<?php

namespace Tests\Feature\Admission;

use Database\Seeders\AdmissionMenuSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationAndSeederTest extends AdmissionTestCase
{
    protected const TABLES = [
        'admission_workflow', 'admission_history', 'admission_setting', 'admission_enrollment_lock',
        'admission_document_requirement', 'admission_document', 'admission_eligibility_rule', 'admission_offer',
    ];

    protected const MIGRATIONS = [
        'database/migrations/2026_10_18_100003_create_admission_eligibility_and_offer_tables.php',
        'database/migrations/2026_10_18_100002_create_admission_document_tables.php',
        'database/migrations/2026_10_18_100001_create_admission_core_tables.php',
    ];

    public function test_the_migrations_create_only_new_tables_and_never_alter_a_legacy_table(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} should exist");
        }
        // legacy tables are untouched: none of the new workflow columns were added to them
        $this->assertFalse(Schema::hasColumn('admission_enquiry', 'application_status'));
        $this->assertFalse(Schema::hasColumn('tblstudent', 'confirmation_status'));
    }

    public function test_the_migrations_roll_back_cleanly_and_can_be_re_applied(): void
    {
        foreach (self::MIGRATIONS as $path) {
            Artisan::call('migrate:rollback', ['--path' => $path, '--force' => true]);
        }
        foreach (self::TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), "{$table} should be gone after rollback");
        }
        $this->assertTrue(Schema::hasTable('admission_enquiry'), 'rollback never touches legacy tables');

        Artisan::call('migrate', ['--path' => array_reverse(self::MIGRATIONS), '--force' => true]);
        foreach (self::TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} should be back");
        }
    }

    public function test_the_unique_indexes_that_back_the_business_rules_exist(): void
    {
        DB::table('admission_workflow')->insert(['sub_institute_id' => 1, 'enquiry_id' => 5]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('admission_workflow')->insert(['sub_institute_id' => 1, 'enquiry_id' => 5]); // one workflow per enquiry
    }

    public function test_the_menu_seeder_is_idempotent_creates_no_duplicates_and_never_adds_a_second_admin_profile(): void
    {
        $this->buildMenuSchema();
        DB::table('school_setup')->insert([['Id' => 1, 'client_id' => 10], ['Id' => 2, 'client_id' => 10]]);
        DB::table('tbluserprofilemaster')->insert([
            ['parent_id' => 0, 'name' => 'Admin', 'sub_institute_id' => 1], ['parent_id' => 0, 'name' => 'Admin', 'sub_institute_id' => 2],
        ]);
        $existingRoot = DB::table('tblmenumaster')->insertGetId([
            'name' => 'Admission', 'parent_menu_id' => 0, 'level' => 1, 'link' => 'javascript:void(0);', 'status' => 1, 'sort_order' => 3,
        ]);

        (new AdmissionMenuSeeder())->run();
        $afterFirst = [DB::table('tblmenumaster')->count(), DB::table('tbluserprofilemaster')->count(), DB::table('tblgroupwise_rights')->count()];
        (new AdmissionMenuSeeder())->run();
        $afterSecond = [DB::table('tblmenumaster')->count(), DB::table('tbluserprofilemaster')->count(), DB::table('tblgroupwise_rights')->count()];

        $this->assertSame($afterFirst, $afterSecond, 're-running changes nothing');
        $this->assertSame(1, DB::table('tblmenumaster')->where('name', 'Admission')->where('level', 1)->count(), 'the existing Admission parent is reused');
        $children = DB::table('tblmenumaster')->where('parent_menu_id', $existingRoot)->pluck('link')->all();
        $this->assertContains('admission.dashboard.index', $children);
        $this->assertContains('admission.settings.index', $children);
        $this->assertSame(2, DB::table('tbluserprofilemaster')->where('name', 'Admin')->count(), 'no extra Admin profile');
        $this->assertSame(2, DB::table('tbluserprofilemaster')->where('name', 'Admission Officer')->count(), 'one per institute');

        // an officer can use the workflow pages but not settings; accounts only sees dashboard/fees/admitted/reports
        $rights = fn (string $profile, string $link) => DB::table('tblgroupwise_rights as r')
            ->join('tbluserprofilemaster as p', 'p.id', '=', 'r.profile_id')->join('tblmenumaster as m', 'm.id', '=', 'r.menu_id')
            ->where('p.name', $profile)->where('p.sub_institute_id', 1)->where('m.link', $link)->exists();
        $this->assertTrue($rights('Admission Officer', 'admission.review.index'));
        $this->assertFalse($rights('Admission Officer', 'admission.settings.index'));
        $this->assertTrue($rights('Admin', 'admission.settings.index'));
        $this->assertTrue($rights('Accounts Operator', 'admission.fees.index'));
        $this->assertFalse($rights('Accounts Operator', 'admission.documents.index'));
        $this->assertFalse(DB::table('tblmenumaster')->whereIn('link', ['admission_enquiry.index', 'admission_registration.index'])->exists(), 'legacy menus are not duplicated');
    }

    /** The wider ERP menu/profile tables the seeder writes to (the base test only has the minimal ones). */
    protected function buildMenuSchema(): void
    {
        Schema::drop('tblmenumaster');
        Schema::drop('tblgroupwise_rights');
        Schema::drop('tbluserprofilemaster');
        Schema::create('tblmenumaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name')->nullable();
            $t->bigInteger('parent_menu_id')->default(0);
            $t->integer('level')->nullable();
            $t->string('description')->nullable();
            $t->integer('status')->default(1);
            $t->integer('sort_order')->nullable();
            $t->string('link')->nullable();
            $t->string('icon')->nullable();
            $t->string('sub_institute_id')->nullable();
            $t->string('client_id')->nullable();
            $t->string('menu_type')->nullable();
            $t->string('site_map_name')->nullable();
            $t->string('menu_path')->nullable();
            $t->timestamps();
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
        Schema::create('tbluserprofilemaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('parent_id')->default(0);
            $t->string('name')->nullable();
            $t->string('description')->nullable();
            $t->integer('sort_order')->nullable();
            $t->integer('status')->default(1);
            $t->bigInteger('sub_institute_id')->nullable();
        });
        Schema::create('school_setup', function (Blueprint $t) {
            $t->bigInteger('Id')->primary();
            $t->bigInteger('client_id')->nullable();
        });
    }
}
