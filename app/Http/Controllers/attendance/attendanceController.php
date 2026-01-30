<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use App\Http\Controllers\AJAXController;
use App\Http\Controllers\student\studentAttendanceController;
use function App\Helpers\getCountDays;
use App\Models\student\tblstudentModel;
use App\Models\school_setup\sub_std_mapModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class attendanceController extends Controller
{
    //
    public function index(Request $request)
    {
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $todayDate = Date::now();
        $formattedDate = $todayDate->format('Y-m-d');

        $res['show'] = $this->get_proxy($request);
        // echo "<pre>";print_r($res);exit;

        // return is_mobile($type, 'attendance/show', $res, 'view');
        return is_mobile($type, 'attendance/takeAttendance', $res, 'view');
    }

    public function get_proxy($request)
    {
        $syear = session()->get('syear');
        $sub_institute_id = session()->get('sub_institute_id');
        $user_id = session()->get('user_id');
        $todayDate = Date::now();
        $formattedDate = $todayDate->format('Y-m-d');
        if (session()->get('user_profile_name') == "Lecturer" || session()->get('user_profile_name') == "LMS Teacher") {
            $get_proxy = DB::table('proxy_master')->where(['syear' => $syear, 'sub_institute_id' => $sub_institute_id])->where('teacher_id', $user_id)->whereRaw('proxy_date = "' . $formattedDate . '"')->get()->toArray();

            if (!empty($get_proxy)) {
                return "1";
            }
        } elseif (session()->get('user_profile_name') == "Admin" || session()->get('user_profile_name') == "Super Admin") {
            return  "1";
        }
    }
    public function create(Request $request)
    {
        //echo "<pre>";print_r($request->all());exit;
        $term_id = session()->get('term_id');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        $type = $request->input('type');
        $date = $request->input('from_date');
        $marking_period_id = session()->get('term_id');
        $res['show'] = $this->get_proxy($request);

        if ($type == "API") {
            $term_id = $request->input('term_id');
            $syear = $request->input('syear');
            $sub_institute_id = $request->input('sub_institute_id');
        } else {
            $term_id = $request->session()->get('term_id');
            $syear = $request->session()->get('syear');
            $sub_institute_id = $request->session()->get('sub_institute_id');
        }
        $standard = $request->standard;
        $division = $request->division;

        $explode=explode('|||',$request->get('subject') ?? '');
        $subject_id = $explode[0] ?? '';
        $period_id = $explode[1] ?? '';

        $sundays = getCountDays($date, $date);

        $holidays = DB::table("calendar_events")
            ->where('school_date', '=', $date)
            ->whereIn('event_type', ['holiday', 'vacation'])
            ->where('sub_institute_id', '=', $sub_institute_id)
            ->where('syear', '=', $syear)
            ->whereRaw('FIND_IN_SET(?, standard)', [$standard])
            ->get()
            ->toArray();


        if (!empty($sundays)) {
            $sundays = array_map(function ($value) {
                return (int)date('d', strtotime($value));
            }, $sundays['S']);
        }

        // ... (The rest of your code)
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
        if ($request->has('batch') && $request->get('batch') != '' && $request->get('lecture_name') != 'Tutorial') {
            $extraRaw .= " AND batch.id='" . $request->batch . "'";
        }
        //     $res['batch_id'] = $request->get('batch') ?? '-';    
        //START Check for class teacher assigned standards

        $classTeacherStdArr = session()->get('classTeacherStdArr');
        if (isset($classTeacherStdArr)) {
            if (count($classTeacherStdArr) > 0) {
                $extraRaw .= "AND standard.id IN (" . implode(",", $classTeacherStdArr) . ")";
            } else {
                $extraRaw .= "AND standard.id IN (' ')";
            }
        }

        $classTeacherDivArr = session()->get('classTeacherDivArr');
        if (isset($classTeacherStdArr)) {
            if (count($classTeacherDivArr) > 0) {
                $extraRaw .= " and division.id IN (" . implode(",", $classTeacherDivArr) . ")";
            }
        }
        //END Check for class teacher assigned standards

        $query = tblstudentModel::select(
            'tblstudent_enrollment.*',
            'tblstudent.*',
            'standard.name as standard',
            'division.name as division',
            'academic_section.title as grade',
            'batch.id as batch_id',
            'batch.title as batch_title'
        )
            ->join("tblstudent_enrollment", function ($join) {
                $join->on("tblstudent_enrollment.student_id", "=", "tblstudent.id")
                    ->on("tblstudent_enrollment.sub_institute_id", "=", "tblstudent.sub_institute_id")
                    ->whereNull('tblstudent_enrollment.end_date');
            })
            ->join("academic_section", function ($join) {
                $join->on("academic_section.id", "=", "tblstudent_enrollment.grade_id")
                    ->on("academic_section.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id");
            })
            ->join("standard", function ($join) use ($marking_period_id) {
                $join->on("standard.id", "=", "tblstudent_enrollment.standard_id")
                    ->on("standard.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id");
            })
            ->join("division", function ($join) {
                $join->on("division.id", "=", "tblstudent_enrollment.section_id")
                    ->on("division.sub_institute_id", "=", "tblstudent_enrollment.sub_institute_id");
            });

        if ($request->get('lecture_name') == 'Tutorial' && $request->has('batch') && $request->get('batch') != '') {
            $query->join('student_optional_subject as s', function ($join) use ($subject_id, $request) {
                $join->on('s.student_id', '=', 'tblstudent.id')
                    ->on('s.syear', '=', 'tblstudent_enrollment.syear')
                    ->on('s.sub_institute_id', '=', 'tblstudent_enrollment.sub_institute_id')
                    ->where('s.batch_id', $request->get('batch'))
                    ->where('s.subject_id', $subject_id);
            })
            ->leftJoin('batch', 'batch.id', '=', 's.batch_id');
        } else {
            $query->leftJoin('batch', 'batch.id', '=', 'tblstudent.studentbatch');
        }

        $student_data = $query->where($extraSearchArray)
            ->whereRaw($extraRaw)
            ->orderby('tblstudent.enrollment_no')
            ->get()->toArray();
        // echo "<pre>";print_r($student_data);exit;

        $single_standard = DB::table('standard')->where('id', $standard)->first();
        $single_division = DB::table('division')->where('id', $division)->first();

        if (count($student_data) == 0) {
            $res['status_code'] = 0;
            $res['message'] = "No Student Data Found";
        }
        
        $err = 0;
        if (!empty($holidays) && $holidays[0]->event_type === "holiday") {
            $err = 1;
            $res['status_code'] = 0;
            $res['message'] = "$date is a holiday, so you can't take attendance for " . $single_standard->name . '/' . $single_division->name;
        } else if (!empty($sundays)) {
            $err = 1;
            $res['status_code'] = 0;
            $res['message'] = "$date is sunday, so you can't take attendance for " . $single_standard->name . '/' . $single_division->name;
        } else {
            $res['status_code'] = 1;
            $res['message'] = "Success";
            $res['student_data'] = $student_data;
        }

        // use ajax controller function 
        $ajaxController = new AJAXController;

        // get subjects 
        $sub_req = new Request(['standard_id' => $standard, 'division_id' => $division, 'sub_institute_id' => $sub_institute_id, 'syear' => $syear, 'date' => $request->get('from_date')]);
        $res['all_subject'] = json_decode(json_encode($ajaxController->getSubjectListTimetable($sub_req)), true);
        if ($request->has('batch') && $request->get('batch') != '') {
            // echo "batch";exit;
            $batch_req = new Request([
                'standard_id' => $standard,
                'division_id' => $division,
                'subject_id' => $request->subject,
                'sub_institute_id' => $sub_institute_id,
                'syear' => $syear,
                'date' => $request->get('from_date')
            ]);
            $res['batchs'] = json_decode(json_encode($ajaxController->getBatchTimetable($batch_req)), true);
        }
        $res['batch_id'] = $request->get('batch') ?? '-';
// Hide by Rajesh 29-09-2025
/*        
        // get lectures 
        $lect_req = new Request([
            'standard_id' => $standard,
            'division_id' => $division,
            'subject_id' => $request->get('subject'),
            'date' => $request->get('from_date')
        ]);
        $res['all_lecture'] = $ajaxController->getLectureList($lect_req);
        // echo "<pre>";print_r($request->get('subject'));exit;
*/

        $res['exampleRadios'] = $request->get('exampleRadios');
        $res['attendance_type'] = $request->get('attendance_type');
        $res['from_date'] = $request->get('from_date');
        $res['grade_id'] = $request->get('grade');
        $res['standard_id'] = $request->get('standard');
        $res['division_id'] = $request->get('division');
        $res['subject'] = $request->get('subject');
        $res['lecture'] = $request->get('lecture');
        $res['subject_name'] = $request->get('subject_name') ?? '-';
        $res['lecture_name'] = $request->get('lecture_name') ?? '-';
        $res['timetable_id'] = $request->get('timetable_id');
        $res['period_id'] = $period_id;//$request->get('period_id');
        $res['batch_name'] = $request->get('batch_name');


        $attendanceArray = [
            'syear'             => $syear,
            'sub_institute_id'  => $sub_institute_id,
            'attendance_date'   => $date,
            'standard_id'       => $standard,
            'section_id'        => $division,
            'subject_id'        => $subject_id,//$request->get('subject'),
            'attendance_type'   => $request->get('exampleRadios'),
            'attendance_for'    => $request->get('attendance_type'),
            'timetable_id'      => $request->get('timetable_id'),
        ];

        $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();

        $attendanceData = [];
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $attendanceData[$value->student_id] = $value->attendance_code;
            }
        }

        $res['attendance_data'] = $attendanceData;

        //echo "<pre>";print_r($res);exit;
        if ($err == 1) {
            return is_mobile($type, "students_attendance.index", $res);
        } else {
            // return is_mobile($type, 'attendance/show', $res, 'view');
            return is_mobile($type, 'attendance/takeAttendance', $res, 'view');
        }
    }

    public function store(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;

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
            $date = $request->input('date');
            if ($syear == '' || $date == '' || $students == '' || $user_id == '' || $user_profile_id == '' || $sub_institute_id == '') {
                $res['status_code'] = 0;
                $res['message'] = "Parameter Missing.";

                return is_mobile($type, "student_attendance.index", $res);
            }
        }

        $standard_division_orignal = $request->input('standard_division');
        $standard_division = explode("||", $standard_division_orignal);
        $standard = $standard_division[0] ?? 0;
        $division = $standard_division[1] ?? 0;
        $date = $request->input('date');
        $periods_id = $request->has('periods_id') ? explode('###', $request->input('periods_id') ?? '') : [];
        $subjects_id = $request->input('subjects_id');
        $batchs_id = $request->input('batchs_id');
        $timetables_id = $request->input('timetables_id');
        $att_type = $request->input('att_type');
        $att_for = $request->input('att_for');

        if ($request->att_type == "Extra") {
            $res['status_code'] = 1;
            $res['message'] = "Attendance Taken Successfully.";
            // DB::enableQueryLog();
            $i = 0;
            $getExtraLecture = DB::table('assign_extra_lecture')->where(['syear' => $syear, 'sub_institute_id' => $sub_institute_id, 'standard_id' => $standard, 'section_id' => $division, 'teacher_id' => $user_id, 'extra_date' => $date])->whereNull('deleted_at')->get()->toArray();

            foreach ($getExtraLecture as $key => $val) {
                $todayDay = substr(date('l', strtotime($date)), 0, 1); // Get first letter of day
                if (strtolower($todayDay) == 't') { // Handle Tuesday/Thursday ambiguity
                    $fullDay = strtolower(date('l', strtotime($date)));
                    $todayDay = ($fullDay == 'thursday') ? 'H' : 'T';
                }
                $getTimetable = DB::table('timetable')->where([
                    'syear' => $syear,
                    'sub_institute_id' => $sub_institute_id,
                    'standard_id' => $standard,
                    'division_id' => $division,
                    'subject_id' => $subjects_id,
                    'week_day' => $todayDay
                ])->get()->toArray();

                foreach ($getTimetable as $key1 => $val1) {
                    foreach ($students as $student_id => $attendance) {
                        $i++;
                        DB::table('attendance_student')->insert([
                            'syear' => $syear,
                            'student_id' => $student_id, // Fixed: Changed from $students array to single $student_id
                            'term_id' => $term_id,
                            'attendance_date' => $date,
                            'attendance_code' => $attendance,
                            'teacher_id' => $user_id,
                            'user_group_id' => $user_profile_id,
                            'created_on' => now(),
                            'created_by' => $user_id,
                            'standard_id' => $standard,
                            'section_id' => $division,
                            'sub_institute_id' => $sub_institute_id,
                            'period_id' => $val1->period_id ?? 0,
                            'subject_id' => $val1->subject_id ?? 0,
                            'timetable_id' => $val1->id ?? 0,
                            'attendance_type' => $att_type,
                            'lecture_no' => $val->lecture_no,
                            'attendance_teacher_code' => 0,
                            'attendance_for' => $att_for,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
            // dd(DB::getQueryLog($getExtraLecture));
            if ($i == 0) {
                $res['status_code'] = 0;
                $res['message'] = "No Extra Lecture Found.";
            }

            return is_mobile($type, "students_attendance.index", $res);
        }

        if ($batchs_id != "" && is_numeric($batchs_id)) {
            $todayDay = substr(date('l', strtotime($request->date)), 0, 1); // Get first letter of day
            if (strtolower($todayDay) == 't') { // Handle Tuesday/Thursday ambiguity
                $fullDay = strtolower(date('l', strtotime($request->date)));
                $todayDay = ($fullDay == 'thursday') ? 'H' : 'T';
            }
            $getTimetable = DB::table('timetable')->where(['syear' => $syear, 'sub_institute_id' => $sub_institute_id, 'standard_id' => $standard, 'division_id' => $division, 'batch_id' => $batchs_id, 'subject_id' => $subjects_id, 'week_day' => $todayDay])->whereIn('period_id', $periods_id)->get()->toArray();

            // return $getTimetable;
            if (!empty($getTimetable)) {
                foreach ($getTimetable as $key => $timetable) {
                    foreach ($students as $student_id => $attendance) {
                        $attendanceArray = [];
                        $attendanceArray['syear'] = $syear;
                        $attendanceArray['sub_institute_id'] = $sub_institute_id;
                        $attendanceArray['student_id'] = $student_id;
                        $attendanceArray['attendance_date'] = $date;
                        $attendanceArray['standard_id'] = $standard;
                        $attendanceArray['section_id'] = $division;

                        // new fields added like sasit 
                        $attendanceArray['term_id'] = $term_id;
                        $attendanceArray['period_id'] = $timetable->period_id ?? 0;
                        $attendanceArray['subject_id'] = $timetable->subject_id ?? 0;
                        $attendanceArray['attendance_type'] = $att_type;
                        $attendanceArray['attendance_for'] = $timetable->type ?? $att_for;
                        //$attendanceArray['attendance_teacher_code'] = $attendance;
                        
                        // echo "<pre>";print_r($attendanceArray);
                        $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();
                        // echo "<pre>";print_r($data);
                        if (count($data) > 0) {
                            $attendanceArray['timetable_id'] = $timetable->id ?? 0;
                            $attendanceArray['attendance_code'] = $attendance;
                            $attendanceArray['created_by'] = $user_id;
                            $attendanceArray['updated_at'] = now();
                            DB::table("attendance_student")->where(['id' => $data[0]->id])->update($attendanceArray);
                        } else {
                            $attendanceArray['timetable_id'] = $timetable->id ?? 0;
                            $attendanceArray['attendance_code'] = $attendance;
                            $attendanceArray['teacher_id'] = $user_id;
                            $attendanceArray['user_group_id'] = $user_profile_id;
                            $attendanceArray['created_by'] = $user_id;
                            $attendanceArray['created_at'] = now();
                            DB::table("attendance_student")->insert($attendanceArray);
                        }
                    }
                }
            }
            // exit;
        } else {
            $type = DB::table('timetable')->where('id', $timetables_id)->value('type');
            foreach ($students as $student_id => $attendance) {
                $attendanceArray = [];
                $attendanceArray['syear'] = $syear;
                $attendanceArray['sub_institute_id'] = $sub_institute_id;
                $attendanceArray['student_id'] = $student_id;
                $attendanceArray['attendance_date'] = $date;
                $attendanceArray['standard_id'] = $standard;
                $attendanceArray['section_id'] = $division;

                // new fields added like sasit 
                $attendanceArray['term_id'] = $term_id;
                $attendanceArray['period_id'] = $periods_id[0] ?? 0;
                $attendanceArray['subject_id'] = $subjects_id;
                $attendanceArray['attendance_type'] = $att_type;
                $attendanceArray['attendance_for'] = $type ?? $att_for;
                //$attendanceArray['attendance_teacher_code'] = $attendance;

                $data = DB::table("attendance_student")->where($attendanceArray)->get()->toArray();
                // echo "<pre>";print_r($attendanceArray);exit;
                if (count($data) > 0) {
                    $attendanceArray['timetable_id'] = $timetables_id ?? 0;
                    $attendanceArray['attendance_code'] = $attendance;
                    $attendanceArray['created_by'] = $user_id;
                    $attendanceArray['updated_at'] = now();
                    DB::table("attendance_student")->where(['id' => $data[0]->id])->update($attendanceArray);
                } else {
                    $attendanceArray['timetable_id'] = $timetables_id ?? 0;
                    $attendanceArray['attendance_code'] = $attendance;
                    $attendanceArray['teacher_id'] = $user_id;
                    $attendanceArray['user_group_id'] = $user_profile_id;
                    $attendanceArray['created_by'] = $user_id;
                    $attendanceArray['created_at'] = now();
                    DB::table("attendance_student")->insert($attendanceArray);
                }
            }
        }
        // exit;
        // return $request;

        $res['status_code'] = 1;
        $res['message'] = "Attendance successfully taken";

        return is_mobile($type, "students_attendance.index", $res);
    }
}
