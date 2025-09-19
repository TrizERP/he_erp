<?php

namespace App\Http\Controllers\fees\fees_collect;

use App\Http\Controllers\Controller;
use App\Models\fees\bank_master\bankmasterModel;
use App\Models\fees\map_year\map_year;
use App\Models\fees\tblfeesConfigModel;
use App\Models\school_setup\SchoolModel;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\FeeBreackoff;
use function App\Helpers\FeeBreakoffHeadWise;
use function App\Helpers\FeeMonthId;
use function App\Helpers\is_mobile;
use function App\Helpers\get_string;
use function App\Helpers\OtherBreackOff;
use function App\Helpers\OtherBreackOffHead;
use function App\Helpers\OtherBreackOfMonth;
use function App\Helpers\OtherBreackOfMonthHead;
use function Illuminate\Session\expired;
use App\Models\fees\fees_breackoff\fees_breackoff;
use App\Http\Controllers\easy_com\send_sms_parents\send_sms_parents_controller;

class fees_collect_controller extends Controller
{
    use GetsJwtToken;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return false|Application|Factory|View|RedirectResponse|string
     */
    public function index(Request $request)
    {
        //get message for success or failed from any function in controller which call index function in return
        if (session()->has('data')) {
            $data_arr = session('data');
            if (isset($data_arr['message'])) {
                $school_data['message'] = $data_arr['message'];
            }
        }
        $school_data['data'] = [];
        $type = $request->input('type');
        return is_mobile($type, "fees/fees_collect/show", $school_data, "view");
    }

