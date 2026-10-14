<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Optional demo data (NOT called from DatabaseSeeder). Seeds a season, a policy, an eligibility
 * template and a few companies for ONE institute:
 *
 *   php artisan db:seed --class=PlacementDemoSeeder   (set PLACEMENT_DEMO_INSTITUTE=<sub_institute_id>)
 */
class PlacementDemoSeeder extends Seeder
{
    public function run()
    {
        $instituteId = (int) env('PLACEMENT_DEMO_INSTITUTE');
        if (! $instituteId || ! DB::table('school_setup')->where('Id', $instituteId)->exists()) {
            $this->command?->error('Set PLACEMENT_DEMO_INSTITUTE to a valid sub_institute_id first.');

            return;
        }
        $now = now();
        $base = ['sub_institute_id' => $instituteId, 'created_at' => $now, 'updated_at' => $now];

        DB::transaction(function () use ($instituteId, $base) {
            $seasonId = DB::table('placement_season')->insertGetId($base + [
                'name' => 'Placement 2026-27 (demo)', 'syear' => 2026, 'start_date' => '2026-07-01',
                'end_date' => '2027-06-30', 'is_current' => 0, 'status' => 1,
            ]);

            DB::table('placement_policy')->insert($base + [
                'season_id' => $seasonId, 'name' => 'Default policy (demo)', 'one_offer_rule' => 1,
                'dream_offer_rule' => 1, 'dream_min_ctc' => 10, 'max_applications' => 10,
                'max_active_backlogs' => 0, 'block_blacklisted' => 1, 'status' => 1,
            ]);

            DB::table('placement_eligibility_template')->insert($base + [
                'name' => 'CGPA 6.5+, no backlogs (demo)', 'min_cgpa' => 6.5, 'max_active_backlogs' => 0, 'status' => 1,
            ]);

            $industry = DB::table('placement_master')->where(['sub_institute_id' => $instituteId, 'master_type' => 'industry'])->value('id');
            foreach (['Acme Technologies (demo)' => 'prospect', 'Globex Consulting (demo)' => 'invited', 'Initech Systems (demo)' => 'confirmed'] as $name => $status) {
                $companyId = DB::table('placement_company')->insertGetId($base + [
                    'name' => $name, 'industry_id' => $industry, 'season_id' => $seasonId,
                    'mou_status' => 'none', 'status' => $status,
                ]);
                DB::table('placement_company_contact')->insert($base + [
                    'company_id' => $companyId, 'name' => 'HR Team', 'contact_type' => 'hr',
                    'email' => 'hr@example.com', 'is_primary' => 1,
                ]);
            }
        });
    }
}
