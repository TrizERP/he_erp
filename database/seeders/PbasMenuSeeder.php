<?php

namespace Database\Seeders;

use App\Services\Pbas\PbasEntries;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Registers the PBAS menu in tblmenumaster as a three-level tree, the same shape the ERP uses elsewhere
 * (Level 1 module > Level 2 group > Level 3 page) and the same as the original PBAS sidebar (section > pages):
 *
 *   PBAS (L1)
 *     Dashboard (L2, page)
 *     1.Teaching Process / 2. Departmental Activity / 3. Institute Activites / 4. Contribution To Society (L2) > entry pages (L3)
 *     Your PBAS Summary (L2) > API Form (L3)
 *     HOD Activities (L2) > Lock/UnLock Data (L3)
 *     Principal (L2) > View data and input ACR (L3)
 *
 * and grants group rights to the existing staff profiles (no profile is created). Idempotent: safe to re-run.
 *
 *   php artisan db:seed --class=PbasMenuSeeder
 *
 * Run it deliberately, against the right database. It is not called by DatabaseSeeder. To limit it to some
 * institutes set PBAS_SEED_INSTITUTES=1,2 (default: every institute in school_setup).
 */
class PbasMenuSeeder extends Seeder
{
    /** audience => profile names that get the menu (matched case-insensitively per institute; Admin always gets everything) */
    protected array $audiences = [
        'faculty' => ['HOD', 'Principal', 'Lecturer', 'Assistant Professor', 'Associate Professor', 'Adhoc Assistant Professor', 'Adhoc Lecturer', 'Visiting Lecturer'],
        'hod' => ['HOD', 'Principal'],
        'principal' => ['Principal'],
    ];

    /** level-3 page icons by entry slug (ERP convention: mdi class with fa-fw) */
    protected array $pageIcons = [
        'lectures' => 'clipboard-text-outline', 'materials' => 'file-document-box', 'papers' => 'newspaper', 'expert-lectures' => 'microphone',
        'books' => 'book-open-page-variant', 'research-projects' => 'flask', 'research-individuals' => 'microscope',
        'project-guides' => 'account-supervisor', 'sttps' => 'school', 'awards' => 'trophy-outline', 'admin-duties' => 'clipboard-account',
        'events' => 'calendar-star', 'industrial-visits' => 'factory', 'memberships' => 'card-text-outline', 'community-activities' => 'hand-heart',
    ];

    /** rights = [view, add, edit, delete] */
    protected array $entryRights = [1, 1, 0, 1];

    public function run()
    {
        $institutes = DB::table('school_setup')->pluck('client_id', 'Id');
        if ($only = array_filter(array_map('intval', explode(',', (string) env('PBAS_SEED_INSTITUTES', ''))))) {
            $institutes = $institutes->only($only);
        }
        if ($institutes->isEmpty()) {
            $this->command?->warn('No institutes in school_setup; nothing seeded.');

            return;
        }
        $instituteList = $institutes->keys()->implode(',');
        $clientList = $institutes->values()->unique()->implode(',');

        DB::transaction(function () use ($instituteList, $clientList, $institutes) {
            $menus = $this->seedMenus($instituteList, $clientList);
            foreach ($institutes->keys() as $instituteId) {
                $this->seedRights((int) $instituteId, $menus);
            }
        });
    }