    /**
     * function is used to get student and fees details on search
     *
     * @return void
     */
     public function show_student(Request $request)
     {
         $responce_arr = [];
         $type = $request->type ?? "";
         $last_year = (session()->get('syear') - 1);
 
         $sub_institute_id = session()->get('sub_institute_id');
         $syear = session()->get('syear');
         $marking_period_id = session()->get('term_id');
 
         if($type=="API"){
             $sub_institute_id=$request->sub_institute_id;
             $syear=$request->syear;
             $last_syear = ($syear-1);
             $marking_period_id = $request->term_id;
         }
         // get month_id by month name
         $month_arr = FeeMonthId();
         $currunt_month = date('m');
         $currunt_year = date('Y');
         $currunt_month_id = $currunt_month . $currunt_year;
 
         $search_ids = [];
         foreach ($month_arr as $id => $arr) {
             if ($id == $currunt_month_id) {
                 $search_ids[] = $id;
             } else {
                 $search_ids[] = $id;
             }
         }
 
         $breackoff_join = $breackoff_other_join = $fees_join = $paid_other_join = "";
 
         // below foreach is for where condition to search month_id wise
         foreach ($search_ids as $id => $val) {
             if ($id == 0) {
                 $breackoff_join .= " AND (";
                 $breackoff_other_join .= " AND (";
                 $fees_join .= " AND (";
                 $paid_other_join .= " AND (";
             }
             if (count($search_ids) == ($id + 1)) {
                 $breackoff_join .= "fb.month_id = $val)";
                 $breackoff_other_join .= "fbo.month_id = $val)";
                 $fees_join .= "fc.term_id = $val)";
                 $paid_other_join .= "fpo.month_id = $val)";
             } else {
                 $breackoff_join .= "fb.month_id = $val OR ";
                 $breackoff_other_join .= "fbo.month_id = $val OR ";
                 $fees_join .= "fc.term_id = $val OR ";
                 $paid_other_join .= "fpo.month_id = $val OR ";
             }
         }
 
         $extra_where = "";
         if (isset($request->mobile) && $request->mobile != '') {
             $responce_arr['mobile'] = $request->mobile;
         }
         if (isset($request->grno) && $request->grno != '') {
             $responce_arr['grno'] = $request->grno;
         }
         if (isset($request->uniqueid) && $request->uniqueid != '') {
             $responce_arr['uniqueid'] = $request->uniqueid;
         }
         if (isset($_REQUEST['grade']) && $_REQUEST['grade'] != '') {
             $grade_val = $_REQUEST['grade'];
             $responce_arr['grade'] = $_REQUEST['grade'];
 
         }
         if (isset($request->standard) && $request->standard != '') {
             $responce_arr['standard'] = $request->standard;
         }
         if (isset($request->division) && $request->division != '') {
             $responce_arr['division'] = $request->division;
         }
         if (isset($request->stu_name) && $request->stu_name != '') {
             $responce_arr['stu_name'] = $request->stu_name;
         }
         if (isset($request->including_inactive) && $request->including_inactive != '') {
             $responce_arr['including_inactive'] = $request->including_inactive;
         // get fees_breakoff of student by matching above conditions
         $studentData = DB::table('tblstudent as s')
             ->join('tblstudent_enrollment as se','se.student_id', '=' ,'s.id')
             ->join('academic_section as g', 'g.id', '=', 'se.grade_id')
            //  ->join('standard as st','st.id' ,'=' ,'se.standard_id') // commented on 03-03-2025 and added below query by uma
             ->join('standard as st',function($q) use($marking_period_id) 
             { 
                $q->on('st.id' ,'=' ,'se.standard_id')->where('st.marking_period_id',$marking_period_id);
             })
             ->join('division as d', 'd.id' ,'=','se.section_id')
             ->join('student_quota as sq', function ($join) {
                 $join->whereRaw('sq.id = se.student_quota AND sq.sub_institute_id = se.sub_institute_id');
             })
             ->selectRaw("s.*,se.syear,se.student_id,se.grade_id,
                 se.standard_id,se.section_id,se.student_quota,sq.title AS stu_quota,se.start_date,
                 se.end_date,se.enrollment_code,se.drop_code,se.drop_remarks,
                 se.drop_remarks,se.term_id,se.remarks,se.admission_fees,
                 se.house_id,se.lc_number,st.name standard_name, d.name as division_name")
             ->where('s.sub_institute_id', $sub_institute_id)
             ->where('se.syear', $syear)
             ->where(function ($q) use ($request) {
                 if (isset($request->mobile) && $request->mobile != '') {
                     $q->where('s.mobile', $request->mobile);
                 }
                 if (isset($request->grno) && $request->grno != '') {
                     $q->where('s.enrollment_no', $request->grno);
                 }
                 if (isset($request->uniqueid) && $request->uniqueid != '') {
                     $q->where('s.uniqueid', $request->uniqueid);
                 }
                 if (isset($request->grade) && $request->grade != '') {
                     $q->where('se.grade_id', $request->grade);
                 }
                 if (isset($request->standard) && $request->standard != '') {
                     $q->where('se.standard_id', $request->standard);
                 }
                 if (isset($request->division) && $request->division != '') {
                     $q->where('se.section_id', $request->division);
                 }
                 if (isset($request['stu_name']) && $request['stu_name'] != '') {
                     $q->where(function ($query) use ($request) {
                         $query->where('s.first_name', 'like', '%' . $request->stu_name . '%')
                             ->orWhere('s.middle_name', 'like', '%' . $request->stu_name . '%')
                             ->orWhere('s.last_name', 'like', '%' . $request->stu_name . '%');
                     });
                 }
                 if (isset($request['including_inactive']) && $request['including_inactive'] != '' && $request['including_inactive'] == 'Yes') {
                         $q->whereNotNull('se.end_date');
                 } else {
                     $q->whereNull('se.end_date');
                 }
             })->groupByRaw('s.id,se.standard_id')->get()->toArray();
 
             $result = $bks = $obks = [];
             foreach ($studentData as $key => $value) {
                 $stu_arr[0] = $value->student_id;
                 $standardId = $value->standard_id;
                 $breakoff = FeeBreackoff($stu_arr,$standardId,$syear);
                 // echo "<pre>";print_r($breakoff);
                 $OtherBreackOff = OtherBreackOff($stu_arr, $search_ids,'', null,null, $syear,$sub_institute_id);
                 $bk = $obk = 0;
                 if(count($breakoff)!=0 && is_array($breakoff)){
                     foreach ($breakoff as $k => $v) {
                         $bk += $v->bkoff;
                     }
                 }
                 if(count($OtherBreackOff)!=0 && is_array($OtherBreackOff)){
                     $obk = array_sum($OtherBreackOff);
                 }
                 $value->fees_breakoff = $bk;
                 $value->fees_other_breakoff = $obk;
                 $value->bkoff = ($bk+$obk);
 
                 $result[$key]=$value;
             }
             // echo "<pre>";print_r($result);
             // exit;
         $request = Request::capture();
         foreach ($result as $id => $arr) {
             $bk_stu_id = $arr->id;
             $bk_std_id = $arr->standard_id;
             // get paid and unpiad history of student by his/her id
             $paid_result = $this->getBk($request, $bk_stu_id,$bk_std_id);
             //echo "<pre>";print_r($paid_result);exit;
             if(isset($paid_result) && !empty($paid_result)){
                 $pd_stu_id = $paid_result['stu_data']['student_id'];
                 // $remain = $paid_result['final_fee']['Total'];
                 $remain = $paid_result['stu_data']['pending'] ?? 0; // 2024-07-26
                 $previous = $paid_result['stu_data']['previous_fees'] ?? 0;
                 if ($bk_stu_id == $pd_stu_id) {
                     
                     if ($remain > 0){
                         $arr->bkoff = ($remain + $previous);
                     }
                     else if ($previous < 0) {
                         $arr->bkoff = ($remain - $previous);
                     }
                     else if($previous > 0){
                         $arr->bkoff = ($previous > $remain) ? ($remain + $previous) : ($remain - $previous);
                     } else {
                         if ($remain > 0){
                         $arr->bkoff = $remain;
                         }else{
                             $arr->bkoff = 0;
                         }
                     }
                 }
             }
         }
         // echo "<pre>";print_r($result);
         // exit;
         if (empty($bks) && empty($obks) && empty($studentData) ) {
 
             // if student details are missing then this code will give missing detail in message
             $check =  $check = DB::table('tblstudent as s')
                     ->join('tblstudent_enrollment as se','se.student_id','=' ,'s.id')
                     ->join('standard as st', 'st.id' ,'=', 'se.standard_id')
                     ->leftJoin('division as d','d.id', '=' ,'se.section_id')
                     ->leftJoin('student_quota as sq',function($join){
                         $join->whereRaw('sq.id = se.student_quota AND sq.sub_institute_id = se.sub_institute_id');
                     })
                     ->where('s.sub_institute_id',$sub_institute_id)
                     ->where('se.syear', $syear)
                     ->selectRaw("s.*,se.syear,se.student_id,se.grade_id,concat(s.first_name,' ',s.middle_name,' ',s.last_name) as full_name,
                     se.standard_id,se.section_id,se.student_quota,sq.title AS stu_quota,se.start_date,
                     se.end_date, st.name standard_name, d.name as division_name,s.admission_year")
                     ->where(function ($q) use ($request) {
                         if (isset($request->mobile) && $request->mobile != '') {
                             $q->where('s.mobile', $request->mobile);
                         }
                         if (isset($request->grno) && $request->grno != '') {
                             $q->where('s.enrollment_no', $request->grno);
                         }
                         if (isset($request->uniqueid) && $request->uniqueid != '') {
                             $q->where('s.uniqueid', $request->uniqueid);
                         }
                         if (isset($request->grade) && $request->grade != '') {
                             $q->where('se.grade_id', $request->grade);
                         }
                         if (isset($request->standard) && $request->standard != '') {
                             $q->where('se.standard_id', $request->standard);
                         }
                         if (isset($request->division) && $request->division != '') {
                             $q->where('se.section_id', $request->division);
                         }
                         if (isset($request['stu_name']) && $request['stu_name'] != '') {
                             $q->where(function ($query) use ($request) {
                                 $query->where('s.first_name', 'like', '%' . $request->stu_name . '%')
                                     ->orWhere('s.middle_name', 'like', '%' . $request->stu_name . '%')
                                     ->orWhere('s.last_name', 'like', '%' . $request->stu_name . '%');
                             });
                         }
                         if (isset($request->including_inactive) && $request->including_inactive != '') {
                             if ($request->including_inactive == 'Yes') {
                                 $q->whereNotNull('se.end_date');
                             }
                         } else {
                             $q->whereNull('se.end_date');
                         }
                     })->groupBy('s.id')->get()->toArray();
 
             if (!empty($check)) {
                 if ($check[0]->section_id == null || $check[0]->section_id == 0) {
                     $responce_arr['status_code'] = 0;
                     $responce_arr['message'] = "Devision Not Found";
                 } elseif ($check[0]->student_quota == null || $check[0]->student_quota == 0) {
                     $responce_arr['status_code'] = 0;
                     $responce_arr['message'] = "Student Quota Not Found";
                 } elseif ($check[0]->admission_year == null || $check[0]->admission_year == 0) {
                     $responce_arr['status_code'] = 0;
                     $responce_arr['message'] = "Admission Year Not Found";
                 } elseif ($check[0]->end_date != null) {
                     $responce_arr['status_code'] = 0;
                     $responce_arr['message'] = "Inactive User Not Found";
                 } else {
                     $responce_arr['status_code'] = 0;
                     $responce_arr['message'] = "Fees Breakoff Not Found";
                 }
                 $stud_details=[
                     "Student Name"=>$check[0]->full_name,
                     "Standard"=>$check[0]->standard_name  ,
                     "Division"=>$check[0]->division_name,
                     "Student Quota"=>$check[0]->stu_quota,
                     "Admission Year"=>$check[0]->admission_year,
                 ];
                 $responce_arr['Error_details']=$stud_details;
             } else {
                 $responce_arr['status_code'] = 0;
                 $responce_arr['message'] = "Student Details Not Found";
             }
         }
         $responce_arr['stu_data'] = $result;
 
         $responce_arr['grade_id'] = $request->grade ?? '';
         $responce_arr['standard_id'] = $request->standard ?? '';
         $responce_arr['division_id'] = $request->division ?? '';
 
         return is_mobile($type, "fees/fees_collect/show", $responce_arr, "view");
     }

    /**
     * function is to insert fees data into database by getting details from other functions
     *
     * @param  Request  $request
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return array
     */
    public function pay_fees(Request $request)
    {
        $fees_data = [];
        // $_REQUEST['fees_data'] has month_id,fees_title and amount in array
        foreach ($_REQUEST['fees_data'] as $id => $arr) {
            if ($arr != 0) {
                $fees_data[$id] = $arr;
            }
        }
        $_REQUEST['fees_data'] = $fees_data;

        $stu_arr = session()->get('stu_arr');











        // get all month name with month_id
        $month_arr = FeeMonthId();
        $currunt_month = date('m');
        $currunt_year = date('Y');
        $currunt_month_id = $currunt_month . $currunt_year;

        $search_ids = [];
        foreach ($month_arr as $id => $arr) {
            if ($id == $currunt_month_id) {
                $search_ids[] = $id;
                // break;
            } else {
                $search_ids[] = $id;
            }
        }

        // get fees_breackoff of student from helper.php
        $reg_bk_off = FeeBreackoff($stu_arr);

        // OtherBreackOff is for additional fee from helper.php
        $other_bk_off = OtherBreackOff($stu_arr, $search_ids);

        //  OtherBreackOfMonth get additional fees monthwise from helper.php
        $other_bk_off_month_wise = OtherBreackOfMonth($stu_arr);

        //  OtherBreackOfMonthHead additional fees_title from helper.php
        $other_bk_off_month_head_wise = OtherBreackOfMonthHead($stu_arr, $search_ids);

        //  FeeBreakoffHeadWise get fees_title from helper.php
        $head_wise_fees = FeeBreakoffHeadWise($stu_arr);
        $reg_fee_heads = [];
        $reg_fee_bk = [];
        foreach ($head_wise_fees as $student_id => $detail_arr) {
            $reg_fee_bk = $detail_arr['breakoff'];
            foreach ($detail_arr['breakoff'] as $id => $arr) {
                foreach ($arr as $head_name => $vals) {
                    if (!in_array($head_name, $reg_fee_heads)) {
                        $reg_fee_heads[] = $head_name;
                    }
                }
            }
        }

        $other_fee_heads = [];
        foreach ($_REQUEST['fees_data'] as $id => $vals) {
            if (!in_array($id, $reg_fee_heads)) {
                $other_fee_heads[] = $id;
            }
        }

        //getting reg fee month_id that we need to pay
        $reg_months_pay = [];
        foreach ($reg_fee_bk as $month_id => $arr) {
            if (in_array($month_id, $_REQUEST['months'])) {
                $reg_months_pay[] = $month_id;
            }
        }

        $oth_months_pay = [];
        foreach ($other_bk_off_month_wise as $month_id => $arr) {
            if (in_array($month_id, $_REQUEST['months'])) {
                $oth_months_pay[] = $month_id;
            }
        }

        $reg_insert_arr = [];
        foreach ($reg_fee_bk as $month => $bk_off) {
            if (in_array($month, $reg_months_pay)) {
                foreach ($bk_off as $title => $arr) {
                    if (array_key_exists($title, $_REQUEST['fees_data'])) {
                        $insert_amount = 0;
                        if ($_REQUEST['fees_data'][$title] > $arr['amount']) {
                            $_REQUEST['fees_data'][$title] = $_REQUEST['fees_data'][$title] - $arr['amount'];
                            $insert_amount = $arr['amount'];
                        } else {
                            $insert_amount = $_REQUEST['fees_data'][$title];
                            $_REQUEST['fees_data'][$title] = 0;
                        }
                        $reg_insert_arr[$month][$title] = $insert_amount;
                    }
                }
            }
        }
        $last_syear = (session()->get('syear')-1);
        // last year fees start
        if (isset($_REQUEST['fees_data']['previous_fees']) && $_REQUEST['fees_data']['previous_fees'] != 0) {
            $other_bk_off2 = OtherBreackOff($stu_arr, $search_ids,'','','','',$last_syear); // for previous year
            $other_bk_off_month_wise2 = OtherBreackOfMonth($stu_arr,$last_syear);   // for previous year
            $other_bk_off_month_head_wise2 = OtherBreackOfMonthHead($stu_arr, $search_ids,$last_syear); // for previous year
            $year_arr2 = FeeMonthId($last_syear) ?? []; // for previous year
            $head_wise_fees2 = FeeBreakoffHeadWise($stu_arr,'','','',$last_syear); // for previous year

            $reg_fee_heads2 = [];
            $reg_fee_bk2 = [];

            foreach ($head_wise_fees2 as $student_id => $detail_arr) {
                $reg_fee_bk2 = $detail_arr['breakoff'];
                foreach ($detail_arr['breakoff'] as $id => $arr) {
                    foreach ($arr as $head_name => $vals) {
                        if (!in_array($head_name, $reg_fee_heads2)) {
                            $reg_fee_heads2[] = $head_name;
                        }
                    }
                }
            }

        //getting reg fee month_id that we need to pay
            $syear = session()->get('syear');
            $last_y_month_id = $currunt_month . ($syear - 1);
            $reg_months_pay2 = [];
            foreach ($year_arr2 as $id => $arr) {
                if ($id == $last_y_month_id) {
                    $reg_months_pay2[] = $id;
                // break;
                } else {
                    $reg_months_pay2[] = $id;
                }
            }

            foreach ($reg_fee_bk2 as $month => $bk_off) {
                if (in_array($month, $reg_months_pay2)) {
                    foreach ($bk_off as $title => $arr) {
                        if (array_key_exists($title, $_REQUEST['fees_data'])) {
                            $insert_amount = 0;
                            if ($_REQUEST['fees_data'][$title] < $arr['amount']) {
                                $_REQUEST['fees_data'][$title] = $_REQUEST['fees_data'][$title] - $arr['amount'];
                                $insert_amount = $arr['amount'];
                            } else {
                                $insert_amount = $_REQUEST['fees_data'][$title];
                                $_REQUEST['fees_data'][$title] = 0;
                            }
                            if ($insert_amount != 0) {
                                $reg_insert_arr[$month][$title] = $insert_amount;
                            }
                        }
                    }
                }
            }
            $reg_insert_arr2 = [];
        }
        //get last generated receipt number fees_heads
        $receipt_number = $this->gunrate_receipt_number();
        // getting all heads with id
        $ret_heds_with_id = DB::table('fees_title')->selectRaw('id,fees_title')
            ->where('SUB_INSTITUTE_ID', session()->get('sub_institute_id'))
            ->where('syear', session()->get('syear'))
            ->orderBy('sort_order')->get()->toArray();
        $heds_with_id = [];
        foreach ($ret_heds_with_id as $id => $arr) {
            $heds_with_id[$arr->fees_title] = $arr->id;
        }
        $new_insert_arr = [];
        foreach ($reg_insert_arr as $month_id => $arr) {
            foreach ($arr as $id => $val) {
                $head_id = $heds_with_id[$id];
                foreach ($receipt_number as $temp_id => $arr_head_rid) {
                    $heds = explode(',', $arr_head_rid['heds']);
                    if (in_array($head_id, $heds)) {
                        $receipt_number[$temp_id]['used'] = 1;
                        $new_insert_arr[$month_id][$arr_head_rid['rid'] . '_' . $temp_id][$id] = $val;
                    }
                }
            }
        }

        $oth_insert_arr = [];
        foreach ($other_bk_off_month_head_wise as $month => $bk_off) {
            if (in_array($month, $oth_months_pay)) {

                foreach ($bk_off as $title => $amount) {

                    if (array_key_exists($title, $_REQUEST['fees_data'])) {
                        $insert_amount = 0;
                        if ($_REQUEST['fees_data'][$title] > $amount) {
                            $_REQUEST['fees_data'][$title] = $_REQUEST['fees_data'][$title] - $amount;
                            $insert_amount = $amount;
                        } else {
                            $insert_amount = $_REQUEST['fees_data'][$title];
                            $_REQUEST['fees_data'][$title] = 0;
                        }
                        $oth_insert_arr[$month][$title] = $insert_amount;
                    }
                }
            }
        }

        $new_insert_other_arr = [];
        foreach ($oth_insert_arr as $month_id => $arr) {
            foreach ($arr as $id => $val) {
                $head_id = $heds_with_id[$id];
                foreach ($receipt_number as $temp_id => $arr_head_rid) {
                    $heds = explode(',', $arr_head_rid['heds']);

                    if (in_array($head_id, $heds)) {
                        $receipt_number[$temp_id]['used'] = 1;
                        $new_insert_other_arr[$month_id][$arr_head_rid['rid'] . '_' . $temp_id][$id] = $val;
                    }
                }
            }
        }
        // get discount add while collecting fees in array for fees
        $new_insert_arr = $this->add_discount($new_insert_arr, 'fees_collect');
        // get discount add while collecting fees in array for aditional fees
        $new_insert_other_arr = $this->add_discount($new_insert_other_arr, 'fees_paid_other');
        // get fine add while collecting fees in array for fees
        $new_insert_arr = $this->add_fine($new_insert_arr);
        // get fine add while collecting fees in array for aditional fees
        $new_insert_other_arr = $this->add_fine($new_insert_other_arr);

        $standard_ids = $syears = [];
        foreach ($new_insert_arr as $key => $val) {
            if (array_key_exists($key, $month_arr)) {
                $standard_ids[$key] = $_REQUEST['standard_id'];
                $syears[$key] = session()->get('syear');
            }
            if (isset($year_arr2) && array_key_exists($key, $year_arr2)) {
                // $standard_ids
                $standard_ids[$key] = ($_REQUEST['standard_id'] - 1);
                $syears[$key] = (session()->get('syear') - 1);
            }
        }

        // insert into fees_collect
        $regular_insert_arr=[];
        foreach ($new_insert_arr as $month_id => $arr) {
            foreach ($arr as $r_id => $vals) {
                if (isset($vals['fine']) && $vals['fine'] !== null && $vals['fine'] != 0) {
                    $amount = $vals['amount'];
                    $fine = $vals['fine'];
                    $amount = (int)$amount;
                    $fine = (int)$fine;

                    $totalAmount = $amount + $fine;
                    $vals['amount'] = $totalAmount;
                }

                if (isset($_REQUEST['cheque_date']) && $_REQUEST['cheque_date'] != '') {
                    $cheque_date = $_REQUEST['cheque_date'];
                } else {
                    $cheque_date = $_REQUEST['receiptdate'];
                }

                if (isset($_REQUEST['remarks']) && $_REQUEST['remarks'] != '') {
                    $remarks = $_REQUEST['remarks'];
                } else {
                    $remarks = '';
                }

                $receipt_id_arr = explode('_', $r_id);
                $receipt_id = $receipt_id_arr[0];

                $insert_arr = [
                    'student_id' => $stu_arr[0],
                    'standard_id' => $standard_ids[$month_id] ?? null,
                    'term_id' => $month_id,
                    'syear' => $syears[$month_id],
                    'sub_institute_id' => session()->get('sub_institute_id'),
                    'payment_mode' => $_REQUEST['PAYMENT_MODE'],
                    'created_date' => date('Y-m-d h:i:s'),
                    'bank_branch' => $_REQUEST['bank_branch'],
                    'receiptdate' => $_REQUEST['receiptdate'],
                    'cheque_no' => $_REQUEST['cheque_no'],
                    'cheque_date' => $cheque_date,
                    'cheque_bank_name' => $_REQUEST['bank_name'],
                    'receipt_no' => $receipt_id,
                    'remarks' => $remarks,
                    'created_by' => session()->get('user_id'),
                ];

                $insert_arr = array_merge($insert_arr, $vals);
                $insert_id = DB::table('fees_collect')->insertGetId($insert_arr);
                $regular_insert_arr[] = $insert_id;

            }
        }

        $other_insert_arr = array();
        // insert into fees_paid_other table aditional fees
        foreach ($new_insert_other_arr as $month_id => $arr)
        {
            foreach ($arr as $r_id => $vals)
            {
                if(isset($_REQUEST['cheque_date']) && $_REQUEST['cheque_date'] != ''){
                    $cheque_date = $_REQUEST['cheque_date'];
                }else{
                    $cheque_date = $_REQUEST['receiptdate'];
                }

                if(isset($_REQUEST['remarks']) && $_REQUEST['remarks'] != ''){
                    $remarks = $_REQUEST['remarks'];
                }else{
                    $remarks = '';
                }

                $receipt_id_arr = explode('_', $r_id);
                $receipt_id = $receipt_id_arr[0];
                $insert_arr = array(
                    'student_id' => $stu_arr[0],
                    'month_id' => $month_id,
                    'syear' => session()->get('syear'),
                    'sub_institute_id' => session()->get('sub_institute_id'),
                    'payment_mode' => $_REQUEST['PAYMENT_MODE'],
                    'created_date' => date('Y-m-d h:i:s'),
                    'bank_branch' => $_REQUEST['bank_branch'],
                    'receiptdate' => $_REQUEST['receiptdate'],
                    'cheque_dd_no' => $_REQUEST['cheque_no'],
                    'cheque_dd_date' => $cheque_date,
                    'bank_name' => $_REQUEST['bank_name'],
                    'reciept_id' => $receipt_id,
                    'remarks' => $remarks,
                    'created_by' => session()->get('user_id')
                );

                $insert_arr = $insert_arr + $vals;

                $insert_id = DB::table('fees_paid_other')->insertGetId($insert_arr);
                $other_insert_arr[] = $insert_id;
            }
        }
        //getting array ready for insert into fees receipt
        $fees_receipt_insert = [];
        foreach ($receipt_number as $id => $arr) {
            if (isset($arr['used'])) {
                $fees_receipt_insert['RECEIPT_ID_' . $id] = $arr['rid'];
            }
        }
        $fees_receipt_insert['FEES_ID'] = implode(',', $regular_insert_arr);
        $fees_receipt_insert['OTHER_FEES_ID'] = implode(',', $other_insert_arr);
        $fees_receipt_insert['SYEAR'] = session()->get('syear');
        $fees_receipt_insert['SUB_INSTITUTE_ID'] = session()->get('sub_institute_id');
        $fees_receipt_insert['STANDARD'] = $_REQUEST['standard_id'];
        $fees_receipt_insert['CREATED_ON'] = date('Y-m-d');
        $insert_id = DB::table('fees_receipt')->insertGetId($fees_receipt_insert);
        // get html for receipt from receipt table and insert into tables
        $receipt_html = $this->gunrate_receipt($insert_id, $receipt_number, $heds_with_id);
        $receipt_id_html = '';
        foreach ($receipt_number as $s_order => $val_number) {
            if (isset($val_number['used'])) {
                $receipt_id_html = $val_number['rid'];
            }
        }

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $fees_config = DB::table('fees_config_master as fc')
            ->join('fees_receipt_css as frc', 'frc.receipt_id', '=', 'fc.fees_receipt_template')
            ->selectRaw('fc.* ,frc.css')->where(['fc.sub_institute_id'=>$sub_institute_id,'syear'=>$syear])->get()->toArray();

        $res = [];

        // send recipt to view page
        if (count($fees_config)) {

            $receipt_html_with_css = '<style>' . $fees_config[0]->css . '</style>' . $receipt_html;

            $res = [
                "data" => $receipt_html_with_css,
                "paper" => $fees_config[0]->fees_receipt_template,
                "css" => $fees_config[0]->css,
                "student_id" => $stu_arr[0],
                "receipt_id_html" => $receipt_id_html,
            ];
        } else {
            $fees_config = DB::table('fees_receipt_css')->select('css')
                ->where('frc.receipt_id', 'A5')->get()->toArray();

            $receipt_html_with_css = '<style>' . $fees_config[0]->css . '</style>' . $receipt_html;

            $res = [
                "data" => $receipt_html_with_css,
                "paper" => "A5",
                "css" => $fees_config[0]->css,
                "student_id" => $stu_arr[0],
                "receipt_id_html" => $receipt_id_html,
            ];
        }

        return $res;
    }
    // function is used call pay_fees function to insert fees details
    public function store(Request $request)
    {
        // call pay_fees to pay fees according to freakoff and month
        $res = $this->pay_fees($request);
        $res['standard_id'] = $request->standard_id;
        $type = $request->input('type');
        if(!empty($res) && isset($res['data']) && isset($request->send_sms)){
        // send sms to parent after fees paid
         $res['sms_sent'] = $this->send_sms_to_parents($res);
        }
        return is_mobile($type, "fees/fees_collect/receipt_view", $res, "view");
    }

    // function is used to add discount in tables
    public function add_discount($fees_arr, $insert_table)
    {
        $discount_field = "";
        $total_field = "";
        if ($insert_table == "fees_collect") {
            $discount_field = "fees_discount";
            $total_field = "amount";
        } else {
            $discount_field = "fees_discount";
            $total_field = "actual_amountpaid";
        }

        foreach ($fees_arr as $month_id => $detail_arr) {
            foreach ($detail_arr as $receipt_id => $arr) {
                $sum = array_sum($arr);
                if ($sum == 0) {
                    unset($fees_arr[$month_id][$receipt_id]);
                }
            }
            if (count($fees_arr[$month_id]) == 0) {
                unset($fees_arr[$month_id]);
            }
        }

        /** START If Total Discount is there unset regular discount added on 16th Jun **/
        if (isset($_REQUEST['discount_data']) && isset($_REQUEST['totalDis']) && array_sum($_REQUEST['discount_data']) < $_REQUEST['totalDis']) {
            unset($_REQUEST['discount_data']);
        } else {
            unset($_REQUEST['totalDis']);
        }
        /** END If Total Discount is there unset regular discount added on 16th Jun **/

        foreach ($fees_arr as $month_id => $detail_arr) {
            foreach ($detail_arr as $receipt_id => $arr) {
                $fees_arr[$month_id][$receipt_id][$discount_field] = 0;
                foreach ($arr as $title => $val) {
                    if (isset($_REQUEST['discount_data'][$title])) {
                        $dis = 0;

                        if ($val > $_REQUEST['discount_data'][$title] || $val == $_REQUEST['discount_data'][$title]) {
                            $dis = $_REQUEST['discount_data'][$title];
                            $_REQUEST['discount_data'][$title] = 0;
                            unset($_REQUEST['discount_data'][$title]);
                        } else {
                            // 26/08/2021 Start Added for The Millennium School for Advanced Imprest Collection payment
                            if ($val < 0) {
                                $dis = 0;
                                $_REQUEST['discount_data'][$title] = $_REQUEST['discount_data'][$title] - 0;//$val
                            } else {
                                $dis = $val;
                                $_REQUEST['discount_data'][$title] = $_REQUEST['discount_data'][$title] - $val;
                            }
                            // 26/08/2021 END Added for The Millennium School for Advanced Imprest Collection payment

                        }
                        $fees_arr[$month_id][$receipt_id][$discount_field] = $fees_arr[$month_id][$receipt_id][$discount_field] + $dis;
                    }
                }
            }
        }

        /** START Cumulative Discount code added on 16th Jun **/
        if (isset($_REQUEST['totalDis']) && $_REQUEST['totalDis'] != 0) {
            $newdis = $_REQUEST['totalDis'];
            foreach ($fees_arr as $month_id => $detail_arr) {
                foreach ($detail_arr as $receipt_id => $arr) {
                    $soni_val = array_sum($arr);
                    $fees_arr[$month_id][$receipt_id][$discount_field] = 0;

                    /* START Cumulative Logic for discount */
                    if ($soni_val > $newdis) {
                        $fees_arr[$month_id][$receipt_id][$discount_field] = $newdis;
                        $newdis = 0;
                    } else {
                        $newdis -= $soni_val;
                        $fees_arr[$month_id][$receipt_id][$discount_field] = $soni_val;
                    }
                    /* END Cumulative Logic for discount */
                }
            }
        }
        /** END Cumulative Discount code added on 16th Jun **/

        foreach ($fees_arr as $month_id => $detail_arr) {
            foreach ($detail_arr as $receipt_id => $arr) {
                $sum = 0;
                foreach ($arr as $id => $val) {
                    if ($id != $discount_field) {
                        $sum += $val;
                    } else {
                        $sum -= $val;
                    }
                }
                $fees_arr[$month_id][$receipt_id][$total_field] = $sum;
            }
        }

        return $fees_arr;
    }

    // function is used to add fine in tables
    public function add_fine($fees_arr)
    {
        $discount_field = "";
        $total_field = "";

        $fine_data = $_REQUEST['fine_data'] ?? [];
        foreach ($fine_data as $id => $val) {
            if ($val == 0) {
                unset($fine_data[$id]);
            }
        }

        if (count($fine_data) > 0) {
            foreach ($fees_arr as $month_id => $detail_arr) {
                foreach ($detail_arr as $receipt_id => $arr) {
                    $fees_arr[$month_id][$receipt_id]['fine'] = 0;
                    foreach ($arr as $title => $val) {
                        if (isset($fine_data[$title])) {
                            $fin = $fine_data[$title];
                            if (isset($_REQUEST['hidden_cheque_return_charges'])) {
                                $fin = $fin + $_REQUEST['hidden_cheque_return_charges'];
                            }
                            if (!isset($fees_arr[$month_id][$receipt_id]['fine'])) {
                                $fees_arr[$month_id][$receipt_id]['fine'] = 0;
                            }
                            $fees_arr[$month_id][$receipt_id]['fine'] = $fees_arr[$month_id][$receipt_id]['fine'] + $fin;
                            unset($fine_data[$title]);
                            unset($_REQUEST['hidden_cheque_return_charges']);
                        }
                    }
                }
            }

        } else {

            // 30-12-2021 START for display fine total value in fees receipt if indiviual fine not given
            foreach ($fees_arr as $month_id => $detail_arr) {
                foreach ($detail_arr as $receipt_id => $arr) {
                    $fees_arr[$month_id][$receipt_id]['fine'] = 0;
                    if (isset($_REQUEST['fees_data']['fine'])) {
                        $fees_arr[$month_id][$receipt_id]['fine'] = $_REQUEST['fees_data']['fine'];
                        unset($_REQUEST['fees_data']['fine']);
                    }
                }
            }
            // 30-12-2021 END for display fine total value in fees receipt if indiviual fine not given
        }

        return $fees_arr;
    }

    // function is used to get generate recepit number and to get fees_heads
    public function gunrate_receipt_number()
    {
        $fc_syear = "";
        if (session()->get('sub_institute_id') != 47) {
            $fc_syear = " AND fr.syear = '" . session()->get('syear') . "' ";
        }

        $result = DB::table('fees_receipt_book_master')
            ->selectRaw("fees_receipt_book_master.*,GROUP_CONCAT(fees_receipt_book_master.fees_head_id ORDER BY fees_title.sort_order) heads")
            ->join('fees_title', 'fees_title.id', '=', 'fees_receipt_book_master.fees_head_id')
            ->where('fees_receipt_book_master.grade_id', $_REQUEST['grade_id'])
            ->where('fees_receipt_book_master.standard_id', $_REQUEST['standard_id'])
            ->where('fees_receipt_book_master.syear', session()->get('syear'))
            ->where('fees_receipt_book_master.sub_institute_id', session()->get('sub_institute_id'))
            ->groupBy('fees_receipt_book_master.receipt_line_1', 'fees_receipt_book_master.receipt_line_2', 'fees_receipt_book_master.receipt_line_3', 'fees_receipt_book_master.receipt_line_4', 'fees_receipt_book_master.receipt_prefix', 'fees_receipt_book_master.receipt_logo', 'fees_receipt_book_master.last_receipt_number')
            ->orderBy('fees_title.sort_order')
            ->get()
            ->toArray();

        $id_arr = [];
        foreach ($result as $id => $arr) {

            if (isset($arr->receipt_prefix) && $arr->receipt_prefix != '') {
                $sub_string_count = (strlen($arr->receipt_prefix) + 1);

                $result_id = DB::table('fees_receipt as fr')
                    ->leftJoin('fees_collect as fc', function ($join) use ($arr) {
                        $join->whereRaw("fc.receipt_no = fr.RECEIPT_ID_" . $arr->sort_order . "");
                    })->leftJoin('fees_paid_other as fo', function ($join) use ($arr) {
                        $join->whereRaw("fo.reciept_id = fr.RECEIPT_ID_" . $arr->sort_order . "");
                    })->selectRaw("ifnull(max(cast(fr.RECEIPT_ID_" . $arr->sort_order . " as UNSIGNED))," . $arr->last_receipt_number . ") as rid1,
                        MAX(CAST(SUBSTRING(fr.RECEIPT_ID_" . $arr->sort_order . "," . $sub_string_count . ") AS UNSIGNED)) as rid")
                    ->where('fr.SUB_INSTITUTE_ID', session()->get('sub_institute_id'))
                    ->where(function ($q) {
                        if (session()->get('sub_institute_id') != 47) {
                            $q->where('fr.syear', session()->get('syear'));
                        }
                    })->get()->toArray();

                $rid = $arr->receipt_prefix . ($result_id[0]->rid + 1);

                $id_arr[$arr->sort_order]['heds'] = $arr->heads;
                $id_arr[$arr->sort_order]['rid'] = $rid;
            } else {
                $result_id = DB::table('fees_receipt as fr')
                    ->leftJoin('fees_collect as fc', function ($join) use ($arr) {
                        $join->whereRaw("fc.receipt_no = fr.RECEIPT_ID_" . $arr->sort_order . "");
                    })->leftJoin('fees_paid_other as fo', function ($join) use ($arr) {
                        $join->whereRaw("fo.reciept_id = fr.RECEIPT_ID_" . $arr->sort_order . "");
                    })->selectRaw("ifnull(max(cast(fr.RECEIPT_ID_" . $arr->sort_order . " as UNSIGNED))," . $arr->last_receipt_number . ") as rid")
                    ->where('fr.SUB_INSTITUTE_ID', session()->get('sub_institute_id'))
                    ->where(function ($q) {
                        if (session()->get('sub_institute_id') != 47) {
                            $q->where('fr.syear', session()->get('syear'));
                        }
                    })->get()->toArray();

                $id_arr[$arr->sort_order]['heds'] = $arr->heads;
                $id_arr[$arr->sort_order]['rid'] = $result_id[0]->rid + 1;
            }


        }

        return $id_arr;
    }

    // function is used to genrate fees reciept html and insert into table return back to pay fees
    public function gunrate_receipt($receipt_id, $receipt_arr, $id_heads)
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep',
            10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];
        
        $month_header_name = [];
        $month_name = [];
        
        foreach ($_REQUEST['months'] as $monthId) {
            $month_header = DB::table('fees_month_header')
                ->where('sub_institute_id', session()->get('sub_institute_id'))
                ->where('month_id', $monthId)
                ->first();
        
            if ($month_header) {
                $month_header_name[] = $month_header->header;
            } else {
                $month_header_name[] = 'N/A';
            }
        
            $y = $monthId / 10000;
            $month = (int)$y;
            $year = substr($monthId, -4);
            $month_name[] = $months[$month] . "/" . $year;
        }
        
        $month_header_name = implode(', ', $month_header_name);
        $month_name = implode(', ', $month_name);
        /*echo("<pre>");
print_r($month_header_name);
print_r($month_name);
echo("</pre>");
die;*/

        $fees_paid_name = [];
        //$month_name = substr($month_name, 0, -1); //Rajesh: Jul/2023, Aug/202 

        // config master is added to add month beside fees title if show_month has value 1
        $config_master = DB::table('fees_config_master')->whereRaw('sub_institute_id=' . session()->get('sub_institute_id') . ' and syear=' . session()->get('syear') . ' and show_month !=0')->get()->toArray();

        if (!empty($config_master)) {
            $fees_paid_name = DB::table('fees_collect as fc')
                ->join('fees_receipt as fr', function ($join) {
                    $join->whereRaw('find_in_set(fc.id,fr.FEES_ID)');
                })->selectRaw('fc.term_id,fc.tution_fee,fc.admission_fee,fc.activity_fee,fc.term_fee,fc.deposit,fc.co_curriculam_fees,fc.computer_fees,fc.smart_class,fc.security_charges,fc.photograph,fc.cal_misc,fc.title_1,fc.title_2,fc.title_3,fc.title_4,fc.title_5,fc.title_6,fc.title_7,fc.title_8,fc.title_9,fc.title_10,fc.title_11,fc.title_12')
                ->where('fr.id', $receipt_id)
                ->get()->map(function ($row) {
                    // Filter out the columns that are equal to 0
                    return collect($row)->filter(function ($value, $key) {
                        return $value != 0;
                    })->toArray();
                })->toArray();
        }
        foreach ($fees_paid_name as $id => $arr) {
            $y = $arr['term_id'] / 10000;
            $month = (int)$y;
            $year = substr($arr['term_id'], -4);
            $month_name2 = $months[$month] . ',';
            $fees_paid_name[$id]['term_id'] = substr($month_name2, 0, -1);
        }

        $fees_paid = DB::table('fees_collect as fc')
            ->join('fees_receipt as fr', function ($join) {
                $join->whereRaw('find_in_set(fc.id,fr.FEES_ID)');
            })->selectRaw('fc.*')
            ->where('fr.id', $receipt_id)->get()->toArray();

        $other_fees_paid = DB::table('fees_paid_other as fc')
            ->join('fees_receipt as fr', function ($join) {
                $join->whereRaw('find_in_set(fc.id,fr.OTHER_FEES_ID)');
            })->selectRaw('fc.*')
            ->where('fr.id', $receipt_id)->get()->toArray();

        $ret_heds_with_id = DB::table('fees_title')
            ->where('SUB_INSTITUTE_ID', session()->get('sub_institute_id'))
            ->where('syear', session()->get('syear'))
            ->orderBy('sort_order')
            ->get()->toArray();
        $other_fees_heads = [];
        $reg_fees_heads = [];

        foreach ($fees_paid_name as $index => $data) {
            foreach ($data as $key => $value) {
                foreach ($ret_heds_with_id as $ret_head) {
                    if ($ret_head->fees_title === $key) {
                        $fees_paid_name[$index][$ret_head->display_name] = $value;
                        unset($fees_paid_name[$index][$key]);
                        break;
                    }
                }
            }
        }

        foreach ($ret_heds_with_id as $id => $arr) {
            if ($arr->fees_title_id == '1') {
                $other_fees_heads[] = $arr;
            } else {
                $reg_fees_heads[] = $arr;
            }
        }
        $fees_arr = [];
        $insert_html_ids = [];
        foreach ($receipt_arr as $sort_order => $arr) {
            $heads_arr = explode(',', $arr['heds']);
            $insert_html_ids[$sort_order] = [];
            foreach ($heads_arr as $id => $head_id) {
                $head = "REG";
                foreach ($other_fees_heads as $temp_id => $detail) {
                    if ($detail->id == $head_id) {
                        $head = "OTHER";
                    }
                }
                if ($head == "REG") {
                    $head_name = "";
                    foreach ($id_heads as $ids => $val) {
                        if ($val == $head_id) {
                            $head_name = $ids;
                        }
                    }
                    $total = 0;
                    if ($head_name != "") {
                        $total = 0;

                        foreach ($fees_paid as $ids => $arrs) {
                            if ($arrs->$head_name != null && $arrs->$head_name != '' && $arrs->$head_name != 0) {
                                if (isset($insert_html_ids[$sort_order]['REG'])) {
                                    if (!in_array($arrs->id, $insert_html_ids[$sort_order]['REG'])) {
                                        $insert_html_ids[$sort_order]['REG'][] = $arrs->id;
                                    }
                                } else {
                                    $insert_html_ids[$sort_order]['REG'][] = $arrs->id;
                                }
                            }
                            $total += $arrs->$head_name;
                        }
                    }
                    // finding display name
                    $diplay_name = "";
                    foreach ($reg_fees_heads as $ids => $arrs) {
                        if ($head_id == $arrs->id) {
                            $diplay_name = $arrs->display_name;
                        }
                    }

                    $fees_arr[$arr['rid'] . "_" . $sort_order][$diplay_name] = $total;
                } else {
                    $head_name = "";
                    foreach ($id_heads as $ids => $val) {
                        if ($val == $head_id) {
                            $head_name = $ids;
                        }
                    }
                    $total = 0;
                    if ($head_name != "") {
                        $total = 0;
                        foreach ($other_fees_paid as $ids => $arrs) {
                            if ($arrs->$head_name != null && $arrs->$head_name != '' && $arrs->$head_name != 0) {
                                if (isset($insert_html_ids[$sort_order]['OTHER'])) {
                                    if (!in_array($arrs->id, $insert_html_ids[$sort_order]['OTHER'])) {
                                        $insert_html_ids[$sort_order]['OTHER'][] = $arrs->id;
                                    }
                                } else {
                                    $insert_html_ids[$sort_order]['OTHER'][] = $arrs->id;
                                }
                            }
                            $total += $arrs->$head_name;
                        }
                    }
                    // finding display name
                    $diplay_name = "";
                    foreach ($other_fees_heads as $ids => $arrs) {
                        if ($head_id == $arrs->id) {
                            $diplay_name = $arrs->display_name;
                        }
                    }
                }

                $fees_arr[$arr['rid'] . "_" . $sort_order][$diplay_name] = $total;
            }
        }
        //adding discount in array
        foreach ($insert_html_ids as $sort_order => $arr) {
            $total_discount = 0;
            $total_fine = 0;
            foreach ($arr as $key => $detai_arr) {
                if ($key == 'REG') {
                    $paid_result = DB::table('tblstudent as s')
                        ->join('fees_collect as fc', function ($join) {
                            $join->whereRaw("(fc.student_id = s.id AND fc.sub_institute_id = '" . session()->get('sub_institute_id') . "')");
                        })->selectRaw('SUM(fc.fees_discount) amount,SUM(fc.fine) fine_amount')
                        ->where('s.sub_institute_id', session()->get('sub_institute_id'))
                        ->whereIn('fc.id', $detai_arr)->get()->toArray();
                    $total_discount += $paid_result[0]->amount;
                    $total_fine += $paid_result[0]->fine_amount;
                } else {
                    $paid_result = DB::table('tblstudent as s')
                        ->join('fees_paid_other as fpo', function ($join) {
                            $join->whereRaw("(fpo.student_id = s.id)");
                        })->selectRaw('SUM(fpo.fees_discount) amount,SUM(fpo.fine) fine_amount')
                        ->where('s.sub_institute_id', session()->get('sub_institute_id'))
                        ->whereIn('fpo.id', $detai_arr)->get()->toArray();
                    $total_discount += $paid_result[0]->amount;
                    $total_fine += $paid_result[0]->fine_amount;
                }
            }
            foreach ($fees_arr as $sort_order_id => $arr) {
                $order_id = explode('_', $sort_order_id);
                if ($order_id[1] == $sort_order) {
                    $fees_arr[$sort_order_id]['Fine'] = $total_fine;

                    $fees_arr[$sort_order_id][get_string('discount', 'request')] = $total_discount;
                }
            }
        }

        //fees title or fees head with  month and without month like tution fees (apr)
        $new_fees_arr = [];
        foreach ($fees_arr as $id => $arr) {
            foreach ($arr as $head_id => $amount) {
                if ($amount != 0) {
                    $months = [];
                    foreach ($fees_paid_name as $paid_arr) {
                        if (isset($paid_arr[$head_id])) {
                            $months[] = $paid_arr['term_id'];
                        }
                    }
                    $new_head_id = $head_id;
                    if (!empty($months)) {
                        $new_head_id .= ' (' . implode(',', $months) . ')';
                    }
                    $new_fees_arr[$id][$new_head_id] = $amount;
                }
            }
        }

        foreach ($new_fees_arr as $id => $arr) {
            if (count($arr) == 0) {
                unset($new_fees_arr[$id]);
            }
        }
        $fees_arr = $new_fees_arr;

        // 31/03/2021 - START FOR making cumulative fees recepit array
        $get_cumulative_result = DB::table('fees_title')
            ->selectRaw('id,display_name,cumulative_name,append_name')
            ->where('sub_institute_id', session()->get('sub_institute_id'))
            ->whereNotNull('cumulative_name')
            ->orderBy('sort_order')->get()->toArray();

        $get_cumulative_result = array_map(function ($value) {
            return (array)$value;
        }, $get_cumulative_result);

        $cumulative_arr = $append_arr = array();
        foreach ($get_cumulative_result as $key => $value) {
            $cumulative_arr[$value['display_name']] = $value['cumulative_name'];
            $append_arr[$value['display_name']] = $value['append_name'];
        }
        // 31/03/2021 - END FOR making cumulative fees recepit array

        $result = DB::table('fees_receipt_book_master')
            ->selectRaw('*,GROUP_CONCAT(fees_head_id) heads')
            ->where('grade_id', $_REQUEST['grade_id'])
            ->where('standard_id', $_REQUEST['standard_id'])
            ->where('syear', session()->get('syear'))
            ->where('sub_institute_id', session()->get('sub_institute_id'))
            ->groupByRaw('receipt_line_1,receipt_line_2,receipt_line_3,receipt_line_4,receipt_prefix,receipt_logo,last_receipt_number')
            ->get()->toArray();

        $sub_institute_id = session()->get('sub_institute_id');

        // create fees receipt html to display and insert into fees_collect or fee_paid_other table
        $final_html = "";

        foreach ($fees_arr as $id => $arr) {

            $id_arr = explode('_', $id);
            $RECEIPT_NO = $id_arr[0];
            $sort_order = $id_arr[1];

            $receipt_book_arr = [];
            foreach ($result as $temp_id => $receipt_detail) {
                if ($sort_order == $receipt_detail->sort_order) {
                    $receipt_book_arr = $receipt_detail;
                }
            }

            $image_path1 = "/storage/fees/" . $receipt_book_arr->receipt_logo;
            $image_path = '<img class="logo" src="' . $image_path1 . '" alt="SCHOOL LOGO">';


            $syear1 = session()->get('syear');
            $syear2 = $syear1 + 1;
            $edu_year = "$syear1-$syear2";


            $rwspan = count($fees_arr);
            $recTotal = 0;

            foreach ($arr as $key => $pval) {
                if ($key == 'Discount') {
                    $recTotal = $recTotal - $pval;
                } else {
                    $recTotal = $recTotal + $pval;
                }
            }

            $fees_head_content = '<table class="particulars" width="100%" border="0">
               <tbody><tr>';
               if($sub_institute_id==133){
                $fees_head_content .= '<td style="background-color:white;width:10%"><b>Sr.No.</b></td>';
               }
               $fees_head_content .= '<td colspan="3" style="background-color:white"><b>Particulars</b></td>
                  <td style="background-color:white;white-space:nowrap;"><b>Received (Rs.)</b></td>
               </tr>';

            // 31/03/2021 START for Cumulative Fees Receipt

            if (count($cumulative_arr) > 0) {
                $arrnew = $appendnew = [];
                foreach ($arr as $pkey => $pval) {
                    if (array_key_exists($pkey, $cumulative_arr)) {
                        $newkey = $cumulative_arr[$pkey];

                        if (array_key_exists($newkey, $arrnew)) {
                            $arrnew[$newkey] = $arrnew[$newkey] + $pval;
                            $appendnew[$newkey][] = $append_arr[$pkey];
                        } else {
                            $arrnew[$newkey] = $pval;
                            $appendnew[$newkey][] = $append_arr[$pkey];
                        }
                    } else //for discount ,fines and other types
                    {
                        $arrnew[$pkey] = $pval;
                    }
                }
                $arr = $arrnew;
            }

            // 31/03/2021 END for Cumulative Fees Receipt
            $i=1;
            foreach ($arr as $pkey => $pval) {

                //  31/03/2021 - Start For Cumulative name
                if (isset($appendnew[$pkey])) {
                    $append_name = implode(",", $appendnew[$pkey]);
                    if ($append_name != "") {
                        $pkey .= ' (' . $append_name . ') ';
                    }
                }
                //START Added on 16th june 2021
                if ($pkey == 'Discount') {
                    $minus_sign = "-";
                } else {
                    $minus_sign = "";
                }
                //END Added on 16th june 2021
                $totalCol = '3';
                $fees_head_content .= '<tr>';
                if($sub_institute_id==133){
                    $fees_head_content .= '  <td align="center" style="width:10%">' . $i++ . '</td>';
                    $totalCol = '4';
                }
                $fees_head_content .= '  <td colspan="3" align="left">' . $pkey . '</td>'; //&nbsp;(' . $TERM_SHORT_NAME . ')
                $fees_head_content .= '  <td align="right">' . $minus_sign . $pval . '</td>'; //&nbsp;(' . $TERM_SHORT_NAME . ')
                $fees_head_content .= '</tr>';
            }

            $fees_head_content .= '<tr>
                  <td align="right" colspan="'.$totalCol.'"><b>Total</b></td>
                  <td align="right"><b>&lt;&lt;grand_total&gt;&gt;</b></td>
               </tr>
            </tbody></table>';

            $total_amount_in_words = ucwords($this->convert_number_to_words($recTotal));
            if ($total_amount_in_words != "") {
                $total_amount_in_words_str = $total_amount_in_words . " Only";
            } else {
                $total_amount_in_words_str = "";
            }

            $payMethod = $_REQUEST['PAYMENT_MODE'];
            if ($payMethod == '') {
                $payment_mode = $payMethod;
            }else if ($payMethod == 'Cash') {
                $payment_mode = $payMethod;
            } else {
                $payment_mode = $payMethod . ' ' . strtoupper($_REQUEST['bank_name']) . ' - ' . strtoupper($_REQUEST['bank_branch']) . ' - ' . strtoupper($_REQUEST['cheque_date']) . ' - ' . $_REQUEST['cheque_no'];
            }

            if (isset($_REQUEST['remarks']) && $_REQUEST['remarks'] != '' && $_REQUEST['remarks'] != '-') {
                $discount_remarks = $_REQUEST['remarks'];
            } else {
                $discount_remarks = '';

            }
            // START Dynamic Template Logic
            $tData = DB::table('template_master')
                ->where('module_name', '=', 'Fees')
                ->whereRaw('sub_institute_id = IFNULL((SELECT sub_institute_id FROM template_master WHERE module_name ="Fees" AND
                    sub_institute_id = "' . session()->get('sub_institute_id') . '"),0)')
                ->get()->toArray();

            $tData = json_decode(json_encode($tData), true);

            $father_name = $_REQUEST['father_name'] ?? '-';
            $mother_name = $_REQUEST['mother_name'] ?? '-';
            $medium = $_REQUEST['medium'] ?? '-';
            $standard_short_name = $_REQUEST['standard_short_name'] ?? '-';
            $standard_medium = $_REQUEST['standard_medium'] ?? '-';
            $uniqueid = $_REQUEST['uniqueid'] ?? '-';
            $enrollment = $_REQUEST['enrollment'] ?? '-';
            $roll_no = $_REQUEST['roll_no'] ?? '-';
            $bank_name = $_REQUEST['bank_name'] ?? '-';
            $bank_branch = $_REQUEST['bank_branch'] ?? '-';
            $cheque_dd_ref = $_REQUEST['cheque_no'] ?? '-';
            $cheque_date = date('d-m-Y',strtotime($_REQUEST['cheque_date'])) ?? '-';
            $current_date = date('d-m-Y');
            $payment_mode = $_REQUEST['PAYMENT_MODE'] ?? '-';

            $html_content = $tData[0]['html_content'];

            $html_content = str_replace(htmlspecialchars("<<receipt_logo>>"), $image_path, $html_content);
            if ($receipt_book_arr->receipt_line_1 != '') {
                $html_content = str_replace(
                    htmlspecialchars("<<receipt_line_1>>"),
                    $receipt_book_arr->receipt_line_1,
                    $html_content
                );
            }
            if ($receipt_book_arr->receipt_line_2 != '') {
                $html_content = str_replace(
                    htmlspecialchars("<<receipt_line_2>>"),
                    $receipt_book_arr->receipt_line_2,
                    $html_content
                );
            }
            if ($receipt_book_arr->receipt_line_3 != '') {
                $html_content = str_replace(
                    htmlspecialchars("<<receipt_line_3>>"),
                    $receipt_book_arr->receipt_line_3,
                    $html_content
                );
            }
            if ($receipt_book_arr->receipt_line_4 != '') {
                $html_content = str_replace(
                    htmlspecialchars("<<receipt_line_4>>"),
                    $receipt_book_arr->receipt_line_4,
                    $html_content
                );
            }
            $html_content = str_replace(htmlspecialchars("<<student_board_value>>"), $medium, $html_content);
            $html_content = str_replace(htmlspecialchars("<<admission_number_value>>"), $uniqueid, $html_content);
            $html_content = str_replace(htmlspecialchars("<<receipt_year_value>>"), $edu_year, $html_content);

            $html_content = str_replace(htmlspecialchars("<<receipt_number_value>>"), $RECEIPT_NO, $html_content);
            $html_content = str_replace(htmlspecialchars("<<receipt_date_value>>"),date("d-m-Y", strtotime($_REQUEST['receiptdate'])),$html_content);

            $html_content = str_replace(htmlspecialchars("<<student_name_value>>"),$_REQUEST['full_name'],$html_content);
            $html_content = str_replace(htmlspecialchars("<<student_enrollment_value>>"), $enrollment, $html_content);
            $html_content = str_replace(htmlspecialchars("<<student_roll_value>>"), $roll_no, $html_content);
            $html_content = str_replace(htmlspecialchars("<<student_father_name>>"), $father_name, $html_content);
            $html_content = str_replace(htmlspecialchars("<<student_mother_name>>"), $mother_name, $html_content);
            $html_content = str_replace(htmlspecialchars("<<student_standard_value>>"),$_REQUEST['std_div'],$html_content);
            $html_content = str_replace(htmlspecialchars("<<student_division_value>>"),$_REQUEST['std_div'],$html_content);
            $html_content = str_replace(htmlspecialchars("<<student_mobile_value>>"),$_REQUEST['mobile'],$html_content);
            $html_content = str_replace(htmlspecialchars("<<standard_short_name>>"),$standard_short_name,$html_content);
            $html_content = str_replace(htmlspecialchars("<<standard_medium>>"),$standard_medium,$html_content);
            $html_content = str_replace(htmlspecialchars("<<fees_months_display>>"), $month_name, $html_content);

            $html_content = str_replace(htmlspecialchars("<<fees_month_header>>"), $month_header_name, $html_content);

            $html_content = str_replace(htmlspecialchars("<<fees_head_content>>"), $fees_head_content, $html_content);
            $html_content = str_replace(htmlspecialchars("<<grand_total>>"), $recTotal, $html_content);

            $html_content = str_replace(
                htmlspecialchars("<<total_amount_in_words>>"),
                $total_amount_in_words_str,
                $html_content
            );
            $html_content = str_replace(htmlspecialchars("<<payment_mode>>"), $payment_mode, $html_content);
            $html_content = str_replace(htmlspecialchars("<<discount_remarks>>"), $discount_remarks, $html_content);
            $html_content = str_replace(htmlspecialchars("<<admin_user>>"), session()->get('name'), $html_content);
            $html_content = str_replace(htmlspecialchars("<<bank_name>>"), $bank_name, $html_content);
            $html_content = str_replace(htmlspecialchars("<<bank_branch>>"), $bank_branch, $html_content);
            $html_content = str_replace(htmlspecialchars("<<cheque_dd_ref>>"), $cheque_dd_ref, $html_content);
            $html_content = str_replace(htmlspecialchars("<<cheque_date>>"), $cheque_date, $html_content);
            $html_content = str_replace(htmlspecialchars("<<current_date>>"), $current_date, $html_content);
            $html_content = str_replace(htmlspecialchars("<<payment_mode>>"), $payment_mode, $html_content);

            $recHtml = $html_content;

            $sArr = ["'"];//'"',
            $rArr = ["\'"];//'\"',

            foreach ($insert_html_ids as $sort_order_id => $other_reg) {
                if ($sort_order == $sort_order_id) {
                    foreach ($other_reg as $identifiyer => $vals) {
                        if ($identifiyer == "OTHER") {
                            DB::table('fees_paid_other')
                                ->whereIn('id', $vals)
                                ->update([
                                    'paid_fees_html' => str_replace($sArr, $rArr, $recHtml),
                                ]);
                        } else {
                            DB::table('fees_collect')
                                ->whereIn('id', $vals)
                                ->update([
                                    'fees_html' => str_replace($sArr, $rArr, $recHtml),
                                ]);
                        }
                    }
                }
            }
            $final_html .= $recHtml;
        }
       
        return $final_html;
    }

    public function convert_number_to_words($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );

            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public function get_receipt_id()
    {
        $result = DB::table('fees_collect')
            ->selectRaw('ifnull(max(receipt_no),1)+1 as maxid')
            ->where('sub_institute_id', session()->get('sub_institute_id'))->get()->toArray();

        return $result[0]->maxid;
    }

    /**
     * function is used to get fees details of particular student paid or remain
     *
     * @param  Request  $request
     * @return false|string|JsonResponse
     */

    public function PaidUnpaid(Request $request)
    {
        // for api chek for token first
        try {
            if (!$this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 200);
        }
        $response = ['response' => '', 'status' => '0', 'message' => 'Data Not Found.'];
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|numeric',
            'sub_institute_id' => 'required|numeric',
            'syear' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
        } else {
            //process the request
            $sub_institute_id = $_REQUEST['sub_institute_id'];
            $student_id = $_REQUEST['student_id'];
            $syear = $_REQUEST['syear'];

            $data = map_year::where([
                'sub_institute_id' => $sub_institute_id,
                'syear' => $syear,
            ])->get()->toArray();

            if (!$data) {
                $response['response'] = ["year_error" => ["Maping Year Error."]];
                return $response;
            }

            $start_month = $data[0]['from_month'];
            $end_month = $data[0]['to_month'];

            $months = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
            ];
            $months_arr = [];

            for ($i = 1; $i <= 12; $i++) {
                $months_arr[$start_month . $syear] = $months[$start_month] . '/' . $syear;
                if ($start_month == 12) {
                    $start_month = 0;
                    ++$syear;
                }
                ++$start_month;
            }
            $month_arr = $months_arr;
            $responce_arr = [];

            $currunt_month = date('m');
            $currunt_year = date('Y');
            $currunt_month_id = $currunt_month . $currunt_year;

            $search_ids = [];
            foreach ($month_arr as $id => $arr) {
                if ($id == $currunt_month_id) {
                    $search_ids[] = $id;
                } else {
                    $search_ids[] = $id;
                }
            }

            $breackoff_join = "";
            $breackoff_other_join = "";
            $fees_join = "";
            $paid_other_join = "";
            foreach ($search_ids as $id => $val) {
                if ($id == 0) {
                    $breackoff_join .= " AND (";
                    $breackoff_other_join .= " AND (";
                    $fees_join .= " AND (";
                    $paid_other_join .= " AND (";
                }
                if (count($search_ids) == ($id + 1)) {
                    $breackoff_join .= "fb.month_id = $val)";
                    $breackoff_other_join .= "fbo.month_id = $val)";
                    $fees_join .= "fc.term_id = $val)";
                    $paid_other_join .= "fpo.month_id = $val)";
                } else {
                    $breackoff_join .= "fb.month_id = $val OR ";
                    $breackoff_other_join .= "fbo.month_id = $val OR ";
                    $fees_join .= "fc.term_id = $val OR ";
                    $paid_other_join .= "fpo.month_id = $val OR ";
                }
            }

            $requestData = $_REQUEST;

            $result = DB::table('tblstudent as s')
                ->join('tblstudent_enrollment as se','se.student_id','=','s.id')
                ->join('academic_section as g', 'g.id','=','se.grade_id')
                ->join('standard as st', 'st.id','=','se.standard_id')
                ->Join('division as d','d.id','=','se.section_id')
                ->join('fees_breackoff as fb', function ($join) use ($breackoff_join, $requestData) {
                    $join->whereRaw("(fb.syear = '" . $requestData['syear'] . "' AND
                 fb.admission_year = s.admission_year AND fb.quota = se.student_quota AND fb.grade_id = se.grade_id AND
                 fb.standard_id = se.standard_id AND fb.sub_institute_id = '" . session()->get('sub_institute_id') . "' $breackoff_join)");
                })->selectRaw("s.*,se.syear,se.student_id,se.grade_id,
                    se.standard_id,se.section_id,se.student_quota,se.start_date,
                    se.end_date,se.enrollment_code,se.drop_code,se.drop_remarks,
                    se.drop_remarks,se.term_id,se.remarks,se.admission_fees,
                    se.house_id,se.lc_number,
                    sum(fb.amount)+ (select ifnull(sum(fbo.amount),0) from fees_breakoff_other fbo where fbo.syear = '" . $_REQUEST['syear'] . "'
                    AND fbo.student_id = s.id AND fb.standard_id = se.standard_id AND fbo.sub_institute_id = '" . $sub_institute_id . "' )
                    bkoff,st.name standard_name, d.name as division_name")
                ->where('s.sub_institute_id', $sub_institute_id)
                ->where('se.syear', $requestData['syear'])
                ->where(function ($q) use ($requestData) {
                    if (isset($requestData['student_id']) && $requestData['student_id'] != '') {
                        $q->where('s.id', $requestData['student_id']);
                    }
                })->groupBy('s.id')->havingNotNull('bkoff')->get()->toArray();

            if (!$result) {
                $response['response'] = ["bf_error" => ["No Breackoff Found."]];

                return $response;
            }

           // code convert - 10-07-23

            $paid_result = DB::table(function ($query) use ($sub_institute_id, $student_id, $fees_join, $paid_other_join) {
                $query->selectRaw('SUM(amount) as paid_amt, student_id as id')
                    ->from(function ($subQuery) use ($sub_institute_id, $student_id, $fees_join) {
                        $subQuery->selectRaw('SUM(fc.amount) + SUM(fc.fees_discount) as amount, se.student_id')
                            ->from('tblstudent as s')
                            ->join('tblstudent_enrollment as se', 'se.student_id', '=', 's.id')
                            ->join('academic_section as g', 'g.id', '=', 'se.grade_id')
                            ->join('standard as st', 'se.student_id', '=', 'st.id')
                            ->leftJoin('division as d', 'd.id', '=', 'se.section_id')
                            ->join('fees_collect as fc', function ($join) use ($sub_institute_id) {
                                $join->on('fc.student_id', '=', 's.id')
                                    ->where('fc.is_deleted', '=', 'N')
                                    ->where('fc.sub_institute_id', '=', $sub_institute_id);
                            })
                            ->where('s.sub_institute_id', '=', $sub_institute_id)
                            ->where('s.id', '=', $student_id)
                            ->groupBy('s.id');

                        if (!empty($fees_join)) {
                            $subQuery->whereRaw($fees_join);
                        }
                    }, 'temp_table')
                    ->unionAll(function ($subQuery) use ($sub_institute_id, $student_id, $paid_other_join) {
                        $subQuery->selectRaw('SUM(fpo.actual_amountpaid) + SUM(fpo.fees_discount) as aa, se.student_id')
                            ->from('tblstudent as s')
                            ->join('tblstudent_enrollment as se', 'se.student_id', '=', 's.id')
                            ->join('academic_section as g', 'g.id', '=', 'se.grade_id')
                            ->join('standard as st','se.student_id', '=', 'st.id')
                            ->Join('division as d', 'd.id', '=', 'se.section_id')
                            ->join('fees_paid_other as fpo', 'fpo.student_id', '=', 's.id')
                            ->where('s.sub_institute_id', '=', $sub_institute_id)
                            ->where('s.id', '=', $student_id)
                            ->groupBy('s.id');

                        if (!empty($paid_other_join)) {
                            $subQuery->whereRaw($paid_other_join);
                        }
                    })
                    ->groupBy('student_id');
            })
                ->get();

            $return_data = [
                "student_id" => $student_id,
            ];

            $return_data['breack_off_amount'] = $result[0]->bkoff;
            if ($paid_result) {
                $return_data['paid_amount'] = $paid_result[0]->paid_amt;
            } else {
                $return_data['paid_amount'] = 0;
            }
            $return_data['unpaid_amount'] = $return_data['breack_off_amount'] - $return_data['paid_amount'];

            $response['response'] = $return_data;
            $response['message'] = "Sucsess";
            $response['status'] = '1';
        }

        return json_encode($response);
    }

