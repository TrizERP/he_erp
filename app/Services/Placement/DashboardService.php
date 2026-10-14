<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementSeason;
use Illuminate\Support\Facades\DB;

/** Data for the four placement dashboards. Every number comes from MetricsService so reports reconcile. */
class DashboardService
{
    protected MetricsService $metrics;
    protected StudentLookup $students;

    public function __construct(MetricsService $metrics, StudentLookup $students)
    {
        $this->metrics = $metrics;
        $this->students = $students;
    }

    public function currentSeasonId(): ?int
    {
        return PlacementSeason::where('is_current', 1)->value('id');
    }

    /**
     * TPO / coordinator KPIs. $standardIds restricts to a coordinator's courses (null = whole institute).
     */
    public function management(?int $seasonId, ?array $standardIds = null): array
    {
        $inst = session('sub_institute_id');
        $f = ['season_id' => $seasonId] + ($standardIds !== null ? ['standard_ids' => $standardIds] : []);

        $registered = $this->metrics->registered($f)->count();
        $verified = $this->metrics->registered($f)->where('p.verification_status', 'verified')->count();
        $pendingVerification = $this->metrics->registered($f)->where('p.verification_status', 'pending')->count();

        $placedRows = $this->metrics->placed($f)->get(['o.ctc_total', 'o.company_id', 'o.student_id', 'st.name as course', 'e.standard_id']);
        $placed = $placedRows->count();
        $stats = $this->metrics->ctcStats($placedRows->pluck('ctc_total'));

        // Department-wise placed vs unplaced
        $regByCourse = $this->metrics->registered($f)->selectRaw("COALESCE(st.name, 'Unassigned') as course, COUNT(*) as total")->groupBy('st.name')->pluck('total', 'course');
        $placedByCourse = $placedRows->groupBy(fn ($r) => $r->course ?: 'Unassigned')->map->count();
        $departments = $regByCourse->map(fn ($total, $course) => [
            'course'   => $course,
            'total'    => (int) $total,
            'placed'   => (int) ($placedByCourse[$course] ?? 0),
            'unplaced' => max((int) $total - (int) ($placedByCourse[$course] ?? 0), 0),
            'percent'  => $total ? round(($placedByCourse[$course] ?? 0) / $total * 100, 1) : 0,
        ])->values();

        $eligible = DB::table('placement_drive_eligible_student as pe')
            ->join('placement_drive as d', 'd.id', '=', 'pe.drive_id')
            ->when($standardIds !== null, fn ($q) => $q->join('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 'pe.student_id')->where('e.syear', session('syear')))
                ->whereIn('e.standard_id', $standardIds ?: [0]))
            ->where('pe.sub_institute_id', $inst)->where('pe.is_eligible', 1)->whereNull('d.deleted_at')
            ->when($seasonId, fn ($q) => $q->where('d.season_id', $seasonId))
            ->distinct()->count('pe.student_id');

        $companyStatus = DB::table('placement_company')->where('sub_institute_id', $inst)->whereNull('deleted_at')
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $visited = (int) ($companyStatus['visited'] ?? 0);
        $invited = (int) (($companyStatus['invited'] ?? 0) + ($companyStatus['confirmed'] ?? 0) + $visited);

        $driveQuery = fn () => DB::table('placement_drive')->where('sub_institute_id', $inst)->whereNull('deleted_at')->when($seasonId, fn ($q) => $q->where('season_id', $seasonId));

        return [
            'season_id'      => $seasonId,
            'registered'     => $registered,
            'verified'       => $verified,
            'eligible'       => $eligible,
            'placed'         => $placed,
            'unplaced'       => max($registered - $placed, 0),
            'placed_percent' => $registered ? round($placed / $registered * 100, 1) : 0,
            'companies'      => ['visited' => $visited, 'invited' => $invited, 'prospect' => (int) ($companyStatus['prospect'] ?? 0), 'blacklisted' => (int) ($companyStatus['blacklisted'] ?? 0)],
            'offers_made'    => $this->metrics->offersMade($f)->count(),
            'ctc'            => $stats,
            'active_drives'  => $driveQuery()->whereIn('status', ['published', 'registration_open', 'registration_closed', 'in_progress'])->count(),
            'pending_verification' => $pendingVerification,
            'pending_offer_verification' => $standardIds === null
                ? DB::table('placement_offer')->where('sub_institute_id', $inst)->whereNull('deleted_at')->where('status', 'accepted')->whereNull('verified_at')
                    ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))->count()
                : 0,
            'departments'    => $departments,
            'upcoming'       => $this->upcoming(),
        ];
    }

    /** Next drives and rounds, as a date-ordered agenda. */
    public function upcoming(int $limit = 12): array
    {
        $inst = session('sub_institute_id');
        $drives = DB::table('placement_drive as d')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->where('d.sub_institute_id', $inst)->whereNull('d.deleted_at')->whereNotIn('d.status', ['draft', 'cancelled', 'completed'])
            ->whereDate('d.drive_date', '>=', now()->toDateString())->orderBy('d.drive_date')->limit($limit)
            ->get(['d.id', 'd.title', 'd.drive_date as date', 'd.start_time', 'c.name as company'])
            ->map(fn ($r) => ['type' => 'Drive', 'date' => $r->date, 'time' => $r->start_time ? substr($r->start_time, 0, 5) : '', 'title' => $r->company.' - '.$r->title, 'url' => route('placement.drives.show', $r->id)]);

        $rounds = DB::table('placement_drive_round as r')->join('placement_drive as d', 'd.id', '=', 'r.drive_id')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->where('r.sub_institute_id', $inst)->whereNull('r.deleted_at')->where('r.status', 'scheduled')
            ->whereDate('r.round_date', '>=', now()->toDateString())->orderBy('r.round_date')->limit($limit)
            ->get(['r.id', 'r.name', 'r.round_date as date', 'r.start_time', 'c.name as company'])
            ->map(fn ($r) => ['type' => 'Round', 'date' => $r->date, 'time' => $r->start_time ? substr($r->start_time, 0, 5) : '', 'title' => $r->company.' - '.$r->name, 'url' => route('placement.rounds.show', $r->id)]);

        return $drives->concat($rounds)->sortBy(fn ($e) => $e['date'].' '.$e['time'])->take($limit)->values()->all();
    }

    /** Principal / management analytics across seasons. */
    public function principal(?int $seasonId): array
    {
        $inst = session('sub_institute_id');

        $trend = PlacementSeason::orderBy('start_date')->get()->map(function ($season) {
            $f = ['season_id' => $season->id];
            $rows = $this->metrics->placed($f)->get(['o.ctc_total']);
            $stats = $this->metrics->ctcStats($rows->pluck('ctc_total'));

            return [
                'season' => $season->name, 'placed' => $rows->count(),
                'average' => $stats['average'], 'median' => $stats['median'], 'highest' => $stats['highest'],
                'offers' => $this->metrics->offersMade($f)->count(),
            ];
        })->values();

        $f = ['season_id' => $seasonId];
        $placed = $this->metrics->placed($f)->get(['o.ctc_total', 'c.name as company', 'ind.name as industry']);

        $topRecruiters = $placed->groupBy('company')->map(fn ($g, $company) => ['company' => $company ?: 'Unknown', 'hires' => $g->count(), 'highest' => round($g->max('ctc_total'), 2)])
            ->sortByDesc('hires')->take(10)->values();

        $sectors = $placed->groupBy(fn ($r) => $r->industry ?: 'Unclassified')->map->count()->sortDesc();

        return [
            'season_id'    => $seasonId,
            'trend'        => $trend,
            'top'          => $topRecruiters,
            'distribution' => $this->metrics->distribute($placed->pluck('ctc_total')->map(fn ($v) => (float) $v)),
            'sectors'      => $sectors,
            'ctc'          => $this->metrics->ctcStats($placed->pluck('ctc_total')),
        ];
    }

    /** Coordinator extras: students needing action. */
    public function coordinatorActions(array $standardIds): array
    {
        $f = ['standard_ids' => $standardIds];

        $notRegistered = DB::table('tblstudent as s')
            ->join('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('placement_student_profile as p', fn ($j) => $j->on('p.student_id', '=', 's.id')->whereNull('p.deleted_at'))
            ->where('s.sub_institute_id', session('sub_institute_id'))->whereIn('e.standard_id', $standardIds ?: [0])
            ->where(fn ($q) => $q->whereNull('p.id')->orWhere('p.registration_status', '!=', 'registered'))->count();

        $incomplete = $this->metrics->registered($f)->where('p.completeness', '<', 60)->count();
        $rejected = $this->metrics->registered($f)->where('p.verification_status', 'rejected')->count();

        return ['not_registered' => $notRegistered, 'incomplete_profiles' => $incomplete, 'rejected_profiles' => $rejected];
    }

    /** Student dashboard. */
    public function student(int $studentId): array
    {
        $inst = session('sub_institute_id');
        $profile = DB::table('placement_student_profile')->where(['sub_institute_id' => $inst, 'student_id' => $studentId])->whereNull('deleted_at')->first();

        $eligibleDrives = DB::table('placement_drive_eligible_student as pe')->join('placement_drive as d', 'd.id', '=', 'pe.drive_id')
            ->where('pe.sub_institute_id', $inst)->where(['pe.student_id' => $studentId, 'pe.is_eligible' => 1])
            ->whereIn('d.status', ['published', 'registration_open'])->whereNull('d.deleted_at')->distinct()->count('pe.drive_id');

        $applications = DB::table('placement_application as a')->join('placement_drive as d', 'd.id', '=', 'a.drive_id')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->join('placement_drive_role as r', 'r.id', '=', 'a.role_id')
            ->where('a.sub_institute_id', $inst)->where('a.student_id', $studentId)->whereNull('a.deleted_at')
            ->orderByDesc('a.applied_at')->limit(8)
            ->get(['c.name as company', 'r.title as role', 'a.status', 'a.applied_at', 'a.status_changed_at']);

        $rounds = DB::table('placement_round_result as rr')->join('placement_drive_round as r', 'r.id', '=', 'rr.round_id')
            ->join('placement_drive as d', 'd.id', '=', 'r.drive_id')->join('placement_company as c', 'c.id', '=', 'd.company_id')
            ->where('rr.sub_institute_id', $inst)->where('rr.student_id', $studentId)->whereNull('r.deleted_at')
            ->where('r.status', 'scheduled')->whereDate('r.round_date', '>=', now()->toDateString())->orderBy('r.round_date')->limit(5)
            ->get(['c.name as company', 'r.name', 'r.round_date', 'r.start_time', 'r.venue']);

        $offers = DB::table('placement_offer as o')->join('placement_company as c', 'c.id', '=', 'o.company_id')
            ->where('o.sub_institute_id', $inst)->where('o.student_id', $studentId)->whereNull('o.deleted_at')->orderByDesc('o.id')->limit(5)
            ->get(['c.name as company', 'o.role_title', 'o.ctc_total', 'o.status']);

        return [
            'profile'       => $profile,
            'eligible_drives' => $eligibleDrives,
            'applications'  => $applications,
            'rounds'        => $rounds,
            'offers'        => $offers,
            'unread'        => DB::table('placement_notification_log')->where(['sub_institute_id' => $inst, 'student_id' => $studentId, 'channel' => 'inapp'])->whereNull('read_at')->count(),
        ];
    }
}
