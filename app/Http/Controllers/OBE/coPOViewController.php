<?php

namespace App\Http\Controllers\OBE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Validator;
use function App\Helpers\is_mobile;

class coPOViewController extends Controller
{
    //
    public function index(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');

        if($type == 'API'){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 200);
            }

            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');
            $user_id = $request->get('user_id');
            
            $validator = Validator::make($request->all(), [
                'sub_institute_id' => 'required|numeric',
                'syear' => 'required|numeric',
                'user_id' => 'required|numeric',
            ]);

            if($validator->fails()){
                $response = ['status' => '2', 'message' => $validator->errors(), 'data' => []];
                return response()->json($response, 200);
            }
        }
        $res['printType'] = ['print_1'=>'Print 1','print_2'=>'Print 2', 'print_3'=>'Print 3'];
        return is_mobile($type, "OBE/printView", $res, "view");
    }

    public function create(Request $request){
        $type = $request->input('type');
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');

        if($type == 'API'){
            try {
                if (!$this->jwtToken()->validate()) {
                    $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];
    
                    return response()->json($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];
    
                return response()->json($response, 200);
            }

            $sub_institute_id = $request->get('sub_institute_id');
            $syear = $request->get('syear');
            $user_id = $request->get('user_id');
            
            $validator = Validator::make($request->all(), [
                'sub_institute_id' => 'required|numeric',
                'syear' => 'required|numeric',
                'user_id' => 'required|numeric',
            ]);

            if($validator->fails()){
                $response = ['status' => '2', 'message' => $validator->errors(), 'data' => []];
                return response()->json($response, 200);
            }
        }
        $res['status'] = 0;
        $res['message'] = 'Something went wrong!';

        $res['addCourseData'] = DB::table('tbladd_course_co as acc')
        ->join('standard as std','std.id','=','acc.semester')
        ->join('sub_std_map as ssm','ssm.subject_id','=','acc.course')
        ->selectRaw('acc.*,ssm.display_name as course_name,std.name as semester_name')
        ->where('acc.sub_institute_id', $sub_institute_id)
        ->where('acc.syear', $syear)
        ->where('acc.semester', $request->standard)
        ->where('acc.course',$request->subject)
        ->whereNull('acc.deleted_at')
        ->first();
        // DB::enableQueryLog();
        $res['coData'] = DB::table('lo_category as co')
        ->selectRaw('co.*,group_concat(co.title separator "||") as co_title,group_concat(co.id separator "||") as all_co_id')
        ->where('co.sub_institute_id', $sub_institute_id)
        ->where('co.syear', $syear)
        ->where('co.standard_id', $request->standard)
        ->where('co.subject_id',$request->subject)
        ->groupBy('co.subject_id')
        ->first();

        $res['poData'] = DB::table('lo_master')
        ->where('sub_institute_id', $sub_institute_id)
        ->where('syear', $syear)
        ->where('show_hide',1)
        ->get()->toArray();

        $res['co_po_mapped'] = DB::table('tblco_po_mapping')
        ->where('sub_institute_id', $sub_institute_id)
        ->where('syear', $syear)
        ->where('standard_id', $request->standard)
        ->where('subject_id',$request->subject)
        ->whereNull('deleted_at')
        ->get()->toArray();

        $studentData = DB::table('tblstudent as s')
        ->join('tblstudent_enrollment as se',function($join) use($sub_institute_id,$syear,$request){
            $join->on('se.student_id','=','s.id')->on('se.sub_institute_id','=','s.sub_institute_id')
            ->where(['se.syear'=>$syear,'se.standard_id'=>$request->standard]);
        })
        ->join('result_exam_master as rem',function($join) use($sub_institute_id,$syear,$request){
            $join->on('rem.standard_id','=','se.standard_id')->on('rem.SubInstituteId','=','se.sub_institute_id');
        })
        ->join('result_create_exam as rce',function($join) use($sub_institute_id,$syear,$request){
            $join->on('rce.exam_id','=','rem.id')->on('rce.standard_id','=','se.standard_id')->on('rce.sub_institute_id','=','se.sub_institute_id');
        })
        ->leftJoin('result_marks as rm',function($join) use($sub_institute_id,$syear,$request){
            $join->on('rm.exam_id','=','rce.id')->on('s.id','=','rm.student_id')->on('rce.sub_institute_id','=','se.sub_institute_id');  
        })
        ->selectRaw('s.id,CONCAT_WS(" ",COALESCE(s.first_name,"-"),COALESCE(s.middle_name,"-"),COALESCE(s.last_name,"-")) as student_name,s.enrollment_no,s.roll_no,rem.Id as master_id ,rem.examTitle,rem.weightage,rce.co_id,rce.id as create_id,rce.title,rce.points,rm.points as obt_marks')
        ->where('s.sub_institute_id', $sub_institute_id)
        ->orderBy('s.enrollment_no')
        ->get()->toArray();

        $studentMarks = [];
        foreach ($studentData as $key => $value) {
            $studentMarks[$value->id]['enrollment_no'] = $value->enrollment_no;
            $studentMarks[$value->id]['name'] = $value->student_name;
            $studentMarks[$value->id][$value->examTitle][$value->co_id]= $value;
        }
        // dd(DB::getQueryLog($res['coData']));
        $res['studentMarks'] = $studentMarks;

        $ret_grade=DB::table('result_std_grd_maping as sgm')
        ->join('grade_master_data as dt', 'dt.grade_id', '=', 'sgm.grade_scale')
        ->select('dt.*')
        // ->where('sgm.standard', $request->standard) // if standard wise
        ->where('sgm.sub_institute_id', $sub_institute_id)
        ->where('dt.syear', $syear)
        ->orderBy('dt.breakoff', 'DESC')
        ->get()->toArray();

        $grade_arr = array();
        foreach ($ret_grade as $id => $arr) {
            $grade_arr[$id]['id'] = $arr->id;
            $grade_arr[$id]['grade_id'] = $arr->grade_id;
            $grade_arr[$id]['title'] = $arr->title;
            $grade_arr[$id]['breakoff'] = $arr->breakoff;
            $grade_arr[$id]['gp'] = $arr->gp;
            $grade_arr[$id]['sort_order'] = $arr->sort_order;
            $grade_arr[$id]['comment'] = $arr->comment;
            $grade_arr[$id]['sub_institute_id'] = $arr->sub_institute_id;
            $grade_arr[$id]['created_at'] = $arr->created_at;
            $grade_arr[$id]['updated_at'] = $arr->updated_at;
        }
        $res['gradeScale'] = $grade_arr;
        // echo "<pre>";print_r($res['gradeScale']);exit;

        if(isset($request->print_type) && $request->print_type == 'print_1')
        {
            return is_mobile($type, "OBE/print1", $res, "view");
        }
        elseif(isset($request->print_type) && $request->print_type == 'print_2')
        {
            return is_mobile($type, "OBE/print2", $res, "view");
        }
        elseif(isset($request->print_type) && $request->print_type == 'print_3')
        {
            return is_mobile($type, "OBE/print3", $res, "view");
        }

        return is_mobile($type, "print_co_po.index", $res);
    }
}
