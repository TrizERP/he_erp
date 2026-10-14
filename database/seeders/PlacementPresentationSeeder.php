<?php

namespace Database\Seeders;

use App\Models\placement\PlacementDrive;
use App\Services\Placement\EligibilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Presentation data for the Placement module: seasons, policy, companies, drives at every stage,
 * applications, rounds, offers, training, outcomes, notices and logs for ONE institute.
 *
 *   PLACEMENT_DEMO_INSTITUTE=133 php artisan db:seed --class=PlacementPresentationSeeder
 *   PLACEMENT_DEMO_INSTITUTE=133 PLACEMENT_DEMO_CLEAN=1 php artisan db:seed --class=PlacementPresentationSeeder   (removes it again)
 *
 * It inserts straight into the tables (no services), so NO email / WhatsApp is ever sent. Companies are
 * fictional. Every row it creates is tagged (created_by = 999999, notification related_type =
 * demo_seed, audit user_id = 999999) so the clean mode can remove exactly what it added.
 */
class PlacementPresentationSeeder extends Seeder
{
    protected const MARK = 999999;

    protected int $I;
    protected $now;
    protected array $held = [];       // student ids currently holding an offer
    protected array $appCount = [];   // student id => applications made
    protected array $ids = [];

    public function run()
    {
        $this->I = (int) env('PLACEMENT_DEMO_INSTITUTE');
        if (! $this->I || ! DB::table('school_setup')->where('Id', $this->I)->exists()) {
            $this->command?->error('Set PLACEMENT_DEMO_INSTITUTE to a valid sub_institute_id.');

            return;
        }
        if (env('PLACEMENT_DEMO_CLEAN')) {
            $this->clean();

            return;
        }
        if (DB::table('placement_season')->where('sub_institute_id', $this->I)->where('created_by', self::MARK)->exists()) {
            $this->command?->warn('Presentation data already present. Run with PLACEMENT_DEMO_CLEAN=1 first to rebuild it.');

            return;
        }

        mt_srand(2026);
        $this->now = now();
        session(['sub_institute_id' => $this->I, 'syear' => 2026, 'user_id' => 0]);

        DB::transaction(function () {
            $this->masters();
            $this->seasonsAndPolicy();
            $pool = $this->students();
            $this->companies();
            $this->priorSeason($pool);
            $this->drives();
            $this->training($pool);
            $this->extras($pool);
            $this->syncPlaced();
        });

        $this->command?->info('Presentation data created.');
    }

    // ------------------------------------------------------------------ helpers

    protected function row(string $table, array $data, bool $std = true): int
    {
        $base = ['sub_institute_id' => $this->I, 'created_at' => $this->now, 'updated_at' => $this->now];
        if ($std) {
            $base['created_by'] = self::MARK;
        }

        return (int) DB::table($table)->insertGetId($base + $data);
    }

    protected function pick(array $a)
    {
        return $a[mt_rand(0, count($a) - 1)];
    }

    protected function chance(float $p): bool
    {
        return mt_rand(0, 9999) / 10000 < $p;
    }

    protected function master(string $type, string $name): ?int
    {
        return DB::table('placement_master')->where(['sub_institute_id' => $this->I, 'master_type' => $type, 'name' => $name])->whereNull('deleted_at')->value('id');
    }

    protected function masters(): void
    {
        foreach (['industry', 'round_type', 'job_type', 'job_role', 'skill'] as $type) {
            $this->ids[$type] = DB::table('placement_master')->where(['sub_institute_id' => $this->I, 'master_type' => $type])->whereNull('deleted_at')->pluck('id', 'name')->all();
        }
        if (empty($this->ids['industry'])) {
            throw new \RuntimeException('Run PlacementMasterSeeder first (the masters are missing).');
        }
    }

    // ------------------------------------------------------------------ season, policy, templates