    /** @return array<int, array{id:int, audience:string, rights:array}> menu id => rights definition */
    protected function seedMenus(string $instituteList, string $clientList): array
    {
        $out = [];
        $add = function (string $name, int $parent, int $level, string $link, string $icon, int $sort, string $audience, array $rights) use (&$out, $instituteList, $clientList) {
            $id = $this->menu($name, $parent, $level, $link, $icon, $sort, $instituteList, $clientList);
            $out[$id] = ['id' => $id, 'audience' => $audience, 'rights' => $rights];

            return $id;
        };
        $none = [1, 0, 0, 0]; // containers: visible only

        $root = $add('PBAS', 0, 1, 'javascript:void(0);', 'user-report', 46, 'faculty', $none);

        $sort = 1;
        $add('Dashboard', $root, 2, 'pbas.profile.index', 'mdi mdi-view-dashboard fa-fw', $sort++, 'faculty', [1, 1, 0, 0]);

        $groupNames = [
            'teaching' => '1.Teaching Process', 'departmental' => '2. Departmental Activity',
            'institute' => '3. Institute Activites', 'society' => '4. Contribution To Society',
        ];
        $groupIcons = ['teaching' => 'mdi mdi-presentation fa-fw', 'departmental' => 'mdi mdi-domain fa-fw', 'institute' => 'mdi mdi-bank fa-fw', 'society' => 'mdi mdi-account-group fa-fw'];
        foreach (PbasEntries::menuGroups() as $group => $pages) {
            $groupId = $add($groupNames[$group], $root, 2, 'javascript:void(0);', $groupIcons[$group], $sort++, 'faculty', $none);
            $order = 1;
            foreach ($pages as $slug => $title) {
                $add($title, $groupId, 3, "pbas.{$slug}.index", 'mdi mdi-'.$this->pageIcons[$slug].' fa-fw', $order++, 'faculty', $this->entryRights);
            }
        }

        $summary = $add('Your PBAS Summary', $root, 2, 'javascript:void(0);', 'mdi mdi-file-chart fa-fw', $sort++, 'faculty', $none);
        $add('API Form', $summary, 3, 'pbas.report.index', 'mdi mdi-file-document fa-fw', 1, 'faculty', [1, 0, 0, 0]);

        $hod = $add('HOD Activities', $root, 2, 'javascript:void(0);', 'mdi mdi-account-key fa-fw', $sort++, 'hod', $none);
        $add('Lock/UnLock Data', $hod, 3, 'pbas.hod.index', 'mdi mdi-lock fa-fw', 1, 'hod', [1, 1, 0, 0]);

        $principal = $add('Principal', $root, 2, 'javascript:void(0);', 'mdi mdi-account-star fa-fw', $sort++, 'principal', $none);
        $add('View data and input ACR', $principal, 3, 'pbas.principal.index', 'mdi mdi-clipboard-check fa-fw', 1, 'principal', [1, 1, 0, 0]);

        // The root must be visible to everyone who sees any child.
        $out[$root]['audience'] = 'faculty';

        return $out;
    }

    protected function menu(string $name, int $parent, int $level, string $link, string $icon, int $sort, string $institutes, string $clients): int
    {
        $match = ['name' => $name, 'parent_menu_id' => $parent, 'level' => $level];
        $values = [
            'description' => $name,
            'status' => 1,
            'sort_order' => $sort,
            'link' => $link,
            'icon' => $icon,
            'sub_institute_id' => $institutes,
            'client_id' => $clients,
            'menu_type' => 'ENTRY',
            'site_map_name' => mb_substr($name, 0, 50),
            'menu_path' => mb_substr($name, 0, 50),
            'updated_at' => now(),
        ];

        $existing = DB::table('tblmenumaster')->where($match)->first();
        if ($existing) {
            DB::table('tblmenumaster')->where('id', $existing->id)->update($values);

            return (int) $existing->id;
        }

        return (int) DB::table('tblmenumaster')->insertGetId($match + $values + ['created_at' => now()]);
    }

    protected function seedRights(int $instituteId, array $menus): void
    {
        $profiles = DB::table('tbluserprofilemaster')->where('sub_institute_id', $instituteId)->get(['id', 'name']);

        foreach ($profiles as $profile) {
            $name = strtolower(trim($profile->name));
            $isAdmin = $name === 'admin';
            $audiences = [];
            foreach ($this->audiences as $audience => $names) {
                if (in_array($name, array_map('strtolower', $names), true)) {
                    $audiences[] = $audience;
                }
            }
            if (! $isAdmin && ! $audiences) {
                continue;
            }

            foreach ($menus as $menu) {
                if (! $isAdmin && ! in_array($menu['audience'], $audiences, true)) {
                    continue;
                }
                // admin gets the full rights of every page, containers stay view-only
                $rights = $menu['rights'];
                if ($isAdmin && $rights !== [1, 0, 0, 0]) {
                    $rights = [1, 1, 1, 1];
                }
                DB::table('tblgroupwise_rights')->updateOrInsert(
                    ['menu_id' => $menu['id'], 'profile_id' => $profile->id, 'sub_institute_id' => $instituteId],
                    ['can_view' => $rights[0], 'can_add' => $rights[1], 'can_edit' => $rights[2], 'can_delete' => $rights[3]]
                );
            }
        }
    }
}
