<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use App\Http\Controllers\AJAXController;
use App\Models\student\tblstudentModel;
use DB;
use Illuminate\Support\Facades\Date;

class attendanceReportController extends Controller
{
    //
     public function index(Request $request){
        $type= $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id=session()->get('user_id');
        $res['status_code'] = 1;
        return is_mobile($type, 'attendance/semwiseReport', $res, 'view');
    }

    public function create(Request $request){
        $type= $request->get('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $grade= $request->get('grade');
        $standard= $request->get('standard');
        $division= $request->get('division');
        $att_type= $request->get('att_type');
        $batch= $request->get('batch');
        $report_type= $request->get('report_type');
        $below_per= $request->get('below_percent');
        $from_date= $request->get('from_date');
        $to_date= $request->get('to_date');
        // echo "<pre>";print_r($request->all());exit;
        // get batch
        $ajaxController = new AJAXController;
    if ($batch != '' && $att_type!='' && $att_type!="Lecture") {
        $res['batch']=$ajaxController->get_batch($request);
}
        // get subjects from timetable
        // db::enableQueryLog();
        $get_sub = DB::table('attendance_student as ats')
        ->selectRaw("ats.subject_id,ssm.display_name,sub.short_name,
            COUNT(DISTINCT CASE WHEN ats.attendance_for = 'Lab' THEN CONCAT(ats.subject_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) WHEN ats.attendance_for = 'Tutorial' THEN CONCAT(ats.subject_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) ELSE CONCAT(ats.subject_id,ats.period_id,ats.attendance_date,ats.attendance_type,IFNULL(ats.period_id,0)) END) as TOTAL_LEC")
        ->Join('sub_std_map as ssm','ats.subject_id','=','ssm.subject_id')
        ->Join('subject as sub','ssm.subject_id','=','sub.id')
        ->Join('tblstudent as s','s.id','=','ats.student_id')
        ->join('tblstudent_enrollment as se',function($join) use ($standard,$division){
            $join->on('s.id','=','se.student_id')->where('se.standard_id',$standard)->where('se.section_id',$division);
        })
       ->where('attendance_for',$att_type)->whereBetween('ats.attendance_date',[$from_date,$to_date])->where(['se.sub_institute_id'=>$sub_institute_id,'se.syear'=>$syear])->where('ssm.standard_id',$standard);
        if ($batch != '' && $att_type!='' && $att_type!="Lecture") {
            $get_sub->where('s.studentbatch',$batch);
        }

        $get_sub = $get_sub->groupBy(['ats.subject_id'])->get()->toArray();
        // dd(db::getQueryLog($get_sub));
       // echo "<pre>";print_r($get_sub);exit;

        // get students
        // db::enableQueryLog();
        $get_students = DB::table('tblstudent as s')
        ->selectRaw("s.id as student_id,s.enrollment_no,s.mobile,CONCAT_WS(' ',s.last_name,s.first_name,CONCAT(SUBSTRING(s.middle_name,1,1),'.')) as student_name,ats.attendance_date,ats.subject_id,ats.attendance_code,ats.attendance_for,if(ats.attendance_code='P',1,0) as present,
         CASE
            WHEN ats.attendance_for = 'Lab' THEN CONCAT(ats.subject_id, ats.attendance_date, ats.attendance_type, IFNULL(ats.id, 0))
            WHEN ats.attendance_for = 'Tutorial' THEN CONCAT(ats.subject_id, ats.attendance_date, ats.attendance_type, IFNULL(ats.id, 0))
            ELSE CONCAT(ats.subject_id, ats.period_id, ats.attendance_date, ats.attendance_type,ats.timetable_id,ats.student_id, IFNULL(ats.id, 0))
        END as group_column,
        group_concat(ats.id) as att_id")
        ->join('tblstudent_enrollment as se','se.student_id','=','s.id')
        ->join('academic_section as grd','grd.id','=','se.grade_id')
        ->join('standard as std','std.id','=','se.standard_id')
        ->join('division as d','d.id','=','se.section_id')
        ->leftJoin('attendance_student as ats',function($join) use ($request,$from_date,$to_date,$att_type){
                $join->on('ats.student_id','=','s.id')->where('ats.attendance_code','=','P')->where('attendance_for',$att_type)->whereBetween('ats.attendance_date',[$from_date,$to_date]);
            })
        ->leftJoin('period as p','p.id','=','ats.period_id')
        ->leftJoin('batch as b',function($join) use ($request){
                $join->on('s.studentbatch','=','b.id');
            });
        if(isset($batch) && $batch!='' && $att_type!='' && $att_type!="Lecture"){
            $get_students->where('b.id',$batch);
        }

        $get_students->where('s.sub_institute_id',$sub_institute_id)
        ->where('se.syear',$syear)->whereNull('se.end_date')->where('se.standard_id',$standard)
        ->where('se.section_id',$division);
       
       $students_details = $get_students->groupBy(['s.id', 'group_column'])->get()->toArray();
       // dd(db::getQueryLog($students_details));
        $students_details = json_decode(json_encode($students_details), true);

       $stuArr=[];
        $headerCnt = count($get_sub);
       foreach ($students_details as $stuRow) {
            $stuId = $stuRow['student_id'];
            $subjectId = $stuRow['subject_id'];

            if (!isset($stuArr[$stuId]['total'])) {
                $stuArr[$stuId]['total'] = 0;
            }
            if (!isset($stuArr[$stuId][$subjectId])) {
                $stuArr[$stuId][$subjectId] = 0;
            }

            $stuArr[$stuId]['student_id'] = $stuRow['student_id'];
            $stuArr[$stuId]['att_id'] = $stuRow['att_id'];
            $stuArr[$stuId]['enrollment_no'] = $stuRow['enrollment_no'];
            $stuArr[$stuId]['student_name'] = $stuRow['student_name'];
            $stuArr[$stuId]['mobile'] = $stuRow['mobile'];
            $stuArr[$stuId][$subjectId] += $stuRow['present'];
            $blankCnt = $grandPercentage = $totalAttendance= $totalHeaderSum=  $courseatt = $coursetotday = 0;
        foreach ($get_sub as $headRow) {

            if (isset($stuArr[$stuId]) && isset($stuArr[$stuId][$headRow->subject_id])) {
                $perArr[$headRow->subject_id] = $headRow->TOTAL_LEC;

                if (!isset($stuArr[$stuId]["COURSE_" . $headRow->subject_id])) {
                    $stuArr[$stuId]["COURSE_" . $headRow->subject_id] = 0;
                }

                if ($report_type == "pw") {
                    $stuArr[$stuId]["COURSE_" . $headRow->subject_id] = number_format((($stuArr[$stuId][$headRow->subject_id] / $perArr[$headRow->subject_id]) * 100), 2);
                    $grandPercentage += (($stuArr[$stuId][$headRow->subject_id] / $perArr[$headRow->subject_id]) * 100);
                    $courseatt += $stuArr[$stuId][$headRow->subject_id];
                    $coursetotday += $perArr[$headRow->subject_id];
                } else {
                    $stuArr[$stuId]["COURSE_" . $headRow->subject_id] = $stuArr[$stuId][$headRow->subject_id];
                    $totalAttendance += $stuArr[$stuId][$headRow->subject_id];
                    $totalHeaderSum += $headRow->TOTAL_LEC;

                }
            }
             else {
                $blankCnt++;
                if ($report_type == "pw") {
                    $stuArr[$stuId]["COURSE_" . $headRow->subject_id] = '-';
                    $grandPercentage += 0;
                } else {
                    $stuArr[$stuId]["COURSE_" . $headRow->subject_id] = '-';
                    $totalAttendance += 0;
                }
            }

            // get total percent
        if ($report_type == "pw") {
            $totalPercentage = (($courseatt / $coursetotday) * 100);
            $stuArr[$stuId]['TOTAL'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '-';
            $stuArr[$stuId]['TOTAL_PERCENTAGE'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '-';
        } else {
            $denominator = ($totalHeaderSum - $blankCnt);
            $totalPercentage = ($denominator != 0) ? (($totalAttendance / $denominator) * 100) : 0;

            $stuArr[$stuId]['TOTAL'] = ($blankCnt < $headerCnt) ? $totalAttendance : '-';
            $stuArr[$stuId]['TOTAL_PERCENTAGE'] = ($blankCnt < $headerCnt) ? number_format($totalPercentage, 2) : '-';

        }

        }
            $stuArr[$stuId]['total'] += $stuRow['present'];
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

        $res['grade_id']=$grade;
        $res['standard_id']=$standard;
        $res['division_id']=$division;
        $res['batch_id']=$batch;
        $res['attendance_type']=$att_type;
        $res['report_type']=$report_type;
        $res['below_percent']=$below_per;
        $res['to_date']=$to_date;
        $res['from_date']=$from_date;
        $res['header']=$get_sub;
        $res['details']=$stuArr;

        return is_mobile($type, 'attendance/semwiseReport', $res, 'view');
    }
}