    // function is used to get data for online fees payment

    public function getOnlinebk(Request $request, $sub_institute_id, $syear, $student_id)
    {
        $request->session()->put('sub_institute_id', $sub_institute_id);
        $request->session()->put('syear', $syear);
        $request->session()->put('student_id', $student_id);

        return $this->getBk($request, $student_id);
    }

    // function is used into getBk function to get data according to syear
    public function get_syear_data($syear,$sub_institute_id,$student_id, $fees_join, $paid_other_join,$std='',$marking_period_id=''){
        $termIdQuery = DB::table(function ($query) use ($syear,$sub_institute_id, $student_id, $fees_join, $paid_other_join,$std,$marking_period_id) {
            $query->selectRaw('SUM(fc.amount) + SUM(fc.fees_discount) as amount, fc.term_id,se.standard_id')
                ->from('tblstudent as s')
                ->join('tblstudent_enrollment as se',function($join) use($syear){
                    $join->on('se.student_id','=','s.id')->where('syear',$syear);
                })
                ->join('fees_collect as fc', function ($join) use ($sub_institute_id) {
                    $join->on('fc.student_id', '=', 's.id')
                        ->where('fc.is_deleted', '=', 'N')
                        ->where('fc.sub_institute_id', '=', $sub_institute_id);
                })
                ->join('standard as st', function($join) use($marking_period_id) {
                    $join->on('st.id', '=', 'se.standard_id')
                    ->when($marking_period_id,function($query) use($marking_period_id){ // added on 03-03-2025
                        $query->where('st.marking_period_id',$marking_period_id);
                    });
                })
                ->where('s.sub_institute_id', '=', $sub_institute_id)
                ->where('s.id', '=', $student_id)
                ->where('fc.syear', '=', $syear)
                ->where('fc.is_deleted', '=',"N")
                ->when($std!='',function($q) use($std){
                    $q->where('se.standard_id',$std);
                })
                ->groupBy('s.id', 'fc.term_id','se.standard_id');
            if (!empty($fees_join)) {
                $query->whereRaw($fees_join);
            }

            $query->unionAll(function ($subQuery) use ($syear,$sub_institute_id, $student_id, $paid_other_join,$std,$marking_period_id) {
                $subQuery->selectRaw('SUM(fpo.actual_amountpaid) + SUM(fpo.fees_discount) as amount, fpo.month_id,se.standard_id')
                    ->from('tblstudent as s')
                    ->join('tblstudent_enrollment as se',function($join) use($syear){
                        $join->on('se.student_id','=','s.id')->where('syear',$syear);
                    })
                    ->join('standard as st', function($join) use($marking_period_id) {
                        $join->on('st.id', '=', 'se.standard_id')
                        ->when($marking_period_id,function($query) use($marking_period_id){ // added on 03-03-2025
                            $query->where('st.marking_period_id',$marking_period_id);
                        });
                    })
                    ->join('fees_paid_other as fpo', function ($join) {
                        $join->on('fpo.student_id', '=', 's.id');
                    })
                    ->where('s.sub_institute_id', '=', $sub_institute_id)
                    ->where('s.id', '=', $student_id)
                    ->where('fpo.syear', '=',$syear)
                    ->where('fpo.is_deleted', '=',"N")
                    ->when($std!='',function($q) use($std){
                        $q->where('se.standard_id',$std);
                    })
                    ->groupBy('s.id', 'fpo.month_id','se.standard_id');

                if (!empty($paid_other_join)) {
                    $subQuery->whereRaw($paid_other_join);
                }
            });
        }, 'temp_table')
            ->selectRaw('SUM(amount) as amount, term_id')
            ->groupBy('term_id','standard_id');

        $paid_result = $termIdQuery->selectRaw('SUM(amount) as amount, term_id')
            ->groupBy('term_id')
            ->get();
            return $paid_result;
    }

