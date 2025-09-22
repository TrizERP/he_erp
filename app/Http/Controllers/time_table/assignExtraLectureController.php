<?php

namespace App\Http\Controllers\time_table;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class assignExtraLectureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // assignExtra.blade.php
        $type = $request->type;
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');

        $from_date = $request->from_date ?? now();
        $to_date = $request->to_date ?? now();

        $allData = DB::table('assign_extra_lecture')
            ->join('tbluser', 'assign_extra_lecture.teacher_id', '=', 'tbluser.id')
            ->join('hrms_departments', 'assign_extra_lecture.department_id', '=', 'hrms_departments.id')
            ->selectRaw('assign_extra_lecture.*,CONCAT_WS(" ",COALESCE(tbluser.first_name,"-"),COALESCE(tbluser.middle_name,"-"),COALESCE(tbluser.last_name,"-")) as emp_name,hrms_departments.department as department_name')
            ->where(['assign_extra_lecture.sub_institute_id' => $sub_institute_id,'assign_extra_lecture.syear' => $syear])
            ->when($request->has('search') && $request->search=="Search", function ($query) use ($request) {
                $query->when($request->has('department_id'), function ($query) use($request){
                        $query->where('assign_extra_lecture.department_id', $request->department_id);
                    })
                    ->when($request->has('search_emp'), function ($query) use($request){
                        $query->where('assign_extra_lecture.teacher_id', $request->search_emp);
                    });
            })
            ->when($from_date && $request->to_date, function ($query) use($from_date,$to_date){
                $query->whereBetween('assign_extra_lecture.extra_date', [$from_date,$to_date]);
            })
            ->whereNull('assign_extra_lecture.deleted_at')
            ->orderBy('assign_extra_lecture.id', 'desc')
            ->get()
            ->toArray();
        // return $allData; 
        $res['lecture_types'] = ['Lecture', 'Lab', 'Tutorial'];
        $res['extra_nos'] = [1, 2, 3, 4, 5];
        $res['all_departments'] = DB::table('hrms_departments')->where(['sub_institute_id' => $sub_institute_id, 'status' => 1])->orderBy('department')->get()->toArray();
        $res['allData'] = $allData;
        $res['from_date'] = $request->from_date;
        $res['to_date'] = $request->to_date;
        $res['search_dept'] = $request->search_dept;
        $res['search_emp'] = $request->search_emp;
        return is_mobile($type, "time_table/assignExtra", $res, "view");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $type = $request->type;
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $department_id = $request->department_id;
        $employee_id = $request->emp_id;
        $extra_date = $request->extra_date;
        $grade = $request->grade;
        $standard = $request->standard;
        $section = $request->division;
        $lecture_type = $request->lecture_type;
        $extra_no = $request->lecture_no;
        $batch = $request->batch;

        $res['status_code'] = 0;
        $res['message'] = 'Something went wrong';

        $insertData = [
            'sub_institute_id' => $sub_institute_id,
            'syear' => $syear,
            'department_id' => $department_id,
            'teacher_id' => $employee_id,
            'extra_date' => date('Y-m-d',strtotime($extra_date)),
            'grade_id' => $grade,
            'standard_id' => $standard,
            'section_id' => $section,
            'type' => $lecture_type,
            'lecture_no' => $extra_no,
            'batch_id' => $batch,
            'created_at' => now(),
            'created_by' => $user_id,
        ];
        // return $insertData;
        $insert = DB::table('assign_extra_lecture')->insert($insertData);
        if ($insert) {
            $res['status_code'] = 1;
            $res['message'] = 'Extra lecture assigned successfully';
        }
        return is_mobile($type, "assign-extra-lecture.index", $res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $type = $request->type;
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $user_id = session()->get('user_id');
        $res['status_code'] = 0;
        $res['message'] = 'Something went wrong';

        $delete = DB::table('assign_extra_lecture')->where('id',$id)->update(['deleted_at'=>now(),'deleted_by'=>$user_id]);

        if ($delete) {
            $res['status_code'] = 1;
            $res['message'] = 'Extra lecture deleted successfully';
        }
        return is_mobile($type, "assign-extra-lecture.index", $res);
    }
}
