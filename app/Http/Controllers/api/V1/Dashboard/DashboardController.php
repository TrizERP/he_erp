<?php

namespace App\Http\Controllers\api\V1\Dashboard;

use App\Http\Controllers\api\V1\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    /**
     * GET /dashboard. Headline counters plus academic-year progress for the current institute / year.
     * The Blade dashboard's per-profile widgets (fees/attendance charts, tasks, birthdays...) are listed as pending
     * in the module report.
     */
    public function summary(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        $students = DB::table('tblstudent_enrollment')->where('sub_institute_id', $c['institute_id'])
            ->where('syear', $c['syear'])->whereNull('end_date')->count();
        $staff = DB::table('tbluser')->where('sub_institute_id', $c['institute_id'])->where('status', '1')->count();
        $classes = DB::table('standard')->where('sub_institute_id', $c['institute_id'])
            ->when($c['term_id'], fn ($q) => $q->where('marking_period_id', $c['term_id']))->count();

        return $this->ok([
            'counters' => [
                ['key' => 'students', 'label' => 'Students', 'value' => $students, 'hint' => 'Enrolled this year'],
                ['key' => 'staff', 'label' => 'Staff', 'value' => $staff, 'hint' => 'Active users'],
                ['key' => 'classes', 'label' => 'Classes', 'value' => $classes, 'hint' => 'This term'],
            ],
            'academic_year' => $this->yearProgress($c['institute_id'], $c['syear']),
        ]);
    }

    /** Start / end of the selected academic year and how much of it has elapsed (0-100). */
    private function yearProgress($institute, $syear): ?array
    {
        $row = DB::table('academic_year')->where('sub_institute_id', $institute)->where('syear', $syear)
            ->selectRaw('min(start_date) as start_date, max(end_date) as end_date')->first();
        if (! $row || ! $row->start_date || ! $row->end_date) {
            return null;
        }
        $start = strtotime($row->start_date);
        $end = strtotime($row->end_date);
        $span = max(1, $end - $start);
        $elapsed = min($span, max(0, time() - $start));

        return [
            'syear' => $syear,
            'start_date' => $row->start_date,
            'end_date' => $row->end_date,
            'progress' => (int) round($elapsed / $span * 100),
            'days_left' => max(0, (int) ceil(($end - time()) / 86400)),
        ];
    }
}