    // function is used to get fees BREAKOFF of particular student students
    public function getBk(Request $request, $id,$std='')
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $last_syear = (session()->get('syear') - 1);
        $marking_period_id = session()->get('term_id'); // 03-03-2025 added by uma

        $record = DB::table('standard')
            ->select('id', 'marking_period_id')
            ->where('sub_institute_id', $sub_institute_id)
            ->where('next_standard_id', $std)
            ->first();

        $last_std = $record?->id ?? null;
        $last_marking_period_id = $record?->marking_period_id ?? null;
        
        $stu_arr = [
            "0" => $id,
        ];

        session(['stu_arr' => $stu_arr]);

        $student_id = $id;

        // get current syear month_id with name and month_id
        $month_arr = FeeMonthId();

        // get last syear month_id with name and month_id
        $month_arr2 = FeeMonthId($last_syear);
        $currunt_month = date('m');
        $currunt_year = date('Y');
        $currunt_month_id = $currunt_month . $currunt_year;
        $last_y_month_id = $currunt_month . ($syear - 1);

        $search_ids = [];
        foreach ($month_arr as $id => $arr) {
            if ($id == $currunt_month_id) {
                $search_ids[] = $id;
                // break;
            } else {
                $search_ids[] = $id;
            }
        }

        foreach ($month_arr2 as $id => $arr) {
            if ($id == $last_y_month_id) {
                $search_ids[] = $id;
                // break;
            } else {
                $search_ids[] = $id;
            }
        }

