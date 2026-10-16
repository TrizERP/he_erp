<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Fees Waiver" screen under the existing "Fees" parent menu in tblmenumaster and gives the Admin
 * profile of every institute view/add/edit rights. Idempotent: safe to re-run.
 *
 *   php artisan db:seed --class=FeesWaiverMenuSeeder
 *
 * Run it deliberately, against the right database. It is not called by DatabaseSeeder.
 */
class FeesWaiverMenuSeeder extends Seeder
{
    public function run()
    {
        $root = DB::table('tblmenumaster')->where('name', 'Fees')->where('level', 1)->first();
        if (! $root) {
            $this->command?->warn('No level-1 "Fees" menu found; nothing seeded.');

            return;
        }

        $institutes = DB::table('school_setup')->pluck('client_id', 'Id');
        $match = ['name' => 'Fees Waiver', 'parent_menu_id' => $root->id, 'level' => 2];
        $values = [
            'description'      => 'Fees Waiver',
            'status'           => 1,
            'sort_order'       => (int) DB::table('tblmenumaster')->where('parent_menu_id', $root->id)->max('sort_order') + 1,
            'link'             => 'fees_waiver.index',
            'icon'             => 'mdi mdi-chevron-right',
            'sub_institute_id' => $institutes->keys()->implode(','),
            'client_id'        => $institutes->values()->unique()->implode(','),
            'menu_type'        => 'ENTRY',
            'site_map_name'    => 'Fees Waiver',
            'menu_path'        => 'Fees Waiver',
            'updated_at'       => now(),
        ];

        DB::transaction(function () use ($match, $values, $institutes) {
            $existing = DB::table('tblmenumaster')->where($match)->first();
            if ($existing) {
                DB::table('tblmenumaster')->where('id', $existing->id)->update($values);
                $menuId = (int) $existing->id;
            } else {
                $menuId = (int) DB::table('tblmenumaster')->insertGetId($match + $values + ['created_at' => now()]);
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
        });
    }
}
