<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class monthwiseAttendanceReportController extends Controller
{
    //
    public function index(Request $request){
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $res['from_date'] = now();
        $res['to_date'] = now();
        $res['types'] = ["Lecture","Lab","Tutorial"];
        $res['reportType'] = ["Percentage wise","Number of Lecture wise"];
        return is_mobile($type, 'attendance/monthwiseAttendanceReport', $res, 'view');
    }
}