        $fees_join = "";
        $paid_other_join = "";

        foreach ($search_ids as $id => $val) {
            if ($id == 0) {
                $fees_join .= "  (";
                $paid_other_join .= " (";
            }
            if (count($search_ids) == ($id + 1)) {
                $fees_join .= "fc.term_id = $val)";
                $paid_other_join .= "fpo.month_id = $val)";
            } else {
                $fees_join .= "fc.term_id = $val OR ";
                $paid_other_join .= "fpo.month_id = $val OR ";
            }
        }

        // get student data according to syear and conditions
       $paid_result = $this->get_syear_data($syear,$sub_institute_id,$student_id, $fees_join, $paid_other_join,$std,$marking_period_id);
       $paid_result2 = $this->get_syear_data($last_syear,$sub_institute_id,$student_id, $fees_join, $paid_other_join,$last_std,$last_marking_period_id);

        $fees_paid_arr = [];
        foreach ($paid_result as $id => $arr) {
            $fees_paid_arr[$arr->term_id] = $arr->amount;
        }

        $fees_paid_arr2 = [];
        foreach ($paid_result2 as $id => $arr) {
            $fees_paid_arr2[$arr->term_id] = $arr->amount;
        }
        //echo "lastyear-".$last_syear."###marking-".$marking_period_id;exit;
        // get fees breakoff of all years
        $reg_bk_off = FeeBreackoff($stu_arr, $std,$syear,$marking_period_id); //for current year
        $reg_bk_off2 = FeeBreackoff($stu_arr, $last_std,$last_syear,$last_marking_period_id); // for previous year
//echo "<pre>";print_r($reg_bk_off2);exit;
        $reg_bk_off_count = is_array($reg_bk_off) ? count($reg_bk_off) : $reg_bk_off->count();

