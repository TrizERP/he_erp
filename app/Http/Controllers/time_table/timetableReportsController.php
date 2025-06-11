<?php

namespace App\Http\Controllers\time_table;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\school_setup\periodModel;
use App\Models\user\tbluserModel;
use App\Models\school_setup\timetableModel;
use function App\Helpers\is_mobile;

class timetableReportsController extends Controller
{
    //
      public function index(Request $request){
        $type = $request->type;
        $res = "";

          if (session()->has('data')) {
            $data_arr = session('data');
            if (isset($data_arr['message'])) {
                $inward_data['message'] = $data_arr['message'];
            }
        }

        return is_mobile($type, "time_table/classwiseReport", $res, "view");
    }

    public function create(Request $request)
    {
        // code...
        $academic_section_id = $request->input("grade");
        $standard_id = $request->input("standard");
        $division_id = $request->input("division");
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $syear = $request->session()->get('syear');
        $marking_period_id = session()->get('term_id');

        $get_name_data = DB::table('academic_section as ac')
            ->join('standard as s', function ($join) use($marking_period_id){
                $join->whereRaw('s.grade_id = ac.id AND ac.sub_institute_id = s.sub_institute_id');
            })->join('std_div_map as sd', function ($join) {
                $join->whereRaw('sd.standard_id = s.id AND sd.sub_institute_id = s.sub_institute_id');
            })->join('division as d', function ($join) {
                $join->whereRaw('d.id = sd.division_id');
            })->selectRaw("ac.title AS academic_name,s.name AS std_name,d.name AS div_name")
            ->where('ac.sub_institute_id', $sub_institute_id)
            ->where('ac.id', $academic_section_id)
            ->where('s.id', $standard_id)
            ->where('d.id', $division_id)->get()->toArray();

        $html = "";
        $old_timetable_data = [];
        $timetable_data = timetableModel::select('timetable.*',
            DB::raw('concat(first_name," ",last_name) as teacher_name'),
            'subject.subject_name', 'subject.subject_code', 'batch.title as batch_name')
            ->join('academic_section', 'academic_section.id', "=", 'timetable.academic_section_id')
            ->join('subject', 'subject.id', "=", 'timetable.subject_id')
            ->join('tbluser', 'tbluser.id', "=", 'timetable.teacher_id')
            ->leftJoin('batch', 'batch.id', "=", 'timetable.batch_id')
            ->where([
                'timetable.sub_institute_id'    => $sub_institute_id,
                'timetable.academic_section_id' => $academic_section_id,
                'timetable.standard_id'         => $standard_id,
                'timetable.division_id'         => $division_id,
                'timetable.syear'               => $syear,
            ])->get()->toArray(); //'concat(first_name," ",middle_name," ",last_name) as teacher_name'


        foreach ($timetable_data as $k => $p) {
            $old_timetable_data[$p['week_day']][$p['period_id']]['SUBJECT'][] = $p['subject_name'];
            $old_timetable_data[$p['week_day']][$p['period_id']]['SUBJECT_CODE'][] = $p['subject_code'];
            $old_timetable_data[$p['week_day']][$p['period_id']]['TEACHER'][] = $p['teacher_name'];
            $old_timetable_data[$p['week_day']][$p['period_id']]['TYPE'][] = $p['type'];
            if (isset($p['batch_name'])) {
                $old_timetable_data[$p['week_day']][$p['period_id']]['BATCH'][] = $p['batch_name'];
            }
        }

        $res['period_data'] = periodModel::select('period.*', DB::raw('date_format(period.start_time,"%H:%i") as s_time,
            date_format(period.end_time,"%H:%i") as e_time'))
            ->where(['sub_institute_id' => $sub_institute_id])//, 'academic_section_id' => $academic_section_id
            ->orderby('sort_order')
            ->get()
            ->toArray();

        $res['week_data'] = [
            "Monday"   => "M", "Tuesday" => "T", "Wednesday" => "W", "Thursday" => "H", "Friday" => "F",
            "Saturday" => "S",
        ];

        $type = $request->input('type');
        $res['status_code'] = 1;
        $res['old_timetable_data'] =$old_timetable_data;
        $res['message'] = "SUCCESS";
        $res['grade_id'] = $academic_section_id;
        $res['standard_id'] = $standard_id;
        $res['division_id'] = $division_id;

      return is_mobile($type, "time_table/classwiseReport", $res, "view");
    }

    public function facultyTimetableIndex(Request $request){
         if (session()->has('data')) {
                    $data_arr = session('data');
                    if (isset($data_arr['message'])) {
                        $inward_data['message'] = $data_arr['message'];
                    }
        }
         $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_data = $this->getusers($request);
        $res['teacher_data'] = $user_data;
        return is_mobile($type, "time_table/facultyReport", $res, "view");
    }

    public function facultyTimetableCreate(Request $request){
        $teacher_id = $request->input("teacher_id");
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');
        $type = $request->input('type');

        $html = "";
        $marking_priod_id=session()->get('term_id');
        $get_teacher_name = DB::table('tbluser')
            ->selectRaw("id,CONCAT_WS(' ',first_name,middle_name,last_name) as teacher_name")
            ->where('id', $teacher_id)
            ->where('sub_institute_id', $sub_institute_id)->get()->toArray();

        $timetable_data_arr = timetableModel::select('timetable.*',
            'subject.subject_name', 'subject.subject_code', 'batch.title as batch_name', 'period.title as period_name',
            'standard.name as standard_name', 'division.name as division_name')
            ->join('standard',function($join) use($marking_priod_id){
                $join->on('standard.id', "=", 'timetable.standard_id');
                // ->when($marking_priod_id,function($query) use($marking_priod_id){
                //     $query->where('standard.marking_period_id',$marking_priod_id);
                // });
            })
            ->join('subject', 'subject.id', "=", 'timetable.subject_id')
            ->leftjoin('division', 'division.id', "=", 'timetable.division_id')
            ->join('period', 'period.id', "=", 'timetable.period_id')
            ->leftJoin('batch', 'batch.id', "=", 'timetable.batch_id')
            ->where([
                'timetable.sub_institute_id' => $sub_institute_id,
                'timetable.teacher_id'       => $teacher_id,
                'timetable.syear'            => $syear,
            ])
            ->orderby('period.sort_order')
            ->get()->toArray();

        foreach ($timetable_data_arr as $k => $p) {
            $res['period_data'][$p['period_id']]["title"] = $p['period_name'];
            $res['period_data'][$p['period_id']]["id"] = $p['period_id'];
            $res['timetable_data'][$p['week_day']][$p['period_id']]['SUBJECT'][] = $p['subject_name'].' / '.$p['subject_code'];
            $res['timetable_data'][$p['week_day']][$p['period_id']]['STANDARD'][] = $p['standard_name'].' / '.$p['division_name'];
            $res['timetable_data'][$p['week_day']][$p['period_id']]['TYPE'][] = $p['type'];

            if (isset($p['batch_name'])) {
                $res['timetable_data'][$p['week_day']][$p['period_id']]['BATCH'][] = $p['batch_name'];
            }
        }

        $res['period_data'] = periodModel::select('period.*', DB::raw('date_format(period.start_time,"%H:%i") as s_time,
            date_format(period.end_time,"%H:%i") as e_time'))
            ->where(['sub_institute_id' => $sub_institute_id])//, 'academic_section_id' => $academic_section_id
            ->orderby('sort_order')
            ->get()
            ->toArray();

        $res['week_data'] = [
            "Monday"   => "M", "Tuesday" => "T", "Wednesday" => "W", "Thursday" => "H", "Friday" => "F",
            "Saturday" => "S",
        ];

        $type = $request->input('type');
        $res['status_code'] = 1;
        $res['message'] = "SUCCESS";
        $res['teacher_id'] = $teacher_id;
        $user_data = $this->getusers($request);
        $res['teacher_data'] = $user_data;
        return is_mobile($type, "time_table/facultyReport", $res, "view");
    }

     public function getusers(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');

        return tbluserModel::select('tbluser.*',
            DB::raw('concat(tbluser.first_name," ",tbluser.middle_name," ",tbluser.last_name) as teacher_name'))
            ->join('tbluserprofilemaster', 'tbluserprofilemaster.id', "=", 'tbluser.user_profile_id')
            ->where(['tbluser.sub_institute_id' => $sub_institute_id, 'tbluserprofilemaster.parent_id' => 2,'tbluser.status'=>1])
            ->orderby('tbluser.first_name')
            ->pluck('teacher_name','id');
    }
}
