<?php

namespace App\Http\Controllers\fees\fees_report;

use App\Http\Controllers\Controller;
use App\Models\easy_com\manage_sms_api\manage_sms_api;
use App\Models\fees\fees_title\fees_title;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use function App\Helpers\FeeBreackoff;
use function App\Helpers\OtherBreackOff;
use Illuminate\Support\Facades\Session;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\FeeBreakoffHeadWise;
use function App\Helpers\FeeMonthId;
use function App\Helpers\is_mobile;
use function App\Helpers\SearchStudent;

class feesStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return false|Application|Factory|View|RedirectResponse|string
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $months = FeeMonthId();
        $feesHead = fees_title::where(['sub_institute_id' => $sub_institute_id, 'other_fee_id' => 0])
        ->orderBy('sort_order') 
        ->pluck('display_name', 'fees_title')
        ->toArray();
        asort($feesHead);
        
        $number_types = [
            "mobile"   => "Father Mobile",
            "student_mobile" => "Student Mobile",
            "mother_mobile" => "Mother Mobile",
        ];
        $res['number_types'] =$number_types;
        $res['status_code'] = "1";
        $res['message'] = "Success";
        $res['months'] = $months;
        $res['fees_heads'] = $feesHead;

        return is_mobile($type, "fees/fees_report/status_report", $res, "view");
    }

    public function feesStatusReport(Request $request)
{
    $type             = $request->input('type');
    $grade            = $request->input('grade');
    $standard         = $request->input('standard');
    $division         = $request->input('division');
    $month            = $request->input('month');
    $fees_head        = (array)$request->input('fees_head');
    $number_type      = $request->input('number_type');
    $fees_status      = $request->input('fees_status', 'unpaid');
    $sub_institute_id = (int)$request->session()->get('sub_institute_id');

    // ***** ADDED FOR BK LOGIC *****
    $syear             = session()->get('syear');
    $last_syear        = $syear - 1;
    $marking_period_id = session()->get('term_id');

    $months = FeeMonthId();

    $number_types = [
        "mobile"         => "Father Mobile",
        "student_mobile" => "Student Mobile",
        "mother_mobile"  => "Mother Mobile",
    ];

    // ============ LOAD HEADS ============
    $feesHead = fees_title::where([
        'sub_institute_id' => $sub_institute_id,
        'other_fee_id' => 0
    ])
    ->orderBy('sort_order')
    ->pluck('display_name', 'fees_title')
    ->toArray();
    asort($feesHead);

    // ============ STUDENTS ============
    $studentData = SearchStudent($grade, $standard, $division);

    if (count($studentData) === 0) {
        return is_mobile($type, "fees_status_report.index", [
            'status_code' => 0,
            'message' => "No student found please check your search panel"
        ]);
    }

    $student_ids = [];
    foreach ($studentData as $s) {
        $student_ids[] = (int)$s['student_id'];
    }

    $uiHeadCols = array_values($fees_head);

    // ============ BREAKOFF DATA ============
    $breakoffData = FeeBreakoffHeadWise($student_ids);

    $displayBreakoff = [];
    $displayBreakoffByHead = [];

    foreach ($student_ids as $sid) {
        if (empty($breakoffData[$sid]['breakoff'])) continue;

        foreach ($breakoffData[$sid]['breakoff'] as $termId => $heads) {

            if (!empty($month) && !in_array($termId, $month)) continue;

            foreach ($heads as $head => $row) {
                if (!in_array($head, $uiHeadCols, true)) continue;
                $title = $row['title'];
                $amt   = (float)($row['amount'] ?? 0);

                $displayBreakoff[$sid][$title] =
                    ($displayBreakoff[$sid][$title] ?? 0) + $amt;

                $displayBreakoffByHead[$sid][$head] =
                    ($displayBreakoffByHead[$sid][$head] ?? 0) + $amt;
            }
        }
    }

    // ============ PAID AMOUNTS ============
    $whereRaw = "sub_institute_id = {$sub_institute_id} AND is_deleted != 'Y'";

    if (!empty($month)) {
        $whereRaw .= " AND term_id IN (" . implode(",", $month) . ")";
    }

    $whereRaw .= " AND student_id IN (" . implode(",", $student_ids) . ")";

    $paidRows = DB::table("fees_collect")->whereRaw($whereRaw)->get();

    $paidAmounts = [];
    foreach ($paidRows as $r) {
        foreach ($uiHeadCols as $headName) {
            if (isset($r->$headName)) {
                $paidAmounts[$r->student_id][$headName] =
                    ($paidAmounts[$r->student_id][$headName] ?? 0)
                    + (float)$r->$headName;
            }
        }
    }

    // ============ PREVIOUS YEAR DUE ============
    $previousDues = array_fill_keys($student_ids, 0.0);

    foreach ($student_ids as $sid) {
        // load previous year breakoff for exactly one student at a time
        $prevBk = FeeBreakoffHeadWise([$sid], '', '', '', $last_syear);

        $due = 0;
        if (!empty($prevBk[$sid]['breakoff'])) {
            foreach ($prevBk[$sid]['breakoff'] as $m => $heads) {
                foreach ($heads as $row) {
                    $amount = (float)($row['amount'] ?? 0);
                    $paid   = (float)($row['paid_amount'] ?? 0);
                    $due   += max($amount - $paid, 0);
                }
            }
        }
        $previousDues[$sid] = $due;
    }

    // ============ FILTER PAID / UNPAID ============
    $filtered = [];

    foreach ($student_ids as $sid) {
        $charges = array_sum($displayBreakoffByHead[$sid] ?? []);
        $paid    = array_sum($paidAmounts[$sid] ?? []);
        $prevDue = $previousDues[$sid];

        $currentDue = max($charges - $paid, 0);
        $totalDue   = $currentDue + $prevDue;

        if ($fees_status == "paid" && $totalDue == 0) {
            $filtered[] = $sid;
        } elseif ($fees_status == "unpaid" && $totalDue > 0) {
            $filtered[] = $sid;
        } elseif ($fees_status == "") {
            $filtered[] = $sid;
        }
    }

    $finalFeesData = [];
    foreach ($filtered as $sid) {
        if (isset($breakoffData[$sid])) {
            $finalFeesData[$sid] = $breakoffData[$sid];
        }
    }

    $finalBreakoff = array_intersect_key($displayBreakoff, array_flip($filtered));
    $finalPrevDue  = array_intersect_key($previousDues, array_flip($filtered));

    // ==========================================================
    // ⭐⭐ TOTAL PAYABLE EXACT LIKE BK ⭐⭐
    // ==========================================================
    foreach ($finalFeesData as $sid => &$data) {
        // RECEIPTS
        $syear = $request->session()->get('syear');
        $receipts = DB::table("fees_collect")
            ->where("student_id", $sid)
            ->where("sub_institute_id", $sub_institute_id)
            ->where("is_deleted", "!=", "Y")
            ->where("syear", $syear - 1)
            ->get();

        $data['all_receipts'] = $receipts->pluck("receipt_no")->toArray();
        $data['total_paid']   = $receipts->sum("amount");

        // ***** FULL BK LOGIC FOR TOTAL PAYABLE *****
        // 1) Regular breakoff (current year)
        $bk1 = FeeBreackoff([$sid], '', $syear, $marking_period_id);

        // 2) Additional fees (current year)
        $other1 = OtherBreackOff([$sid], [], '', null, null, $syear, $sub_institute_id);

        // 3) Regular breakoff (previous year)
        $bk2 = FeeBreackoff([$sid], '', $last_syear, $marking_period_id);

        // 4) Additional fees (previous year)
        $other2 = OtherBreackOff([$sid], [], '', null, null, $last_syear, $sub_institute_id);

        // SUM current year
        $sum1 = 0;
        if (!empty($bk1)) {
            foreach ($bk1 as $obj) $sum1 += $obj->bkoff;
        }
        foreach ($other1 as $v) $sum1 += $v;

        // SUM previous year
        $sum2 = 0;
        if (!empty($bk2)) {
            foreach ($bk2 as $obj) $sum2 += $obj->bkoff;
        }
        foreach ($other2 as $v) $sum2 += $v;

        $data['total_payable'] = $sum1 + $sum2;
    }

    unset($data);

    // FINAL
    $res = [
        'status_code'    => 1,
        'message'        => "Success",
        'grade_id'       => $grade,
        'standard_id'    => $standard,
        'division_id'    => $division,
        'months'         => $months,
        'month'          => $month,
        'fees_heads'     => $feesHead,
        'fees_head'      => $fees_head,
        'fees_data'      => $finalFeesData,
        'number_type'    => $number_type,
        'number_types'   => $number_types,
        'fees_details'   => $finalBreakoff,
        'previous_dues'  => $finalPrevDue,
        'fees_status'    => $fees_status
    ];

    return is_mobile($type, "fees/fees_report/status_report", $res, "view");
}



    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function ajaxRemainFeesSMSsend(Request $request)
    {
        if ($request->ajax()) {
            $studentsData = $request->studentsData;

            $sub_institute_id = session()->get('sub_institute_id');
            $syear = session()->get('syear');

            $data = manage_sms_api::where(['sub_institute_id' => $sub_institute_id])
            ->get()->first();
            $data = $data->toArray();

            $message_sent = [];
            foreach ($studentsData as $student) {

                $id = $student['student_id'];
                $name = $student['student_name'];
                $mobile = $student['student_mobile'];
                $remain_fees = $student['student_remain_fees'];

                $message = 'Dear '.$name.', Your unpaid fees Rs. '.$remain_fees.'. So please pay them at the earliest.'.$data['last_var'];

                $responce = $this->sendSMS($mobile, $message, $sub_institute_id);
                if ($responce['error'] == 1) {
                    $response = ['status' => 400, 'msg' => 'SMS sent failed.'];
                    break;
                } else {
                    array_push($message_sent, $id);
                    $response = ['status' => 200, 'msg' => 'SMS sent successfull.'];
                    // $student_id = 0;
                    // foreach ($student_data as $id => $arr) {
                    // 	if ($arr['mobile'] == $number) {
                    // 		$student_id = $arr['student_id'];
                    // 	}
                    // }
                    $message_id = $responce['message'];
                    $this->saveParentLog($id, $message, $mobile,$sub_institute_id,$syear,$message_id);
                }
            }
            if ($response['status'] == 200) {
                // Session::put('success', 'SMS sent');
                Session::flash('success', 'SMS sent');
            }
            echo json_encode($response);
        }
    }

    public function sendSMS($mobile, $text, $sub_institute_id)
    {
        //$sub_institute_id = session()->get('sub_institute_id');
        $data = manage_sms_api::where(['sub_institute_id' => $sub_institute_id])
            ->get()->first();
        // ->toArray();
        $isError = 0;
        // if($data){

        //     echo '<pre>'; print_r($data); exit;
        // }
        if ($data) {
            $data = $data->toArray();
            $isError = 0;
            $errorMessage = true;

            $text = urlencode($text);

            try {
                $url = $data['url'].$data['pram'].$data['mobile_var'].$mobile.$data['text_var'].$text;
            
                // Send GET request
                $response = Http::withoutVerifying()->get($url);
            
                // Get raw response
                $output = trim($response->body());
            
                // If API returns JSON (most common case)
                $result = json_decode($output);
            
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Invalid JSON response: " . json_last_error_msg());
                }
            
                $message_id = $result->data[0]->id ?? null;
            
            } catch (\Exception $e) {
                $isError = true;
                $errorMessage = $e->getMessage();
            }

        } else {
            $isError = 1;
            $errorMessage = "Please add api details first.";
        }
        $responce = array();
        if ($isError) {
            $responce = array('error' => 1, 'message' => $errorMessage);
        } else {
            $responce = array('error' => 0, 'message' => $message_id);
        }
        return $responce;
    }

    public function saveParentLog($student_id, $msg, $number, $sub_institute_id, $syear,$message_id)
    {
        DB::table('sms_sent_parents')->insert([
            'syear'            => $syear,
            'student_id'       => $student_id,
            'sms_text'         => $msg,
            'sms_no'           => $number,
            'module_name'      => 'Fees',
            'message_id'       => $message_id,
            'sub_institute_id' => $sub_institute_id,
        ]);
    }
    /*
    Changed on 23 April 2021

    1) Commented feesPaid code on line no 181-184
    2) Commented amountlogs condition on line no 192-195
    3) Added new whereRaw condition form line no 133-137
    4) Changed in helper.php (FeeBreakoffHeadWise) changed student condition on line no 753
    5) Changed in helper.php (FeeBreakoffHeadWise) commented condition if ($value->amount != 0) on line no 758
    6) Changed in helper.php (SearchStudent) commented condition from line no 502-505
    */
}