        if (count($reg_bk_off) == 0) {
            return [];
        }
        // get aditional fees breakoff of all years
        $other_bk_off = OtherBreackOff($stu_arr, $search_ids);//for current year
        $other_bk_off_month_wise = OtherBreackOfMonth($stu_arr);//for current year

        $year_arr = FeeMonthId();//for current year

        $reg_bk_month_wise = $reg_bk_month_wise2 = [];
        foreach ($reg_bk_off as $id => $arr) {
            $reg_bk_month_wise[$arr->month_id] = $arr->bkoff;
        }

        $new_month_arr = [];
        $new_month_arr2 = [];
        foreach ($reg_bk_month_wise as $month_id => $val) {
            if(isset($month_arr[$month_id])){
                $new_month_arr[$month_id] = $month_arr[$month_id];
            }
        }
        foreach ($other_bk_off_month_wise as $month_id => $val) {
            if(isset($month_arr[$month_id])){
                $new_month_arr[$month_id] = $month_arr[$month_id];
            }
        }

        $merge_bk_month_wise = [];
        foreach ($reg_bk_month_wise as $month_id => $amount) {
            $merge_bk_month_wise[$month_id] = $amount;
            foreach ($other_bk_off_month_wise as $MonthId => $amt) {
                if ($month_id == $MonthId) {
                    $merge_bk_month_wise[$month_id] += $amt;
                }
            }
        }

        $left_bk_table = $this_month = $last_month = $left_bk_table2 = [];
        $i = 1;
        $last_fees = 0;
        $fees_total = $fees_total_last = 0;
        $paid_total = $paid_total_last = 0;
        $remain_total = $remain_total_last = 0;
        // this foreach will create fees structure of student paid unpaid fees
        foreach ($merge_bk_month_wise as $id => $val) {
            if(isset($year_arr[$id])){
            $left_bk_table[$i]['month'] = $year_arr[$id];
            $left_bk_table[$i]['month_this'] = substr($year_arr[$id], 0, 3);
            $this_month[] = $left_bk_table[$i]['month_this'];
            $left_bk_table[$i]['month_id'] = $id;
            $left_bk_table[$i]['bk'] = $val;
            if (isset($fees_paid_arr[$id]) && $fees_paid_arr[$id] > 0) {
                $left_bk_table[$i]['paid'] = $fees_paid_arr[$id];
            } else {
                $left_bk_table[$i]['paid'] = 0;
            }
            if ($left_bk_table[$i]['paid'] > $left_bk_table[$i]['bk']) {
                $left_bk_table[$i]['remain'] = 0;
            } else {
                $left_bk_table[$i]['remain'] = $left_bk_table[$i]['bk'] - $left_bk_table[$i]['paid'];
            }

            $fees_total = $fees_total + $left_bk_table[$i]['bk'];
            $paid_total = $paid_total + $left_bk_table[$i]['paid'];
            $remain_total = $remain_total + $left_bk_table[$i]['remain'];
            $i = $i + 1;
        }
        }
        $pending_fees = 0;

        foreach ($search_ids as $id => $val) {
            foreach ($left_bk_table as $temp_id => $arr) {
                if ($arr['month_id'] == $val) {
                    $pending_fees = $pending_fees + $arr['remain'];
                }
            }
        }
        if (isset($reg_bk_off2) && $reg_bk_off2 != null) {
            $reg_bk_off_count2 = is_array($reg_bk_off2) ? count($reg_bk_off2) : $reg_bk_off2->count();
            if (count($reg_bk_off2) == 0) {
                return [];
            }
            $other_bk_off2 = OtherBreackOff($stu_arr, $search_ids,'','','',$last_syear); //for previous year
            foreach ($reg_bk_off2 as $id => $arr) {
                $reg_bk_month_wise2[$arr->month_id] = $arr->bkoff;
            }
            foreach ($reg_bk_month_wise2 as $month_id2 => $val) {
                $new_month_arr2[$month_id2] = $month_arr2[$month_id2];
            }
            $merge_bk_month_wise2 = [];
            foreach ($reg_bk_month_wise2 as $month_id => $amount) {
                $merge_bk_month_wise2[$month_id] = $amount;
                foreach ($other_bk_off_month_wise as $MonthId => $amt) {
                    if ($month_id == $MonthId) {
                        $merge_bk_month_wise2[$month_id] += $amt;
                    }
                }
            }

        }

        $pending_fees = 0;
        foreach ($search_ids as $id => $val) {
            foreach ($left_bk_table as $temp_id => $arr) {
                if ($arr['month_id'] == $val) {
                    $pending_fees = $pending_fees + $arr['remain'];
                }
            }
        }

        $syear = session()->get('syear');
        $prviouse_syear = $syear - 1;

