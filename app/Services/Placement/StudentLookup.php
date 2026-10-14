<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementCoordinator;
use Illuminate\Support\Facades\DB;

/**
 * Read-only access to the ERP student master (tblstudent + tblstudent_enrollment). Placement never
 * copies student data; it references tblstudent.id and reads the rest from here.
 */
class StudentLookup
{
    /**
     * @param  int[]|null  $studentIds  null = every enrolled student of the institute
     * @return \Illuminate\Support\Collection keyed by student id
     */
    public function students(?array $studentIds = null, ?array $standardIds = null, $instituteId = null, $syear = null)
    {
        $instituteId = $instituteId ?? session('sub_institute_id');
        $syear = $syear ?? session('syear');

        return DB::table('tblstudent as s')
            ->join('tblstudent_enrollment as e', function ($j) use ($syear) {
                $j->on('e.student_id', '=', 's.id')->where('e.syear', $syear);
            })
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('s.sub_institute_id', $instituteId)
            ->when($studentIds !== null, fn ($q) => $q->whereIn('s.id', $studentIds))
            ->when($standardIds !== null, fn ($q) => $q->whereIn('e.standard_id', $standardIds))
            ->selectRaw("s.id, s.enrollment_no, s.gender, s.email, s.mobile, e.standard_id, st.name as standard_name,
                CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS name")
            ->get()
            ->keyBy('id');
    }

    public function find($studentId)
    {
        return $this->students([(int) $studentId])->first();
    }

    /**
     * Courses the logged-in user may manage, or null when unrestricted (TPO / admin / principal).
     * A coordinator with no assignment sees nothing.
     */
    public function scopeStandardIds(): ?array
    {
        if (PlacementAccess::role() !== PlacementAccess::COORDINATOR) {
            return null;
        }

        return PlacementCoordinator::where('user_id', session('user_id'))->where('status', 1)->get()
            ->flatMap(fn ($c) => $c->standard_ids ?? [])->map(fn ($v) => (int) $v)->unique()->values()->all();
    }
}
