<?php

namespace Tests\Feature\ApiV1;

use App\Support\Api\ApiToken;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Base for V1 module tests. Builds the minimal legacy tables on in-memory SQLite and seeds two institutes with a
 * few users. A guard aborts if the connection is anything but in-memory SQLite, so the real database is never touched.
 *
 * Users: 10 = Admin (inst 1, full rights on menu 138), 11 = Teacher (inst 1, view only), 12 = Admin of inst 2,
 * 13 = a user with no rights rows at all.
 */
abstract class ApiV1TestCase extends TestCase
{
    protected const INST = 1;
    protected const OTHER = 2;
    protected int $syear;

    protected function setUp(): void
    {
        parent::setUp();
        $c = DB::connection();
        if ($c->getDriverName() !== 'sqlite' || $c->getDatabaseName() !== ':memory:') {
            $this->fail('Refusing to run outside in-memory SQLite.');
        }
        $this->syear = (int) date('Y');
        $this->buildSchema();
        $this->seedCore();
    }

    private function buildSchema(): void
    {
        Schema::create('tbluser', function (Blueprint $t) {
            $t->increments('id');
            foreach (['user_name', 'email', 'password', 'first_name', 'last_name', 'image', 'status'] as $col) {
                $t->string($col)->nullable();
            }
            $t->integer('user_profile_id')->nullable();
            $t->integer('sub_institute_id')->default(0);
            $t->integer('client_id')->nullable();
            $t->integer('is_admin')->default(0);
        });
        Schema::create('school_setup', function (Blueprint $t) {
            $t->increments('id');
            $t->string('SchoolName')->nullable();
            $t->string('ShortCode')->nullable();
            $t->string('institute_type')->nullable();
            $t->integer('client_id')->nullable();
        });
        Schema::create('academic_year', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('sub_institute_id');
            $t->integer('syear');
            $t->integer('term_id')->nullable();
            $t->string('title')->nullable();
            $t->integer('sort_order')->default(0);
            $t->date('start_date');
            $t->date('end_date');
        });
        Schema::create('tbluserprofilemaster', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->integer('parent_id')->nullable();
            $t->integer('sub_institute_id')->nullable();
        });
        Schema::create('tblmenumaster', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->integer('parent_menu_id')->default(0);
            $t->integer('level');
            $t->integer('status')->default(1);
            $t->integer('sort_order')->default(0);
            $t->string('link')->nullable();
            $t->string('icon')->nullable();
            $t->string('sub_institute_id')->nullable();
            $t->string('client_id')->nullable();
            $t->string('menu_type')->nullable();
        });
        foreach (['tblgroupwise_rights' => 'profile_id', 'tblindividual_rights' => 'user_id'] as $table => $key) {
            Schema::create($table, function (Blueprint $t) use ($key) {
                $t->increments('id');
                $t->integer($key);
                $t->integer('menu_id');
                $t->integer('sub_institute_id');
                foreach (['can_view', 'can_add', 'can_edit', 'can_delete'] as $c) {
                    $t->integer($c)->default(0);
                }
            });
        }
        Schema::create('academic_section', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('sub_institute_id');
            foreach (['title', 'short_name', 'shift', 'medium', 'payment_link'] as $col) {
                $t->string($col)->nullable();
            }
            $t->integer('sort_order')->nullable();
            $t->timestamps();
        });
        Schema::create('standard', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('grade_id')->nullable();
            $t->string('name')->nullable();
            $t->string('short_name')->nullable();
            $t->integer('sort_order')->nullable();
            $t->string('medium')->nullable();
            $t->integer('sub_institute_id');
            $t->string('course_duration')->nullable();
            $t->integer('next_grade_id')->nullable();
            $t->integer('next_standard_id')->nullable();
            $t->string('school_stream')->nullable();
            $t->integer('marking_period_id')->nullable();
            $t->timestamps();
        });
        Schema::create('division', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->integer('sub_institute_id');
            $t->timestamps();
        });
        Schema::create('subject', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('standard_id')->nullable();
            $t->string('subject_name')->nullable();
            $t->string('subject_code')->nullable();
            $t->string('subject_type')->nullable();
            $t->string('short_name')->nullable();
            $t->integer('sub_institute_id');
            $t->integer('marking_period_id')->nullable();
            $t->string('status')->nullable();
            $t->timestamps();
        });
        Schema::create('app_language', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('menu_id')->nullable();
            $t->string('menu')->nullable();
            $t->string('string')->nullable();
            $t->string('value')->nullable();
            $t->integer('status')->default(1);
            $t->integer('sub_institute_id')->default(0);
            $t->integer('created_by')->nullable();
            $t->timestamps();
        });
    }

    private function seedCore(): void
    {
        $y = $this->syear;
        DB::table('school_setup')->insert([
            ['id' => 1, 'SchoolName' => 'School One', 'ShortCode' => 'S1', 'institute_type' => 'college', 'client_id' => 9],
            ['id' => 2, 'SchoolName' => 'School Two', 'ShortCode' => 'S2', 'institute_type' => 'school', 'client_id' => 9],
        ]);
        foreach ([1, 2] as $i) {
            DB::table('academic_year')->insert([
                ['sub_institute_id' => $i, 'syear' => $y, 'term_id' => 1, 'title' => 'SEMESTER-1', 'start_date' => "$y-01-01", 'end_date' => "$y-12-31"],
                ['sub_institute_id' => $i, 'syear' => $y, 'term_id' => 2, 'title' => 'SEMESTER-2', 'start_date' => "$y-01-01", 'end_date' => "$y-12-31"],
            ]);
        }
        DB::table('tbluserprofilemaster')->insert([['id' => 1, 'name' => 'Admin'], ['id' => 2, 'name' => 'Teacher'], ['id' => 3, 'name' => 'Clerk']]);
        $base = ['status' => '1', 'client_id' => 9, 'is_admin' => 0, 'password' => 'x'];
        DB::table('tbluser')->insert([
            $base + ['id' => 10, 'email' => 'admin@x.test', 'first_name' => 'Ada', 'last_name' => 'Min', 'user_profile_id' => 1, 'sub_institute_id' => 1],
            $base + ['id' => 11, 'email' => 'teacher@x.test', 'first_name' => 'Tea', 'last_name' => 'Cher', 'user_profile_id' => 2, 'sub_institute_id' => 1],
            $base + ['id' => 12, 'email' => 'admin2@x.test', 'first_name' => 'Other', 'last_name' => 'Admin', 'user_profile_id' => 1, 'sub_institute_id' => 2],
            $base + ['id' => 13, 'email' => 'none@x.test', 'first_name' => 'No', 'last_name' => 'Rights', 'user_profile_id' => 3, 'sub_institute_id' => 1],
        ]);

        // 37 "Institute (Master)" (heading) > 138 "Create Subject" (route subject_master.index), both MASTER-type
        $menu = ['status' => 1, 'sort_order' => 0, 'link' => null, 'icon' => null, 'sub_institute_id' => '1,2', 'client_id' => '9', 'menu_type' => 'MASTER'];
        DB::table('tblmenumaster')->insert($menu + ['id' => 37, 'name' => 'Institute (Master)', 'level' => 1, 'parent_menu_id' => 0]);
        DB::table('tblmenumaster')->insert(['link' => 'subject_master.index'] + $menu + ['id' => 138, 'name' => 'Create Subject', 'level' => 2, 'parent_menu_id' => 37]);
        $rights = fn ($profile, $inst, $v, $a, $e, $d) => ['profile_id' => $profile, 'menu_id' => 138, 'sub_institute_id' => $inst, 'can_view' => $v, 'can_add' => $a, 'can_edit' => $e, 'can_delete' => $d];
        DB::table('tblgroupwise_rights')->insert([
            $rights(1, 1, 1, 1, 1, 1), // admin of institute 1: everything
            $rights(2, 1, 1, 0, 0, 0), // teacher: view only
            $rights(1, 2, 1, 1, 1, 1), // admin of institute 2
        ]);
    }

    /** Standards for the current term (1) of institute 1: ids 1 and 2; one for term 2 (id 3); one for institute 2 (id 4). */
    protected function seedStandards(): void
    {
        DB::table('academic_section')->insert([
            ['id' => 1, 'sub_institute_id' => 1, 'title' => 'Degree', 'short_name' => 'DEG', 'sort_order' => 1],
            ['id' => 2, 'sub_institute_id' => 1, 'title' => 'Diploma', 'short_name' => 'DIP', 'sort_order' => 2],
            ['id' => 3, 'sub_institute_id' => 2, 'title' => 'School', 'short_name' => 'SCH', 'sort_order' => 1],
        ]);
        DB::table('standard')->insert([
            ['id' => 1, 'grade_id' => 1, 'name' => 'Sem 1', 'short_name' => 'S1', 'sort_order' => 1, 'sub_institute_id' => 1, 'marking_period_id' => 1],
            ['id' => 2, 'grade_id' => 1, 'name' => 'Sem 3', 'short_name' => 'S3', 'sort_order' => 2, 'sub_institute_id' => 1, 'marking_period_id' => 1],
            ['id' => 3, 'grade_id' => 2, 'name' => 'Sem 2', 'short_name' => 'S2', 'sort_order' => 1, 'sub_institute_id' => 1, 'marking_period_id' => 2],
            ['id' => 4, 'grade_id' => 3, 'name' => 'Class 5', 'short_name' => 'C5', 'sort_order' => 1, 'sub_institute_id' => 2, 'marking_period_id' => 1],
        ]);
    }

    protected function token(int $userId): string
    {
        return ApiToken::issue($userId, 'user');
    }

    /** Request helper: as user $id, optionally with context headers (e.g. X-Term-Id). */
    protected function as(int $userId, array $headers = [])
    {
        $this->defaultHeaders = []; // headers persist between requests in a test; start clean each time

        return $this->withHeaders(['Authorization' => 'Bearer '.$this->token($userId)] + $headers);
    }
}