        $get_imprest_sql = DB::table('fees_breakoff_other as fb')
            ->join('fees_title as ft', function ($join) {
                $join->whereRaw("ft.fees_title = fb.fee_type_id AND ft.sub_institute_id = fb.sub_institute_id
                    AND ft.syear = '" . session()->get('syear') . "'");
            })
            ->selectRaw("fb.id,fb.student_id,fb.sub_institute_id,IFNULL(fb.amount,0) as previous_imprest_amt,fb.syear,
                ft.fees_title,ft.display_name ")
            ->where('fb.sub_institute_id', session()->get('sub_institute_id'))
            ->where('fb.syear', $prviouse_syear)
            ->where('ft.display_name', 'LIKE', '%Imprest%')
            ->where('fb.student_id', $reg_bk_off[0]->student_id)
            ->orderBy('ft.sort_order')->get()->toArray();

        $get_imprest_balance = json_decode(json_encode($get_imprest_sql), true);

        if (count($get_imprest_balance) > 0) {
            $previous_year_imprest_balance = $get_imprest_balance[0]['previous_imprest_amt'];
        } else {
            $previous_year_imprest_balance = 0;
        }

        // End Getting previous year imprest balance for The Millennium School Surat

        $stu_detail = [
            "student_id" => $reg_bk_off[0]->student_id,
            "enrollment" => $reg_bk_off[0]->enrollment_no,
            "roll_no" => $reg_bk_off[0]->roll_no,
            "name" => $reg_bk_off[0]->first_name . " " . $reg_bk_off[0]->middle_name . " " . $reg_bk_off[0]->last_name,
            "stddiv" => $reg_bk_off[0]->standard_name . "/" . $reg_bk_off[0]->division_name,
            "admission" => $reg_bk_off[0]->admission_year,
            "email" => $reg_bk_off[0]->email,
            "medium" => $reg_bk_off[0]->medium,
            "standard_short_name" => $reg_bk_off[0]->standard_short_name,
            "standard_medium" => $reg_bk_off[0]->standard_medium,
            "father_name" => $reg_bk_off[0]->father_name,
            "mother_name" => $reg_bk_off[0]->mother_name,
            "pending" => $pending_fees,
            "previous_fees"=>0,
            "mobile" => $reg_bk_off[0]->mobile,
            "uniqueid" => $reg_bk_off[0]->uniqueid,
            "std_id" => $reg_bk_off[0]->standard_id,
            "grade_id" => $reg_bk_off[0]->grade_id,
            "div_id" => $reg_bk_off[0]->section_id,
            "student_quota" => $reg_bk_off[0]->stu_quota,
            "previous_year_imprest_balance" => $previous_year_imprest_balance,
        ];

        // get fees breakoff according to fees titile from hrlper.php
        $head_wise_fees = FeeBreakoffHeadWise($stu_arr,'','','','','',$marking_period_id); //for current year
        $head_wise_fees2 = FeeBreakoffHeadWise($stu_arr,'','','',$last_syear,'',$last_marking_period_id); //for previous year

        $till_now_breckoff = $till_now_breckoff2 = [];
        foreach ($search_ids as $id => $val) {
            foreach ($head_wise_fees as $temp_id => $arr) {
                foreach ($head_wise_fees[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff[$month_id] = $fees_detail;
                    }
                }
            }

            foreach ($head_wise_fees2 as $temp_id => $arr) {
                foreach ($head_wise_fees2[$temp_id]['breakoff'] as $month_id => $fees_detail) {
                    if ($month_id == $val) {
                        $till_now_breckoff2[$month_id] = $fees_detail;
                    }
                }
            }
        }

        $reg_bk_month_wise = $reg_bk_month_wise2 = [];
        $reg_month_wise = $reg_month_wise2 = array();
        $final_bk_name = [];
        $total = 0;

        foreach ($till_now_breckoff as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if (!isset($reg_bk_month_wise[$arr['title']])) {
                    $reg_bk_month_wise[$arr['title']] = 0;
                    $reg_month_wise[$arr['title']] = [
                        'title' => $arr['title'],
                        'amount' => 0,
                        'mandatory' => $arr['mandatory'],
                    ];
                }
                if (isset($arr['amount'])) {
                    $reg_bk_month_wise[$arr['title']] += $arr['amount'];
                    $reg_month_wise[$arr['title']] = [
                        'title' => $arr['title'],
                        'amount' => $reg_bk_month_wise[$arr['title']],
                        'mandatory' => $arr['mandatory'],
                    ];
                }
                $final_bk_name[$arr['title']] = $head_name;
            }
        }

        foreach ($till_now_breckoff2 as $month_id => $fees_detail) {
            foreach ($fees_detail as $head_name => $arr) {
                if (!isset($reg_bk_month_wise2[$arr['title']])) {
                    $reg_bk_month_wise2[$arr['title']] = 0;
                    $reg_month_wise2[$arr['title']] = [
                        'title' => $arr['title'],
                        'amount' => 0,
                        'mandatory' => $arr['mandatory'],
                    ];
                }
                if (isset($arr['amount'])) {
                    $reg_bk_month_wise2[$arr['title']] += $arr['amount'];
                    $reg_month_wise2[$arr['title']] = [
                        'title' => $arr['title'],
                        'amount' => $reg_bk_month_wise2[$arr['title']],
                        'mandatory' => $arr['mandatory'],
                    ];
                }
                $final_bk_name[$arr['title']] = $head_name;
            }
        }

        $full_bk = array_merge($reg_bk_month_wise2, $other_bk_off);
        $full_bk_new = array_merge($reg_month_wise, $other_bk_off);
        if (isset($reg_bk_off2) && !empty($reg_bk_off2)) {

            $full_bk2 = array_merge($reg_bk_month_wise2, $other_bk_off2);
            $full_bk_new2 = array_merge($reg_month_wise2, $other_bk_off2);
            //echo "<pre>";print_r($full_bk2);exit;
            $previous = array_sum($full_bk2);

            if($previous > 0){
                $full_bk['Previous Fees'] = $previous;
                $stu_detail['previous_fees'] = $previous;
                $full_bk_new['Previous Fees'] = array(
                    'title' => 'Previous Fees',
                    'amount' => $previous,
                    'mandatory' => 1,
                );
            }
        }
     //24-04-2021 START Check Cheque Return charges

        $get_cheque_return_amt = SchoolModel::where(['id' => $sub_institute_id])->get()->toArray();
        $cheque_return_charges = $get_cheque_return_amt[0]['cheque_return_charges'];

        $cheque_return_exist_RET = DB::table('fees_collect as fc')
            ->join('fees_cancel as f', function ($join) {
                $join->whereRaw('f.reciept_id = fc.receipt_no AND f.student_id = fc.student_id
                    AND f.sub_institute_id = fc.sub_institute_id AND f.syear = fc.syear');
            })
            ->selectRaw("fc.id,fc.student_id,fc.sub_institute_id,fc.syear,fc.receipt_no,fc.is_deleted,
                f.id AS fees_cancel_id,f.cancel_type,f.cancel_remark,f.cancel_date,f.received_date")
            ->where('fc.syear', $syear)
            ->where('fc.sub_institute_id', $sub_institute_id)
            ->where('fc.student_id', $stu_detail['student_id'])
            ->where('is_deleted', '=', 'Y')
            ->where('f.cancel_type', '=', 'Cheque Return')
            ->orderBy('fc.id', 'DESC')->limit(1)->get()->toArray();
        $cheque_return_exist = count($cheque_return_exist_RET);

        // 06/01/2022 SQL for checking if cheque return charges already paid
        $check_paid_cheque_return_charge = DB::table('fees_collect as f')
            ->whereRaw("f.receipt_no > CAST((SELECT fc.reciept_id FROM fees_cancel fc WHERE fc.syear = '" . $syear . "' AND
                fc.sub_institute_id = '" . $sub_institute_id . "' AND fc.student_id = '" . $stu_detail['student_id'] . "' AND
                fc.cancel_type = 'Cheque Return' ORDER BY id DESC LIMIT 0,1) AS UNSIGNED)")
            ->where('f.syear', $syear)
            ->where('f.sub_institute_id', $sub_institute_id)
            ->where('f.student_id', $stu_detail['student_id'])
            ->where('f.student_id', $stu_detail['student_id'])
            ->where('f.is_deleted', '=', 'N')->get()->toArray();

        if ($cheque_return_charges > 0 && $cheque_return_exist > 0 && count($check_paid_cheque_return_charge) == 0) {
            $cheque_return_charges_new[] = $cheque_return_charges;
        } else {
            $cheque_return_charges_new[] = 0;
        }

        //24-04-2021 END Check Cheque Return charges

        foreach ($full_bk as $id => $val) {
            $total += $val;
        }
        // get breakoff with aditional fees title
        $other_fee_title = OtherBreackOffHead(); //for current year
        foreach ($other_fee_title as $id => $arr) {
            foreach ($full_bk as $title => $val) {
                if ($title == $arr->display_name) {
                    $final_bk_name[$title] = $arr->other_fee_id;
                }

            }
            if (isset($reg_bk_off2) && !empty($reg_bk_off2)) {

                if ($previous > 0) {
                    $final_bk_name["Previous Fees"] = "previous_fees";
                }
            }
        }



        $full_bk["Total"] = $total;
        $full_bk_new["Total"] = $total;

        $type = "web";
        $res['total_fees'] = $left_bk_table ?? [];
        $res['stu_data'] = $stu_detail;
        $res['month_arr'] = $new_month_arr;
        $res['search_ids'] = $search_ids;
        $res['final_fee'] = $full_bk;
        //echo "RAJ";$previous;exit;
        if (isset($reg_bk_off2) && !empty($reg_bk_off2)) {

            $res['previous_fees'] = array('Previous Fees' => $previous);
        }
        $res['final_fee_new'] = $full_bk_new;
        $res['cheque_return_charges'] = $cheque_return_charges_new;
        $res['final_fee_name'] = $final_bk_name;
        $res['search_id'] = $search_ids;

        $fees_config = DB::table('fees_config_master as fc')
            ->join('fees_receipt_css as frc', function ($join) {
                $join->whereRaw('frc.receipt_id = fc.fees_receipt_template');
            })->selectRaw("fc.* ,frc.css")
            ->where('fc.sub_institute_id', $sub_institute_id)
            ->where('fc.syear', $syear)->get()->toArray();

            $late_fees_amount = $fees_config[0]->late_fees_amount;
        if (count($fees_config) > 0) {
            $receipt_css = $fees_config[0]->css;
            $paper_size = $fees_config[0]->fees_receipt_template;
        } else {
            $fees_config = DB::table('fees_receipt_css')->select('css')
                ->where('receipt_id', 'A5')->get()->toArray();
            $receipt_css = $fees_config[0]->css;
            $paper_size = 'A5';
        }

        $res['receipt_css_data'] = $receipt_css;
        $res['paper_size'] = $paper_size;
        //echo "<pre>";print_r($res);exit;
        return $res;
    }

    // function is used to get data of collected fees into fees_collect edit blade
    public function edit($id, Request $request)
    {
        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $res = $this->getBk($request, $id,$request->std);
        // echo "<pre>";print_r($res);exit;
        $res['bank_data'] = bankmasterModel::get()->toArray();
        $res['fees_config_data'] = tblfeesConfigModel::where([
            'sub_institute_id' => $sub_institute_id, 'syear' => $syear,
        ])->get()->toArray();
        if (count($res['fees_config_data']) > 0) {
            $res['fees_config_data'] = $res['fees_config_data'][0];
            $type = "web";

            return is_mobile($type, "fees/fees_collect/fees_collect", $res, "view");
        } else {
            $type = "web";

            $res = [
                "status_code" => 0,
                "message" => "Fees config master setting is missing",
            ];

            return is_mobile($type, "fees_collect.index", $res, "redirect");
        }
    }

    // function is used to get data of collected fees for perticular student
    public function studentFeesDetailAPI(Request $request)
    {
        // for api token is required
        try {
            if (!$this->jwtToken()->validate()) {
                $response = ['status' => '2', 'message' => 'Token Auth Failed', 'data' => []];

                return response()->json($response, 401);
            }
        } catch (\Exception $e) {
            $response = ['status' => '2', 'message' => $e->getMessage(), 'data' => []];

            return response()->json($response, 401);
        }

        $student_id = $request->input("student_id");
        $type = $request->input("type");
        $sub_institute_id = $request->input("sub_institute_id");
        $syear = $request->input("syear");

        if ($student_id != "" && $sub_institute_id != "" && $syear != "") {
            //START Get Fees pending array
            $request->session()->put('sub_institute_id', $sub_institute_id);
            $request->session()->put('syear', $syear);
            $request->session()->put('student_id', $student_id);
            $new_pending_arr = [];


            $fees_online_link = DB::table('fees_online_maping')
                ->where('syear', $syear)
                ->where('sub_institute_id', $sub_institute_id)
                ->get()->toArray();

            $fees_online_link = json_decode(json_encode($fees_online_link), true);

            $online_link = "";
            if (count($fees_online_link) > 0) {
                $online_link = env('APP_URL') . "fees/online_fees_collect";
            }
            $array = [1,72];
            if(in_array($sub_institute_id, $array)){
                $pay_link = DB::table('tblstudent_enrollment as se')
                    ->selectRaw('ac.payment_link')
                    ->join('academic_section as ac','ac.id','=','se.grade_id')
                    ->where('se.student_id', $student_id)
                    ->where('se.syear', $syear)
                    ->where('se.sub_institute_id', $sub_institute_id)
                    ->get();
                
                    $online_link = "";
                    if (count($pay_link) > 0) {
                        $online_link = $pay_link[0]->payment_link;
                    }
            }

            $fees_data = $this->getBk($request, $student_id);
            if (isset($fees_data['total_fees'])) {
                foreach ($fees_data['total_fees'] as $key => $val) {
                    unset($val['bk']);
                    unset($val['paid']);
                    //Set link in PAY NOW
                    if ($online_link != "") {
                        $val['PayNow'] = $online_link;
                    }
                    if ($val['remain'] != 0 && $val['month'] != 'Total') {
                        $new_pending_arr[] = (object)$val;
                    }
                }
            }

            $data['PENDING'] = $new_pending_arr;
            //END Get Fees pending array

            //START Get Fees paid array
            $paid_data = DB::table('fees_collect as c')
                ->selectRaw('c.receipt_no,c.receiptdate,c.payment_mode,c.bank_branch,c.bank_branch,c.bank_name,c.fees_html,
                    c.cheque_date,c.cheque_no,c.cheque_bank_name,SUM(amount) as paid_amount')
                ->where('c.student_id', $student_id)
                ->where('c.syear', $syear)
                ->where('c.is_deleted', 'N')
                ->where('c.sub_institute_id', $sub_institute_id)
                ->groupBy('receipt_no')->get()->toArray();

            $data['PAID'] = $paid_data;
            //END Get Fees paid array

            $res['status'] = 1;
            $res['message'] = "Success";
            $res['data'] = $data;
        } else {
            $res['status'] = 0;
            $res['message'] = "Parameter Missing";
        }

        return json_encode($res);
    }

    // function is used to get data of collected fees for perticular student
    public function retrieveDataByUserId(Request $request, $user_id, $stud_id)
    {
        $division = $request->input('division');
        $enrollment_no = $user_id;
        $stud_id = $stud_id;
        $name = $request->input('name');
        $mb_no = $request->input('mb_no');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $receipt_no = $request->input('receipt_no');
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $extra_fp = "  AND fp.syear = '" . $syear . "' AND te.syear = '" . $syear . "' AND t.sub_institute_id = '" . $sub_institute_id . "' AND fp.sub_institute_id = '" . $sub_institute_id . "' AND fp.is_deleted = 'N' ";

        $extra_fo = "  AND fo.syear = '" . $syear . "' AND te.syear = '" . $syear . "' AND t.sub_institute_id = '" . $sub_institute_id . "' AND fo.sub_institute_id = '" . $sub_institute_id . "' AND fo.is_deleted = 'N' ";

        if ($division != '') {
            $extra_fp .= " AND te.section_id = '" . $division . "'";
            $extra_fo .= " AND te.section_id = '" . $division . "'";
        }

        if ($stud_id != '') {
            $extra_fp .= " AND te.student_id = '" . $stud_id . "'";
            $extra_fo .= " AND te.student_id = '" . $stud_id . "'";
        }

        if ($enrollment_no != '') {
            $extra_fp .= " AND t.enrollment_no = '" . $enrollment_no . "'";
            $extra_fo .= " AND t.enrollment_no = '" . $enrollment_no . "'";
        }
        if ($name != '') {
            $extra_fp .= " AND (t.first_name = '" . $name . "' OR t.last_name = '" . $name . "' OR t.middle_name = '" . $name . "') ";
            $extra_fo .= " AND (t.first_name = '" . $name . "' OR t.last_name = '" . $name . "' OR t.middle_name = '" . $name . "')";
        }
        if ($mb_no != '') {
            $extra_fp .= " AND t.mobile = '" . $mb_no . "'";
            $extra_fo .= " AND t.mobile = '" . $mb_no . "'";
        }
        if ($from_date != '') {
            $extra_fp .= " AND fp.receiptdate >= '" . $from_date . "'";
            $extra_fo .= " AND fo.receiptdate >= '" . $from_date . "'";
        }

        if ($to_date != '') {
            $extra_fp .= " AND fp.receiptdate <= '" . $to_date . "'";
            $extra_fo .= " AND fo.receiptdate <= '" . $to_date . "'";
        }
        if ($sub_institute_id == 200) {
            $extra_fp .= " AND fp.standard_id=te.standard_id ";
            //$extra_fo .= " AND fo.receiptdate <= '".$to_date."'";
        }


        $data = DB::table(function ($query) use ($sub_institute_id, $syear, $extra_fo, $extra_fp) {
            $query->selectRaw('t.id as student_id, t.enrollment_no, te.roll_no, t.uniqueid, t.place_of_birth, '
                . DB::raw("CONCAT_WS(' ', t.first_name, t.middle_name, t.last_name) as student_name") . ', g.title as grade, s.name as standard_name, d.name as division_name, fp.created_date, '
                . DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) AS user_name, fp.term_id, fp.receiptdate, fp.receipt_no, fp.payment_mode, '
                . 'fp.cheque_bank_name, fp.bank_branch, fp.cheque_no, fp.cheque_date, b.title as batch, sq.title as quota, '
                . 'IFNULL(fp.amount, 0) AS actual_amountpaid'))
                ->from('tblstudent as t')
                ->join('tblstudent_enrollment as te', function ($join) use($syear){
                    $join->on('te.student_id', '=', 't.id')->where('te.syear',$syear);
                })
                ->leftJoin('academic_section as g', 'g.id', '=', 'te.grade_id')
                ->leftJoin('standard as s', 's.id', '=', 'te.standard_id')
                ->leftJoin('division as d', 'd.id', '=', 'te.section_id')
                ->leftJoin('student_quota as sq', 'sq.id', '=', 'te.student_quota')
                ->leftjoin('batch as b', function ($join) {
                    $join->on('b.standard_id', '=', 'te.standard_id')
                        ->whereRaw('b.division_id = te.section_id')
                        ->whereRaw('b.id = t.studentbatch')
                        ->whereRaw('b.syear = te.syear');
                })
                ->join('fees_collect as fp', function ($join) {
                    $join->on('fp.student_id', '=', 'te.student_id')
                         ->on('fp.standard_id', '=', 'te.standard_id');
                })
                ->leftJoin('tbluser as u', 'fp.created_by', '=', 'u.id')
                ->whereRaw("1=1 " . $extra_fp)

                ->unionAll(function ($query) use ($sub_institute_id, $syear, $extra_fo, $extra_fp) {
                    $query->selectRaw('t.id as student_id, t.enrollment_no, te.roll_no, t.uniqueid, t.place_of_birth, '
                        . DB::raw("CONCAT_WS(' ', t.first_name, t.middle_name, t.last_name) as student_name") . ', g.title as grade, s.name as standard_name, d.name as division_name, NULL AS created_date, '
                        . DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) AS user_name, fo.month_id AS term_id, fo.receiptdate AS receiptdate, fo.reciept_id AS receipt_no, fo.payment_mode AS payment_mode, '
                        . 'fo.bank_name as cheque_bank_name, fo.bank_branch, fo.cheque_dd_no as cheque_no, fo.cheque_dd_date AS cheque_date, b.title as batch, sq.title as quota, '
                        . 'IFNULL(fo.actual_amountpaid, 0) AS actual_amountpaid'))->from('tblstudent as t')
                        ->join('tblstudent_enrollment as te', function ($join) use($syear){
                            $join->on('te.student_id', '=', 't.id')->where('te.syear',$syear);
                        })
                        ->leftJoin('academic_section as g', 'g.id', '=', 'te.grade_id')
                        ->leftJoin('standard as s', 's.id', '=', 'te.standard_id')
                        ->leftJoin('division as d', 'd.id', '=', 'te.section_id')
                        ->leftJoin('student_quota as sq', 'sq.id', '=', 'te.student_quota')
                        ->leftjoin('batch as b', function ($join) {
                            $join->on('b.standard_id', '=', 'te.standard_id')
                                ->whereRaw('b.division_id = te.section_id')
                                ->whereRaw('b.id = t.studentbatch')
                                ->whereRaw('b.syear = te.syear');
                        })
                        ->leftJoin('fees_paid_other as fo', 'fo.student_id', '=', 'te.student_id')
                        ->leftJoin('tbluser as u', 'fo.created_by', '=', 'u.id')
                        ->whereRaw("1=1 " . $extra_fo);
                });
        })
            ->selectRaw('student_id, enrollment_no, roll_no, uniqueid, place_of_birth, student_name, grade,standard_name, division_name,created_date, user_name, GROUP_CONCAT(term_id) AS term_ids, receiptdate, receipt_no,  payment_mode, cheque_bank_name, bank_branch, cheque_no, cheque_date, batch,  quota,   SUM(IFNULL(actual_amountpaid, 0)) AS actual_amountpaid')
            ->groupBy('receipt_no');

        $data = $data->get()->toArray();
        $feesData = json_decode(json_encode($data), true);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['fees_data'] = $feesData;
        $res['enrollment_no'] = $enrollment_no;

        return $feesData;
    }

    // send sms to parent after fees successfully paid
    function send_sms_to_parents($request){

        $sub_institute_id = session()->get('sub_institute_id');
        $syear = session()->get('syear');
        $receipt_id = $request['receipt_id_html'];
        $student_id= $request['student_id'];

        // get student fees details
        $get_data = DB::table(function ($query) use ($sub_institute_id, $syear, $student_id, $receipt_id) {
            $query->selectRaw('fc.id, fc.student_id, GROUP_CONCAT(fc.term_id) as months,sum(fc.amount) as amount,fc.receiptdate as receipt_date,CONCAT_WS(" ",s.first_name,s.last_name) as student_name,s.mobile')
                ->from('fees_collect as fc')
                ->join('fees_receipt as fr', function ($join) {
                    $join->whereRaw('FIND_IN_SET(fc.id, fr.FEES_ID) AND fr.SUB_INSTITUTE_ID = fc.sub_institute_id');
                })
                ->join('tblstudent as s','s.id','=','fc.student_id')
                ->where('fc.sub_institute_id', $sub_institute_id)
                ->where('fc.syear', $syear)
                ->where('fc.student_id', $student_id)
                ->whereRaw("(fr.RECEIPT_ID_1 = '" . $receipt_id . "' OR fr.RECEIPT_ID_2 = '" . $receipt_id . "' OR fr.RECEIPT_ID_3 = '" . $receipt_id . "'
                    OR fr.RECEIPT_ID_4 = '" . $receipt_id . "' OR fr.RECEIPT_ID_5 = '" . $receipt_id . "' OR fr.RECEIPT_ID_6 = '" . $receipt_id . "' OR fr.RECEIPT_ID_7 = '" . $receipt_id . "' OR fr.RECEIPT_ID_8 = '" . $receipt_id . "'
                    OR fr.RECEIPT_ID_9 = '" . $receipt_id . "' OR fr.RECEIPT_ID_10 = '" . $receipt_id . "')")
                ->groupBy('fc.fees_html')
                ->unionAll(
                    DB::table('fees_paid_other as fo')
                        ->selectRaw('fo.id, fo.student_id,GROUP_CONCAT(fo.month_id) as months,sum(fo.actual_amountpaid) as amount,fo.receiptdate as receipt_date,CONCAT_WS(" ",s.first_name,s.last_name) as student_name,s.mobile')
                        ->join('fees_receipt as fro', function ($join) {
                            $join->whereRaw('FIND_IN_SET(fo.id, fro.OTHER_FEES_ID) AND fro.SUB_INSTITUTE_ID = fo.sub_institute_id');
                        })
                        ->join('tblstudent as s','s.id','=','fo.student_id')
                        ->where('fo.sub_institute_id', $sub_institute_id)
                        ->where('fo.syear', $syear)
                        ->where('fo.student_id', $student_id)
                        ->whereRaw("(fro.RECEIPT_ID_1 = '" . $receipt_id . "' OR fro.RECEIPT_ID_2 = '" . $receipt_id . "' OR fro.RECEIPT_ID_3 = '" . $receipt_id . "' OR fro.RECEIPT_ID_4 = '" . $receipt_id . "' OR fro.RECEIPT_ID_5 = '" . $receipt_id . "' OR fro.RECEIPT_ID_6 = '" . $receipt_id . "' OR fro.RECEIPT_ID_7 = '" . $receipt_id . "' OR fro.RECEIPT_ID_8 = '" . $receipt_id . "'
                            OR fro.RECEIPT_ID_9 = '" . $receipt_id . "' OR fro.RECEIPT_ID_10 = '" . $receipt_id . "')")
                        ->groupBy('fo.paid_fees_html')
                );
        })
        ->selectRaw('student_id, months, sum(amount) as amount,receipt_date,student_name,mobile')
        ->groupBy('student_id')
        ->first();

        $mobile = $get_data->mobile;
        $student_name = $get_data->student_name;
        $amount = $get_data->amount;
        $receipt_date = $get_data->receipt_date;
        $months = $get_data->months;
        $temp_id = '';
        $months = $get_data->months;
        // get month names with ids
        $month_arr= FeeMonthId();
        $month_values = explode(',', $months);
        $month_names = [];
            foreach ($month_values as $value) {
                if (isset($month_arr[$value])) {
                    $month_names[] = $month_arr[$value];
                }
            }
            $month_name = implode(', ', $month_names);
            $text = "Dear, ".$student_name." your ".$amount." for ".$month_name." are received successfully on ". $receipt_date;
            // Uma - only for CN school
            if($sub_institute_id==257){
                $text .=". CNSA";
                $temp_id ="&template_id=1407169571014046264";
            }
            // get function from controller easy_com/send_sms_parents/send_sms_parents_controller
                $sms_controller = new send_sms_parents_controller;
                $get_sms_status = $sms_controller->sendSMS($mobile, $text, $sub_institute_id,$temp_id);
            if($get_sms_status['error'] != 1){
                $store_status = $sms_controller->saveParentLog($student_id, $text, $mobile, $sub_institute_id, $syear);
                $res = "1";
            }else{
                $res = "0";
            }
        // 9874632014
        return $res;
    }
}
