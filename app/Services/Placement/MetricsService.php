<?php

namespace App\Services\Placement;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single definition of the placement numbers, shared by the dashboards and the reports so that
 * their totals always reconcile.
 *
 *  - registered = placement profiles with registration_status = registered
 *  - placed     = a student holding an Accepted or Joined offer; the latest such offer is the
 *                 student's package (one row per placed student)
 *  - offers made = every offer except those revoked
 *
 * Filters (all optional): season_id, standard_ids (int[]), passing_year, from, to (offer date).
 */
class MetricsService
{
    protected function institute()
    {
        return session('sub_institute_id');
    }

    /** Registered students, joined to the ERP student, current enrolment and course. */
    public function registered(array $f = []): Builder
    {
        return DB::table('placement_student_profile as p')
            ->join('tblstudent as s', 's.id', '=', 'p.student_id')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('p.sub_institute_id', $this->institute())->whereNull('p.deleted_at')
            ->where('p.registration_status', 'registered')
            ->when(isset($f['standard_ids']), fn ($q) => $q->whereIn('e.standard_id', $f['standard_ids'] ?: [0]))
            ->when(! empty($f['standard_id']), fn ($q) => $q->where('e.standard_id', $f['standard_id']))
            ->when(! empty($f['passing_year']), fn ($q) => $q->where('p.passing_year', $f['passing_year']));
    }

    /** One row per placed student: their latest accepted/joined offer. */
    public function placed(array $f = []): Builder
    {
        $latest = DB::table('placement_offer')
            ->where('sub_institute_id', $this->institute())->whereNull('deleted_at')
            ->whereIn('status', ['accepted', 'joined'])
            ->when(! empty($f['season_id']), fn ($q) => $q->where('season_id', $f['season_id']))
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('offer_date', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('offer_date', '<=', $f['to']))
            ->groupBy('student_id')->selectRaw('student_id, MAX(id) as offer_id');

        return DB::table('placement_offer as o')
            ->joinSub($latest, 'l', 'l.offer_id', '=', 'o.id')
            ->join('tblstudent as s', 's.id', '=', 'o.student_id')
            ->leftJoin('placement_student_profile as p', fn ($j) => $j->on('p.student_id', '=', 'o.student_id')->on('p.sub_institute_id', '=', 'o.sub_institute_id'))
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', session('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->leftJoin('placement_company as c', 'c.id', '=', 'o.company_id')
            ->leftJoin('placement_master as ind', 'ind.id', '=', 'c.industry_id')
            ->when(isset($f['standard_ids']), fn ($q) => $q->whereIn('e.standard_id', $f['standard_ids'] ?: [0]))
            ->when(! empty($f['standard_id']), fn ($q) => $q->where('e.standard_id', $f['standard_id']))
            ->when(! empty($f['passing_year']), fn ($q) => $q->where('p.passing_year', $f['passing_year']));
    }

    /** All offers except revoked, scoped like placed(). */
    public function offersMade(array $f = []): Builder
    {
        return DB::table('placement_offer as o')
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 'o.student_id')->where('e.syear', session('syear')))
            ->where('o.sub_institute_id', $this->institute())->whereNull('o.deleted_at')
            ->where('o.status', '!=', 'revoked')
            ->when(! empty($f['season_id']), fn ($q) => $q->where('o.season_id', $f['season_id']))
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('o.offer_date', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('o.offer_date', '<=', $f['to']))
            ->when(isset($f['standard_ids']), fn ($q) => $q->whereIn('e.standard_id', $f['standard_ids'] ?: [0]));
    }

    /** highest / average / median / lowest of a list of CTC values (LPA). */
    public function ctcStats(Collection $ctcs): array
    {
        $values = $ctcs->map(fn ($v) => (float) $v)->filter(fn ($v) => $v > 0)->sort()->values();
        if ($values->isEmpty()) {
            return ['highest' => 0, 'average' => 0, 'median' => 0, 'lowest' => 0, 'count' => 0];
        }
        $n = $values->count();
        $median = $n % 2 ? $values[intdiv($n, 2)] : ($values[$n / 2 - 1] + $values[$n / 2]) / 2;

        return [
            'highest' => round($values->last(), 2),
            'average' => round($values->avg(), 2),
            'median'  => round($median, 2),
            'lowest'  => round($values->first(), 2),
            'count'   => $n,
        ];
    }

    /** Package slabs: the institute's salary-band master, else fixed slabs. @return array<int,array{label:string,min:float,max:?float}> */
    public function slabs(): array
    {
        $bands = DB::table('placement_master')->where('sub_institute_id', $this->institute())->where('master_type', 'salary_band')
            ->whereNull('deleted_at')->where('status', 1)->orderBy('min_ctc')->get();

        if ($bands->isNotEmpty()) {
            return $bands->map(fn ($b) => ['label' => $b->name, 'min' => (float) $b->min_ctc, 'max' => $b->max_ctc === null ? null : (float) $b->max_ctc])->all();
        }

        return [
            ['label' => 'Below 3 LPA', 'min' => 0, 'max' => 3], ['label' => '3 - 6 LPA', 'min' => 3, 'max' => 6],
            ['label' => '6 - 10 LPA', 'min' => 6, 'max' => 10], ['label' => '10 - 20 LPA', 'min' => 10, 'max' => 20],
            ['label' => 'Above 20 LPA', 'min' => 20, 'max' => null],
        ];
    }

    /** Counts values per slab: min inclusive, max exclusive. */
    public function distribute(Collection $ctcs): array
    {
        $rows = [];
        foreach ($this->slabs() as $slab) {
            $rows[] = $slab + ['count' => $ctcs->filter(fn ($v) => $v >= $slab['min'] && ($slab['max'] === null || $v < $slab['max']))->count()];
        }

        return $rows;
    }
}
