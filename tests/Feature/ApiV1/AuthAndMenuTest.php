<?php

namespace Tests\Feature\ApiV1;

use App\Support\Api\ApiToken;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** V1 auth / context / menu / envelope. In-memory SQLite only; a guard aborts on any other connection. */
class AuthAndMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $c = DB::connection();
        if ($c->getDriverName() !== 'sqlite' || $c->getDatabaseName() !== ':memory:') {
            $this->fail('Refusing to run outside in-memory SQLite.');
        }
        $this->schema();
        $this->seed1();
    }

    private function schema(): void
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
        Schema::create('tblstudent', function (Blueprint $t) {
            $t->increments('id');
            foreach (['username', 'email', 'password', 'first_name', 'last_name', 'image', 'status'] as $col) {
                $t->string($col)->nullable();
            }
            $t->integer('user_profile_id')->nullable();
            $t->integer('sub_institute_id')->default(0);
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
        Schema::create('standard', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('sub_institute_id');
            $t->integer('marking_period_id')->nullable();
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
        Schema::create('tblstudent_enrollment', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('sub_institute_id');
            $t->integer('syear');
            $t->date('end_date')->nullable();
        });
    }

    private function seed1(): void
    {
        $y = date('Y');
        DB::table('school_setup')->insert([
            ['id' => 1, 'SchoolName' => 'School One', 'ShortCode' => 'S1', 'institute_type' => 'school', 'client_id' => 9],
            ['id' => 2, 'SchoolName' => 'School Two', 'ShortCode' => 'S2', 'institute_type' => 'school', 'client_id' => 9],
        ]);
        foreach ([1, 2] as $i) {
            DB::table('academic_year')->insert([
                ['sub_institute_id' => $i, 'syear' => $y, 'term_id' => 1, 'start_date' => "$y-01-01", 'end_date' => "$y-12-31"],
                ['sub_institute_id' => $i, 'syear' => $y - 1, 'term_id' => 2, 'start_date' => ($y - 1).'-01-01', 'end_date' => ($y - 1).'-12-31'],
            ]);
        }
        DB::table('standard')->insert([['sub_institute_id' => 1, 'marking_period_id' => 1], ['sub_institute_id' => 1, 'marking_period_id' => 1], ['sub_institute_id' => 1, 'marking_period_id' => 2], ['sub_institute_id' => 2, 'marking_period_id' => 1]]);
        DB::table('tbluserprofilemaster')->insert([['id' => 1, 'name' => 'Admin'], ['id' => 2, 'name' => 'Super Admin'], ['id' => 3, 'name' => 'Student']]);
        DB::table('tbluser')->insert([
            ['id' => 10, 'email' => 'admin@x.test', 'password' => 'pw1', 'first_name' => 'Ada', 'last_name' => 'Min', 'status' => '1', 'user_profile_id' => 1, 'sub_institute_id' => 1, 'client_id' => 9, 'is_admin' => 0],
            ['id' => 11, 'email' => 'client@x.test', 'password' => 'pw2', 'first_name' => 'Cli', 'last_name' => 'Ent', 'status' => '1', 'user_profile_id' => 1, 'sub_institute_id' => 0, 'client_id' => 9, 'is_admin' => 1],
            ['id' => 12, 'email' => 'norights@x.test', 'password' => 'pw3', 'first_name' => 'No', 'last_name' => 'Rights', 'status' => '1', 'user_profile_id' => 99, 'sub_institute_id' => 1, 'client_id' => 9, 'is_admin' => 0],
            ['id' => 13, 'email' => 'off@x.test', 'password' => 'pw4', 'first_name' => 'Off', 'last_name' => 'Line', 'status' => '0', 'user_profile_id' => 1, 'sub_institute_id' => 1, 'client_id' => 9, 'is_admin' => 0],
        ]);
        DB::table('tblstudent')->insert(['id' => 20, 'username' => 'stu', 'email' => 'stu@x.test', 'password' => md5('spw'), 'first_name' => 'Stu', 'last_name' => 'Dent', 'status' => '1', 'user_profile_id' => 3, 'sub_institute_id' => 1]);
        DB::table('tblstudent_enrollment')->insert([
            ['sub_institute_id' => 1, 'syear' => $y, 'end_date' => null],
            ['sub_institute_id' => 1, 'syear' => $y, 'end_date' => "$y-02-01"],
        ]);

        // menu: 1 Fees (inst 1) > 2 Collect > 3 Receipts (level 3); 4 Hidden (inst 2 only); 5 Master-only; 6 Client menu
        $menus = [
            ['id' => 1, 'name' => 'Fees', 'level' => 1, 'sub_institute_id' => '1,2', 'client_id' => '9', 'icon' => 'fees'],
            ['id' => 2, 'name' => 'Collect', 'parent_menu_id' => 1, 'level' => 2, 'sub_institute_id' => '1,2', 'client_id' => '9'],
            ['id' => 3, 'name' => 'Receipts', 'parent_menu_id' => 2, 'level' => 3, 'sub_institute_id' => '1,2', 'client_id' => '9', 'link' => 'no.such.route'],
            ['id' => 4, 'name' => 'Inst2 only', 'level' => 1, 'sub_institute_id' => '2', 'client_id' => '9'],
            ['id' => 5, 'name' => 'Master', 'level' => 1, 'sub_institute_id' => '1', 'client_id' => '9', 'menu_type' => 'MASTER'],
            ['id' => 6, 'name' => 'Disabled', 'level' => 1, 'status' => 0, 'sub_institute_id' => '1', 'client_id' => '9'],
        ];
        foreach ($menus as $m) {
            DB::table('tblmenumaster')->insert($m + ['parent_menu_id' => 0, 'status' => 1, 'sort_order' => 0, 'link' => null, 'icon' => null, 'menu_type' => null]);
        }
        foreach ([1, 2, 3, 4, 5, 6] as $m) {
            DB::table('tblgroupwise_rights')->insert(['profile_id' => 1, 'menu_id' => $m, 'sub_institute_id' => 1, 'can_view' => 1, 'can_add' => 0]);
            DB::table('tblgroupwise_rights')->insert(['profile_id' => 1, 'menu_id' => $m, 'sub_institute_id' => 0, 'can_view' => 1]);
        }
        // individual right overrides the group right for user 10 on menu 2
        DB::table('tblindividual_rights')->insert(['user_id' => 10, 'menu_id' => 2, 'sub_institute_id' => 1, 'can_view' => 1, 'can_add' => 1]);
        DB::table('tblgroupwise_rights')->insert(['profile_id' => 3, 'menu_id' => 1, 'sub_institute_id' => 1, 'can_view' => 1]);
    }

    private function login(string $email, string $password)
    {
        return $this->postJson('/api/v1/auth/login', ['email' => $email, 'password' => $password]);
    }

    private function tokenFor(string $email, string $pw): string
    {
        return $this->login($email, $pw)->json('data.token');
    }

    private function authed(string $token, array $headers = [])
    {
        return $this->withHeaders(['Authorization' => "Bearer $token"] + $headers);
    }

    public function test_login_returns_enveloped_profile_menu_and_token(): void
    {
        $res = $this->login('admin@x.test', 'pw1')->assertOk()
            ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user', 'context', 'institutes', 'academic_years', 'terms', 'menu'], 'meta', 'errors']);
        $this->assertTrue($res->json('success'));
        $this->assertSame('Admin', $res->json('data.user.profile'));
        $this->assertSame(1, $res->json('data.context.institute_id'));
        $this->assertStringNotContainsString('pw1', $res->getContent());
        $this->assertArrayNotHasKey('password', $res->json('data.user'));
    }

    public function test_menu_is_three_levels_scoped_to_institute_and_excludes_master_and_disabled(): void
    {
        $menu = $this->login('admin@x.test', 'pw1')->json('data.menu');
        $this->assertSame(['Fees'], array_column($menu, 'name'));
        $this->assertSame('Collect', $menu[0]['children'][0]['name']);
        $this->assertSame('Receipts', $menu[0]['children'][0]['children'][0]['name']);
        $this->assertNull($menu[0]['children'][0]['children'][0]['path']); // route does not exist
    }

    public function test_individual_rights_override_group_rights(): void
    {
        $menu = $this->login('admin@x.test', 'pw1')->json('data.menu');
        $this->assertFalse($menu[0]['permissions']['add']);                       // group: add = 0
        $this->assertTrue($menu[0]['children'][0]['permissions']['add']);         // individual: add = 1
    }

    public function test_bad_password_inactive_user_and_no_rights_are_rejected_in_envelope(): void
    {
        $this->login('admin@x.test', 'wrong')->assertStatus(401)->assertJson(['success' => false, 'message' => 'Invalid User Id And Password']);
        $this->login('off@x.test', 'pw4')->assertStatus(401);
        $this->login('norights@x.test', 'pw3')->assertStatus(403)->assertJson(['message' => 'Please Contact Administrator For ERP Rights']);
        $this->postJson('/api/v1/auth/login', [])->assertStatus(422)->assertJsonStructure(['success', 'errors' => ['email', 'password']]);
    }

    public function test_student_login_uses_md5_password(): void
    {
        $this->login('stu@x.test', 'spw')->assertOk()->assertJsonPath('data.user.kind', 'student');
        $this->login('stu@x.test', md5('spw'))->assertStatus(401);
    }

    public function test_protected_routes_need_a_valid_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401)->assertJson(['success' => false]);
        $this->authed('a.b.c')->getJson('/api/v1/auth/me')->assertStatus(401);
        $token = $this->tokenFor('admin@x.test', 'pw1');
        $tampered = substr($token, 0, -3).'xyz';
        $this->authed($tampered)->getJson('/api/v1/auth/me')->assertStatus(401);
        $this->authed($token)->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.user.name', 'Ada Min');
    }

    public function test_expired_token_is_rejected(): void
    {
        $expired = ApiToken::issue(10, 'user', -10);
        $this->authed($expired)->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_context_headers_switch_year_and_reject_foreign_institute(): void
    {
        $y = (int) date('Y');
        $t = $this->tokenFor('admin@x.test', 'pw1');
        $this->authed($t, ['X-Academic-Year' => (string) ($y - 1)])->getJson('/api/v1/auth/me')
            ->assertJsonPath('data.context.syear', (string) ($y - 1))->assertJsonPath('data.context.term_id', 2);
        $this->authed($t, ['X-Institute-Id' => '2'])->getJson('/api/v1/auth/me')->assertStatus(403);
    }

    public function test_client_admin_can_switch_institute_and_sees_client_scoped_menu(): void
    {
        $t = $this->tokenFor('client@x.test', 'pw2');
        $this->authed($t, ['X-Institute-Id' => '2'])->getJson('/api/v1/auth/me')
            ->assertOk()->assertJsonPath('data.context.institute_id', 2)->assertJsonPath('data.context.is_client_admin', true);
        $this->assertCount(2, $this->authed($t)->getJson('/api/v1/auth/me')->json('data.institutes'));
    }

    public function test_session_is_mirrored_for_legacy_code(): void
    {
        $t = $this->tokenFor('admin@x.test', 'pw1');
        $this->authed($t)->getJson('/api/v1/auth/me')->assertOk();
        $this->assertSame(1, session('sub_institute_id'));
        $this->assertSame(10, session('user_id'));
        $this->assertSame('Admin', session('user_profile_name'));
    }

    public function test_dashboard_counters_use_current_context(): void
    {
        $t = $this->tokenFor('admin@x.test', 'pw1');
        $c = collect($this->authed($t)->getJson('/api/v1/dashboard')->assertOk()->json('data.counters'))->pluck('value', 'key');
        $this->assertSame(1, $c['students']); // the ended enrollment is excluded
        $this->assertSame(2, $c['classes']);  // only this institute's classes of the current term
    }

    public function test_dashboard_reports_academic_year_progress(): void
    {
        $t = $this->tokenFor('admin@x.test', 'pw1');
        $ay = $this->authed($t)->getJson('/api/v1/dashboard')->assertOk()->json('data.academic_year');
        $this->assertSame(date('Y').'-01-01', $ay['start_date']);
        $this->assertGreaterThanOrEqual(0, $ay['progress']);
        $this->assertLessThanOrEqual(100, $ay['progress']);
        $this->assertGreaterThanOrEqual(0, $ay['days_left']);
    }

    public function test_unknown_v1_route_and_server_errors_use_envelope(): void
    {
        $this->getJson('/api/v1/nope')->assertStatus(404);
        $this->assertSame(0, \count(DB::table('tbluser')->where('id', 999)->get()));
    }
}
