<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\student\tblstudentModel;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function App\Helpers\getCountDays;
use function App\Helpers\is_mobile;
use function App\Helpers\SearchStudent;
use function Symfony\Component\HttpKernel\Profiler\read;

class studentAttendanceController extends Controller
{

    use GetsJwtToken;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $submit = $request->input('submit');
        $marking_period_id = session()->get('term_id');
        if ($type == "API") {
            $sub_institute_id = $request->input('sub_institute_id');
            $syear = $request->input('syear');
            $user_id = $request->input('user_id');

            $result = DB::table('class_teacher as ct')
                ->join('standard as s', function ($join) {
                    $join->whereRaw('ct.standard_id = s.id AND ct.sub_institute_id = s.sub_institute_id');
                    // ->when($marking_period_id,function($query) use ($marking_period_id){
                    //     $query->where('s.marking_period_id',$marking_period_id);
                    // });
                })->join('division as d', function ($join) {
                    $join->whereRaw('d.id = ct.division_id AND d.sub_institute_id = ct.sub_institute_id');
                })
                ->selectRaw('ct.standard_id,ct.division_id,s.name as standard_name,d.name as division_name')
                ->where('ct.sub_institute_id', $sub_institute_id)
                ->where('syear', $syear)
                ->where('ct.teacher_id', $user_id)
                ->get()->toArray();

            $result = array_map(function ($value) {
                return (array)$value;
            }, $result);

            $res['standardDivision'] = $result;
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";

        return is_mobile($type, "student/student_attendance", $res, "view");
    }

