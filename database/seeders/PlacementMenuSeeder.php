<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Registers the Placement menu tree in tblmenumaster, creates the TPO / Placement Coordinator /
 * Principal user profiles per institute, and grants group rights. Idempotent: safe to re-run.
 *
 *   php artisan db:seed --class=PlacementMenuSeeder
 *
 * Menu links are route names (the sidebar and MenuMiddleware match on them).
 */
class PlacementMenuSeeder extends Seeder
{
    // [name, level-2 group, link, level-3 pages [name => link]]
    protected array $tree = [
        ['Dashboard', 'page', 'placement.dashboard', 'all'],
        ['Masters & Setup', 'group', [
            'Masters'              => 'placement.masters.index',
            'Placement Seasons'    => 'placement.seasons.index',
            'Templates'            => 'placement.templates.index',
            'Coordinators'         => 'placement.coordinators.index',
        ]],
        ['Policy & Eligibility', 'group', [
            'Placement Policy'       => 'placement.policies.index',
            'Eligibility Templates'  => 'placement.eligibility.index',
        ]],
        ['Companies', 'page', 'placement.companies.index'],
        ['Reports', 'page', 'placement.reports.index'],
        ['Job Drives', 'page', 'placement.drives.index'],
        ['Profile Verification', 'page', 'placement.verification.index'],
        ['Offers', 'page', 'placement.offers.index'],
        ['Training & Mock Tests', 'page', 'placement.training.index'],
        ['Resume Reviews', 'page', 'placement.resume_reviews.index'],
        ['Recruiter Requirements', 'page', 'placement.requirements.index'],
        ['Other Outcomes', 'page', 'placement.outcomes.index'],
        ['Announcements', 'page', 'placement.announcements.index'],
        ['Communication', 'page', 'placement.notifications.index'],
        ['Settings & Audit', 'page', 'placement.settings.index'],
        // Student-facing pages (granted to the Student profile only)
        ['My Placement Profile', 'page', 'placement.student.profile', 'student'],
        ['Drives For Me', 'page', 'placement.student.drives', 'student'],
        ['My Applications', 'page', 'placement.student.applications', 'student'],
        ['My Rounds', 'page', 'placement.student.rounds', 'student'],
        ['My Offers', 'page', 'placement.student.offers', 'student'],
        ['Notices', 'page', 'placement.student.notices', 'student'],
        ['My Training', 'page', 'placement.student.training', 'student'],
    ];

    public function run()
    {
        $institutes = DB::table('school_setup')->pluck('client_id', 'Id');
        if ($institutes->isEmpty()) {
            $this->command?->warn('No institutes in school_setup; nothing seeded.');

            return;
        }
        $instituteList = $institutes->keys()->implode(',');
        $clientList = $institutes->values()->unique()->implode(',');

        DB::transaction(function () use ($instituteList, $clientList, $institutes) {
            $menuIds = $this->seedMenus($instituteList, $clientList);
            foreach ($institutes->keys() as $instituteId) {
                $this->seedProfilesAndRights($instituteId, $menuIds);
            }
        });
    }

    /** @return array{all:int[], student:int[]} */
    protected function seedMenus(string $instituteList, string $clientList): array
    {
        $root = $this->menu('Placement', 0, 1, 'javascript:void(0);', 'mdi mdi-briefcase-check', 90, $instituteList, $clientList);
        $all = [$root];
        $student = [$root];
        $order = 1;

        foreach ($this->tree as $node) {
            if ($node[1] === 'page') {
                $id = $this->menu($node[0], $root, 2, $node[2], 'mdi mdi-domain', $order++, $instituteList, $clientList);
                $audience = $node[3] ?? 'admin';
                if ($audience !== 'student') {
                    $all[] = $id;
                }
                if ($audience !== 'admin') {
                    $student[] = $id;
                }
                continue;
            }
            $group = $this->menu($node[0], $root, 2, 'javascript:void(0);', 'mdi mdi-settings', $order++, $instituteList, $clientList);
            $all[] = $group;
            $i = 1;
            foreach ($node[2] as $name => $link) {
                $all[] = $this->menu($name, $group, 3, $link, 'mdi mdi-chevron-right', $i++, $instituteList, $clientList);
            }
        }

        return ['all' => $all, 'student' => $student];
    }

    protected function menu(string $name, int $parent, int $level, string $link, string $icon, int $sort, string $institutes, string $clients): int
    {
        $match = ['name' => $name, 'parent_menu_id' => $parent, 'level' => $level];
        $values = [
            'description'    => $name,
            'status'         => 1,
            'sort_order'     => $sort,
            'link'           => $link,
            'icon'           => $icon,
            'sub_institute_id' => $institutes,
            'client_id'      => $clients,
            'menu_type'      => 'ENTRY',
            'site_map_name'  => $name,
            'menu_path'      => $name,
            'updated_at'     => now(),
        ];

        $existing = DB::table('tblmenumaster')->where($match)->first();
        if ($existing) {
            DB::table('tblmenumaster')->where('id', $existing->id)->update($values);

            return (int) $existing->id;
        }

        return (int) DB::table('tblmenumaster')->insertGetId($match + $values + ['created_at' => now()]);
    }

    protected function seedProfilesAndRights(int $instituteId, array $menuIds): void
    {
        $profiles = [
            // profile name => [view, add, edit, delete]
            'Admin'                 => [1, 1, 1, 1],
            'TPO'                   => [1, 1, 1, 1],
            'Principal'             => [1, 0, 0, 0],
            'Placement Coordinator' => [1, 0, 0, 0],
            'Student'               => [1, 0, 0, 0],
        ];

        foreach ($profiles as $name => $rights) {
            $profile = DB::table('tbluserprofilemaster')
                ->where('sub_institute_id', $instituteId)->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

            if (! $profile) {
                if (in_array($name, ['Admin', 'Student'], true)) {
                    continue; // never create a second built-in profile
                }
                $profileId = DB::table('tbluserprofilemaster')->insertGetId([
                    'parent_id'        => 0,
                    'name'             => $name,
                    'description'      => $name.' (placement module)',
                    'sort_order'       => (int) DB::table('tbluserprofilemaster')->where('sub_institute_id', $instituteId)->max('sort_order') + 1,
                    'status'           => 1,
                    'sub_institute_id' => $instituteId,
                ]);
            } else {
                $profileId = (int) $profile->id;
            }

            // Students only get the student pages; everyone else gets the admin pages.
            $menus = $name === 'Student' ? $menuIds['student'] : $menuIds['all'];
            foreach ($menus as $menuId) {
                DB::table('tblgroupwise_rights')->updateOrInsert(
                    ['menu_id' => $menuId, 'profile_id' => $profileId, 'sub_institute_id' => $instituteId],
                    ['can_view' => $rights[0], 'can_add' => $rights[1], 'can_edit' => $rights[2], 'can_delete' => $rights[3]]
                );
            }
        }
    }
}
