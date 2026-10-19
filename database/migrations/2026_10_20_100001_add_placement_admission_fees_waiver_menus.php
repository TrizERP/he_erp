<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Registers the new screens in tblmenumaster (and the right "Show Sidebar" panel in rightside_menumaster):
 *
 *   Institute (level 1)
 *     ├─ Placement (level 2, dropdown)  -> 13 pages + 7 student pages (level 3); the 6 master pages are MASTER type
 *     │                                    and are listed in the right sidebar when Placement is clicked
 *     ├─ Admission (existing level 2)   -> 10 admission workflow pages added (level 3)
 *     └─ Fees (existing level 2)        -> Fees Waiver added (level 3)
 *
 * Menus only: no rights and no user profiles are created. Give rights from the ERP rights screens afterwards.
 * Idempotent: existing rows are reused and the institute is appended to their sub_institute_id / client_id lists.
 *
 * INSTITUTES below are the institutes the menus are made visible for.
 */
return new class extends Migration
{
    private const INSTITUTES = [133];

    private const GROUP_ICON = 'setting'; // right sidebar tab icon: admin_dep/images/side-setting.png

    private function placementPages(): array
    {
        // [name, route, icon]
        return [
            ['Dashboard', 'placement.dashboard', 'view-dashboard'],
            ['Companies', 'placement.companies.index', 'domain'],
            ['Reports', 'placement.reports.index', 'chart-bar'],
            ['Job Drives', 'placement.drives.index', 'briefcase-search'],
            ['Profile Verification', 'placement.verification.index', 'account-check'],
            ['Offers', 'placement.offers.index', 'handshake'],
            ['Training & Mock Tests', 'placement.training.index', 'school'],
            ['Resume Reviews', 'placement.resume_reviews.index', 'file-document-box'],
            ['Recruiter Requirements', 'placement.requirements.index', 'account-search'],
            ['Other Outcomes', 'placement.outcomes.index', 'flag-checkered'],
            ['Announcements', 'placement.announcements.index', 'bullhorn'],
            ['Communication', 'placement.notifications.index', 'email-outline'],
            ['Settings & Audit', 'placement.settings.index', 'tune'],
            ['My Placement Profile', 'placement.student.profile', 'account'],
            ['Drives For Me', 'placement.student.drives', 'briefcase-search'],
            ['My Applications', 'placement.student.applications', 'clipboard-text-outline'],
            ['My Rounds', 'placement.student.rounds', 'flag-checkered'],
            ['My Offers', 'placement.student.offers', 'handshake'],
            ['Notices', 'placement.student.notices', 'bullhorn'],
            ['My Training', 'placement.student.training', 'school'],
        ];
    }

    /** right sidebar tab => [[master page, route], ...] */
    private function masterPages(): array
    {
        return [
            'Masters & Setup' => [
                ['Masters', 'placement.masters.index'],
                ['Placement Seasons', 'placement.seasons.index'],
                ['Templates', 'placement.templates.index'],
                ['Coordinators', 'placement.coordinators.index'],
            ],
            'Policy & Eligibility' => [
                ['Placement Policy', 'placement.policies.index'],
                ['Eligibility Templates', 'placement.eligibility.index'],
            ],
        ];
    }

    private function admissionPages(): array
    {
        return [
            ['Admission Dashboard', 'admission.dashboard.index', 'view-dashboard'],
            ['Application Review', 'admission.review.index', 'clipboard-text-outline'],
            ['Document Verification', 'admission.documents.index', 'file-document-box'],
            ['Merit / Selection', 'admission.merit.index', 'trophy-outline'],
            ['Offer / Seat Allocation', 'admission.offers.index', 'handshake'],
            ['Admission Fee', 'admission.fees.index', 'cash'],
            ['Confirmation Checklist', 'admission.confirm.index', 'clipboard-check'],
            ['Admitted Students', 'admission.admitted.index', 'school'],
            ['Admission Workflow Reports', 'admission.reports.index', 'chart-bar'],
            ['Admission Settings', 'admission.settings.index', 'tune'],
        ];
    }

    public function up()
    {
        $institutes = DB::table('school_setup')->whereIn('Id', self::INSTITUTES)->pluck('client_id', 'Id');
        if ($institutes->isEmpty()) {
            throw new RuntimeException('None of the institutes '.implode(',', self::INSTITUTES).' exist in school_setup.');
        }
        $instituteIds = $institutes->keys()->implode(',');
        $clientIds = $institutes->values()->unique()->implode(',');

        // The level-1 menu that holds the Admission and Fees dropdowns ("Institute").
        $admission = DB::table('tblmenumaster')->where('name', 'Admission')->where('level', 2)->first();
        $fees = DB::table('tblmenumaster')->where('name', 'Fees')->where('level', 2)->first();
        if (! $admission || ! $fees || (int) $admission->parent_menu_id !== (int) $fees->parent_menu_id) {
            throw new RuntimeException('Expected level-2 "Admission" and "Fees" menus under the same level-1 menu; nothing was changed.');
        }
        $instituteMenuId = (int) $admission->parent_menu_id;

        DB::transaction(function () use ($instituteIds, $clientIds, $instituteMenuId, $admission, $fees) {
            $menu = fn (string $name, int $parent, int $level, string $link, string $icon, ?int $sort, string $type = 'ENTRY') => $this->menu(
                $name, $parent, $level, $link, $icon, $sort, $type, $instituteIds, $clientIds
            );

            // Placement dropdown
            $placement = $menu('Placement', $instituteMenuId, 2, 'javascript:void(0);', 'mdi mdi-briefcase-check', null);
            $order = 1;
            foreach ($this->placementPages() as [$name, $route, $icon]) {
                $menu($name, $placement, 3, $route, 'mdi mdi-'.$icon, $order++);
            }

            // Master pages: hidden from the left menu, shown in the right "Show Sidebar" panel
            $tabSort = 1;
            foreach ($this->masterPages() as $tab => $pages) {
                $tabId = $this->rightside($tab, $placement, 0, null, $tabSort++, $instituteIds);
                $itemSort = 1;
                foreach ($pages as [$name, $route]) {
                    $id = $menu($name, $placement, 3, $route, 'mdi mdi-chevron-right', $order++, 'MASTER');
                    $this->rightside($name, $placement, $tabId, $id, $itemSort++, $instituteIds);
                }
            }

            // Admission workflow pages inside the existing Admission dropdown
            $order = 50;
            foreach ($this->admissionPages() as [$name, $route, $icon]) {
                $menu($name, (int) $admission->id, 3, $route, 'mdi mdi-'.$icon, $order++);
            }

            // Fees Waiver inside the existing Fees dropdown
            $menu('Fees Waiver', (int) $fees->id, 3, 'fees_waiver.index', 'mdi mdi-cash', null);
        });
    }

    public function down()
    {
        DB::transaction(function () {
            $placement = $this->findByName('Placement', 2);
            $ids = [];

            if ($placement) {
                DB::table('rightside_menumaster')->where('main_menu_id', $placement->id)->delete();
                $ids = DB::table('tblmenumaster')->where('parent_menu_id', $placement->id)->pluck('id')->all();
                $ids[] = $placement->id;
            }
            foreach ($this->admissionPages() as [$name]) {
                $row = $this->findByName($name, 3);
                $row && $ids[] = $row->id;
            }
            $row = $this->findByName('Fees Waiver', 3);
            $row && $ids[] = $row->id;

            if ($ids) {
                DB::table('tblgroupwise_rights')->whereIn('menu_id', $ids)->delete();
                DB::table('tblindividual_rights')->whereIn('menu_id', $ids)->delete();
                DB::table('tblmenumaster')->whereIn('id', $ids)->delete();
            }
        });
    }

    private function findByName(string $name, int $level)
    {
        return DB::table('tblmenumaster')->where('name', $name)->where('level', $level)->first();
    }

    /** Insert or reuse a tblmenumaster row (matched on name + level + parent) and make sure the institutes are in its lists. */
    private function menu(string $name, int $parent, int $level, string $link, string $icon, ?int $sort, string $type, string $institutes, string $clients): int
    {
        $match = ['name' => $name, 'parent_menu_id' => $parent, 'level' => $level];
        $existing = DB::table('tblmenumaster')->where($match)->first();

        if ($existing) {
            DB::table('tblmenumaster')->where('id', $existing->id)->update([
                'link' => $link,
                'icon' => $icon,
                'status' => 1,
                'menu_type' => $type,
                'sub_institute_id' => $this->mergeList($existing->sub_institute_id, $institutes),
                'client_id' => $this->mergeList($existing->client_id, $clients),
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        $sort ??= (int) DB::table('tblmenumaster')->where('parent_menu_id', $parent)->max('sort_order') + 1;

        return (int) DB::table('tblmenumaster')->insertGetId($match + [
            'description' => $name,
            'status' => 1,
            'sort_order' => $sort,
            'link' => $link,
            'icon' => $icon,
            'sub_institute_id' => $institutes,
            'client_id' => $clients,
            'menu_type' => $type,
            'site_map_name' => $name,
            'menu_path' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Insert or reuse a rightside_menumaster row: main_menu_id = the menu whose click/page opens the panel. */
    private function rightside(string $name, int $mainMenuId, int $parent, ?int $menuId, int $sort, string $institutes): int
    {
        $match = ['main_menu_id' => $mainMenuId, 'parent_menu_id' => $parent, 'name' => $name];
        $existing = DB::table('rightside_menumaster')->where($match)->first();

        if ($existing) {
            DB::table('rightside_menumaster')->where('id', $existing->id)->update([
                'tblmenu_master_id' => $menuId,
                'status' => 1,
                'sub_institute_id' => $this->mergeList($existing->sub_institute_id, $institutes),
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('rightside_menumaster')->insertGetId($match + [
            'description' => $name,
            'tblmenu_master_id' => $menuId,
            'icon' => self::GROUP_ICON,
            'status' => 1,
            'sort_order' => $sort,
            'sub_institute_id' => $institutes,
        ]);
    }

    /** Union of two comma lists: keeps the existing ids and appends the new ones. */
    private function mergeList(?string $existing, string $new): string
    {
        $merged = array_unique(array_filter(array_merge(explode(',', (string) $existing), explode(',', $new)), 'strlen'));

        return implode(',', $merged);
    }
};