    public function showStudent(Request $request)
    {
        $type = $request->input('type');
        $date = $request->input('date');
        $marking_period_id = session()->get('term_id');

        if ($type == "API") {
            $term_id = $request->input('term_id');
            $syear = $request->input('syear');
            $sub_institute_id = $request->input('sub_institute_id');
        } else {
            $term_id = $request->session()->get('term_id');
            $syear = $request->session()->get('syear');
            $sub_institute_id = $request->session()->get('sub_institute_id');
        }
        $standard_division_orignal = $request->input('standard_division');
        $standard_division = explode("||", $standard_division_orignal);
        $standard = $standard_division[0];
        $division = $standard_division[1];
        $grade = '';

        $sundays = getCountDays($date, $date);
       
        $holidays = DB::table("calendar_events")
            ->where('school_date', '=', $date)
            ->whereIn('event_type',['holiday','vacation'])
            ->where('sub_institute_id', '=', session()->get('sub_institute_id'))
            ->where('syear', '=', session()->get('syear'))
            ->whereRaw('FIND_IN_SET(?, standard)', [$standard])
            ->get()
            ->toArray();

        $single_standard = DB::table("standard")
            ->select('name')
            ->where('id', '=', $standard)
            ->where('sub_institute_id', '=', session()->get('sub_institute_id'))
            ->first();
        
        $single_division = DB::table("division")
            ->select('name')
            ->where('id', '=', $division)
            ->where('sub_institute_id', '=', session()->get('sub_institute_id'))
            ->first();
            
        if(!empty($sundays))
        {
            foreach ($sundays['S'] as $key => $value) {
                $sundays[$key] = (int)date('d', strtotime($value));
            }
        }

        unset($sundays['S']);

        // $student_data = SearchStudent($grade, $standard, $division, $sub_institute_id, $syear);

        $extraSearchArray = [];
        $extraSearchArray['tblstudent.sub_institute_id'] = $sub_institute_id;
        $extraSearchArray['tblstudent_enrollment.syear'] = $syear;
        $extraSearchArray['tblstudent.status'] = 1;
        if ($standard != '') {
            $extraSearchArray['tblstudent_enrollment.standard_id'] = $standard;
        }
        if ($division != '') {
            $extraSearchArray['tblstudent_enrollment.section_id'] = $division;
        }

        $extraRaw = " 1 = 1 AND tblstudent_enrollment.end_date IS NULL ";
        // search by batch 
        if($request->has('batch_sel') && $request->input('batch_sel') != null){
            $batchs = DB::table('batch')->where(['sub_institute_id'=>$sub_institute_id,'syear'=>$syear,'standard_id'=>$standard,'division_id'=>$division])->get()->toArray();
            $res['batch_id'] = $request->batch_sel;    
            $res['batchs']=$batchs;    
            
            $extraRaw.=" AND batch.id='".$request->batch_sel."'";
        }
        //START Check for class teacher assigned standards

        $classTeacherStdArr = session()->get('classTeacherStdArr');
        if (isset($classTeacherStdArr)) {
            if (count($classTeacherStdArr) > 0) {
                $extraRaw = "standard.id IN (" . implode(",", $classTeacherStdArr) . ")";
            } else {
                $extraRaw = "standard.id IN (' ')";
            }
        }

        $classTeacherDivArr = session()->get('classTeacherDivArr');
        if (isset($classTeacherStdArr)) {
            if (count($classTeacherDivArr) > 0) {
                $extraRaw .= " and division.id IN (" . implode(",", $classTeacherDivArr) . ")";
            }
        }
        //END Check for class teacher assigned standards


        $student_data = tblstudentModel::select('tblstudent_enrollment.*', 'tblstudent.*', 'standard.name as standard',
            'division.name as division', 'academic_section.title as grade','batch.id as batch_id','batch.title as batch_title')
            ->join("tblstudent_enrollment", function ($join) {
                $join->on("tblstudent_enrollment.student_id", "=", "tblstudent.id")
                    ->on("tblstudent_enrollment.sub_institute_id", "=", "tblstudent.sub_institute_id")
                    ->whereNull('tblstudent_enrollment.end_date');
            })
            ->join("academic_section", function ($join) {
                $join->on("academic_section.id", "=", "tblstudent_enrollment.grade_id")
                    ->on("academic_section.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id");
            })
            ->join("standard", function ($join) use($marking_period_id) {
                $join->on("standard.id", "=", "tblstudent_enrollment.standard_id")
                    ->on("standard.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id")
                    ->when($marking_period_id,function($query) use($marking_period_id){
                        $query->where('standard.marking_period_id',$marking_period_id);
                    });
            })
            ->join("division", function ($join) {
                $join->on("division.id", "=", "tblstudent_enrollment.section_id")
                    ->on("division.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id");
            })
            ->leftJoin('batch','batch.id','=','tblstudent.studentbatch')
            ->where($extraSearchArray)
            ->whereRaw($extraRaw)
            ->orderby('tblstudent_enrollment.roll_no')
            ->get()->toArray();
        if (count($student_data) == 0) {
            $res['status_code'] = 0;
            $res['message'] = "No Student Data Found";
            return is_mobile($type, "student_attendance.index", $res);
        }

        $attendanceArray = [];
        $attendanceArray['syear'] = $syear;
        $attendanceArray['sub_institute_id'] = $sub_institute_id;
        // $attendanceArray['term_id'] = $term_id;
        $attendanceArray['attendance_date'] = $date;
        $attendanceArray['standard_id'] = $standard;
        $attendanceArray['section_id'] = $division;

        $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();
        $attendanceData = [];
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $attendanceData[$value->student_id] = $value->attendance_code;
            }
        }

        if (!empty($holidays) && $holidays[0]->event_type === "holiday") 
        {
            $res['status_code'] = 0;
            $res['message'] = "$date is a holiday, so you can't take attendance for " . $single_standard->name . '/' . $single_division->name;

            return is_mobile($type, "student_attendance.index", $res);
        } 
        else if (!empty($sundays)) 
        {
            $res['status_code'] = 0;
            $res['message'] = "$date is sunday, so you can't take attendance for " . $single_standard->name . '/' . $single_division->name;

            return is_mobile($type, "student_attendance.index", $res);
        } 
        else 
        {
            $res['status_code'] = 1;
            $res['message'] = "Success";
            $res['student_data'] = $student_data;
        }
        
        $res['date'] = $date;
        $res['standard_division'] = $standard_division_orignal;
        $res['attendance_data'] = $attendanceData;

        return is_mobile($type, "student/student_attendance", $res, "view");
    }

    public function saveStudentAttendance(Request $request)
    {
        $date = $request->input('date');
        $type = $request->input('type');
        $students = $request->input('student');

        if ($type != "API") {
            $syear = $request->session()->get('syear');
            $term_id = $request->session()->get('term_id');
            $user_id = $request->session()->get('user_id');
            $user_profile_id = $request->session()->get('user_profile_id');
            $sub_institute_id = $request->session()->get('sub_institute_id');
        } else {
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
                    return response()->json($response, 401);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
                return response()->json($response, 401);
            }

            $syear = $request->input('syear');
            $user_id = $request->input('teacher_id');
            $user_profile_id = $request->input('user_profile_id');
            $sub_institute_id = $request->input('sub_institute_id');
            if ($syear == '' || $date == '' || $students == '' || $user_id == '' || $user_profile_id == '' || $sub_institute_id == '') {
                $res['status_code'] = 0;
                $res['message'] = "Parameter Missing.";

                return is_mobile($type, "student_attendance.index", $res);
            }
        }

        $standard_division_orignal = $request->input('standard_division');
        $standard_division = explode("||", $standard_division_orignal);
        $standard = $standard_division[0];
        $division = $standard_division[1];

        foreach ($students as $student_id => $attendance) {
            $attendanceArray = [];
            $attendanceArray['syear'] = $syear;
            $attendanceArray['sub_institute_id'] = $sub_institute_id;
            $attendanceArray['student_id'] = $student_id;
            //$attendanceArray['term_id'] = $term_id;
            $attendanceArray['attendance_date'] = $date;
            $attendanceArray['standard_id'] = $standard;
            $attendanceArray['section_id'] = $division;

            $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();

            $attendanceArray['attendance_code'] = $attendance;
            $attendanceArray['teacher_id'] = $user_id;
            $attendanceArray['user_group_id'] = $user_profile_id;
            $attendanceArray['created_by'] = $user_id;

            if (count($data) > 0) {
                DB::table("attendance_student")->where(['id' => $data[0]->id])->update($attendanceArray);
            } else {
                DB::table("attendance_student")->insert($attendanceArray);
            }
        }

        $res['status_code'] = 1;
        $res['message'] = "Attendance successfully taken";

        return is_mobile($type, "student_attendance.index", $res);
    }

    /*Old Save Student Attendance function
    public function saveStudentAttendance(Request $request) {
        $date = $request->input('date');
        $type = $request->input('type');
        $students = $request->input('student');

        if ($type != "API") {
            $syear = $request->session()->get('syear');
            $term_id = $request->session()->get('term_id');
            $user_id = $request->session()->get('user_id');
            $user_profile_id = $request->session()->get('user_profile_id');
            $sub_institute_id = $request->session()->get('sub_institute_id');
        } else {
            $syear = $request->input('syear');
            $term_id = $request->input('term_id');
            $user_id = $request->input('user_id');
            $user_profile_id = $request->input('user_profile_id');
            $sub_institute_id = $request->input('sub_institute_id');
        }

        $standard_division_orignal = $request->input('standard_division');
        $standard_division = explode("||", $standard_division_orignal);
        $standard = $standard_division[0];
        $division = $standard_division[1];

        foreach ($students as $student_id => $attendance) {
            $attendanceArray = array();
            $attendanceArray['syear'] = $syear;
            $attendanceArray['sub_institute_id'] = $sub_institute_id;
            $attendanceArray['student_id'] = $student_id;
            $attendanceArray['term_id'] = $term_id;
            $attendanceArray['attendance_date'] = $date;
            $attendanceArray['standard_id'] = $standard;
            $attendanceArray['section_id'] = $division;

            $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();

            $attendanceArray['attendance_code'] = $attendance;
            $attendanceArray['teacher_id'] = $user_id;
            $attendanceArray['user_group_id'] = $user_profile_id;
            $attendanceArray['created_by'] = $user_id;

            if (count($data) > 0) {
                DB::table("attendance_student")->where(['id' => $data[0]->id])->update($attendanceArray);
            } else {
                DB::table("attendance_student")->insert($attendanceArray);
            }

        }

        $res['status_code'] = 1;
        $res['message'] = "Attendance successfully taken";
        return is_mobile($type, "student_attendance.index", $res);
    }*/

    public function daywiseStudentAttendance(Request $request)
    {
        $type = $request->input('type');
        $submit = $request->input('submit');

        $res['status_code'] = 1;
        $res['message'] = "Success";

        return is_mobile($type, "student/daywise_attendance_report", $res, "view");
    }

    public function get_batch(Request $request){
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $standard_id = $request->standard_id;
        $division_id = $request->division_id;
        
        $batch = DB::table('batch')->where(['sub_institute_id'=>$sub_institute_id,'syear'=>$syear,'standard_id'=>$standard_id,'division_id'=>$division_id])->get()->toArray();
        return $batch;
    }

    public function showDaywiseStudentAttendance(Request $request)
    {
        $type = $request->input('type');
        $date = $request->input('date');
        $taken = $request->input('taken');
        $syear = $request->session()->get('syear');
        $term_id = $request->session()->get('term_id');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $marking_period_id=session()->get('term_id');

        $data = DB::table('tblstudent as s')
            ->join('tblstudent_enrollment as se', function ($join) use ($syear) {
                $join->whereRaw("s.id = se.student_id AND se.syear = '" . $syear . "' AND s.sub_institute_id = se.sub_institute_id
                AND se.end_date IS NULL");
            })->join('standard as sm', function ($join) use($marking_period_id) {
                $join->whereRaw('se.standard_id = sm.id')
                ->when($marking_period_id,function($query) use($marking_period_id){
                    $query->where('sm.marking_period_id',$marking_period_id);
                });
            })->join('division as dm', function ($join) {
                $join->whereRaw('se.section_id = dm.id');
            })->leftJoin('attendance_student as a', function ($join) use ($date, $syear) {
                $join->whereRaw("s.id = a.student_id AND sm.id = a.standard_id AND dm.id = a.section_id
                    AND s.sub_institute_id = a.sub_institute_id AND a.attendance_date = '" . $date . "' and a.syear = '" . $syear . "'");
            })->selectRaw("CONCAT_WS('/',sm.name,dm.name) AS standard_name, dm.name AS division_name,se.standard_id,
                se.section_id,se.student_id,a.attendance_code,s.gender, SUM(CASE WHEN s.gender = 'M' THEN 1 ELSE 0 END) AS BOY,
                SUM(CASE WHEN s.gender = 'F' THEN 1 ELSE 0 END) AS GIRL, SUM(CASE WHEN s.gender = 'M' AND a.attendance_code = 'P'
                THEN 1 ELSE 0 END) TBP, SUM(CASE WHEN s.gender = 'F' AND a.attendance_code = 'P' THEN 1 ELSE 0 END) TGP,
                SUM(CASE WHEN s.gender = 'M' AND a.attendance_code = 'A' THEN 1 ELSE 0 END) TBA, SUM(CASE WHEN s.gender = 'F'
                AND a.attendance_code = 'A' THEN 1 ELSE 0 END) TGA")
            ->where('s.sub_institute_id', $sub_institute_id)
            ->groupBy('se.standard_id', 'se.section_id');

        if ($taken == 'no') {
            $data = $data->havingNull('attendance_code');
        } else {
            $data = $data->havingNotNull('attendance_code');
        }

        $data = $data->orderBy('sm.sort_order', 'ASC')->get()->toArray();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['date'] = $date;
        $res['taken'] = $taken;
        $res['attendance_data'] = $data;

        return is_mobile($type, "student/daywise_attendance_report", $res, "view");
    }

    public function monthwiseStudentAttendance(Request $request)
    {
        $type = $request->input('type');

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['types'] = ["Lecture", "Lab", "Tutorial"];

        return is_mobile($type, "student/monthwise_attendance_report", $res, "view");
    }

    public function showMonthwiseStudentAttendance(Request $request)
    {
        $type = $request->input('type');
        $month = $request->input('month');
        $grade_id = $request->input("grade");
        $standard_id = $request->input("standard");
        $division_id = $request->input("division");
        $selected_year = $request->input("year");
        $syear = $request->session()->get('syear');
        $term_id = $request->session()->get('term_id');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $batch="";
        if($request->has('batch_sel')){
            $batchs = DB::table('batch')->where(['sub_institute_id'=>$sub_institute_id,'syear'=>$syear,'standard_id'=>$standard_id,'division_id'=>$division_id])->get()->toArray();
            $res['batch_id'] = $request->batch_sel;    
            $res['batchs']=$batchs;    
            
            $batch=$request->batch_sel;
        }

        // get student list 
        $student_data = SearchStudent($grade_id, $standard_id, $division_id,"","", "","","", "", "","",$batch);

        $from_date = $selected_year . "-" . $month . "-01";
        $to_date = date('Y-m-t', strtotime($selected_year . "-" . $month));

        $sundays = getCountDays($from_date, $to_date);

        $whereAtt['ast.syear'] = $syear;
        $whereAtt['ast.sub_institute_id'] = $sub_institute_id;
       
        $holidays = DB::table("calendar_events")
            ->selectRaw("DATE_FORMAT(school_date,'%d') AS DATE")
            ->where('event_type', '=', 'holiday')
            ->whereRaw("month(school_date) = " . $month)
            ->where(['sub_institute_id'=>$sub_institute_id,'syear'=>$syear])
            ->pluck('DATE')
            ->toArray();
            
        $events = DB::table("calendar_events")
            ->selectRaw("DATE_FORMAT(school_date,'%d') AS DATE, event_type")
            ->where(['sub_institute_id'=>$sub_institute_id,'syear'=>$syear])
            ->where('event_type', '=', 'event')
            ->whereRaw("month(school_date) = " . $month)
            ->get();
        
        $eventsArray = [];
        foreach ($events as $event) {
            $eventsArray[] = $event->DATE; // Add event date to the array without event type
        }
        
        /* echo("<pre>");
        print_r($holidays);
        print_r($eventsArray);
        die; */

        // $whereAtt['term_id'] = $term_id;
        if(isset($standard_id)){
            $whereAtt['ast.standard_id'] = $standard_id;
        }
        if(isset($division_id)){
            $whereAtt['ast.section_id'] = $division_id;    
        }
        // DB::enableQueryLog();
        $attendanceData = DB::table("attendance_student as ast")
        ->join('timetable as tt', function ($join) use($sub_institute_id,$syear){
            $join->on('ast.timetable_id', '=', 'tt.id')->where(['tt.sub_institute_id'=>$sub_institute_id,'tt.syear'=>$syear]);
        })
            ->where($whereAtt)
            ->whereBetween("ast.attendance_date", [$from_date, $to_date])
            ->when($request->has('batch_sel') && $request->batch_sel!='',function($query) use($request){
                $query->where('tt.batch_id',$request->batch_sel);
            })
            ->when($request->has('subject') && $request->subject!='',function($query) use($request){
                $query->whereIn('tt.subject_id',explode('|||',$request->subject));

            })
            ->get()
            ->toArray();
        // dd(DB::getQueryLog($attendanceData));
        if (count($attendanceData) == 0) {
            $res['status_code'] = 0;
            $res['message'] = "No attendance taken in this month";
            return is_mobile($type, "monthwise_student_attendance_report", $res);
        }

        $finalAttendanceArray = array();
        foreach ($attendanceData as $key => $value) {
            $finalAttendanceArray[$value->student_id][(int)date('d', strtotime($value->attendance_date))] = $value->attendance_code;
        }

        foreach ($sundays['S'] as $key => $value) {
            $sundays[$key] = (int)date('d', strtotime($value));
        }

        unset($sundays['S']);
        // echo "<pre>";print_r($student_data);exit;
        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['month'] = $month;
        $res['year'] = $selected_year;
        $res['grade_id'] = $grade_id;
        $res['standard_id'] = $standard_id;
        $res['division_id'] = $division_id;
        $res['student_data'] = $student_data;
        $res['attendance_data'] = $finalAttendanceArray;
        $res['sundays'] = $sundays;
        $res['holidays'] = $holidays;
        $res['events'] = $eventsArray;
        $res['to_date'] = date('d', strtotime($to_date));
        $res['types'] = ["Lecture", "Lab", "Tutorial"];
        $res['lecture_type'] = $request->lecture_type;
        $res['subject'] = $request->subject;
        $res['batch'] = $request->batch_sel;

        return is_mobile($type, "student/monthwise_attendance_report", $res, "view");
    }

    public function studentAttendanceAPI(Request $request)
    {

        // return "hello";exit;
        try {
            if (!$this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
                return response()->json($response, 401);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 401);
        }

        $type = $request->input("type");
        $student_id = $request->input("student_id");
        $sub_institute_id = $request->input("sub_institute_id");
        $syear = $request->input("syear");

        $attendace_data = $event_data = $holiday_data = $vacation_data = [];
        if ($student_id != "" && $sub_institute_id != "" && $syear != "") {
            $attendance_data = DB::table('attendance_student')->select('attendance_date', 'attendance_code')
                ->where('sub_institute_id', $sub_institute_id)
                ->where('student_id', $student_id)
                ->where('syear', $syear)
                ->whereNotNull('attendance_date')
                ->orderBy('attendance_date')
                ->get()->toArray();

            $holiday_data = DB::table('tblstudent_enrollment as s')
                ->leftJoin('calendar_events as c', function ($join) {
                    $join->whereRaw('find_in_set(s.standard_id,c.standard) AND s.syear=c.syear');
                })
                ->selectRaw('c.school_date,c.title,c.description,c.event_type')
                ->where('s.sub_institute_id', $sub_institute_id)
                ->where('s.student_id', $student_id)
                ->where('event_type', '=', 'holiday')
                ->where('c.syear', $syear)->orderBy('school_date')->get()->toArray();

            $event_data = DB::table('tblstudent_enrollment as s')
                ->leftJoin('calendar_events as c', function ($join) {
                    $join->whereRaw('find_in_set(s.standard_id,c.standard) AND s.syear=c.syear');
                })
                ->selectRaw('c.school_date,c.title,c.description,c.event_type')
                ->where('s.sub_institute_id', $sub_institute_id)
                ->where('s.student_id', $student_id)
                ->where('event_type', '=', 'event')
                ->where('c.syear', $syear)->orderBy('school_date')->get()->toArray();

            $vacation_data = DB::table('tblstudent_enrollment as s')
                ->leftJoin('calendar_events as c', function ($join) {
                    $join->whereRaw('find_in_set(s.standard_id,c.standard) AND s.syear=c.syear');
                })
                ->selectRaw('c.school_date,c.title,c.description,c.event_type')
                ->where('s.sub_institute_id', $sub_institute_id)
                ->where('s.student_id', $student_id)
                ->where('event_type', '=', 'vacation')
                ->where('c.syear', $syear)->orderBy('school_date')->get()->toArray();

            $res['status'] = 1;
            $res['message'] = "Success";
            $res['data']['attendance_data'] = $attendance_data;
            $res['data']['calendar_data']['holiday'] = $holiday_data;
            $res['data']['calendar_data']['event'] = $event_data;
            $res['data']['calendar_data']['vacation'] = $vacation_data;
        } else {
            $res['status'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }

    public function studentTeacherListAPI(Request $request)
    {
        try {
            if (!$this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
                return response()->json($response, 401);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
            return response()->json($response, 401);
        }

        $type = $request->input("type");
        $student_id = $request->input("student_id");
        $sub_institute_id = $request->input("sub_institute_id");
        $syear = $request->input("syear");

        if ($student_id != "" && $sub_institute_id != "" && $syear != "") {
            $stud_data = DB::table('tblstudent_enrollment')
                ->where('student_id', $student_id)
                ->where('syear', $syear)
                ->where('sub_institute_id', $sub_institute_id)->get()->toArray();

            if (count($stud_data) > 0) {
                $standard_id = $stud_data[0]->standard_id;
                $section_id = $stud_data[0]->section_id;

//DB::enableQueryLog();
                $data = DB::table('timetable as t')->select(
        DB::raw("CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) AS teacher_name"),
        DB::raw("IF(u.image = '', 'https://" . $_SERVER['SERVER_NAME'] . "/storage/student/noimages.png', CONCAT('https://" . $_SERVER['SERVER_NAME'] . "/storage/user/', u.image)) AS image"),
        'u.mobile',
        DB::raw("GROUP_CONCAT(DISTINCT s.display_name) AS subject_name")
    )->join('tbluser as u', 'u.id', '=', 't.teacher_id')
    ->join('sub_std_map as s', 's.subject_id', '=', 't.subject_id')
    ->where('t.syear', '=', $syear)
    ->where('t.sub_institute_id', '=', $sub_institute_id)
    ->where('t.standard_id', '=', $standard_id)
    ->where('t.division_id', '=', $section_id)
    ->groupBy('t.teacher_id')
    ->orderBy('teacher_name')
    ->get()->toArray();
//dd(DB::getQueryLog($data));die();

                $res['status'] = 1;
                $res['message'] = "Success";
                $res['data'] = $data;
            } else {
                $res['status'] = 0;
                $res['message'] = "Wrong Parameters";
            }
        } else {
            $res['status'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }

    public function studentAbsentListAPI(Request $request)
    {
        try {
            if (!$this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
                return response()->json($response, 401);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
            return response()->json($response, 401);
        }

        $type = $request->input("type");
        $teacher_id = $request->input("teacher_id");
        $sub_institute_id = $request->input("sub_institute_id");
        $standard_division = $request->input("standard_division");
        $start_date = $request->input("start_date");
        $end_date = $request->input("end_date");
        $syear = $request->input("syear");

        if ($teacher_id != "" && $sub_institute_id != "" && $syear != "" && $standard_division != "" && $start_date != "" && $end_date != "") {
            $standard_division_orignal = $request->input('standard_division');
            $standard_division = explode("||", $standard_division_orignal);
            $standard = $standard_division[0];
            $division = $standard_division[1];

            $data = DB::table('attendance_student as a')
                ->join('tblstudent as s', function ($join) {
                    $join->whereRaw('s.id = a.student_id and s.sub_institute_id = a.sub_institute_id');
                })->selectRaw("attendance_code,attendance_date,CONCAT_WS(' ',s.first_name,s.middle_name,s.last_name) as student_name")
                ->where('standard_id', $standard)
                ->where('section_id', $division)
                ->where('attendance_code', '=', 'A')
                ->where('teacher_id', $teacher_id)
                ->where('a.sub_institute_id', $sub_institute_id)
                ->whereBetween('attendance_date', [$start_date, $end_date])->get()->toArray();

            $res['status'] = 1;
            $res['message'] = "Success";
            $res['data'] = $data;
        } else {
            $res['status'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }
}
