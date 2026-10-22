<?php

namespace Tests\Feature\Pbas;

use App\Services\Pbas\PbasEntries;
use Database\Seeders\PbasMenuSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PbasMenuTest extends PbasTestCase
{
    protected function menu(string $name, int $level)
    {
        return DB::table('tblmenumaster')->where(['name' => $name, 'level' => $level])->first();
    }

    protected function rights(string $profile, int $menuId, int $institute = self::INST)
    {
        return DB::table('tblgroupwise_rights')->where([
            'menu_id' => $menuId, 'profile_id' => $this->profileId($profile, $institute), 'sub_institute_id' => $institute,
        ])->first();
    }

    public function test_menu_is_a_three_level_tree_with_pbas_at_level_one(): void
    {
        $root = $this->menu('PBAS', 1);
        $this->assertNotNull($root);
        $this->assertSame(0, (int) $root->parent_menu_id);

        $groups = DB::table('tblmenumaster')->where('parent_menu_id', $root->id)->orderBy('sort_order')->get();
        $this->assertSame(
            ['Dashboard', '1.Teaching Process', '2. Departmental Activity', '3. Institute Activites', '4. Contribution To Society', 'Your PBAS Summary', 'HOD Activities', 'Principal'],
            $groups->pluck('name')->all()
        );
        $this->assertSame([2], $groups->pluck('level')->unique()->values()->all());

        // groups are dropdowns, their children are the level-3 pages
        $counts = DB::table('tblmenumaster')->where('level', 3)->whereIn('parent_menu_id', $groups->pluck('id'))->selectRaw('parent_menu_id, count(*) c')->groupBy('parent_menu_id')->pluck('c', 'parent_menu_id');
        $byName = $groups->keyBy('name');
        $this->assertSame(2, $counts[$byName['1.Teaching Process']->id]);
        $this->assertSame(8, $counts[$byName['2. Departmental Activity']->id]);
        $this->assertSame(2, $counts[$byName['3. Institute Activites']->id]);
        $this->assertSame(3, $counts[$byName['4. Contribution To Society']->id]);
        $this->assertSame(1, $counts[$byName['Your PBAS Summary']->id]);
        $this->assertSame(1, $counts[$byName['HOD Activities']->id]);
        $this->assertSame(1, $counts[$byName['Principal']->id]);
        $this->assertSame('javascript:void(0);', $byName['1.Teaching Process']->link);
        $this->assertSame('pbas.profile.index', $byName['Dashboard']->link); // a page directly under the root, like the original "Dashboard"
    }

    public function test_every_menu_link_is_a_real_route_and_every_page_is_reachable_from_the_menu(): void
    {
        $links = DB::table('tblmenumaster')->where('level', '>', 1)->where('link', '!=', 'javascript:void(0);')->pluck('link');
        foreach ($links as $link) {
            $this->assertTrue(Route::has($link), "menu link {$link} has no route");
        }
        foreach (PbasEntries::all() as $slug => $def) {
            $this->assertSame((bool) $def['group'], $links->contains("pbas.{$slug}.index"), "{$slug} menu presence");
        }
        foreach (['pbas.report.index', 'pbas.hod.index', 'pbas.principal.index', 'pbas.profile.index'] as $link) {
            $this->assertTrue($links->contains($link), $link);
        }
    }

    public function test_page_names_are_the_original_sidebar_texts(): void
    {
        foreach (['Lectures, Seminars, Tutorials', 'Reading/Instructional material Prepared', 'Published / Presented Papers', 'Worked as an Expert',
                     'Grant Received for Research Projects', 'Research Work', 'Project Guided (UG/PG/Ph.D)', 'STTP/FDP Attended', 'Award/ Recognition',
                     'Industrial Visit/Training', 'Professional/Technical Society Membership', 'API Form', 'Lock/UnLock Data', 'View data and input ACR'] as $name) {
            $this->assertNotNull($this->menu($name, 3), $name);
        }
    }

    public function test_rights_follow_the_roles_and_no_profile_is_created(): void
    {
        $profilesBefore = DB::table('tbluserprofilemaster')->count();
        $lectures = $this->menu('Lectures, Seminars, Tutorials', 3);
        $hodPage = $this->menu('Lock/UnLock Data', 3);
        $principalPage = $this->menu('View data and input ACR', 3);
        $api = $this->menu('API Form', 3);

        // faculty profiles: entry pages (view, add, delete - not edit), API form view-only, nothing for HOD/Principal screens
        $r = $this->rights('Lecturer', $lectures->id);
        $this->assertSame([1, 1, 0, 1], [(int) $r->can_view, (int) $r->can_add, (int) $r->can_edit, (int) $r->can_delete]);
        $this->assertSame([1, 0, 0, 0], [(int) $this->rights('Lecturer', $api->id)->can_view, (int) $this->rights('Lecturer', $api->id)->can_add, 0, 0]);
        $this->assertNull($this->rights('Lecturer', $hodPage->id));
        $this->assertNull($this->rights('Lecturer', $principalPage->id));

        // HOD: faculty screens + HOD screen; Principal: everything
        $this->assertNotNull($this->rights('HOD', $lectures->id));
        $this->assertNotNull($this->rights('HOD', $hodPage->id));
        $this->assertNull($this->rights('HOD', $principalPage->id));
        foreach ([$lectures, $hodPage, $principalPage, $api] as $menu) {
            $this->assertNotNull($this->rights('Principal', $menu->id));
            $this->assertSame(1, (int) $this->rights('Admin', $menu->id)->can_view);
        }
        $this->assertSame(1, (int) $this->rights('Admin', $lectures->id)->can_edit);

        // the root and groups are visible to everyone who sees a child
        $this->assertNotNull($this->rights('Lecturer', $this->menu('PBAS', 1)->id));
        $this->assertNotNull($this->rights('HOD', $this->menu('HOD Activities', 2)->id));
        $this->assertNull($this->rights('Lecturer', $this->menu('HOD Activities', 2)->id));

        // students, accountants get nothing; other institutes are seeded independently
        foreach (['Student', 'Accountant'] as $profile) {
            $this->assertSame(0, DB::table('tblgroupwise_rights')->where('profile_id', $this->profileId($profile))->count(), $profile);
        }
        $this->assertNotNull($this->rights('Lecturer', $lectures->id, self::OTHER_INST));
        $this->assertSame($profilesBefore, DB::table('tbluserprofilemaster')->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $menus = DB::table('tblmenumaster')->count();
        $rights = DB::table('tblgroupwise_rights')->count();
        $this->seed(PbasMenuSeeder::class);
        $this->seed(PbasMenuSeeder::class);
        $this->assertSame($menus, DB::table('tblmenumaster')->count());
        $this->assertSame($rights, DB::table('tblgroupwise_rights')->count());
    }

    public function test_menu_rows_follow_the_erp_conventions(): void
    {
        $row = $this->menu('Lectures, Seminars, Tutorials', 3);
        $this->assertSame('ENTRY', $row->menu_type);
        $this->assertSame(1, (int) $row->status);
        $this->assertSame('1,2', $row->sub_institute_id);
        $this->assertSame('11', $row->client_id);
        $this->assertLessThanOrEqual(255, strlen($this->menu('Contribution to Corporate Life and Management of the Institution: HoD/Dean/Committee Membership/Class Coordinator/Lab In charge', 3)->name));
    }

    public function test_icons_follow_the_erp_sidebar_conventions(): void
    {
        // level 1 is an image key (admin_dep/images/menu-<key>.png), levels 2 and 3 are mdi classes, all different
        $this->assertSame('user-report', $this->menu('PBAS', 1)->icon);
        $this->assertFileExists(base_path('public/admin_dep/images/menu-user-report.png'));
        $rows = DB::table('tblmenumaster')->where('level', '>', 1)->get(['name', 'icon']);
        foreach ($rows as $row) {
            $this->assertStringStartsWith('mdi mdi-', $row->icon, $row->name);
        }
        $this->assertSame($rows->count(), $rows->pluck('icon')->unique()->count(), 'every PBAS menu item has its own icon');
    }
}
