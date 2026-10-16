<?php

namespace App\Http\Controllers\student\graph_attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class student_graph_attendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $submit = $request->input('submit');

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $syear = $request->session()->get('syear');
        $term_id = $request->session()->get('term_id');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $marking_period_id = session()->get('term_id');
        $date = "2019-08-22";


        // SUM(CASE WHEN s.gender = 'M' THEN 1 ELSE 0 END) AS BOY,
        // SUM(CASE WHEN s.gender = 'F' THEN 1 ELSE 0 END) AS GIRL,
        // SUM(CASE WHEN s.gender = 'M' AND a.attendance_code = 'P' THEN 1 ELSE 0 END) TBP,
        // SUM(CASE WHEN s.gender = 'F' AND a.attendance_code = 'P' THEN 1 ELSE 0 END) TGP,
        // SUM(CASE WHEN s.gender = 'M' AND a.attendance_code = 'A' THEN 1 ELSE 0 END) TBA,
        // SUM(CASE WHEN s.gender = 'F' AND a.attendance_code = 'A' THEN 1 ELSE 0 END) TGA

        $data = DB::table('tblstudent as s')
            ->join('tblstudent_enrollment as se', function ($join) use ($syear) {
                $join->whereRaw("s.id = se.student_id AND se.syear = '".$syear."'");
            })->join('academic_section as acs', function ($join) {
                $join->whereRaw("acs.id = se.grade_id");
            })->join('standard as sm', function ($join) use($marking_period_id) {
                $join->whereRaw("se.standard_id = sm.id");
                // ->when($marking_period_id,function($query) use($marking_period_id){
                //     $query->where('sm.marking_period_id',$marking_period_id);
                // });
            })->join('division as dm', function ($join) {
                $join->whereRaw("se.section_id = dm.id");
            })->leftJoin('attendance_student as a', function ($join) use ($date) {
                $join->whereRaw("a.student_id = s.id and a.attendance_date = '" . $date . "'");
            })
            ->selectRaw("acs.title,sm.name AS standard_name,dm.name AS division_name,
                se.standard_id,se.section_id,count(se.student_id) total_student,
                SUM(CASE WHEN a.attendance_code = 'A' THEN 1 ELSE 0 END) TA,
                SUM(CASE WHEN a.attendance_code = 'P' THEN 1 ELSE 0 END) TP")
            ->where('s.sub_institute_id', $sub_institute_id)
            ->groupByRaw('se.grade_id,se.standard_id,se.section_id')
            ->get()->toArray();

        $chart_data = "[{
            id: '0.0',
            parent: '',
            name: 'Attendance Chart'
        },";

        $grades = [];
        foreach ($data as $id => $arr) {
            if (! in_array($arr->title, $grades)) {
                $grades[] = $arr->title;
                $chart_data .= "{";
                $chart_data .= "id: "."'1." . count($grades)."',";
                $chart_data .= "parent: '0.0',";
                $chart_data .= "name: "."'".$arr->title."'";
                $chart_data .= "},";
            }
        }
        // $chart_data = rtrim($chart_data, ",");


        $i = 1;
        $standards = [];
        foreach ($grades as $id=>$val) {
            foreach ($data as $key=>$arr) {
                if ($arr->title == $val) {
                    if (!in_array($arr->standard_name, $standards)) {
                        $standards[] = $arr->standard_name;
                        $chart_data .= "{";
                        $chart_data .= "id: "."'2." . count($standards)."',";
                        $chart_data .= "parent: '1.".$i."',";
                        $chart_data .= "name: "."'".$arr->standard_name."'";
                        $chart_data .= "},";
                    }
                }
            }
            $i++;
        }

        // $i = 1;
        $divisioin = [];
        $temp = 0;
        foreach ($standards as $id=>$val) {
            foreach ($data as $key=>$arr) {
                if ($arr->standard_name == $val) {
                    // if (!in_array($arr->division_name, $divisioin)) {
                    $divisioin[] = $arr->division_name;
                    $chart_data .= "{";
                    $chart_data .= "id: "."'3." . count($divisioin)."',";
                    $chart_data .= "parent: '2.".($id+1)."',";
                    $chart_data .= "name: "."'".$arr->division_name."',";
                    $chart_data .= "value: ".$arr->total_student;
                    $chart_data .= "},";

                    if ($arr->TA != 0 || $arr->TP != 0) {
                        $temp++;
                        $chart_data .= "{";
                        $chart_data .= "id: "."'4." . $temp."',";
                        $chart_data .= "parent: '3.".count($divisioin)."',";
                        $chart_data .= "name: 'Present',";
                        $chart_data .= "value: ".$arr->TP;
                        $chart_data .= "},";
                        $temp++;
                        $chart_data .= "{";
                        $chart_data .= "id: "."'4." . $temp."',";
                        $chart_data .= "parent: '3.".count($divisioin)."',";
                        $chart_data .= "name: 'Absent',";
                        $chart_data .= "value: ".$arr->TA;
                        $chart_data .= "},";
                    }
                }
            }
            // $i++;
        }

        $chart_data = rtrim($chart_data, ",");
        $chart_data .= "];";

        $res['chartData'] = $chart_data;

        // Users by department -> drill down: individual users (click opens user edit)
        $userRows = DB::table('tbluser as u')
            ->leftJoin('hrms_departments as d', function ($join) {
                $join->on('d.id', '=', 'u.department_id')->on('d.sub_institute_id', '=', 'u.sub_institute_id');
            })
            ->leftJoin('tbluserprofilemaster as p', 'p.id', '=', 'u.user_profile_id')
            ->selectRaw("u.id, TRIM(CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name)) as name, IFNULL(d.id, 0) as gid, IFNULL(d.department, 'Unassigned') as gname, IFNULL(p.name, '') as role")
            ->where('u.sub_institute_id', $sub_institute_id)
            ->where('u.status', 1)
            ->orderBy('u.first_name')
            ->get();
        $userMain = [];
        $userDrill = [];
        foreach ($userRows as $r) {
            $dKey = 'user_'.$r->gid;
            $roleName = $r->role !== '' ? $r->role : 'Other';
            $rKey = $dKey.'_'.md5($roleName);
            if (! isset($userMain[$dKey])) {
                $userMain[$dKey] = ['name' => $r->gname, 'y' => 0, 'drilldown' => $dKey];
                $userDrill[$dKey] = ['id' => $dKey, 'name' => $r->gname, 'data' => []];
            }
            $userMain[$dKey]['y']++;
            if (! isset($userDrill[$rKey])) {
                // department level: one slice per role, drills into that role's users
                $userDrill[$dKey]['data'][$rKey] = ['name' => $roleName, 'y' => 0, 'drilldown' => $rKey];
                $userDrill[$rKey] = ['id' => $rKey, 'name' => $roleName, 'users' => true, 'data' => []];
            }
            $userDrill[$dKey]['data'][$rKey]['y']++;
            $userDrill[$rKey]['data'][] = [
                'name' => $r->name !== '' ? $r->name : 'User #'.$r->id,
                'y'    => 1,
                'role' => $roleName,
                'url'  => route('add_user.edit', $r->id),
            ];
        }
        foreach ($userDrill as $k => $d) {
            $userDrill[$k]['data'] = array_values($d['data']);
        }
        $userMain = array_values($userMain);
        usort($userMain, function ($a, $b) {
            return $b['y'] <=> $a['y'];
        });
        $res['userChart'] = ['main' => $userMain, 'drill' => array_values($userDrill)];

        // Students: standard -> division -> student (click opens student profile)
        $studentRows = DB::table('tblstudent as ts')
            ->join('tblstudent_enrollment as se', function ($join) use ($sub_institute_id) {
                $join->on('se.student_id', '=', 'ts.id')->where('se.sub_institute_id', '=', $sub_institute_id);
            })
            ->join('standard as s', function ($join) use ($sub_institute_id, $marking_period_id) {
                $join->on('s.id', '=', 'se.standard_id')->where('s.sub_institute_id', '=', $sub_institute_id);
                if (! empty($marking_period_id)) {
                    $join->where('s.marking_period_id', $marking_period_id);
                }
            })
            ->leftJoin('division as dv', 'dv.id', '=', 'se.section_id')
            ->selectRaw("ts.id as sid, TRIM(CONCAT_WS(' ', ts.first_name, ts.middle_name, ts.last_name)) as sname,
                s.id as gid, s.name as gname, IFNULL(dv.id, 0) as mid, IFNULL(dv.name, '-') as mname")
            ->where('ts.sub_institute_id', $sub_institute_id)
            ->where('se.syear', $syear)
            ->whereNull('se.end_date')
            ->orderBy('s.sort_order')->orderBy('s.name')->orderBy('dv.name')->orderBy('ts.first_name')
            ->get()
            ->map(function ($r) {
                return [
                    'gid' => $r->gid, 'gname' => $r->gname, 'mid' => $r->mid, 'mname' => $r->mname,
                    'name' => $r->sname !== '' ? $r->sname : 'Student #'.$r->sid, 'value' => 1,
                    'url' => route('studentProfileData', ['student_id' => $r->sid]),
                ];
            });
        $res['studentChart'] = $this->buildTree($studentRows, 'student');

        // Fees: month -> standard -> student (regular + other fees; click opens student profile)
        $paid = [];
        foreach (['fees_collect' => 'amount', 'fees_paid_other' => 'actual_amountpaid'] as $table => $col) {
            $rows = DB::table($table.' as fc')
                ->leftJoin('tblstudent_enrollment as se', function ($join) {
                    $join->on('se.student_id', '=', 'fc.student_id')->on('se.syear', '=', 'fc.syear');
                })
                ->leftJoin('standard as s', 's.id', '=', 'se.standard_id')
                ->leftJoin('tblstudent as ts', 'ts.id', '=', 'fc.student_id')
                ->selectRaw("DATE_FORMAT(fc.receiptdate,'%Y-%m') as ym, IFNULL(s.id, 0) as mid, IFNULL(s.name, 'Other') as mname,
                    fc.student_id as sid, TRIM(CONCAT_WS(' ', ts.first_name, ts.middle_name, ts.last_name)) as sname,
                    IFNULL(SUM(fc.$col),0) as total")
                ->where(['fc.sub_institute_id' => $sub_institute_id, 'fc.syear' => $syear, 'fc.is_deleted' => 'N'])
                ->groupBy('ym', 'mid', 'mname', 'sid', 'sname')
                ->get();
            foreach ($rows as $r) {
                $k = $r->ym.'|'.$r->mid.'|'.$r->sid;
                if (isset($paid[$k])) {
                    $paid[$k]['value'] += (float) $r->total;
                    continue;
                }
                $paid[$k] = [
                    'gid' => $r->ym, 'gname' => date('M Y', strtotime($r->ym.'-01')),
                    'mid' => $r->mid, 'mname' => $r->mname,
                    'name' => $r->sname !== '' ? $r->sname : 'Student #'.$r->sid,
                    'value' => (float) $r->total,
                    'url' => route('studentProfileData', ['student_id' => $r->sid]),
                ];
            }
        }
        $paid = collect(array_values($paid))->sortBy('gid')->values();
        $res['feesChart'] = $this->buildTree($paid, 'fees');

        return is_mobile($type, "student/graph_attendance/view", $res, "view");
    }

    /**
     * Build 3-level Highcharts drilldown data from rows
     * [gid, gname, mid, mname, name, value, url]: group -> middle -> leaf (leaf click opens url).
     */
    private function buildTree($rows, $prefix)
    {
        $main = [];
        $drill = [];
        foreach ($rows as $r) {
            $gKey = $prefix.'_'.$r['gid'];
            $mKey = $gKey.'_'.$r['mid'];
            if (! isset($main[$gKey])) {
                $main[$gKey] = ['name' => $r['gname'], 'y' => 0, 'drilldown' => $gKey];
                $drill[$gKey] = ['id' => $gKey, 'name' => $r['gname'], 'data' => []];
            }
            if (! isset($drill[$gKey]['data'][$mKey])) {
                $drill[$gKey]['data'][$mKey] = ['name' => $r['mname'], 'y' => 0, 'drilldown' => $mKey];
                $drill[$mKey] = ['id' => $mKey, 'name' => $r['mname'], 'leaf' => true, 'data' => []];
            }
            $main[$gKey]['y'] += $r['value'];
            $drill[$gKey]['data'][$mKey]['y'] += $r['value'];
            $drill[$mKey]['data'][] = ['name' => $r['name'], 'y' => round($r['value'], 2), 'url' => $r['url']];
        }
        foreach ($main as $k => $m) {
            $main[$k]['y'] = round($m['y'], 2);
        }
        foreach ($drill as $k => $d) {
            if (empty($d['leaf'])) {
                $drill[$k]['data'] = array_values($d['data']);
                foreach ($drill[$k]['data'] as $i => $pt) {
                    $drill[$k]['data'][$i]['y'] = round($pt['y'], 2);
                }
            }
        }

        return ['main' => array_values($main), 'drill' => array_values($drill)];
    }
}