    protected function seasonsAndPolicy(): void
    {
        $this->ids['prior_season'] = $this->row('placement_season', ['name' => 'Placement 2025-26', 'syear' => 2025, 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_current' => 0, 'status' => 1]);
        DB::table('placement_season')->where('sub_institute_id', $this->I)->update(['is_current' => 0]);
        $this->ids['season'] = $this->row('placement_season', ['name' => 'Placement 2026-27', 'syear' => 2026, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_current' => 1, 'status' => 1]);

        foreach ([['season', 'Placement Policy 2026-27'], ['prior_season', 'Placement Policy 2025-26']] as [$key, $name]) {
            $this->row('placement_policy', [
                'season_id' => $this->ids[$key], 'name' => $name, 'one_offer_rule' => 1, 'dream_offer_rule' => 1, 'dream_min_ctc' => 10,
                'max_applications' => 5, 'max_active_backlogs' => 2, 'block_blacklisted' => 1, 'debar_days' => 30,
                'notes' => 'One offer per student. A student holding an offer may apply again only for packages above 10 LPA. Students who accept and then do not join are debarred for 30 days.',
                'approved_at' => $this->now, 'status' => 1,
            ]);
        }

        $this->ids['tpl'] = [
            'core'  => $this->row('placement_eligibility_template', ['name' => 'Core IT roles (CGPA 7.0, no backlogs)', 'min_cgpa' => 7.0, 'max_active_backlogs' => 0, 'min_12th_percent' => 60, 'status' => 1]),
            'mass'  => $this->row('placement_eligibility_template', ['name' => 'Mass recruiters (CGPA 6.0, up to 2 backlogs)', 'min_cgpa' => 6.0, 'max_active_backlogs' => 2, 'status' => 1]),
            'dream' => $this->row('placement_eligibility_template', ['name' => 'Dream / Super Dream (CGPA 8.0)', 'min_cgpa' => 8.0, 'max_active_backlogs' => 0, 'min_10th_percent' => 70, 'min_12th_percent' => 70, 'status' => 1]),
        ];
    }

    // ------------------------------------------------------------------ students

    protected function students(): array
    {
        $courses = ['cs7' => [4564, 44], 'it7' => [4580, 34], 'me7' => [4588, 12], 'ce7' => [4550, 8], 'ec7' => [4572, 6], 'cs5' => [4562, 14]];
        $demoStudent = 238330; // set up earlier by hand; keep her profile as it is
        $pool = [];

        foreach ($courses as $key => [$std, $n]) {
            $ids = DB::table('tblstudent as s')->join('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', 2026))
                ->where('s.sub_institute_id', $this->I)->where('s.status', 1)->where('e.standard_id', $std)->where('s.id', '!=', $demoStudent)
                ->orderBy('s.id')->pluck('s.id')->all();
            shuffle($ids);
            $pool[$key] = array_slice($ids, 0, $key === 'cs5' ? $n - 1 : $n);
            if ($key === 'cs5') {
                array_unshift($pool[$key], $demoStudent);
            }
        }
        $this->ids['std'] = array_map(fn ($c) => $c[0], $courses);
        $skills = array_keys($this->ids['skill']);

        foreach ($pool as $key => $students) {
            foreach ($students as $sid) {
                if ($sid === $demoStudent) {
                    $this->ids['profile'][$sid] = (int) DB::table('placement_student_profile')->where(['sub_institute_id' => $this->I, 'student_id' => $sid])->value('id');
                    continue;
                }
                $backlogs = $this->chance(0.8) ? 0 : (int) $this->pick([1, 1, 2, 3]);
                $roll = mt_rand(1, 100);
                $pid = $this->row('placement_student_profile', [
                    'student_id' => $sid, 'registration_status' => 'registered', 'undertaking_accepted_at' => $this->now,
                    'cgpa' => round(5.2 + mt_rand(0, 430) / 100, 2), 'tenth_percent' => mt_rand(55, 96), 'twelfth_percent' => mt_rand(55, 95),
                    'active_backlogs' => $backlogs, 'passing_year' => $key === 'cs5' ? 2028 : 2027,
                    'verification_status' => $roll <= 88 ? 'verified' : ($roll <= 95 ? 'pending' : 'rejected'),
                    'verification_remarks' => $roll > 95 ? 'Marksheet does not match the CGPA entered' : null,
                    'verified_at' => $roll <= 88 ? $this->now : null, 'completeness' => mt_rand(55, 100),
                    'projects' => json_encode([['title' => $this->pick(['Library Management System', 'E-commerce Website', 'Attendance App', 'Chat Application', 'Inventory Tracker']), 'description' => 'College project']]),
                    'preferences' => json_encode(['locations' => [$this->pick(['Ahmedabad', 'Pune', 'Bengaluru', 'Mumbai'])], 'job_types' => ['Full-time']]),
                ]);
                $this->ids['profile'][$sid] = $pid;
                foreach ((array) array_rand(array_flip($skills), mt_rand(2, 4)) as $skill) {
                    $this->row('placement_student_skill', ['profile_id' => $pid, 'student_id' => $sid, 'skill_id' => $this->ids['skill'][$skill], 'skill_name' => $skill, 'level' => $this->pick(['beginner', 'intermediate', 'intermediate', 'advanced'])]);
                }
            }
        }

        $this->ids['pool'] = $pool;
        $this->ids['demo_student'] = $demoStudent;

        return $pool;
    }

    // ------------------------------------------------------------------ companies

    protected function companies(): void
    {
        $specs = [
            ['Nexora Technologies', 'Information Technology', 'Bengaluru', 'visited', 4.5, 'Well-organised drive, strong interview panel.'],
            ['Brightwave Systems', 'Information Technology', 'Pune', 'visited', 4.0, 'Large intake; quick turnaround on results.'],
            ['Quantora Solutions', 'Information Technology', 'Hyderabad', 'confirmed', null, null],
            ['Veritas Infosoft', 'Information Technology', 'Ahmedabad', 'confirmed', null, null],
            ['Pinnacle Analytics', 'Consulting', 'Mumbai', 'confirmed', null, null],
            ['Orbit Cloud Services', 'Information Technology', 'Pune', 'confirmed', null, null],
            ['Zenith Manufacturing', 'Manufacturing', 'Vadodara', 'visited', 4.0, 'Good core-branch roles; plant visit offered.'],
            ['Alpine Finserv', 'Banking & Finance', 'Mumbai', 'invited', null, null],
            ['Medilink Health Tech', 'Healthcare', 'Ahmedabad', 'prospect', null, null],
            ['Stellar Infotech', 'Information Technology', 'Gandhinagar', 'visited', 4.0, 'Smooth process and timely offers.'],
            ['Urbanedge Software', 'Information Technology', 'Surat', 'visited', 3.5, 'Good volume hiring; offer letters took two weeks.'],
            ['Kinetic Automation', 'Manufacturing', 'Rajkot', 'blacklisted', 2.0, 'Withdrew offers after the process; policy breach.'],
        ];

        foreach ($specs as $i => [$name, $industry, $hq, $status, $rating, $feedback]) {
            $slug = strtolower(preg_replace('/[^a-z]/i', '', explode(' ', $name)[0]));
            $id = $this->row('placement_company', [
                'name' => $name, 'industry_id' => $this->ids['industry'][$industry] ?? null, 'website' => "https://www.{$slug}.example", 'hq_location' => $hq,
                'address' => "{$hq}, India", 'mou_status' => in_array($status, ['visited', 'confirmed']) ? 'signed' : ($status === 'invited' ? 'in_progress' : 'none'),
                'mou_valid_till' => in_array($status, ['visited', 'confirmed']) ? '2028-03-31' : null, 'status' => $status,
                'blacklist_reason' => $status === 'blacklisted' ? $feedback : null, 'rating' => $rating, 'feedback' => $feedback,
                'visit_history' => $status === 'visited' ? 'Visited in the previous season as well.' : null, 'season_id' => $this->ids['season'],
            ]);
            $this->ids['company'][$name] = $id;

            $hr = $this->row('placement_company_contact', ['company_id' => $id, 'name' => $this->pick(['Anita Shah', 'Rahul Mehta', 'Priya Nair', 'Karan Joshi', 'Neha Kulkarni']), 'designation' => 'HR Manager', 'contact_type' => 'hr', 'email' => "hr@{$slug}.example", 'mobile' => '98'.mt_rand(10000000, 99999999), 'is_primary' => 1]);
            $this->row('placement_company_contact', ['company_id' => $id, 'name' => $this->pick(['Vivek Desai', 'Sneha Patel', 'Amit Verma']), 'designation' => 'Campus Relations', 'contact_type' => 'poc', 'email' => "campus@{$slug}.example", 'mobile' => '97'.mt_rand(10000000, 99999999), 'is_primary' => 0]);

            $this->row('placement_company_communication', ['company_id' => $id, 'contact_id' => $hr, 'mode' => 'email', 'subject' => 'Campus recruitment 2026-27', 'notes' => 'Shared the placement brochure and available dates.', 'communicated_at' => now()->subDays(mt_rand(30, 80)), 'follow_up_date' => null, 'follow_up_done' => 0]);
            if (in_array($status, ['confirmed', 'invited', 'prospect'])) {
                $this->row('placement_company_communication', ['company_id' => $id, 'contact_id' => $hr, 'mode' => 'call', 'subject' => 'Confirm drive date', 'notes' => 'Discussed eligibility and the number of openings.', 'communicated_at' => now()->subDays(mt_rand(2, 10)), 'follow_up_date' => now()->addDays(mt_rand(-2, 6))->toDateString(), 'follow_up_done' => 0]);
            }
        }
    }

    // ------------------------------------------------------------------ previous season (PPO conversions)

    protected function priorSeason(array $pool): void
    {
        $candidates = array_merge($pool['cs7'], $pool['it7']);
        shuffle($candidates);
        foreach (array_slice($candidates, 0, 12) as $i => $sid) {
            $company = $this->ids['company'][$this->pick(['Nexora Technologies', 'Brightwave Systems', 'Zenith Manufacturing'])];
            $fixed = round(mt_rand(36, 70) / 10, 1);
            $this->row('placement_offer', [
                'season_id' => $this->ids['prior_season'], 'company_id' => $company, 'student_id' => $sid, 'role_title' => 'Software Engineer (PPO)', 'is_ppo' => 1,
                'ctc_fixed' => $fixed, 'ctc_variable' => 0, 'joining_bonus' => 0, 'ctc_total' => $fixed, 'location' => 'Pune', 'offer_date' => '2025-12-'.mt_rand(10, 28),
                'joining_date' => '2027-07-01', 'status' => 'accepted', 'responded_at' => $this->now, 'verified_at' => $this->now, 'verified_by' => 0,
            ]);
            $this->held[$sid] = true;
        }
    }

    // ------------------------------------------------------------------ drives

    protected function drives(): void
    {
        $std = $this->ids['std'];
        $cs7 = $std['cs7']; $it7 = $std['it7'];
        $specs = [
            ['c' => 'Stellar Infotech', 'title' => 'Stellar Infotech - Campus Hiring', 'status' => 'completed', 'day' => -75, 'tpl' => 'mass', 'cgpa' => 5.5, 'bl' => 3, 'std' => [$cs7, $it7],
                'roles' => [['Associate Engineer', 3.6, 4.4, 'Gandhinagar', 40]], 'apply' => 0.7, 'sl' => 0.85,
                'rounds' => [['Aptitude Test', 'Aptitude', 0.85, 0], ['HR Interview', 'HR', 0.95, 1]]],
            ['c' => 'Nexora Technologies', 'title' => 'Nexora Technologies - Campus Recruitment 2026', 'status' => 'completed', 'day' => -62, 'tpl' => 'core', 'cgpa' => 7.0, 'bl' => 0, 'std' => [$cs7, $it7],
                'roles' => [['Software Engineer', 6, 9, 'Bengaluru', 20], ['Associate Engineer', 4.5, 6, 'Pune', 10]], 'apply' => 0.95, 'sl' => 0.85,
                'rounds' => [['Online Aptitude Test', 'Aptitude', 0.8, 0], ['Technical Interview', 'Technical', 0.7, 0], ['HR Interview', 'HR', 0.95, 1]]],
            ['c' => 'Urbanedge Software', 'title' => 'Urbanedge Software - Volume Hiring', 'status' => 'completed', 'day' => -52, 'tpl' => 'mass', 'cgpa' => 5.5, 'bl' => 3, 'std' => [$cs7, $it7, $std['me7'], $std['ec7']],
                'roles' => [['Associate Engineer', 3.5, 4.0, 'Surat', 40]], 'apply' => 0.75, 'sl' => 0.85,
                'rounds' => [['Aptitude Test', 'Aptitude', 0.85, 0], ['HR Interview', 'HR', 0.95, 1]]],
            ['c' => 'Brightwave Systems', 'title' => 'Brightwave Systems - Mass Recruitment', 'status' => 'completed', 'day' => -45, 'tpl' => 'mass', 'cgpa' => 6.0, 'bl' => 2, 'std' => [$cs7, $it7, $std['cs5']],
                'roles' => [['Associate Engineer', 3.5, 4.2, 'Pune', 60]], 'apply' => 0.9, 'sl' => 0.9,
                'rounds' => [['Aptitude Test', 'Aptitude', 0.85, 0], ['Group Discussion', 'Group Discussion', 0.85, 0], ['HR Interview', 'HR', 0.95, 1]]],
            ['c' => 'Zenith Manufacturing', 'title' => 'Zenith Manufacturing - Core Engineering Drive', 'status' => 'completed', 'day' => -38, 'tpl' => 'mass', 'cgpa' => 6.0, 'bl' => 2, 'std' => [$std['me7'], $std['ce7'], $std['ec7']],
                'roles' => [['Graduate Engineer Trainee', 3.6, 4.8, 'Vadodara', 15]], 'apply' => 0.95, 'sl' => 0.9,
                'rounds' => [['Technical Test', 'Technical', 0.8, 0], ['HR Interview', 'HR', 0.95, 1]]],
            ['c' => 'Quantora Solutions', 'title' => 'Quantora Solutions - Super Dream Drive', 'status' => 'in_progress', 'day' => -3, 'tpl' => 'dream', 'cgpa' => 8.0, 'bl' => 0, 'std' => [$cs7, $it7],
                'roles' => [['Software Engineer', 14, 22, 'Hyderabad', 5]], 'apply' => 0.85, 'sl' => 0.9,
                'rounds' => [['Coding Round', 'Coding', 0.7, 0, 'published'], ['Technical Interview', 'Technical', 0, 1, 'scheduled']]],
            ['c' => 'Orbit Cloud Services', 'title' => 'Orbit Cloud Services - Cloud Support Hiring', 'status' => 'registration_closed', 'day' => 7, 'tpl' => 'mass', 'cgpa' => 6.5, 'bl' => 1, 'std' => [$cs7, $it7],
                'roles' => [['Cloud Support Engineer', 4.5, 6, 'Pune', 12]], 'apply' => 0.5, 'sl' => 0.4, 'rounds' => []],
            ['c' => 'Veritas Infosoft', 'title' => 'Veritas Infosoft - Hiring Drive', 'status' => 'registration_open', 'day' => 10, 'tpl' => 'mass', 'cgpa' => 6.5, 'bl' => 1, 'std' => [$cs7, $it7, $std['cs5']],
                'roles' => [['Software Developer', 5, 7, 'Ahmedabad', 10], ['QA Engineer', 4, 5, 'Ahmedabad', 6]], 'apply' => 0.35, 'sl' => 0, 'rounds' => []],
            ['c' => 'Pinnacle Analytics', 'title' => 'Pinnacle Analytics - Data Roles', 'status' => 'published', 'day' => 20, 'tpl' => 'core', 'cgpa' => 7.0, 'bl' => 0, 'std' => [$cs7, $it7, $std['cs5']],
                'roles' => [['Data Analyst', 5, 7, 'Mumbai', 8]], 'apply' => 0, 'sl' => 0, 'rounds' => []],
            ['c' => 'Alpine Finserv', 'title' => 'Alpine Finserv - Graduate Trainee (planning)', 'status' => 'draft', 'day' => 35, 'tpl' => 'core', 'cgpa' => 7.0, 'bl' => 0, 'std' => [$cs7, $it7],
                'roles' => [['Management Trainee', 4, 5, 'Mumbai', 10]], 'apply' => 0, 'sl' => 0, 'rounds' => []],
        ];

        foreach ($specs as $spec) {
            $this->drive($spec);
        }
    }

    protected function drive(array $s): void
    {
        $date = now()->addDays($s['day']);
        $open = in_array($s['status'], ['registration_open', 'published']);
        $deadline = $open ? now()->addDays($s['status'] === 'published' ? 14 : 5)->setTime(17, 0) : $date->copy()->subDays(8)->setTime(17, 0);
        $driveId = $this->row('placement_drive', [
            'season_id' => $this->ids['season'], 'company_id' => $this->ids['company'][$s['c']], 'title' => $s['title'],
            'description' => 'On-campus recruitment drive. Shortlisted candidates go through the rounds listed below.', 'drive_mode' => 'on_campus',
            'drive_date' => $date->toDateString(), 'start_time' => '09:30:00', 'end_time' => '17:00:00', 'venue' => 'Seminar Hall, Main Building',
            'registration_deadline' => $deadline, 'status' => $s['status'], 'bond_details' => 'No service bond.', 'published_at' => $s['status'] === 'draft' ? null : $deadline->copy()->subDays(10),
        ]);

        $roleIds = [];
        foreach ($s['roles'] as [$title, $min, $max, $loc, $seats]) {
            $roleIds[] = $this->row('placement_drive_role', [
                'drive_id' => $driveId, 'role_master_id' => $this->ids['job_role'][$title] ?? null, 'title' => $title, 'job_type_id' => $this->ids['job_type']['Full-time'] ?? null,
                'ctc_min' => $min, 'ctc_max' => $max, 'location' => $loc, 'openings' => $seats,
            ]);
        }
        $this->row('placement_drive_eligibility', [
            'drive_id' => $driveId, 'role_id' => null, 'template_id' => $this->ids['tpl'][$s['tpl']], 'min_cgpa' => $s['cgpa'], 'max_active_backlogs' => $s['bl'], 'standard_ids' => json_encode($s['std']),
        ]);

        if ($s['status'] === 'draft') {
            return;
        }
        app(EligibilityService::class)->computeForDrive(PlacementDrive::withoutGlobalScopes()->find($driveId));
        $this->applications($driveId, $s, $roleIds, ! in_array($s['status'], ['published', 'registration_open']));
    }

    /** Applications, shortlisting, rounds, results and offers for a drive that is past registration. */
    protected function applications(int $driveId, array $s, array $roleIds, bool $closed): void
    {
        $eligible = DB::table('placement_drive_eligible_student')->where(['sub_institute_id' => $this->I, 'drive_id' => $driveId, 'is_eligible' => 1])->get()->groupBy('student_id');
        $students = $eligible->keys()->all();
        shuffle($students);
        $apps = [];

        foreach ($students as $sid) {
            if ($sid === $this->ids['demo_student'] || isset($this->held[$sid]) || ($this->appCount[$sid] ?? 0) >= 5 || ! $this->chance($s['apply'])) {
                continue;
            }
            $role = $this->pick($eligible[$sid]->pluck('role_id')->all());
            $appliedAt = now()->addDays($s['day'])->subDays(mt_rand(8, 14));
            $apps[$sid] = ['id' => $this->row('placement_application', ['drive_id' => $driveId, 'role_id' => $role, 'student_id' => $sid, 'status' => 'applied', 'applied_at' => $appliedAt, 'status_changed_at' => $appliedAt]), 'role' => $role, 'status' => 'applied'];
            $this->appCount[$sid] = ($this->appCount[$sid] ?? 0) + 1;
        }
        if (! $closed) {
            return;
        }

        // shortlisting
        foreach ($apps as $sid => &$a) {
            if ($this->chance($s['sl'])) {
                $a['status'] = 'shortlisted';
            } else {
                $a['status'] = $this->chance(0.1) ? 'withdrawn' : 'rejected';
            }
            DB::table('placement_application')->where('id', $a['id'])->update(['status' => $a['status'], 'status_changed_at' => $this->now, 'remarks' => $a['status'] === 'rejected' ? 'Not shortlisted' : null]);
            if ($a['status'] === 'withdrawn') {
                $this->appCount[$sid]--;
            }
        }
        unset($a);

        $participants = array_filter($apps, fn ($a) => $a['status'] === 'shortlisted');
        foreach ($s['rounds'] as $seq => $r) {
            [$name, $type, $passRate, $final] = $r;
            $mode = $r[4] ?? 'published';
            $roundDate = $mode === 'scheduled' ? now()->addDays(3)->toDateString() : now()->addDays($s['day'])->addDays($seq)->toDateString();
            $roundId = $this->row('placement_drive_round', [
                'drive_id' => $driveId, 'round_type_id' => $this->ids['round_type'][$type] ?? null, 'name' => $name, 'sequence' => $seq + 1, 'mode' => $type === 'Aptitude' || $type === 'Coding' ? 'online' : 'offline',
                'round_date' => $roundDate, 'start_time' => '10:00:00', 'end_time' => '13:00:00', 'venue' => 'Computer Lab 2', 'status' => $mode === 'scheduled' ? 'scheduled' : 'completed',
                'is_final' => $final, 'results_published_at' => $mode === 'scheduled' ? null : $this->now,
            ]);

            $next = [];
            foreach ($participants as $sid => $a) {
                if ($mode === 'scheduled') {
                    $this->row('placement_round_result', ['round_id' => $roundId, 'drive_id' => $driveId, 'application_id' => $a['id'], 'student_id' => $sid, 'attendance' => 'pending', 'result' => 'pending'], false);
                    if ($this->chance(0.6)) {
                        $start = now()->addDays(3)->setTime(10, 0)->addMinutes(20 * count($next));
                        $this->row('placement_round_slot', ['round_id' => $roundId, 'application_id' => $a['id'], 'student_id' => $sid, 'slot_start' => $start, 'slot_end' => $start->copy()->addMinutes(20), 'panel' => 'Panel '.(1 + count($next) % 2), 'venue' => 'Interview Room '.(1 + count($next) % 2)]);
                    }
                    $next[$sid] = $a;
                    continue;
                }
                $present = $this->chance(0.92);
                $cleared = $present && $this->chance($passRate);
                $this->row('placement_round_result', [
                    'round_id' => $roundId, 'drive_id' => $driveId, 'application_id' => $a['id'], 'student_id' => $sid, 'attendance' => $present ? 'present' : 'absent', 'attendance_via' => $this->chance(0.5) ? 'qr' : 'manual',
                    'result' => $cleared ? 'cleared' : 'rejected', 'score' => $present ? mt_rand(40, 95) : null, 'remarks' => $present ? null : 'Did not attend', 'published_at' => $this->now,
                ], false);
                if ($cleared) {
                    $next[$sid] = $a;
                    if ($final) {
                        $apps[$sid]['status'] = 'selected';
                        DB::table('placement_application')->where('id', $a['id'])->update(['status' => 'selected', 'status_changed_at' => $this->now]);
                    }
                } else {
                    $apps[$sid]['status'] = 'rejected';
                    DB::table('placement_application')->where('id', $a['id'])->update(['status' => 'rejected', 'status_changed_at' => $this->now, 'remarks' => "Rejected in {$name}"]);
                }
            }
            $participants = $next;
        }

        if ($s['status'] === 'completed') {
            $this->offers($driveId, $s, array_filter($apps, fn ($a) => $a['status'] === 'selected'));
        }
    }

    protected function offers(int $driveId, array $s, array $selected): void
    {
        $roleRows = DB::table('placement_drive_role')->where('drive_id', $driveId)->get()->keyBy('id');
        $i = 0;
        foreach ($selected as $sid => $a) {
            $role = $roleRows[$a['role']];
            $fixed = round($role->ctc_min + (($role->ctc_max - $role->ctc_min) * mt_rand(0, 100) / 100), 1);
            $variable = $this->chance(0.3) ? round($fixed * 0.08, 1) : 0;
            $holding = isset($this->held[$sid]);
            $roll = mt_rand(1, 100);

            if ($holding) {
                $status = 'rejected';
            } elseif ($i % 17 === 3) {
                $status = 'revoked';
            } elseif ($i === 6 && $s['c'] === 'Brightwave Systems') {
                $status = 'not_joined';
            } elseif ($roll <= 86) {
                $status = 'accepted';
            } elseif ($roll <= 93) {
                $status = 'offered';
            } else {
                $status = 'rejected';
            }
            $i++;

            $this->row('placement_offer', [
                'application_id' => $a['id'], 'drive_id' => $driveId, 'role_id' => $a['role'], 'season_id' => $this->ids['season'], 'company_id' => $this->ids['company'][$s['c']], 'student_id' => $sid,
                'role_title' => $role->title, 'ctc_fixed' => $fixed, 'ctc_variable' => $variable, 'joining_bonus' => $this->chance(0.25) ? 0.5 : 0, 'ctc_total' => $fixed + $variable,
                'location' => $role->location, 'offer_date' => now()->addDays($s['day'])->addDays(3)->toDateString(), 'joining_date' => '2027-07-01', 'status' => $status,
                'responded_at' => in_array($status, ['accepted', 'rejected', 'not_joined']) ? $this->now : null,
                'status_reason' => ['rejected' => $holding ? 'Accepted another offer' : 'Preferred a different role', 'revoked' => 'Company withdrew the position', 'not_joined' => 'Did not report on the joining date'][$status] ?? null,
                'verified_at' => in_array($status, ['accepted', 'not_joined']) && $this->chance(0.8) ? $this->now : null, 'verified_by' => 0,
            ]);
            if (in_array($status, ['offered', 'accepted'])) {
                $this->held[$sid] = true;
            }
            if ($status === 'not_joined') {
                DB::table('placement_student_profile')->where(['sub_institute_id' => $this->I, 'student_id' => $sid])
                    ->update(['debarred_until' => now()->addDays(20)->toDateString(), 'debar_reason' => 'Did not join accepted offer']);
            }
        }
    }

    protected function syncPlaced(): void
    {
        DB::table('placement_student_profile')->where('sub_institute_id', $this->I)->update(['is_placed' => 0]);
        $placed = DB::table('placement_offer')->where('sub_institute_id', $this->I)->whereIn('status', ['accepted', 'joined'])->pluck('student_id')->unique();
        DB::table('placement_student_profile')->where('sub_institute_id', $this->I)->whereIn('student_id', $placed)->update(['is_placed' => 1]);
    }

    // ------------------------------------------------------------------ training

    protected function training(array $pool): void
    {
        $finalYear = array_merge($pool['cs7'], $pool['it7'], $pool['me7'], $pool['ce7'], $pool['ec7']);
        $sessions = [
            ['Aptitude Mock Test 1', 'mock_test', -40, 50, 'Seminar Hall', 'Prof. R. Dave'],
            ['Soft Skills & Communication Workshop', 'soft_skills', -30, null, 'Auditorium', 'Ms. K. Trivedi'],
            ['Mock Technical Interview', 'mock_interview', -20, null, 'Interview Rooms', 'Industry panel'],
            ['Coding Mock Test', 'technical', -12, 100, 'Computer Lab 1', 'Prof. N. Shah'],
            ['Aptitude Mock Test 2', 'mock_test', -5, 50, 'Seminar Hall', 'Prof. R. Dave'],
            ['Resume Building Workshop', 'soft_skills', 6, null, 'Seminar Hall', 'Alumni speaker'],
        ];
        foreach ($sessions as [$title, $type, $day, $max, $venue, $trainer]) {
            $tid = $this->row('placement_training', [
                'title' => $title, 'training_type' => $type, 'trainer' => $trainer, 'session_date' => now()->addDays($day)->toDateString(), 'start_time' => '10:00:00', 'end_time' => '13:00:00',
                'venue' => $venue, 'max_score' => $max, 'standard_ids' => json_encode([$this->ids['std']['cs7'], $this->ids['std']['it7']]), 'status' => $day < 0 ? 'completed' : 'scheduled',
            ]);
            foreach (array_merge($pool['cs7'], $pool['it7']) as $sid) {
                $past = $day < 0;
                $present = $past && $this->chance(0.85);
                $this->row('placement_training_attendance', [
                    'training_id' => $tid, 'student_id' => $sid, 'attendance' => $past ? ($present ? 'present' : 'absent') : 'pending',
                    'score' => $present && $max ? round($max * mt_rand(35, 95) / 100, 1) : null, 'rating' => $present && $type === 'mock_interview' ? mt_rand(2, 5) : null,
                    'feedback' => $present && $type === 'mock_interview' ? $this->pick(['Good fundamentals, improve communication.', 'Confident and clear answers.', 'Needs more practice on data structures.', 'Strong problem solving.']) : null,
                ], false);
            }
        }
    }

    // ------------------------------------------------------------------ outcomes, notices, reviews, requirements, logs

    protected function extras(array $pool): void
    {
        $all = array_merge($pool['cs7'], $pool['it7'], $pool['me7']);
        $unplaced = array_values(array_filter($all, fn ($sid) => ! isset($this->held[$sid])));
        shuffle($unplaced);

        foreach ([['higher_studies', 'Gujarat Technological University', 'M.E. Computer Engineering', 'India'], ['higher_studies', 'Nirma University', 'M.Tech Data Science', 'India'],
            ['higher_studies', 'University of Leeds', 'MSc Computer Science', 'United Kingdom'], ['higher_studies', 'IIM Ahmedabad (CAT prep)', 'MBA', 'India'],
            ['entrepreneurship', 'CampusCart Pvt Ltd', 'Co-founder', 'India'], ['entrepreneurship', 'GreenByte Labs', 'Founder', 'India'],
            ['off_campus_placement', 'Infoedge Software', 'Junior Developer', 'India'], ['off_campus_placement', 'Sterling Auto Parts', 'Design Engineer', 'India']] as $i => [$type, $org, $role, $country]) {
            if (isset($unplaced[$i])) {
                $this->row('placement_outcome_other', ['student_id' => $unplaced[$i], 'outcome_type' => $type, 'organization' => $org, 'program_role' => $role, 'country' => $country, 'outcome_date' => now()->subDays(mt_rand(5, 60))->toDateString(), 'verified' => 1]);
            }
        }

        foreach ([['Placement season 2026-27 is open', 'Registration for placements is open. Complete your profile, upload your resume and get it verified by the placement coordinator.', 1],
            ['Dress code for campus drives', 'Formal attire is mandatory for all drives. Carry two copies of your resume and your college ID.', 0],
            ['Quantora Solutions - round 2 on '.now()->addDays(3)->format('d M'), 'Shortlisted students: check your interview slot under My Rounds and report 15 minutes early.', 1],
            ['Aptitude mock test results', 'Results of Aptitude Mock Test 2 are available under My Training.', 0]] as [$title, $body, $pin]) {
            $this->row('placement_announcement', ['title' => $title, 'body' => $body, 'audience' => 'registered', 'is_pinned' => $pin, 'notify' => 0, 'status' => 1, 'publish_at' => now()->subDays(mt_rand(1, 20))]);
        }

        foreach (array_slice($unplaced, 10, 5) as $i => $sid) {
            $reviewed = $i < 2;
            $this->row('placement_resume_review', [
                'student_id' => $sid, 'profile_id' => $this->ids['profile'][$sid] ?? null, 'status' => $reviewed ? 'reviewed' : 'requested', 'request_note' => 'Please check my formatting and projects section.',
                'feedback' => $reviewed ? 'Move education below projects, quantify your project impact, and keep it to one page.' : null, 'reviewer_id' => $reviewed ? 0 : null,
                'requested_at' => now()->subDays(mt_rand(2, 9)), 'reviewed_at' => $reviewed ? now()->subDay() : null,
            ]);
        }

        foreach ([['Pinnacle Analytics', 'Data Analyst Trainee', 8, '5-7 LPA', 'SQL, Python, Excel', 'pending'], ['Medilink Health Tech', 'Junior Software Engineer', 5, '4-5 LPA', 'Java, Spring', 'pending'], ['Alpine Finserv', 'Management Trainee', 10, '4-5 LPA', 'Communication, Excel', 'accepted']] as [$c, $title, $n, $ctc, $skills, $st]) {
            $this->row('placement_company_requirement', ['company_id' => $this->ids['company'][$c], 'contact_name' => 'HR Team', 'contact_email' => 'hr@'.strtolower(explode(' ', $c)[0]).'.example', 'title' => $title, 'openings' => $n, 'ctc_range' => $ctc, 'skills' => $skills, 'description' => 'Posted through the recruiter portal.', 'status' => $st]);
        }

        // notification history: in-app for a sample of students, a few email rows
        $sample = array_slice($all, 0, 40);
        foreach ($sample as $i => $sid) {
            $this->row('placement_notification_log', ['student_id' => $sid, 'channel' => 'inapp', 'event_key' => 'drive_published', 'recipient' => 'in-app', 'body' => 'Veritas Infosoft drive on '.now()->addDays(10)->format('d-m-Y').'. Registration closes soon.', 'status' => 'sent', 'sent_at' => now()->subDays(mt_rand(1, 4)), 'read_at' => $i % 3 === 0 ? now() : null, 'related_type' => 'demo_seed'], false);
            if ($i < 25) {
                $this->row('placement_notification_log', ['student_id' => $sid, 'channel' => 'email', 'event_key' => 'drive_published', 'recipient' => 'st******@example.com', 'subject' => 'New drive: Veritas Infosoft', 'body' => 'A new drive matching your profile has been published.', 'status' => $i % 11 === 0 ? 'failed' : 'sent', 'error' => $i % 11 === 0 ? 'Mailbox unavailable' : null, 'sent_at' => now()->subDays(2), 'related_type' => 'demo_seed'], false);
            }
        }

        $this->row('placement_notification_log', ['student_id' => $this->ids['demo_student'], 'channel' => 'inapp', 'event_key' => 'drive_published', 'recipient' => 'in-app', 'body' => 'Veritas Infosoft drive on '.now()->addDays(10)->format('d-m-Y').'. You are eligible - apply before the deadline.', 'status' => 'sent', 'sent_at' => now()->subDay(), 'related_type' => 'demo_seed'], false);

        foreach ([['policy', 'approve', 'Principal approved the 2026-27 policy'], ['drive', 'status_change', 'Published after the TPO review'], ['offer', 'create_override', 'Principal approved a second offer above the dream CTC'],
            ['company', 'status_change', 'Blacklisted: withdrew offers after the process'], ['application', 'apply_override', 'Student missed the deadline due to a medical emergency (approved)'],
            ['drive_round', 'publish_results', null], ['offer', 'verify', null], ['student_profile', 'verification', null]] as [$entity, $action, $reason]) {
            DB::table('placement_audit_log')->insert(['sub_institute_id' => $this->I, 'entity_type' => $entity, 'entity_id' => mt_rand(1, 30), 'action' => $action, 'reason' => $reason,
                'old_values' => null, 'new_values' => null, 'user_id' => self::MARK, 'ip_address' => '127.0.0.1', 'created_at' => now()->subDays(mt_rand(0, 40))]);
        }
    }

    // ------------------------------------------------------------------ clean

    protected function clean(): void
    {
        $I = $this->I;
        $m = self::MARK;
        DB::transaction(function () use ($I, $m) {
            $drives = DB::table('placement_drive')->where(['sub_institute_id' => $I, 'created_by' => $m])->pluck('id');
            $rounds = DB::table('placement_drive_round')->where(['sub_institute_id' => $I, 'created_by' => $m])->pluck('id');
            $trainings = DB::table('placement_training')->where(['sub_institute_id' => $I, 'created_by' => $m])->pluck('id');

            DB::table('placement_round_result')->whereIn('round_id', $rounds)->delete();
            DB::table('placement_training_attendance')->whereIn('training_id', $trainings)->delete();
            DB::table('placement_drive_eligible_student')->where('sub_institute_id', $I)->whereIn('drive_id', $drives)->delete();
            DB::table('placement_notification_log')->where(['sub_institute_id' => $I, 'related_type' => 'demo_seed'])->delete();
            DB::table('placement_audit_log')->where(['sub_institute_id' => $I, 'user_id' => $m])->delete();

            foreach (['placement_round_slot', 'placement_offer', 'placement_application', 'placement_drive_round', 'placement_drive_eligibility', 'placement_drive_role', 'placement_drive',
                'placement_training', 'placement_resume_review', 'placement_outcome_other', 'placement_announcement', 'placement_company_requirement', 'placement_company_communication',
                'placement_company_contact', 'placement_company', 'placement_student_skill', 'placement_student_profile', 'placement_eligibility_template', 'placement_policy', 'placement_season'] as $table) {
                DB::table($table)->where(['sub_institute_id' => $I, 'created_by' => $m])->delete();
            }
        });
        $this->command?->info('Presentation data removed.');
    }
}
