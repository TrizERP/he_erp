<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Registers the admission WORKFLOW screens under the existing "Admission" parent menu in tblmenumaster and
 * grants group rights. Inquiry, Application Registration, Confirmation and the legacy reports already have menu
 * rows and are NOT duplicated. Creates the Admission Officer / Admission Approver / Accounts Operator profiles
 * per institute if missing (never a second Admin or Student profile). Idempotent: safe to re-run.
 *
 *   php artisan db:seed --class=AdmissionMenuSeeder
 *
 * Run it deliberately, against the right database. It is not called by DatabaseSeeder.
 */
class AdmissionMenuSeeder extends Seeder
{
    // [menu name => [route name (the sidebar and MenuMiddleware match on it), audience keys]]
    // audiences: officer, approver, accounts (admin gets everything)
    protected array $pages = [
        'Admission Dashboard'      => ['admission.dashboard.index', ['officer', 'approver', 'accounts']],
        'Application Review'       => ['admission.review.index', ['officer', 'approver']],
        'Document Verification'    => ['admission.documents.index', ['officer', 'approver']],
        'Merit / Selection'        => ['admission.merit.index', ['officer', 'approver']],
        'Offer / Seat Allocation'  => ['admission.offers.index', ['officer', 'approver']],
        'Admission Fee'            => ['admission.fees.index', ['officer', 'accounts']],
        'Confirmation Checklist'   => ['admission.confirm.index', ['officer']],
        'Admitted Students'        => ['admission.admitted.index', ['officer', 'approver', 'accounts']],
        'Admission Workflow Reports' => ['admission.reports.index', ['officer', 'approver', 'accounts']],
        'Admission Settings'       => ['admission.settings.index', []],
    ];

    // profile name => [audience key, [view, add, edit, delete]]
    protected array $profiles = [
        'Admin'              => ['admin', [1, 1, 1, 1]],
        'Admission Officer'  => ['officer', [1, 1, 1, 0]],
        'Admission Approver' => ['approver', [1, 0, 0, 0]],
        'Accounts Operator'  => ['accounts', [1, 0, 0, 0]],
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
                $this->seedProfilesAndRights((int) $instituteId, $menuIds);
            }
        });
    }

    /** @return array<string,int> route name => menu id */
    protected function seedMenus(string $instituteList, string $clientList): array
    {
        $root = DB::table('tblmenumaster')->where('name', 'Admission')->where('level', 1)->first();
        $rootId = $root
            ? (int) $root->id
            : $this->menu('Admission', 0, 1, 'javascript:void(0);', 'mdi mdi-school', 40, $instituteList, $clientList);

        $ids = [];
        $order = 50; // after the existing admission entries
        foreach ($this->pages as $name => [$link]) {
            $ids[$link] = $this->menu($name, $rootId, 2, $link, 'mdi mdi-chevron-right', $order++, $instituteList, $clientList);
        }

        return $ids;
    }

    protected function menu(string $name, int $parent, int $level, string $link, string $icon, int $sort, string $institutes, string $clients): int
    {
        $match = ['name' => $name, 'parent_menu_id' => $parent, 'level' => $level];
        $values = [
            'description'      => $name,
            'status'           => 1,
            'sort_order'       => $sort,
            'link'             => $link,
            'icon'             => $icon,
            'sub_institute_id' => $institutes,
            'client_id'        => $clients,
            'menu_type'        => 'ENTRY',
            'site_map_name'    => $name,
            'menu_path'        => $name,
            'updated_at'       => now(),
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
        foreach ($this->profiles as $name => [$audience, $rights]) {
            $profile = DB::table('tbluserprofilemaster')
                ->where('sub_institute_id', $instituteId)->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

            if (! $profile) {
                if ($name === 'Admin') {
                    continue; // never create a second built-in profile
                }
                $profileId = DB::table('tbluserprofilemaster')->insertGetId([
                    'parent_id'        => 0,
                    'name'             => $name,
                    'description'      => $name.' (admission module)',
                    'sort_order'       => (int) DB::table('tbluserprofilemaster')->where('sub_institute_id', $instituteId)->max('sort_order') + 1,
                    'status'           => 1,
                    'sub_institute_id' => $instituteId,
                ]);
            } else {
                $profileId = (int) $profile->id;
            }

            foreach ($this->pages as [$link, $audiences]) {
                if ($audience !== 'admin' && ! in_array($audience, $audiences, true)) {
                    continue;
                }
                DB::table('tblgroupwise_rights')->updateOrInsert(
                    ['menu_id' => $menuIds[$link], 'profile_id' => $profileId, 'sub_institute_id' => $instituteId],
                    ['can_view' => $rights[0], 'can_add' => $rights[1], 'can_edit' => $rights[2], 'can_delete' => $rights[3]]
                );
            }
        }
    }
}
