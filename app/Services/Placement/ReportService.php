<?php

namespace App\Services\Placement;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Report registry (Section 6). Each report is defined once and served as a DataTable, Excel and PDF.
 *
 * A definition has: title, description, filters (names the filter bar shows), roles allowed to run it
 * (ERP admins always can) and a builder that returns either
 *   - a query  (['query' => Builder, 'columns' => [key => [label, sqlExpr, ?formatter]]]) -> server-side DataTable
 *   - rows     (['rows' => Collection, 'columns' => [key => label]])                        -> small aggregates
 * Placed / package numbers come from MetricsService, the same source as the dashboards.
 *
 * Filters understood: season_id, standard_id, drive_id, company_id, status, passing_year, from, to, placed.
 * Coordinators get $f['standard_ids'] injected so student-level reports stay inside their courses.
 */
class ReportService
{
    protected MetricsService $metrics;

    public function __construct(MetricsService $metrics)
    {
        $this->metrics = $metrics;
    }

    protected function inst()
    {
        return session('sub_institute_id');
    }

    /** @return array<string,array{title:string,description:string,filters:string[],roles:string[]}> */
    public function definitions(): array
    {
        $all = ['tpo', 'principal', 'coordinator'];
        $mgmt = ['tpo', 'principal'];

        return [
            'student_register' => ['title' => 'Student placement register', 'description' => 'Every registered student with placement status, company and CTC.', 'filters' => ['season_id', 'standard_id', 'passing_year', 'placed'], 'roles' => $all],
            'placed_unplaced'  => ['title' => 'Placed vs unplaced list', 'description' => 'By course, batch and package category.', 'filters' => ['season_id', 'standard_id', 'passing_year', 'placed'], 'roles' => $all],
            'company_wise'     => ['title' => 'Company-wise report', 'description' => 'Drives, applicants, shortlisted, offers and CTC per company.', 'filters' => ['season_id', 'status'], 'roles' => $mgmt],
            'drive_funnel'     => ['title' => 'Drive-wise funnel', 'description' => 'Eligible → applied → shortlisted → each round → selected → joined.', 'filters' => ['season_id', 'company_id'], 'roles' => $mgmt],
            'dept_stats'       => ['title' => 'Course / batch-wise statistics', 'description' => 'Registered, placed, placement % and package statistics per course.', 'filters' => ['season_id', 'passing_year'], 'roles' => $all],
            'package_analysis' => ['title' => 'Package analysis', 'description' => 'Highest, average, median, lowest and slab-wise distribution.', 'filters' => ['season_id', 'standard_id', 'passing_year'], 'roles' => $all],
            'eligibility'      => ['title' => 'Eligibility report', 'description' => 'Eligible and not-eligible students of a drive, with reasons.', 'filters' => ['drive_id', 'status'], 'roles' => $all],
            'offers'           => ['title' => 'Offer report', 'description' => 'All offers: multiple, accepted, rejected, revoked, joined vs not joined.', 'filters' => ['season_id', 'standard_id', 'company_id', 'status'], 'roles' => $all],
            'attendance'       => ['title' => 'Attendance report', 'description' => 'Round attendance per drive.', 'filters' => ['season_id', 'drive_id'], 'roles' => $all],
            'yoy'              => ['title' => 'Year-on-year comparison', 'description' => 'Placement outcomes season by season.', 'filters' => [], 'roles' => $mgmt],
            'communication'    => ['title' => 'Notification & communication log', 'description' => 'Delivery status of email, WhatsApp and in-app messages.', 'filters' => ['status', 'from', 'to'], 'roles' => $mgmt],
            'naac'             => ['title' => 'NAAC / NIRF / AISHE format', 'description' => 'Median salary, % placed, higher studies and progression by batch.', 'filters' => [], 'roles' => $mgmt],
            'student_profile'  => ['title' => 'Student profile & resume report', 'description' => 'Academic details, skills, completeness and verification.', 'filters' => ['standard_id', 'passing_year', 'status'], 'roles' => $all],
            'pending'          => ['title' => 'Pending actions', 'description' => 'Unverified profiles, unconfirmed and unanswered offers.', 'filters' => [], 'roles' => $all],
        ] + $this->extraDefinitions();
    }

