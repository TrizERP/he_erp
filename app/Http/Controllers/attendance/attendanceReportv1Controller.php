<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use App\Http\Controllers\AJAXController;
use App\Models\student\tblstudentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use function App\Helpers\SearchStudent;

class attendanceReportv1Controller extends Controller
{
    //
    public function index(Request $request)
    {
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $res['status_code'] = 1;
        return is_mobile($type, 'attendanceV1/semwiseReport', $res, 'view');
    }

    public function create(Request $request)
    {
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $grade = $request->get('grade');
        $standard = $request->get('standard');
        $division = $request->get('division');
        $report_type = $request->get('report_type');
        $below_per = $request->get('below_percent');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        // echo "<pre>";print_r($request->all());exit;
        // no batch
        // get subjects from timetable
        // db::enableQueryLog();
        $get_sub = DB::table('attendance_student as ats')
            ->selectRaw("ats.subject_id,ssm.display_name,sub.short_name,ats.attendance_for,
            COUNT(DISTINCT CASE WHEN ats.attendance_for = 'Lab' THEN CONCAT(ats.subject_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) WHEN ats.attendance_for = 'Tutorial' THEN CONCAT(ats.subject_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) ELSE CONCAT(ats.subject_id,ats.period_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) END) as TOTAL_LEC")
            ->Join('sub_std_map as ssm', 'ats.subject_id', '=', 'ssm.subject_id')
            ->Join('subject as sub', 'ssm.subject_id', '=', 'sub.id')
            ->Join('tblstudent as s', 's.id', '=', 'ats.student_id')
            ->join('tblstudent_enrollment as se', function ($join) use ($standard, $division) {
                $join->on('s.id', '=', 'se.student_id')->where('se.standard_id', $standard)->where('se.section_id', $division);
            })
            ->whereBetween('ats.attendance_date', [$from_date, $to_date])->where(['se.sub_institute_id' => $sub_institute_id, 'se.syear' => $syear])->where('ssm.standard_id', $standard);

        $get_sub = $get_sub->groupBy(['ats.subject_id', 'ats.attendance_for'])->get()->toArray();
        $subjects = [];
        foreach ($get_sub as $row) {
            $subjects[$row->subject_id]['name'] = $row->short_name;
            $subjects[$row->subject_id]['display'] = $row->display_name;
            $subjects[$row->subject_id][$row->attendance_for] = $row->TOTAL_LEC;
        }
        // Calculate total L, P, T across all subjects
        $totalL = $totalP = $totalT = 0;
        foreach ($subjects as $subj) {
            $totalL += $subj['Lecture'] ?? 0;
            $totalP += $subj['Lab'] ?? 0;
            $totalT += $subj['Tutorial'] ?? 0;
        }
        // dd(db::getQueryLog($get_sub));
        // echo "<pre>";print_r($get_sub);exit;

        // get students
        // db::enableQueryLog();
        //ats.timetable_id, ELSE CONCAT
        $get_students = DB::table('tblstudent as s')
            ->selectRaw("s.id as student_id,s.enrollment_no,s.mobile,CONCAT_WS(' ',s.last_name,s.first_name,CONCAT(SUBSTRING(s.middle_name,1,1),'.')) as student_name,ats.attendance_date,ats.subject_id,ats.attendance_code,ats.attendance_for,if(ats.attendance_code='P',1,0) as present,
         CASE
            WHEN ats.attendance_for = 'Lab' THEN CONCAT(ats.subject_id, ats.attendance_date, ats.attendance_type, IFNULL(ats.id, 0))
            WHEN ats.attendance_for = 'Tutorial' THEN CONCAT(ats.subject_id, ats.attendance_date, ats.attendance_type, IFNULL(ats.id, 0))
            ELSE CONCAT(ats.subject_id, ats.period_id, ats.attendance_date, ats.attendance_type,ats.student_id, IFNULL(ats.id, 0))
        END as group_column,
        group_concat(ats.id) as att_id")
            ->join('tblstudent_enrollment as se', 'se.student_id', '=', 's.id')
            ->join('academic_section as grd', 'grd.id', '=', 'se.grade_id')
            ->join('standard as std', 'std.id', '=', 'se.standard_id')
            ->join('division as d', 'd.id', '=', 'se.section_id')
            ->leftJoin('attendance_student as ats', function ($join) use ($request, $from_date, $to_date) {
                $join->on('ats.student_id', '=', 's.id')->where('ats.attendance_code', '=', 'P')->whereBetween('ats.attendance_date', [$from_date, $to_date]);
            })
            ->leftJoin('period as p', 'p.id', '=', 'ats.period_id')
            ->leftJoin('batch as b', function ($join) use ($request) {
                $join->on('s.studentbatch', '=', 'b.id');
            });

        $get_students->where('s.sub_institute_id', $sub_institute_id)
            ->where('se.syear', $syear)->whereNull('se.end_date')->where('se.standard_id', $standard)
            ->where('se.section_id', $division);

        $students_details = $get_students->groupBy(['s.id', 'group_column'])
        ->orderBy('s.enrollment_no')->get()->toArray();
        // dd(db::getQueryLog($students_details));
        $students_details = json_decode(json_encode($students_details), true);

        $stuArr = [];
        $headerCnt = count($subjects);
        foreach ($students_details as $stuRow) {
            $stuId = $stuRow['student_id'];
            $subjectId = $stuRow['subject_id'];
            $type = $stuRow['attendance_for'];
            $map = ['Lecture' => 'L', 'Lab' => 'P', 'Tutorial' => 'T'];

            if (!isset($stuArr[$stuId]['total'])) {
                $stuArr[$stuId]['total'] = ['L' => 0, 'P' => 0, 'T' => 0];
            }
            if (!isset($stuArr[$stuId][$subjectId])) {
                $stuArr[$stuId][$subjectId] = ['L' => 0, 'P' => 0, 'T' => 0];
            }

            $stuArr[$stuId]['student_id'] = $stuRow['student_id'];
            $stuArr[$stuId]['att_id'] = $stuRow['att_id'];
            $stuArr[$stuId]['enrollment_no'] = $stuRow['enrollment_no'];
            $stuArr[$stuId]['student_name'] = $stuRow['student_name'];
            $stuArr[$stuId]['mobile'] = $stuRow['mobile'];
            $stuArr[$stuId][$subjectId][$map[$type]] += $stuRow['present'];
            $stuArr[$stuId]['total'][$map[$type]] += $stuRow['present'];
            $blankCnt = $grandPercentage = $totalAttendance = $totalHeaderSum =  $courseatt = $coursetotday = 0;
            foreach ($subjects as $subjId => $subj) {
                $perArr[$subjId] = ($subj['Lecture'] ?? 0) + ($subj['Lab'] ?? 0) + ($subj['Tutorial'] ?? 0);

                if (isset($stuArr[$stuId]) && isset($stuArr[$stuId][$subjId])) {
                    $total_present = array_sum($stuArr[$stuId][$subjId]);

                    if ($report_type == "pw") {
                        $stuArr[$stuId]["COURSE_" . $subjId] = [
                            'L' => ($subj['Lecture'] ?? 0) > 0 ? number_format(($stuArr[$stuId][$subjId]['L'] / ($subj['Lecture'] ?? 0)) * 100, 2) : '-',
                            'P' => ($subj['Lab'] ?? 0) > 0 ? number_format(($stuArr[$stuId][$subjId]['P'] / ($subj['Lab'] ?? 0)) * 100, 2) : '-',
                            'T' => ($subj['Tutorial'] ?? 0) > 0 ? number_format(($stuArr[$stuId][$subjId]['T'] / ($subj['Tutorial'] ?? 0)) * 100, 2) : '-',
                        ];
                        $courseatt += $total_present;
                        $coursetotday += $perArr[$subjId];
                    } else {
                        $stuArr[$stuId]["COURSE_" . $subjId] = $total_present;
                        $totalAttendance += $total_present;
                        $totalHeaderSum += $perArr[$subjId];
                    }
                } else {
                    $blankCnt++;
                    if ($report_type == "pw") {
                        $stuArr[$stuId]["COURSE_" . $subjId] = ['L' => '-', 'P' => '-', 'T' => '-'];
                    } else {
                        $stuArr[$stuId]["COURSE_" . $subjId] = '0';
                        $totalAttendance += 0;
                    }
                }
            }

            // calculate total percentage
            if ($report_type == "pw") {
                $totalPercentage = ($coursetotday > 0) ? (($courseatt / $coursetotday) * 100) : 0;
                $stuArr[$stuId]['TOTAL'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '-';
                $stuArr[$stuId]['TOTAL_PERCENTAGE'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '-';
            } else {
                $denominator = ($totalHeaderSum - $blankCnt);
                $totalPercentage = ($denominator != 0) ? (($totalAttendance / $denominator) * 100) : 0;
                $stuArr[$stuId]['TOTAL'] = ($blankCnt < $headerCnt) ? $totalAttendance : '0';
                $stuArr[$stuId]['TOTAL_PERCENTAGE'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '0';
            }
        }

        if ($report_type == "pw") {

            foreach ($stuArr as $stuId => $stuData) {

                $stuArr[$stuId]['total'] = [
                    'L' => $totalL > 0 ? number_format(($stuArr[$stuId]['total']['L'] / $totalL) * 100, 2) : '-',
                    'P' => $totalP > 0 ? number_format(($stuArr[$stuId]['total']['P'] / $totalP) * 100, 2) : '-',
                    'T' => $totalT > 0 ? number_format(($stuArr[$stuId]['total']['T'] / $totalT) * 100, 2) : '-',
                ];

            }

        }

        // echo "<pre>";print_r($stuArr);exit;

        if ($below_per != '') {
            foreach ($stuArr as $key => $value) {
                if ($value['TOTAL_PERCENTAGE'] >= $below_per) {
                    unset($stuArr[$key]);
                }
            }

            $stuArr_NEW = array_values($stuArr);
            unset($stuArr);
            if (!isset($stuArr)) {
                $stuArr = [];
            }
            foreach ($stuArr_NEW as $key => $val) {
                $stuArr[$key] = $val;
            }
        }


        // echo "<pre>";print_r($students_details);exit;
        // echo "<pre>";print_r($stuArr);exit;

        $res['grade_id'] = $grade;
        $res['standard_id'] = $standard;
        $res['division_id'] = $division;
        $res['report_type'] = $report_type;
        $res['below_percent'] = $below_per;
        $res['to_date'] = $to_date;
        $res['from_date'] = $from_date;
        $res['subjects'] = $subjects;
        $res['details'] = $stuArr;

        return is_mobile($type, 'attendanceV1/semwiseReport', $res, 'view');
    }

    public function store(Request $request)
    {
        // return $request;
        $type = $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $grade = $request->get('grade_id');
        $res['status_code'] = 1;
        $studentData = [];
        foreach ($request->get('student') as $stud_id => $check) {
            $studentData[$stud_id] = SearchStudent("", "", "", "", "", "", "", "", "", "", $stud_id, "", "");
        }
        $res['studentData'] = $studentData;
        // return $res['studentData']; 
        return is_mobile($type, 'attendanceV1/sendSms', $res, 'view');
    }
}
