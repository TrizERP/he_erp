<?php

namespace Tests\Feature\Admission;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuMigrationTest extends AdmissionTestCase
{
    protected function migration()
    {
        return require base_path('database/migrations/2026_10_20_100001_add_placement_admission_fees_waiver_menus.php');
    }

    protected function buildMenus(): void
    {
        Schema::drop('tblmenumaster');
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
        foreach (['tblgroupwise_rights', 'tblindividual_rights'] as $table) {
            Schema::dropIfExists($table);
            Schema::create($table, function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->bigInteger('menu_id');
            });
        }
        Schema::create('rightside_menumaster', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->string('description');
            $t->integer('main_menu_id');
            $t->integer('parent_menu_id');
            $t->integer('tblmenu_master_id')->nullable();
            $t->string('icon');
            $t->integer('status');
            $t->integer('sort_order');
            $t->text('sub_institute_id');
            $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('school_setup', function (Blueprint $t) {
            $t->bigInteger('Id')->primary();
            $t->bigInteger('client_id')->nullable();
        });
        DB::table('school_setup')->insert([['Id' => 133, 'client_id' => 28], ['Id' => 5, 'client_id' => 28]]);
        DB::table('tblmenumaster')->insert([
            ['id' => 1, 'name' => 'Institute', 'parent_menu_id' => 0, 'level' => 1, 'sub_institute_id' => '5', 'client_id' => '28', 'sort_order' => 1],
            ['id' => 6, 'name' => 'Fees', 'parent_menu_id' => 1, 'level' => 2, 'sub_institute_id' => '5', 'client_id' => '28', 'sort_order' => 2],
            ['id' => 173, 'name' => 'Admission', 'parent_menu_id' => 1, 'level' => 2, 'sub_institute_id' => '5', 'client_id' => '28', 'sort_order' => 9],
            ['id' => 174, 'name' => 'Admission Inquiry', 'parent_menu_id' => 173, 'level' => 3, 'sub_institute_id' => '5', 'client_id' => '28', 'sort_order' => 1],
        ]);
    }

    public function test_the_menu_migration_builds_the_tree_is_idempotent_and_rolls_back(): void
    {
        $this->buildMenus();
        $migration = $this->migration();

        $migration->up();
        $snapshot = [DB::table('tblmenumaster')->count(), DB::table('rightside_menumaster')->count()];
        $migration->up();
        $this->assertSame($snapshot, [DB::table('tblmenumaster')->count(), DB::table('rightside_menumaster')->count()], 'running it twice adds nothing');

        $placement = DB::table('tblmenumaster')->where('name', 'Placement')->first();
        $this->assertSame([2, 1], [(int) $placement->level, (int) $placement->parent_menu_id], 'Placement is a level-2 dropdown under Institute');
        $this->assertSame('133', $placement->sub_institute_id);

        $children = DB::table('tblmenumaster')->where('parent_menu_id', $placement->id)->get();
        $this->assertCount(26, $children, '20 pages + 6 master pages');
        $this->assertSame(6, $children->where('menu_type', 'MASTER')->count());
        $this->assertTrue($children->every(fn ($c) => (int) $c->level === 3));
        $this->assertGreaterThan(3, $children->pluck('icon')->unique()->count(), 'pages have different icons');

        $this->assertSame(11, DB::table('tblmenumaster')->where('parent_menu_id', 173)->where('level', 3)->count(), '10 new admission pages plus the existing Inquiry');
        $this->assertSame(1, DB::table('tblmenumaster')->where('name', 'Fees Waiver')->where('parent_menu_id', 6)->where('level', 3)->count());
        $this->assertSame('5', DB::table('tblmenumaster')->where('id', 173)->value('sub_institute_id'), 'existing menus are untouched');

        $right = DB::table('rightside_menumaster')->get();
        $this->assertSame(8, $right->count(), '2 tabs + 6 master pages');
        $this->assertTrue($right->every(fn ($r) => (int) $r->main_menu_id === (int) $placement->id));
        $this->assertSame(2, $right->where('parent_menu_id', 0)->count());

        $this->assertSame(0, DB::table('tblgroupwise_rights')->count(), 'menus only, no rights');

        // another institute can be added later and the lists merge
        $row = DB::table('tblmenumaster')->where('id', $placement->id);
        $row->update(['sub_institute_id' => '5']);
        $migration->up();
        $this->assertSame('5,133', DB::table('tblmenumaster')->where('id', $placement->id)->value('sub_institute_id'));

        $migration->down();
        $this->assertFalse(DB::table('tblmenumaster')->where('name', 'Placement')->exists());
        $this->assertFalse(DB::table('tblmenumaster')->where('name', 'Fees Waiver')->exists());
        $this->assertSame(0, DB::table('rightside_menumaster')->count());
        $this->assertSame(4, DB::table('tblmenumaster')->count(), 'only the original menus remain');
    }
}