    /** Hook for reports that depend on later modules (training, other outcomes). */
    protected function extraDefinitions(): array
    {
        $all = ['tpo', 'principal', 'coordinator'];

        return [
            'training' => ['title' => 'Training & mock-test performance', 'description' => 'Sessions, attendance and scores by training type.', 'filters' => ['from', 'to'], 'roles' => $all],
        ];
    }

    public function build(string $key, array $f): array
    {
        $method = 'report'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        abort_unless(method_exists($this, $method), 404);

        return $this->{$method}($f);
    }

    /** Applies the declared select expressions to a query-mode report. */
    protected function select(Builder $query, array $columns): Builder
    {
        $parts = [];
        foreach ($columns as $key => $col) {
            $parts[] = $col[1].' as '.$key;
        }

        return $query->selectRaw(implode(', ', $parts));
    }

    protected function student(): string
    {
        return "CONCAT_WS(' ', s.first_name, s.last_name)";
    }

    // ---------------------------------------------------------------- 1 & 2: register / placed vs unplaced

    protected function registerQuery(array $f, bool $withCategory = false): array
    {
        $latest = DB::table('placement_offer')->where('sub_institute_id', $this->inst())->whereNull('deleted_at')
            ->whereIn('status', ['accepted', 'joined'])
            ->when(! empty($f['season_id']), fn ($q) => $q->where('season_id', (int) $f['season_id']))
            ->groupBy('student_id')->selectRaw('student_id, MAX(id) as offer_id');

        $query = $this->metrics->registered($f)
            ->leftJoinSub($latest, 'l', 'l.student_id', '=', 'p.student_id')
            ->leftJoin('placement_offer as o', 'o.id', '=', 'l.offer_id')
            ->leftJoin('placement_company as c', 'c.id', '=', 'o.company_id')
            ->when(($f['placed'] ?? '') === 'placed', fn ($q) => $q->whereNotNull('o.id'))
            ->when(($f['placed'] ?? '') === 'unplaced', fn ($q) => $q->whereNull('o.id'));

        $columns = [
            'enrollment_no' => ['Enrollment no', 's.enrollment_no'],
            'student'       => ['Student', $this->student()],
            'course'        => ['Course', 'st.name'],
            'passing_year'  => ['Batch', 'p.passing_year'],
            'status'        => ['Status', "CASE WHEN o.id IS NULL THEN 'Unplaced' ELSE 'Placed' END"],
            'company'       => ['Company', 'c.name'],
            'role_title'    => ['Role', 'o.role_title'],
            'ctc_total'     => ['CTC (LPA)', 'o.ctc_total'],
        ];
        if ($withCategory) {
            $columns['category'] = ['Package category', $this->categoryCase('o.ctc_total')];
        }

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    /** CASE expression mapping a CTC column onto the package-category master (Dream, Super Dream ...). */
    protected function categoryCase(string $col): string
    {
        $cats = DB::table('placement_master')->where('sub_institute_id', $this->inst())->where('master_type', 'package_category')
            ->whereNull('deleted_at')->where('status', 1)->get();
        if ($cats->isEmpty()) {
            return "CASE WHEN o.id IS NULL THEN NULL ELSE 'Uncategorised' END";
        }
        $pdo = DB::getPdo();
        $sql = 'CASE';
        foreach ($cats as $c) {
            $sql .= " WHEN {$col} >= ".(float) $c->min_ctc.($c->max_ctc !== null ? " AND {$col} < ".(float) $c->max_ctc : '').' THEN '.$pdo->quote($c->name);
        }

        return $sql." ELSE 'Uncategorised' END";
    }

    protected function reportStudentRegister(array $f): array
    {
        $r = $this->registerQuery($f);
        $placed = $this->metrics->placed($f)->count();
        $registered = $this->metrics->registered($f)->count();
        $r['summary'] = ['Registered' => $registered, 'Placed' => $placed, 'Unplaced' => max($registered - $placed, 0)];

        return $r;
    }

    protected function reportPlacedUnplaced(array $f): array
    {
        $r = $this->registerQuery($f, true);
        $r['summary'] = ['Rows' => (clone $r['query'])->count()];

        return $r;
    }

    // ---------------------------------------------------------------- 3: company-wise

    protected function reportCompanyWise(array $f): array
    {
        $season = ! empty($f['season_id']) ? ' AND d.season_id = '.(int) $f['season_id'] : '';
        $oSeason = ! empty($f['season_id']) ? ' AND o.season_id = '.(int) $f['season_id'] : '';
        $drives = "(SELECT COUNT(*) FROM placement_drive d WHERE d.company_id = c.id AND d.deleted_at IS NULL{$season})";
        $apps = fn ($status = null) => "(SELECT COUNT(*) FROM placement_application a JOIN placement_drive d ON d.id = a.drive_id WHERE d.company_id = c.id AND a.deleted_at IS NULL AND a.status <> 'withdrawn'{$season}"
            .($status ? " AND a.status IN ({$status})" : '').')';
        $offers = fn ($extra = '') => "(SELECT COUNT(*) FROM placement_offer o WHERE o.company_id = c.id AND o.deleted_at IS NULL AND o.status <> 'revoked'{$oSeason}{$extra})";

        $columns = [
            'company'     => ['Company', 'c.name'],
            'industry'    => ['Industry', 'ind.name'],
            'status'      => ['Status', 'c.status'],
            'drives'      => ['Drives', $drives],
            'applicants'  => ['Applicants', $apps()],
            'shortlisted' => ['Shortlisted / selected', $apps("'shortlisted','selected'")],
            'offers'      => ['Offers', $offers()],
            'accepted'    => ['Accepted / joined', $offers(" AND o.status IN ('accepted','joined')")],
            'highest'     => ['Highest CTC', "(SELECT MAX(o.ctc_total) FROM placement_offer o WHERE o.company_id = c.id AND o.deleted_at IS NULL AND o.status <> 'revoked'{$oSeason})"],
            'average'     => ['Average CTC', "(SELECT ROUND(AVG(o.ctc_total), 2) FROM placement_offer o WHERE o.company_id = c.id AND o.deleted_at IS NULL AND o.status <> 'revoked'{$oSeason})"],
        ];

        $query = DB::table('placement_company as c')->leftJoin('placement_master as ind', 'ind.id', '=', 'c.industry_id')
            ->where('c.sub_institute_id', $this->inst())->whereNull('c.deleted_at')
            ->when(! empty($f['status']), fn ($q) => $q->where('c.status', $f['status']));

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 4: drive funnel

    protected function reportDriveFunnel(array $f): array
    {
        $inst = $this->inst();
        $drives = DB::table('placement_drive as d')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->where('d.sub_institute_id', $inst)->whereNull('d.deleted_at')->where('d.status', '!=', 'draft')
            ->when(! empty($f['season_id']), fn ($q) => $q->where('d.season_id', (int) $f['season_id']))
            ->when(! empty($f['company_id']), fn ($q) => $q->where('d.company_id', (int) $f['company_id']))
            ->orderByDesc('d.drive_date')->limit(500)->get(['d.id', 'd.title', 'd.status', 'c.name as company']);
        $ids = $drives->pluck('id');

        $eligible = DB::table('placement_drive_eligible_student')->where('sub_institute_id', $inst)->whereIn('drive_id', $ids)->where('is_eligible', 1)
            ->select('drive_id', DB::raw('COUNT(DISTINCT student_id) as n'))->groupBy('drive_id')->pluck('n', 'drive_id');
        $apps = DB::table('placement_application')->where('sub_institute_id', $inst)->whereNull('deleted_at')->whereIn('drive_id', $ids)
            ->select('drive_id', 'status', DB::raw('COUNT(*) as n'))->groupBy('drive_id', 'status')->get()->groupBy('drive_id');
        $rounds = DB::table('placement_drive_round as r')->leftJoin('placement_round_result as rr', 'rr.round_id', '=', 'r.id')
            ->where('r.sub_institute_id', $inst)->whereNull('r.deleted_at')->whereIn('r.drive_id', $ids)
            ->select('r.drive_id', 'r.sequence', 'r.name', DB::raw('COUNT(rr.id) as participants'), DB::raw("SUM(CASE WHEN rr.result = 'cleared' THEN 1 ELSE 0 END) as cleared"))
            ->groupBy('r.id', 'r.drive_id', 'r.sequence', 'r.name')->orderBy('r.sequence')->get()->groupBy('drive_id');
        $offers = DB::table('placement_offer')->where('sub_institute_id', $inst)->whereNull('deleted_at')->whereIn('drive_id', $ids)->where('status', '!=', 'revoked')
            ->select('drive_id', DB::raw('COUNT(*) as offers'), DB::raw("SUM(CASE WHEN status = 'joined' THEN 1 ELSE 0 END) as joined"))->groupBy('drive_id')->get()->keyBy('drive_id');

        $rows = $drives->map(function ($d) use ($eligible, $apps, $rounds, $offers) {
            $a = ($apps[$d->id] ?? collect())->pluck('n', 'status');
            $applied = $a->except('withdrawn')->sum();
            $roundText = ($rounds[$d->id] ?? collect())->map(fn ($r) => "{$r->name}: {$r->participants} → {$r->cleared}")->implode('  |  ');
            // Shortlisted = everyone ever shortlisted: those still shortlisted / selected, or who reached round 1.
            $shortlisted = (int) (($a['shortlisted'] ?? 0) + ($a['selected'] ?? 0));
            $firstRound = ($rounds[$d->id] ?? collect())->first();
            if ($firstRound) {
                $shortlisted = max($shortlisted, (int) $firstRound->participants);
            }

            return [
                'company'     => $d->company, 'drive' => $d->title, 'status' => ucwords(str_replace('_', ' ', $d->status)),
                'eligible'    => (int) ($eligible[$d->id] ?? 0), 'applied' => (int) $applied,
                'shortlisted' => $shortlisted,
                'rounds'      => $roundText ?: '-',
                'selected'    => (int) ($a['selected'] ?? 0), 'offers' => (int) ($offers[$d->id]->offers ?? 0), 'joined' => (int) ($offers[$d->id]->joined ?? 0),
            ];
        });

        return ['rows' => $rows, 'columns' => [
            'company' => 'Company', 'drive' => 'Drive', 'status' => 'Status', 'eligible' => 'Eligible', 'applied' => 'Applied',
            'shortlisted' => 'Shortlisted', 'rounds' => 'Rounds (participants → cleared)', 'selected' => 'Selected', 'offers' => 'Offers', 'joined' => 'Joined',
        ]];
    }

    // ---------------------------------------------------------------- 5: department statistics

    protected function reportDeptStats(array $f): array
    {
        $registered = $this->metrics->registered($f)->selectRaw("COALESCE(st.name, 'Unassigned') as course, COUNT(*) as n")->groupBy('st.name')->pluck('n', 'course');
        $placed = $this->metrics->placed($f)->get(['o.ctc_total', 'st.name as course'])->groupBy(fn ($r) => $r->course ?: 'Unassigned');

        $rows = $registered->map(function ($n, $course) use ($placed) {
            $g = $placed[$course] ?? collect();
            $s = $this->metrics->ctcStats($g->pluck('ctc_total'));

            return ['course' => $course, 'registered' => (int) $n, 'placed' => $g->count(), 'unplaced' => max($n - $g->count(), 0),
                'percent' => $n ? round($g->count() / $n * 100, 1) : 0, 'highest' => $s['highest'], 'average' => $s['average'], 'median' => $s['median'], 'lowest' => $s['lowest']];
        })->values();

        $allCtc = $placed->flatten(1)->pluck('ctc_total');
        $s = $this->metrics->ctcStats($allCtc);
        $totalReg = $rows->sum('registered');
        $rows->push(['course' => 'TOTAL', 'registered' => $totalReg, 'placed' => $rows->sum('placed'), 'unplaced' => $rows->sum('unplaced'),
            'percent' => $totalReg ? round($rows->sum('placed') / $totalReg * 100, 1) : 0, 'highest' => $s['highest'], 'average' => $s['average'], 'median' => $s['median'], 'lowest' => $s['lowest']]);

        return ['rows' => $rows, 'columns' => [
            'course' => 'Course', 'registered' => 'Registered', 'placed' => 'Placed', 'unplaced' => 'Unplaced', 'percent' => 'Placed %',
            'highest' => 'Highest CTC', 'average' => 'Average CTC', 'median' => 'Median CTC', 'lowest' => 'Lowest CTC',
        ]];
    }

    // ---------------------------------------------------------------- 6: package analysis

    protected function reportPackageAnalysis(array $f): array
    {
        $ctcs = $this->metrics->placed($f)->pluck('o.ctc_total')->map(fn ($v) => (float) $v);
        $s = $this->metrics->ctcStats($ctcs);

        $rows = collect([
            ['item' => 'Students placed', 'value' => $s['count'], 'students' => '', 'percent' => ''],
            ['item' => 'Highest CTC (LPA)', 'value' => $s['highest'], 'students' => '', 'percent' => ''],
            ['item' => 'Average CTC (LPA)', 'value' => $s['average'], 'students' => '', 'percent' => ''],
            ['item' => 'Median CTC (LPA)', 'value' => $s['median'], 'students' => '', 'percent' => ''],
            ['item' => 'Lowest CTC (LPA)', 'value' => $s['lowest'], 'students' => '', 'percent' => ''],
        ]);
        foreach ($this->metrics->distribute($ctcs) as $slab) {
            $rows->push(['item' => 'Slab: '.$slab['label'], 'value' => '', 'students' => $slab['count'], 'percent' => $s['count'] ? round($slab['count'] / $s['count'] * 100, 1).'%' : '0%']);
        }

        return ['rows' => $rows, 'columns' => ['item' => 'Measure', 'value' => 'Value', 'students' => 'Students', 'percent' => '% of placed']];
    }

    // ---------------------------------------------------------------- 7: eligibility

    protected function reportEligibility(array $f): array
    {
        $columns = [
            'enrollment_no' => ['Enrollment no', 's.enrollment_no'],
            'student'       => ['Student', $this->student()],
            'course'        => ['Course', 'st.name'],
            'role'          => ['Role', 'r.title'],
            'result'        => ['Result', "CASE WHEN pe.is_eligible = 1 THEN 'Eligible' ELSE 'Not eligible' END"],
            'reasons'       => ['Reasons', 'pe.reasons', fn ($v) => $v ? implode('; ', (array) json_decode($v, true)) : ''],
        ];

        $query = DB::table('placement_drive_eligible_student as pe')
            ->join('tblstudent as s', 's.id', '=', 'pe.student_id')
            ->join('placement_drive_role as r', 'r.id', '=', 'pe.role_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('pe.sub_institute_id', $this->inst())
            ->where('pe.drive_id', (int) ($f['drive_id'] ?? 0))
            ->when(($f['status'] ?? '') === 'eligible', fn ($q) => $q->where('pe.is_eligible', 1))
            ->when(($f['status'] ?? '') === 'not_eligible', fn ($q) => $q->where('pe.is_eligible', 0))
            ->when(isset($f['standard_ids']), fn ($q) => $q->whereIn('e.standard_id', $f['standard_ids'] ?: [0]));

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 8: offers

    protected function reportOffers(array $f): array
    {
        $columns = [
            'enrollment_no' => ['Enrollment no', 's.enrollment_no'],
            'student'       => ['Student', $this->student()],
            'course'        => ['Course', 'st.name'],
            'company'       => ['Company', 'c.name'],
            'role_title'    => ['Role', 'o.role_title'],
            'ctc_total'     => ['CTC (LPA)', 'o.ctc_total'],
            'status'        => ['Status', 'o.status', fn ($v) => ucwords(str_replace('_', ' ', (string) $v))],
            'multiple'      => ['Multiple offers', "CASE WHEN (SELECT COUNT(*) FROM placement_offer x WHERE x.student_id = o.student_id AND x.deleted_at IS NULL AND x.status NOT IN ('revoked','rejected')) > 1 THEN 'Yes' ELSE 'No' END"],
            'offer_date'    => ['Offer date', 'o.offer_date'],
            'verified'      => ['Verified', "CASE WHEN o.verified_at IS NULL THEN 'No' ELSE 'Yes' END"],
        ];

        $query = DB::table('placement_offer as o')
            ->join('tblstudent as s', 's.id', '=', 'o.student_id')
            ->join('placement_company as c', 'c.id', '=', 'o.company_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('o.sub_institute_id', $this->inst())->whereNull('o.deleted_at')
            ->when(! empty($f['season_id']), fn ($q) => $q->where('o.season_id', (int) $f['season_id']))
            ->when(! empty($f['company_id']), fn ($q) => $q->where('o.company_id', (int) $f['company_id']))
            ->when(! empty($f['status']), fn ($q) => $q->where('o.status', $f['status']))
            ->when(! empty($f['standard_id']), fn ($q) => $q->where('e.standard_id', (int) $f['standard_id']))
            ->when(isset($f['standard_ids']), fn ($q) => $q->whereIn('e.standard_id', $f['standard_ids'] ?: [0]));

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 9: attendance

    protected function reportAttendance(array $f): array
    {
        $rows = DB::table('placement_drive_round as r')
            ->join('placement_drive as d', 'd.id', '=', 'r.drive_id')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->leftJoin('placement_round_result as rr', 'rr.round_id', '=', 'r.id')
            ->where('r.sub_institute_id', $this->inst())->whereNull('r.deleted_at')
            ->when(! empty($f['season_id']), fn ($q) => $q->where('d.season_id', (int) $f['season_id']))
            ->when(! empty($f['drive_id']), fn ($q) => $q->where('r.drive_id', (int) $f['drive_id']))
            ->groupBy('r.id', 'r.name', 'r.round_date', 'c.name', 'd.title')->orderByDesc('r.round_date')
            ->selectRaw("c.name as company, d.title as drive, r.name as round_name, r.round_date,
                COUNT(rr.id) as participants,
                SUM(CASE WHEN rr.attendance = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN rr.attendance = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN rr.attendance = 'pending' THEN 1 ELSE 0 END) as pending")
            ->get()->map(fn ($r) => [
                'type' => 'Round', 'company' => $r->company, 'drive' => $r->drive, 'session' => $r->round_name, 'date' => $r->round_date,
                'participants' => (int) $r->participants, 'present' => (int) $r->present, 'absent' => (int) $r->absent, 'pending' => (int) $r->pending,
                'percent' => $r->participants ? round($r->present / $r->participants * 100, 1) : 0,
            ]);

        return ['rows' => $rows->concat($this->trainingAttendanceRows($f)), 'columns' => [
            'type' => 'Type', 'company' => 'Company / trainer', 'drive' => 'Drive / training', 'session' => 'Session', 'date' => 'Date',
            'participants' => 'Participants', 'present' => 'Present', 'absent' => 'Absent', 'pending' => 'Not marked', 'percent' => 'Present %',
        ]];
    }

    /** Training sessions join the attendance report once the training module (Phase 5) is installed. */
    protected function trainingAttendanceRows(array $f): Collection
    {
        if (! Schema::hasTable('placement_training_attendance') || ! empty($f['drive_id'])) {
            return collect();
        }

        return DB::table('placement_training as t')->leftJoin('placement_training_attendance as ta', 'ta.training_id', '=', 't.id')
            ->where('t.sub_institute_id', $this->inst())->whereNull('t.deleted_at')
            ->groupBy('t.id', 't.title', 't.trainer', 't.session_date', 't.training_type')->orderByDesc('t.session_date')
            ->selectRaw("t.title, t.trainer, t.session_date, t.training_type, COUNT(ta.id) as participants,
                SUM(CASE WHEN ta.attendance = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN ta.attendance = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN ta.attendance = 'pending' THEN 1 ELSE 0 END) as pending")
            ->get()->map(fn ($t) => [
                'type' => 'Training', 'company' => $t->trainer ?: '-', 'drive' => $t->title, 'session' => ucwords(str_replace('_', ' ', $t->training_type)), 'date' => $t->session_date,
                'participants' => (int) $t->participants, 'present' => (int) $t->present, 'absent' => (int) $t->absent, 'pending' => (int) $t->pending,
                'percent' => $t->participants ? round($t->present / $t->participants * 100, 1) : 0,
            ]);
    }

    // ---------------------------------------------------------------- 10: training (Phase 5 data)

    protected function reportTraining(array $f): array
    {
        $columns = ['title' => 'Training', 'type' => 'Type', 'date' => 'Date', 'trainer' => 'Trainer', 'participants' => 'Participants', 'present' => 'Present', 'average' => 'Average score', 'highest' => 'Highest score', 'max' => 'Max marks'];
        if (! Schema::hasTable('placement_training')) {
            return ['rows' => collect(), 'columns' => $columns];
        }

        $rows = DB::table('placement_training as t')->leftJoin('placement_training_attendance as ta', 'ta.training_id', '=', 't.id')
            ->where('t.sub_institute_id', $this->inst())->whereNull('t.deleted_at')
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('t.session_date', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('t.session_date', '<=', $f['to']))
            ->groupBy('t.id', 't.title', 't.training_type', 't.session_date', 't.trainer', 't.max_score')->orderByDesc('t.session_date')
            ->selectRaw("t.title, t.training_type, t.session_date, t.trainer, t.max_score, COUNT(ta.id) as participants,
                SUM(CASE WHEN ta.attendance = 'present' THEN 1 ELSE 0 END) as present, ROUND(AVG(ta.score), 2) as average, MAX(ta.score) as highest")
            ->get()->map(fn ($t) => [
                'title' => $t->title, 'type' => ucwords(str_replace('_', ' ', $t->training_type)), 'date' => $t->session_date, 'trainer' => $t->trainer ?: '-',
                'participants' => (int) $t->participants, 'present' => (int) $t->present, 'average' => $t->average ?? '-', 'highest' => $t->highest ?? '-', 'max' => $t->max_score ?? '-',
            ]);

        return ['rows' => $rows, 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 11: year on year

    protected function reportYoy(array $f): array
    {
        $rows = DB::table('placement_season')->where('sub_institute_id', $this->inst())->whereNull('deleted_at')->orderBy('start_date')->get()->map(function ($season) {
            $sf = ['season_id' => $season->id];
            $placed = $this->metrics->placed($sf)->pluck('o.ctc_total');
            $s = $this->metrics->ctcStats($placed);

            return [
                'season' => $season->name,
                'companies' => DB::table('placement_drive')->where('sub_institute_id', $this->inst())->whereNull('deleted_at')->where('season_id', $season->id)->where('status', '!=', 'draft')->distinct()->count('company_id'),
                'drives' => DB::table('placement_drive')->where('sub_institute_id', $this->inst())->whereNull('deleted_at')->where('season_id', $season->id)->where('status', '!=', 'draft')->count(),
                'offers' => $this->metrics->offersMade($sf)->count(), 'placed' => $s['count'],
                'highest' => $s['highest'], 'average' => $s['average'], 'median' => $s['median'], 'lowest' => $s['lowest'],
            ];
        });

        return ['rows' => $rows, 'columns' => [
            'season' => 'Season', 'companies' => 'Companies', 'drives' => 'Drives', 'offers' => 'Offers', 'placed' => 'Students placed',
            'highest' => 'Highest CTC', 'average' => 'Average CTC', 'median' => 'Median CTC', 'lowest' => 'Lowest CTC',
        ]];
    }

    // ---------------------------------------------------------------- 12: communication log

    protected function reportCommunication(array $f): array
    {
        $columns = [
            'created_at' => ['When', 'n.created_at'],
            'student'    => ['Student', $this->student()],
            'channel'    => ['Channel', 'n.channel'],
            'event_key'  => ['Event', 'n.event_key'],
            'recipient'  => ['To', 'n.recipient'],
            'subject'    => ['Subject', 'n.subject'],
            'status'     => ['Status', 'n.status'],
            'error'      => ['Error', 'n.error'],
        ];

        $query = DB::table('placement_notification_log as n')->leftJoin('tblstudent as s', 's.id', '=', 'n.student_id')
            ->where('n.sub_institute_id', $this->inst())
            ->when(! empty($f['status']), fn ($q) => $q->where('n.status', $f['status']))
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('n.created_at', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('n.created_at', '<=', $f['to']));

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 13: NAAC / NIRF / AISHE

    protected function reportNaac(array $f): array
    {
        $hasOutcomes = Schema::hasTable('placement_outcome_other');
        $outgoing = $this->metrics->registered()->whereNotNull('p.passing_year')->selectRaw('p.passing_year as batch, COUNT(*) as n')->groupBy('p.passing_year')->pluck('n', 'batch');
        $placed = $this->metrics->placed()->whereNotNull('p.passing_year')->get(['p.passing_year as batch', 'o.ctc_total'])->groupBy('batch');
        $outcomes = $hasOutcomes
            ? DB::table('placement_outcome_other as x')->join('placement_student_profile as p', fn ($j) => $j->on('p.student_id', '=', 'x.student_id')->on('p.sub_institute_id', '=', 'x.sub_institute_id'))
                ->where('x.sub_institute_id', $this->inst())->whereNull('x.deleted_at')->whereNotNull('p.passing_year')
                ->select('p.passing_year as batch', 'x.outcome_type', DB::raw('COUNT(DISTINCT x.student_id) as n'))->groupBy('p.passing_year', 'x.outcome_type')->get()->groupBy('batch')
            : collect();

        $rows = $outgoing->sortKeys()->map(function ($n, $batch) use ($placed, $outcomes) {
            $g = $placed[$batch] ?? collect();
            $s = $this->metrics->ctcStats($g->pluck('ctc_total'));
            $o = ($outcomes[$batch] ?? collect())->pluck('n', 'outcome_type');
            $higher = (int) ($o['higher_studies'] ?? 0);
            $entre = (int) ($o['entrepreneurship'] ?? 0);

            return [
                'batch' => $batch, 'outgoing' => (int) $n, 'placed' => $g->count(), 'placed_percent' => $n ? round($g->count() / $n * 100, 1) : 0,
                'median' => $s['median'], 'highest' => $s['highest'], 'higher_studies' => $higher, 'entrepreneurship' => $entre,
                'progression' => $n ? round(($g->count() + $higher + $entre) / $n * 100, 1) : 0,
            ];
        })->values();

        return ['rows' => $rows, 'columns' => [
            'batch' => 'Graduating batch', 'outgoing' => 'Outgoing students', 'placed' => 'Placed', 'placed_percent' => '% placed',
            'median' => 'Median salary (LPA)', 'highest' => 'Highest salary (LPA)', 'higher_studies' => 'Higher studies', 'entrepreneurship' => 'Entrepreneurs',
            'progression' => 'Progression % (placed + higher studies + entrepreneurs)',
        ]];
    }

    // ---------------------------------------------------------------- 14: student profile & resume

    protected function reportStudentProfile(array $f): array
    {
        $columns = [
            'enrollment_no' => ['Enrollment no', 's.enrollment_no'],
            'student'       => ['Student', $this->student()],
            'course'        => ['Course', 'st.name'],
            'passing_year'  => ['Batch', 'p.passing_year'],
            'cgpa'          => ['CGPA', 'p.cgpa'],
            'tenth'         => ['10th %', 'p.tenth_percent'],
            'twelfth'       => ['12th %', 'p.twelfth_percent'],
            'backlogs'      => ['Backlogs', 'p.active_backlogs'],
            'skills'        => ['Skills', "(SELECT GROUP_CONCAT(k.skill_name) FROM placement_student_skill k WHERE k.profile_id = p.id AND k.deleted_at IS NULL)"],
            'resume'        => ['Resume', "CASE WHEN p.resume_file IS NULL OR p.resume_file = '' THEN 'No' ELSE 'Yes' END"],
            'completeness'  => ['Complete %', 'p.completeness'],
            'verification'  => ['Verification', 'p.verification_status'],
        ];

        $query = $this->metrics->registered($f)
            ->when(! empty($f['status']), fn ($q) => $q->where('p.verification_status', $f['status']));

        return ['query' => $this->select($query, $columns), 'columns' => $columns];
    }

    // ---------------------------------------------------------------- 15: pending actions

    protected function reportPending(array $f): array
    {
        $inst = $this->inst();
        $scope = $f['standard_ids'] ?? null;
        $rows = collect();

        $profiles = $this->metrics->registered($scope !== null ? ['standard_ids' => $scope] : [])->where('p.verification_status', 'pending')
            ->get(['s.enrollment_no', DB::raw($this->student().' as student'), 'p.updated_at']);
        foreach ($profiles as $p) {
            $rows->push(['type' => 'Unverified profile', 'subject' => $p->student.' ('.$p->enrollment_no.')', 'detail' => 'Awaiting verification', 'since' => $p->updated_at]);
        }

        if ($scope === null) {
            $offers = DB::table('placement_offer as o')->join('tblstudent as s', 's.id', '=', 'o.student_id')->join('placement_company as c', 'c.id', '=', 'o.company_id')
                ->where('o.sub_institute_id', $inst)->whereNull('o.deleted_at')
                ->where(fn ($q) => $q->where(fn ($w) => $w->where('o.status', 'accepted')->whereNull('o.verified_at'))->orWhere(fn ($w) => $w->where('o.status', 'offered')->where('o.created_at', '<', now()->subDays(7))))
                ->get(['o.status', 'o.created_at', 'o.role_title', 'c.name as company', 's.enrollment_no', DB::raw($this->student().' as student')]);
            foreach ($offers as $o) {
                $rows->push([
                    'type'    => $o->status === 'accepted' ? 'Unconfirmed offer' : 'Offer awaiting response (7+ days)',
                    'subject' => $o->student.' ('.$o->enrollment_no.')', 'detail' => $o->company.' - '.$o->role_title, 'since' => $o->created_at,
                ]);
            }
        }

        $rows = $rows->map(fn ($r) => $r + ['age' => $r['since'] ? now()->diffInDays($r['since']) : '']);

        return ['rows' => $rows->sortByDesc('age')->values(), 'columns' => ['type' => 'Pending item', 'subject' => 'Student', 'detail' => 'Detail', 'since' => 'Since', 'age' => 'Days pending']];
    }
}
