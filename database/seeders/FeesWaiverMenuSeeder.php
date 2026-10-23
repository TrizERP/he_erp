<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Fees Waiver" and "Fees Waiver Report" screens under the existing "Fees" parent menu in tblmenumaster and gives the Admin
 * profile of every institute view/add/edit rights. Idempotent: safe to re-run.
 *
 *   php artisan db:seed --class=FeesWaiverMenuSeeder
 *
 * Run it deliberately, against the right database. It is not called by DatabaseSeeder.
 */
class FeesWaiverMenuSeeder extends Seeder
{
    // [menu name, route name, parent menu name, menu type, icon]
    protected array $pages = [
        ['Fees Waiver', 'fees_waiver.index', 'Fees', 'ENTRY', 'mdi mdi-cash'],
        ['Fees Waiver Report', 'fees_waiver_report.index', 'Fees Report', 'REPORT', 'mdi mdi-cash-100 fa-fw'], // Reports > Fees Report
    ];

    public function run()
    {
        $institutes = DB::table('school_setup')->pluck('client_id', 'Id');

        DB::transaction(function () use ($institutes) {
            foreach ($this->pages as [$name, $link, $parentName, $type, $icon]) {
                // deepest menu of that name (the sidebar group, not a top-level section)
                $parent = DB::table('tblmenumaster')->where('name', $parentName)->orderByDesc('level')->first();
                if (! $parent) {
                    $this->command?->warn("No \"{$parentName}\" menu found; \"{$name}\" not seeded.");

                    continue;
                }

                $values = [
                    'name'             => $name,
                    'description'      => $name,
                    'parent_menu_id'   => $parent->id,
                    'level'            => $parent->level + 1,
                    'status'           => 1,
                    'link'             => $link,
                    'icon'             => $icon,
                    'sub_institute_id' => $institutes->keys()->implode(','),
                    'client_id'        => $institutes->values()->unique()->implode(','),
                    'menu_type'        => $type,
                    'site_map_name'    => $name,
                    'menu_path'        => $name,
                    'updated_at'       => now(),
                ];

                // find by route first so a menu added by hand is updated, not duplicated
                $existing = DB::table('tblmenumaster')->where('link', $link)->first();
                if ($existing) {
                    unset($values['parent_menu_id'], $values['level']); // keep where it already sits
                    DB::table('tblmenumaster')->where('id', $existing->id)->update($values);
                    $menuId = (int) $existing->id;
                } else {
                    $values['sort_order'] = (int) DB::table('tblmenumaster')->where('parent_menu_id', $parent->id)->max('sort_order') + 1;
                    $menuId = (int) DB::table('tblmenumaster')->insertGetId($values + ['created_at' => now()]);
                }

                foreach ($institutes->keys() as $instituteId) {
                    $profile = DB::table('tbluserprofilemaster')
                        ->where('sub_institute_id', $instituteId)->whereRaw('LOWER(name) = ?', ['admin'])->first();
                    if ($profile) {
                        DB::table('tblgroupwise_rights')->updateOrInsert(
                            ['menu_id' => $menuId, 'profile_id' => $profile->id, 'sub_institute_id' => $instituteId],
                            ['can_view' => 1, 'can_add' => 1, 'can_edit' => 1, 'can_delete' => 0]
                        );
                    }
                }
            }
        });
    }
}
