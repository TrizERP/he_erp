<?php

namespace Tests\Feature\Pbas;

use App\Http\Middleware\LogRouteMiddleware;
use App\Http\Middleware\MenuMiddleware;
use Database\Seeders\PbasMenuSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Base for the PBAS tests. Everything runs on an in-memory SQLite database built from scratch in setUp(): the few
 * ERP tables PBAS reads, then the real PBAS migration and menu seeder. A hard guard aborts on any other connection,
 * so these tests can never touch the real database. The real `session`, `check_permissions` and `pbas.role`
 * middleware run; only `menu` (sidebar builder) and `logRoute` (access log) are switched off because they need the
 * full ERP schema, and the shared ERP header/sidebar/footer are replaced by stubs (tests/views/includes).
 */
abstract class PbasTestCase extends TestCase
{
    protected const INST = 1;
    protected const OTHER_INST = 2;
    protected const SYEAR = 2025;

    protected int $deptCse;
    protected int $deptMech;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
            $this->fail('Refusing to run: PBAS tests require in-memory SQLite (got '.$connection->getDriverName().' / '.$connection->getDatabaseName().').');
        }

        $this->withoutMiddleware([MenuMiddleware::class, LogRouteMiddleware::class]);
        $this->app['view']->getFinder()->prependLocation(base_path('tests/views'));

        $this->buildErpSchema();
        Artisan::call('migrate', ['--path' => ['database/migrations/2026_10_22_100000_create_pbas_tables.php'], '--force' => true]);

        $this->deptCse = DB::table('hrms_departments')->insertGetId(['department' => 'Computer Engineering', 'status' => 1, 'sub_institute_id' => self::INST]);
        $this->deptMech = DB::table('hrms_departments')->insertGetId(['department' => 'Mechanical Engineering', 'status' => 1, 'sub_institute_id' => self::INST]);
        DB::table('hrms_departments')->insert(['department' => 'Other Institute Dept', 'status' => 1, 'sub_institute_id' => self::OTHER_INST]);

        foreach ([self::INST, self::OTHER_INST] as $inst) {
            foreach (['Admin', 'HOD', 'Principal', 'Lecturer', 'Assistant Professor', 'Student', 'Accountant'] as $name) {
                DB::table('tbluserprofilemaster')->insert(['name' => $name, 'sub_institute_id' => $inst]);
            }
        }
        DB::table('school_setup')->insert([['Id' => self::INST, 'client_id' => 11], ['Id' => self::OTHER_INST, 'client_id' => 11]]);
        $this->seed(PbasMenuSeeder::class);
    }

    // -- people -------------------------------------------------------------------------------------------

    protected function profileId(string $name, int $institute = self::INST): int
    {
        return (int) DB::table('tbluserprofilemaster')->where(['name' => $name, 'sub_institute_id' => $institute])->value('id');
    }

    /** Create a staff member (tbluser) and return their id. */
    protected function makeUser(string $profile = 'Lecturer', ?int $department = null, array $o = [], int $institute = self::INST): int
    {
        static $n = 0;
        $n++;

        $id = (int) DB::table('tbluser')->insertGetId($o + [
            'user_name' => 'user'.$n, 'first_name' => 'First'.$n, 'last_name' => 'Last'.$n, 'email' => "u{$n}@example.com", 'mobile' => '99999'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'user_profile_id' => $this->profileId($profile, $institute), 'department_id' => $department ?? $this->deptCse,
            'status' => 1, 'sub_institute_id' => $institute, 'employee_id' => 100 + $n,
        ]);
        DB::table('erptour')->insert(['user_id' => $id, 'sub_institute_id' => $institute]);

        return $id;
    }

    protected function sessionFor(string $profile, int $userId, int $institute = self::INST): array
    {
        return [
            'user_id' => $userId, 'sub_institute_id' => $institute, 'syear' => self::SYEAR, 'term_id' => 1,
            'user_profile_name' => $profile, 'user_profile_id' => $this->profileId($profile, $institute),
            'academicYears' => [(object) ['syear' => self::SYEAR - 1], (object) ['syear' => self::SYEAR]],
        ];
    }

    /** Act as a profile; creates the user unless an id is given. */
    protected function as(string $profile, ?int $userId = null, int $institute = self::INST, ?int $department = null)
    {
        $userId ??= $this->makeUser($profile, $department, [], $institute);

        return $this->withSession($this->sessionFor($profile, $userId, $institute));
    }

    protected function currentYear(): string
    {
        return self::SYEAR.'-'.(self::SYEAR + 1);
    }

    // -- minimal ERP schema -------------------------------------------------------------------------------

    protected function buildErpSchema(): void
    {
        Schema::create('tbluser', function (Blueprint $t) {
            $t->bigIncrements('id');
            foreach (['user_name', 'first_name', 'middle_name', 'last_name', 'email', 'mobile', 'address', 'address_2', 'city', 'pincode'] as $c) {
                $t->string($c)->nullable();
            }
            $t->unsignedInteger('user_profile_id')->nullable();
            $t->unsignedBigInteger('jobtitle_id')->nullable();
            $t->unsignedBigInteger('department_id')->nullable();
            $t->unsignedBigInteger('employee_id')->nullable();
            $t->dateTime('joined_date')->nullable();
            $t->integer('sub_institute_id')->default(0);
            $t->integer('status')->default(1);
        });
        Schema::create('tbluserprofilemaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
        });
        Schema::create('hrms_departments', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('department');
            $t->integer('status')->default(1);
            $t->bigInteger('sub_institute_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('hrms_job_titles', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('title');
        });
        // the real check_permissions middleware expects an ERP-tour row per user
        Schema::create('erptour', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('user_id')->nullable();
            $t->bigInteger('sub_institute_id')->nullable();
            foreach (['dashboard', 'school_sidebar', 'fees_collect', 'fees_title', 'fees_structure', 'fees_receipt', 'student_quota', 'fees_map'] as $c) {
                $t->integer($c)->nullable();
            }
        });
        Schema::create('school_setup', function (Blueprint $t) {
            $t->bigInteger('Id')->primary();
            $t->bigInteger('client_id')->nullable();
        });
        Schema::create('tblmenumaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->string('menu_title')->nullable();
            $t->string('menu_sortorder')->nullable();
            $t->string('description')->nullable();
            $t->bigInteger('parent_menu_id')->default(0);
            $t->integer('level');
            $t->integer('status')->default(1);
            $t->integer('sort_order')->default(0);
            $t->string('link')->nullable();
            $t->string('icon')->nullable();
            $t->text('sub_institute_id')->nullable();
            $t->text('client_id')->nullable();
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
    }
}
