<?php

namespace App\Http\Controllers\time_table;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;
use App\Models\school_setup\academic_sectionModel;
use App\Models\school_setup\batchModel;
use App\Models\school_setup\divisionModel;
use App\Models\school_setup\periodModel;
use App\Models\school_setup\standardModel;
use App\Models\school_setup\std_div_mappingModel;
use App\Models\school_setup\sub_std_mapModel;
use App\Models\school_setup\timetableModel;
use App\Models\user\tbluserModel;
use App\Models\hostel_management\hostel_room_masterModel;

class createTimetableController extends Controller
{
    //
    public function index(Request $request){
        $type = $request->type;
        $res = $request->type;
        // /var/www/html/he_erp/resources/views/time_table/createTimeTable
        return is_mobile($type, "time_table/show", $res, "view");
    }

    public function getData($request)
    {
        $sub_institute_id=session()->get('sub_institute_id');
        $syear = session()->get('syear');
        if($request->type=="API"){
            $sub_institute_id=$request->sub_institute_id;
            $syear = $request->syear;
        }
        $academic_section_data = academic_sectionModel::where(['sub_institute_id' => $sub_institute_id])->get();
        $data['academic_section_data'] = $academic_section_data;

        return $data;
    }

    public function create(Request $request) {
        $html = "";
        $sub_institute_id=session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $type = $request->type;
        if($type=="API"){
            $sub_institute_id=$request->sub_institute_id;
            $syear = $request->syear;
        }
        $academic_section_id=$request->grade;
        $standard_id=$request->standard;
        $division_id = $request->division;

        $timetable_data= timetableModel::
        where([
            'sub_institute_id'    => $sub_institute_id,
            'academic_section_id' => $academic_section_id,
            'standard_id'         => $standard_id,
            'division_id'         => $division_id,
            'syear'               => $syear,
        ])->get()->toArray();
        
        $res['week_data'] =[
            "Monday"   => "M", "Tuesday" => "T", "Wednesday" => "W", "Thursday" => "H", "Friday" => "F",
            "Saturday" => "S",
        ];
        foreach ($timetable_data as $k => $p) {
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['ID'][] = $p['id'];
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['SUBJECT_ID'][] = $p['subject_id'];
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['TEACHER_ID'][] = $p['teacher_id'];
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['ROOM_ID'][] = $p['room'];
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['TYPE'][] = $p['type'] ??'';
            $res['old_timetable_data'][$p['week_day']][$p['period_id']]['LAB'][] = $p['extend_lab'] ??'';
            if (isset($p['batch_id']) && $p['batch_id'] != "") {
                $res['old_timetable_data'][$p['week_day']][$p['period_id']]['BATCH_ID'][] = $p['batch_id'];
            }
        }   
        // echo "<pre>";print_r($res['old_timetable_data']);exit;
        $res['batch_data'] = batchModel::where([
            'sub_institute_id' => $sub_institute_id,
            'standard_id' => $standard_id,
            'division_id' => $division_id,
            'syear' => $syear,
        ])->get()->toArray();
        $res['total_batches'] = count($res['batch_data']);

        $res['period_data'] = periodModel::where(['sub_institute_id' => $sub_institute_id])
            ->orderby('sort_order')->get();//"academic_section_id"=>$academic_section_id

        $res['subject_data'] = sub_std_mapModel::where([
            'sub_institute_id' => $sub_institute_id, "standard_id" => $standard_id,
        ])->get(["subject_id", "display_name"])->toArray();

            // echo "<pre>";print_r($res['old_timetable_data']);exit; //16425,9191

      $res['teacher_data'] = tbluserModel::select('tbluser.*',
            DB::raw('CONCAT_WS(" ",tbluser.last_name,tbluser.first_name,tbluser.middle_name) AS teacher_name,
                (CASE WHEN total_lecture IS NULL THEN "Unlimited" ELSE tbluser.total_lecture - count(t.id) END) AS remaining_lecture'))
            ->join('tbluserprofilemaster', 'tbluserprofilemaster.id', '=', 'tbluser.user_profile_id')
            ->leftJoin('hrms_departments', 'tbluser.department_id', '=', 'hrms_departments.id')
            ->leftjoin("timetable AS t", function ($join) {
                $join->on("t.teacher_id", "=", "tbluser.id")
                    ->on("t.sub_institute_id", "=", "tbluser.sub_institute_id");
            })
            ->where(['tbluser.sub_institute_id' => $sub_institute_id, 'tbluserprofilemaster.parent_id' => 2])
            ->where('tbluser.status', 1)
           ->groupby("tbluser.id")
           ->orderBy('last_name')
           ->orderBy('first_name')
            ->get();
        // echo "<pre>";print_r($res['teacher_data']);exit;

        $res['stdData'] = standardModel::where(['sub_institute_id' => $sub_institute_id, 'grade_id' => $academic_section_id])
            ->get();

        $res['room_data'] = hostel_room_masterModel::where(['sub_institute_id' => $sub_institute_id])
            ->get();

        $res['divData'] = std_div_mappingModel::select('division.*')
            ->join('division', 'division.id', "=", 'std_div_map.division_id')
            ->where(['std_div_map.sub_institute_id' => $sub_institute_id, 'std_div_map.standard_id' => $standard_id])
            ->get();

        $res['data'] = $this->getData($request);

        $res['types'] = ["Tutorial","Lab","Lecture"];
        $res['status_code'] = 1;
        $res['message'] = "SUCCESS";
        $res['grade_id'] = $academic_section_id;
        $res['standard_id'] = $standard_id;
        $res['division_id'] = $division_id;
        // echo "<pre>";print_r($res);exit;
        return is_mobile($type, "time_table/show", $res, "view");
    }

    // add or remove time table row
    public function getBatchTimetable(Request $request)
    {
        $division_id = $request->input("division_id");
        $id = $request->input("id");
        $standard_id = $request->input("standard_id");
        $mode = $request->input("mode");
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');
        $marking_period_id = session()->get('term_id');
        $arr = explode("-", $id);
        // echo "<pre>";print_r($arr);exit;
        $subject_data = sub_std_mapModel::where([
            'sub_institute_id' => $sub_institute_id,
            "standard_id"      => $standard_id,
        ]) 
       ->get(["subject_id", "display_name"])->toArray();

        $teacher_data = tbluserModel::select('tbluser.*')
            ->join('tbluserprofilemaster', 'tbluserprofilemaster.id', "=", 'tbluser.user_profile_id')
            ->where(['tbluser.sub_institute_id' => $sub_institute_id, 'tbluserprofilemaster.parent_id' => 2, 'tbluser.status' => 1])
            ->orderBy('tbluser.last_name')
            ->orderBy('tbluser.first_name')
            ->get();

        $room_data = hostel_room_masterModel::where(['sub_institute_id' => $sub_institute_id])
            ->get();
        
        $batch_data = batchModel::where([
            'sub_institute_id' => $sub_institute_id,
            'standard_id'      => $standard_id,
            'division_id'      => $division_id,
            'syear'            => $syear,
        ])->get()->toArray();
        $total_batches = count($batch_data);
            
        $html = "";
        if ($mode == 'batchwise') {
            $html .= "<div>";
            for ($i = 1; $i <= $total_batches; $i++) {
                $html .= "<select class='form-control' name='subjects[".$arr['1']."][".$arr['0']."][".$i."]' id='subject[".$arr['1']."][".$arr['0']."][".$i."]'>
                        <option value=''>--Subject--</option>";
                foreach ($subject_data as $skey => $sval) {
                    $html .= "<option value='".$sval['subject_id']."'>".$sval['display_name']."</option>";
                }
                $html .= "</select>";

                $html .= "<select class='form-control' style='margin-top:10px;' name='teachers[".$arr['1']."][".$arr['0']."][".$i."]' id='teacher[".$arr['1']."][".$arr['0']."][".$i."]'>
                        <option value=''>--Lecturer--</option>";
                foreach ($teacher_data as $tkey => $tval) {
                    $teacher_name = $tval['last_name']." ".$tval['first_name']." ".$tval['middle_name'];
                    $html .= "<option value='".$tval['id']."'>".$teacher_name."</option>";
                }
                $html .= "</select>";

                $html .= "<select class='form-control' style='margin-top:10px;' name='batches[".$arr['1']."][".$arr['0']."][".$i."]' id='batch[".$arr['1']."][".$arr['0']."][".$i."]'>
                        <option value=''>--Batch--</option>";
                foreach ($batch_data as $bkey => $bval) {
                    $html .= "<option value='".$bval['id']."'>".$bval['title']."</option>";
                }
                $html .= "</select>";

                $html .= "<select class='form-control' style='margin-top:10px;' name='rooms[".$arr['1']."][".$arr['0']."][".$i."]' id='room[".$arr['1']."][".$arr['0']."][".$i."]'>
                        <option value=''>--Room--</option>";
                foreach ($room_data as $rkey => $rval) {
                    $room_name = $rval['room_name'];
                    $html .= "<option value='".$rval['id']."'>".$room_name."</option>";
                }
                $html .= "</select>";
                
                $types= ["Tutorial","Lab","Lecture"];

                 $html .= "<select class='form-control' style='margin-top:10px;' name='types[".$arr['1']."][".$arr['0']."][".$i."]' id='types[".$arr['1']."][".$arr['0']."][".$i."]'>
                        <option value=''>--Types--</option>";
                foreach ($types as $bkey => $bval) {
                    $html .= "<option value='".$bval."'>".$bval."</option>";
                }
                $html .= "</select>";

                if ($i == $total_batches) {
                } else {
                    $html .= "<hr>";
                }
            }
            $html .= "</div>";
            $html .= "<div class='minus_div' style='margin-left: 10px;'>";
            $html .= "<a class='fas fa-minus-square' href='#' onclick=removeNewRow('".$id."','normal');></a>";
            $html .= "<a class='mdi mdi-source-merge fa-fw text-danger' href='#' onclick=addNewStdandardDiv('".$id."');></a>";
            $html .= "<a class='fas fa-window-close text-danger' href='#'></a>";
            $html .= "<input type='checkbox' name='extend_lab[".$arr['1']."][".$arr['0']."][0]'>";
            $html .= "</div>";
        } else {
            if ($mode == 'normal') {
                $html .= "<div>";
                $html .= "<select class='form-control' name='subject[".$arr['1']."][".$arr['0']."]' id='subject[".$arr['1']."][".$arr['0']."]'>
                        <option value=''>--Subject--</option>";
                foreach ($subject_data as $skey => $sval) {
                    $html .= "<option value='".$sval['subject_id']."'>".$sval['display_name']."</option>";
                }
                $html .= "</select>";

                
                $html .= "<select class='form-control' style='margin-top:10px;' name='teachers[".$arr['1']."][".$arr['0']."]' id='teacher[".$arr['1']."][".$arr['0']."]'>
                        <option value=''>--Lecturer--</option>";
                foreach ($teacher_data as $tkey => $tval) {
                    $teacher_name = $tval['last_name']." ".$tval['first_name']." ".$tval['middle_name'];
                    $html .= "<option value='".$tval['id']."'>".$teacher_name."</option>";
                }
                $html .= "</select>";

/*
                if(!empty($batch_data) && $total_batches>0){
                    $html .= "<select class='form-control' style='margin-top:10px;' name='batches[".$arr['1']."][".$arr['0']."]' id='batch[".$arr['1']."][".$arr['0']."]'>
                        <option value=''>--Batch--</option>";
                foreach ($batch_data as $bkey => $bval) {
                    $html .= "<option value='".$bval['id']."'>".$bval['title']."</option>";
                }

                $html .= "</select>";
                }
*/

                $html .= "<select class='form-control' style='margin-top:10px;' name='rooms[".$arr['1']."][".$arr['0']."]' id='room[".$arr['1']."][".$arr['0']."]'>
                        <option value=''>--Room--</option>";
                foreach ($room_data as $rkey => $rval) {
                    $room_name = $rval['room_name'];
                    $html .= "<option value='".$rval['id']."'>".$room_name."</option>";
                }
                $html .= "</select>";

                $types= ["Tutorial","Lab","Lecture"];

                 $html .= "<select class='form-control' style='margin-top:10px;' name='types[".$arr['1']."][".$arr['0']."]' id='types[".$arr['1']."][".$arr['0']."]'>
                        <option value=''>--Types--</option>";
                foreach ($types as $bkey => $bval) {
                    $html .= "<option value='".$bval."'>".$bval."</option>";
                }
                $html .= "</select>";

                $html .= "</div>";
                $html .= "<div class='plus_div' style='margin-left: 10px;'>";
                $html .= "<a class='fas fa-plus-square' href='#' onclick=addNewRow('".$id."');></a>";
                $html .= "<a class='mdi mdi-source-merge fa-fw text-danger' href='#' onclick=addNewStdandardDiv('".$id."','normal');></a>";
                $html .= "<a class='fas fa-window-close text-danger' href=#></a>";
                $html .= "</div>";
            }
        }

        echo $html;
        exit;
    }

      //DELETE Timetable data -- Ajax Call
    public function deleteTimetable(Request $request)
    {
        $division_id = $request->input("division_id");
        $id = $request->input("id");
        $standard_id = $request->input("standard_id");
        $grade_id = $request->input("grade_id");
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $marking_period_id=session()->get('term_id');
        $arr = explode("-", $id);
        $week_day = $arr[0];
        $period_id = $arr[1];
        $teacher_id = $arr[2];

        $check_timetable_data = timetableModel::where([
            'sub_institute_id' => $sub_institute_id,
            'syear'            => $syear,
            'standard_id'      => $standard_id,
            'division_id'      => $division_id,
            'week_day'         => $week_day,
            'period_id'        => $period_id,
            'teacher_id'       => $teacher_id,
            
        ])->get()->toArray();

        if (count($check_timetable_data) > 0) {
            $deleted_record = timetableModel::where(
                [
                    "sub_institute_id" => $sub_institute_id,
                    "standard_id"      => $standard_id,
                    "division_id"      => $division_id,
                    "syear"            => $syear,
                    "week_day"         => $week_day,
                    "period_id"        => $period_id,
                    "teacher_id"       => $teacher_id,
                ])->delete();
        }

        $res['message'] = 'deleted';        
        // $tyspe =$request->input('type');
        return response()->json($res);
    }

   public function store(Request $request)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');
        $grade_id = $request->grade_id;
        $standard_id = $request->standard_id;
        $division_id = $request->division_id;

        $response = [
            'status_code' => 0,
            'message' => 'Timetable operation failed',
            'grade_id' => $grade_id,
            'standard_id' => $standard_id,
            'division_id' => $division_id
        ];

        $period_arr = $request->subjects;
        $teacher_arr = $request->teachers;
        $batch_arr = $request->batches;
        $room_arr = $request->rooms;
        $types_arr = $request->types;

        foreach ($period_arr as $period_id => $periodData) {
            foreach ($periodData as $week_day => $subjectDataArr) {
                if (!is_array($subjectDataArr)) continue;

                $hasExtendLab = $this->checkAnyExtendLab($request, $period_id, $week_day);

                foreach ($subjectDataArr as $key => $subject_id) {
                    if (empty($subject_id) || $subject_id === '--Subject--') continue;

                    $teacher_id = $teacher_arr[$period_id][$week_day][$key] ?? 0;
                    $batch_id = $batch_arr[$period_id][$week_day][$key] ?? null;
                    $room_id = $room_arr[$period_id][$week_day][$key] ?? null;
                    $types = $types_arr[$period_id][$week_day][$key] ?? null;
                    $extend_lab_flag = $request->extend_lab[$period_id][$week_day][$key] ?? '';

                    $extend_lab = (($extend_lab_flag === 'on' || $hasExtendLab) && (in_array($types,["Tutorial","Lab"]))) ? 'Y' : 'N';

                    $types = ($types === '--Types--') ? null : $types;
                    $teacher_id = ($teacher_id === '--Teacher--') ? 0 : $teacher_id;
                    $batch_id = ($batch_id === '--Batch--') ? null : $batch_id;
                    $room_id = ($room_id === '--Room--') ? null : $room_id;

                    $query = [
                        'sub_institute_id' => $sub_institute_id,
                        'syear' => $syear,
                        'academic_section_id' => $grade_id,
                        'standard_id' => $standard_id,
                        'division_id' => $division_id,
                        'period_id' => $period_id,
                        'week_day' => $week_day
                    ];
                    
                    $data = [
                        'subject_id' => $subject_id,
                        'teacher_id' => $teacher_id,
                        'type' => $types,
                        'room' => $room_id,
                        'extend_lab' => $extend_lab,
                        //'merge' => isset($request->standard_arr[$period_id]) && isset($request->division_arr[$period_id]) ? 1 : null
                        //'merge' => $extend_lab == 'Y' ? 1 : null,
                    ];

                    if ($batch_id) {
                        $query['batch_id'] = $batch_id;
                        //$data['batch_id'] = $batch_id;
                        $data['merge'] = 1;
                    }

                    $existing = timetableModel::where($query)->first();

                    if ($existing) {
                        $existing->update($data + ['updated_at' => now()]);
                        $response['message'] = 'Timetable updated successfully';
                    } else {
                        timetableModel::create($query + $data + ['created_at' => now()]);
                        $response['message'] = 'Timetable added successfully';
                    }

                    if ($extend_lab === 'Y') {
                        $this->extendLabs(
                            $sub_institute_id, $syear, $grade_id,
                            $standard_id, $division_id, $week_day,
                            $subject_id, $teacher_id,$period_id, $types,
                            $batch_id,$room_id
                        );
                    }
                }
            }
        }

        $response['status_code'] = 1;
        // return is_mobile($request->input('type'), "create-timetable.index", $response, "redirect");
        return redirect()->back()->with('status_code', $response['status_code'])
            ->with('message', $response['message'])
            ->with('grade_id', $response['grade_id'])
            ->with('standard_id', $response['standard_id'])
            ->with('division_id', $response['division_id']);
    }

    protected function checkAnyExtendLab($request, $period_id, $week_day)
    {
        if (isset($request->extend_lab[$period_id][$week_day])) {
            $value = $request->extend_lab[$period_id][$week_day];
            if (is_array($value)) {
                return in_array('on', $value);
            } else {
                return $value === 'on';
            }
        }
        return false;
    }

    public function extendLabs($sub_institute_id, $syear, $grade_id, $standard_id, $division_id, $week_day, $subject_id, $teacher_id, $period_id, $types, $batch_id = null,$room_id)
    {
        $currentPeriod = periodModel::find($period_id);
        if (!$currentPeriod) return;

        $nextPeriod = periodModel::where('sort_order', $currentPeriod->sort_order + 1)
            ->where('sub_institute_id', $sub_institute_id)
            ->first();

        if (!$nextPeriod) return;

        $query = [
            'sub_institute_id' => $sub_institute_id,
            'syear' => $syear,
            'academic_section_id' => $grade_id,
            'standard_id' => $standard_id,
            'division_id' => $division_id,
            'subject_id' => $subject_id,
            'teacher_id' => $teacher_id,
            'room' => $room_id,
            'period_id' => $nextPeriod->id,
            'week_day' => $week_day,
            'extend_lab' => 'N',
            'type' => $types
        ];

        if ($batch_id) {
            $query['batch_id'] = $batch_id;
        }

        if (!timetableModel::where($query)->exists()) {
            $query['merge'] = 1;
            timetableModel::create($query + ['created_at' => now()]);
        }
    }

}
