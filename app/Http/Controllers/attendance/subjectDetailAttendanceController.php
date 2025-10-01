<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class subjectDetailAttendanceController extends Controller
{
    public function index(Request $request){
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $att_type = $request->get('lecture_type');
        $standard = $request->get('standard');
        $division = $request->get('division');
        $batch = $request->get('batch');
        $from_date = $request->get('from_month');
        $to_date = $request->get('to_month');
        // Convert the format from d-m-Y to Y-m-d for internal use
        $res['from_date'] = ($request->has('from_month') && !empty($request->from_month)) 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->from_month)->format('Y-m-d') 
            : now()->format('Y-m-d');

        $res['to_date'] = ($request->has('to_month') && !empty($request->to_month)) 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->to_month)->format('Y-m-d') 
            : now()->format('Y-m-d');
        $weekTotals = $students_data = [];

        if($request->has('submit')){
            // Fetch raw attendance rows
            $rawRows = DB::table('tblstudent as s')
                ->selectRaw("
                    s.id as student_id,
                    s.enrollment_no,
                    s.mobile,
                    ats.standard_id,
                    se.section_id,
                    ats.subject_id,
                    CONCAT_WS(' ',s.last_name,s.first_name,CONCAT(SUBSTRING(s.middle_name,1,1),'.')) as student_name,
                    YEARWEEK(ats.attendance_date, 1) as yearweek,
                    IF(ats.attendance_code='P',1,0) as present
                ")
                ->join('tblstudent_enrollment as se','se.student_id','=','s.id')
                ->join('attendance_student as ats',function($join) use ($att_type,$from_date,$to_date){
                        $join->on('ats.student_id','=','s.id')
                             ->where('ats.attendance_code','=','P')
                             ->where('attendance_for',$att_type)
                             ->whereBetween('ats.attendance_date',[
                                 date('Y-m-d',strtotime($from_date)),
                                 date('Y-m-d',strtotime($to_date))
                             ]);
                    })
                ->leftJoin('batch as b',function($join){
                        $join->on('s.studentbatch','=','b.id');
                    })
                ->when(
                    isset($batch) && $batch!='' && $att_type!='' && $att_type!="Lecture",
                    fn($q) => $q->where('b.id',$batch)
                )
                ->where('s.sub_institute_id',$sub_institute_id)
                ->where('se.syear',$syear)
                ->whereNull('se.end_date')
                ->where('se.standard_id',$standard)
                ->where('se.section_id',$division)
                ->get()
                ->groupBy(['student_id','yearweek']);

            // Build week-wise summary per student
            $students_details = [];
            $start = new \DateTime($from_date);
            $end   = new \DateTime($to_date);
            $weekPeriod = new \DatePeriod(
                $start,
                new \DateInterval('P1W'),
                $end->modify('+1 week')
            );
            $weeksRange = [];
            foreach ($weekPeriod as $w) {
                $weekStart = $w->format('Y-m-d');
                $yearweek = $w->format('oW'); // ISO-8601 week number
                $weeksRange[$yearweek] = [
                    'label' => $w->format('d/m'),
                    'week_start' => $weekStart,
                    'year' => $w->format('o')
                ];
            }

            foreach($rawRows as $student_id => $weeks){
                $first = $weeks->flatten()->first();
                $base = [
                    'student_id'      => $student_id,
                    'enrollment_no'   => $first->enrollment_no,
                    'mobile'          => $first->mobile,
                    'student_name'    => $first->student_name,
                    'standard_id'    => $first->standard_id,
                    'section_id'    => $first->section_id,
                    'subject_id'    => $first->subject_id,
                ];
                foreach($weeksRange as $yearweek => $weekInfo){
                    $base[$yearweek] = isset($weeks[$yearweek]) ? $weeks[$yearweek]->sum('present') : 0;
                }
                $students_details[] = $base;
            }

            // Week-wise total working days
            foreach($weeksRange as $yearweek => $weekInfo){
                // Safely split only if the string contains 'W'
                if (strpos($yearweek, 'W') !== false) {
                    list($year,$week) = explode('W',$yearweek);
                } else {
                    $year = substr($yearweek, 0, 4);
                    $week = substr($yearweek, 4);
                }
                $weekTotals[$yearweek] = [
                    'total_working_days' => DB::table('attendance_student')
                        ->where('attendance_for',$att_type)
                        ->whereRaw("YEARWEEK(attendance_date, 1) = ?",[$yearweek])
                        ->whereBetween('attendance_date',[
                            date('Y-m-d',strtotime($from_date)),
                            date('Y-m-d',strtotime($to_date))
                        ])
                        ->distinct()
                        ->count('attendance_date'),
                    'week_start' => date('d', strtotime($weekInfo['week_start'])),
                    'year' => $weekInfo['year'],
                    'month' => date('m', strtotime($weekInfo['week_start']))
                ];
            }
            $students_data = $students_details;
        }
        $res['types'] = ["Lecture","Lab","Tutorial"];
        $res['reportType'] = ["Percentage wise","Number of Lecture wise"];
        $res['week_totals'] = $weekTotals;
        $res['student_data'] = $students_data;
        $res['grade_id'] = $request->grade;
        $res['standard_id'] = $request->standard;
        $res['division_id'] = $request->division;
        $res['subject'] = $request->subject;
        $res['lecture_type'] = $request->lecture_type;

        return is_mobile($type, 'attendance/subjectDetailReport', $res, 'view');
    }
}
