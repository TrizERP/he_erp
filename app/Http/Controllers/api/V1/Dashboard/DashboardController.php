<?php

namespace App\Http\Controllers\api\V1\Dashboard;

use App\Http\Controllers\api\V1\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    /**
     * GET /dashboard. Module 0 ships the headline counters only; the Blade dashboard's per-profile widgets
     * (fees/attendance charts, tasks, birthdays...) are listed as pending in the module report.
     */
    public function summary(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        $students = DB::table('tblstudent_enrollment')->where('sub_institute_id', $c['institute_id'])
            ->where('syear', $c['syear'])->whereNull('end_date')->count();
        $staff = DB::table('tbluser')->where('sub_institute_id', $c['institute_id'])->where('status', '1')->count();

        return $this->ok([
            'counters' => [
                ['key' => 'students', 'label' => 'Students', 'value' => $students],
                ['key' => 'staff', 'label' => 'Staff', 'value' => $staff],
            ],
        ]);
    }
}
