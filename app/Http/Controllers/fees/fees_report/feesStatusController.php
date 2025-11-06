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
    $fees_head        = (array)$request->input('fees_head');   // UI-selected heads
    $number_type      = $request->input('number_type');
    $sub_institute_id = (int)$request->session()->get('sub_institute_id');

    $months = FeeMonthId();

    $number_types = [
        "mobile"          => "Father Mobile",
        "student_mobile"  => "Student Mobile",
        "mother_mobile"   => "Mother Mobile",
    ];

    $feesHead = fees_title::where(['sub_institute_id' => $sub_institute_id, 'other_fee_id' => 0])
        ->orderBy('sort_order')
        ->pluck('display_name', 'fees_title')
        ->toArray();
    asort($feesHead);

    $studentData = SearchStudent($grade, $standard, $division);

    if (count($studentData) === 0) {
        $res['status_code'] = 0;
        $res['message'] = "No student found please check your search panel";
        return is_mobile($type, "fees_status_report.index", $res);
    }

    // Current-year student IDs + enrollment nos
    $student_ids    = [];
    $currIdToEnroll = [];
    $enrollNos      = [];
    foreach ($studentData as $s) {
        $sid = (int)$s['student_id'];
        $student_ids[] = $sid;
        $en = $s['enrollment_no'] ?? null;
        if ($en) {
            $currIdToEnroll[$sid] = $en;
            $enrollNos[] = $en;
        }
    }
    $enrollNos = array_values(array_unique($enrollNos));

    // Heads used for CURRENT YEAR table display only
    $uiHeadCols = array_values($fees_head);

    // ---------------- CURRENT YEAR: CHARGES (your helper) ----------------
    $displayBreakoff = [];
    $data = FeeBreakoffHeadWise($student_ids);

    // ---------------- CURRENT YEAR: PAID (respect month filter) ----------------
    $whereRaw = "1 = 1";
    if (!empty($month)) {
        $whereRaw .= " AND `term_id` IN (" . implode(",", (array)$month) . ")";
    }
    $whereRaw .= " AND sub_institute_id = {$sub_institute_id}"
              .  " AND student_id IN (" . implode(",", $student_ids) . ")"
              .  " AND is_deleted != 'Y'";

    $feesPaidRaw = DB::table("fees_collect")->whereRaw($whereRaw)->get()->toArray();

    $feesPaid = [];
    foreach ($feesPaidRaw as $r) {
        foreach ($uiHeadCols as $col) {
            if (!isset($r->$col)) continue;
            $feesPaid[$r->student_id][$r->term_id][$col] =
                ($feesPaid[$r->student_id][$r->term_id][$col] ?? 0) + (float)$r->$col;
        }
    }

    // Build display charges for current year (respect month filter)
    foreach ($student_ids as $sid) {
        if (empty($data[$sid]['breakoff'])) continue;
        foreach ($data[$sid]['breakoff'] as $tId => $heads) {
            if (!empty($month) && !in_array($tId, (array)$month)) continue;
            foreach ($heads as $head => $row) {
                if (!in_array($head, $uiHeadCols, true)) continue; // only show selected heads in UI
                $title = $row['title'];
                $amt   = (float)($row['amount'] ?? 0);
                $displayBreakoff[$sid][$title] = ($displayBreakoff[$sid][$title] ?? 0) + $amt;
            }
        }
    }

    // ---------------- PREVIOUS YEAR DUE (sum amount - paid_amount from breakoff) ----------------
    $currentYear  = (int)(session('syear') ?: date('Y'));
    $previousYear = $currentYear - 1;

    // A) Find previous-year student_ids using fees_collect JOIN tblstudent (by enrollment_no)
    $previousDues = array_fill_keys($student_ids, 0.0);
    $currToPrev   = [];
    $prevIds      = [];

    if (!empty($enrollNos)) {
        $prevRows = DB::table('fees_collect as fc')
            ->join('tblstudent as s', 's.id', '=', 'fc.student_id')
            ->where('fc.sub_institute_id', $sub_institute_id)
            ->where('fc.is_deleted', '!=', 'Y')
            ->where('fc.syear', $previousYear)
            ->whereIn('s.enrollment_no', $enrollNos)
            ->select('s.enrollment_no', 'fc.student_id')
            ->groupBy('s.enrollment_no', 'fc.student_id')
            ->get();

        $prevEnrollToId = [];
        foreach ($prevRows as $row) {
            $prevEnrollToId[$row->enrollment_no] = (int)$row->student_id;
        }

        foreach ($currIdToEnroll as $currId => $en) {
            if (isset($prevEnrollToId[$en])) {
                $pid = $prevEnrollToId[$en];
                $currToPrev[$currId] = $pid;
                $prevIds[] = $pid;
            }
        }
        $prevIds = array_values(array_unique($prevIds));
    }

    if (!empty($prevIds)) {
        // B) Pull previous-year breakoff using your helper and sum (amount - paid_amount)
        $originalSyear = session('syear');
        try {
            session()->put('syear', $previousYear);
            $prevData = FeeBreakoffHeadWise($prevIds);
        } finally {
            session()->put('syear', $originalSyear);
        }

        // Per previous-id due
        $prevDueByPrevId = []; // [prev_id] => due
        foreach ($prevIds as $pid) {
            $due = 0.0;
            if (!empty($prevData[$pid]['breakoff'])) {
                foreach ($prevData[$pid]['breakoff'] as $tId => $heads) {
                    foreach ($heads as $head => $row) {
                        $amount = (float)($row['amount'] ?? 0);
                        $paid   = (float)($row['paid_amount'] ?? 0);
                        $due   += max($amount - $paid, 0.0);
                    }
                }
            }
            $prevDueByPrevId[$pid] = $due;
        }

        // C) Map back to CURRENT ids
        foreach ($student_ids as $currId) {
            $pid = $currToPrev[$currId] ?? null;
            if ($pid !== null && isset($prevDueByPrevId[$pid])) {
                $previousDues[$currId] = $prevDueByPrevId[$pid];
            }
        }
    }

    // ---------------- RESPONSE ----------------
    $res['status_code']    = 1;
    $res['message']        = "Success";
    $res['grade_id']       = $grade;
    $res['standard_id']    = $standard;
    $res['division_id']    = $division;
    $res['months']         = $months;
    $res['month']          = $month;
    $res['fees_heads']     = $feesHead;
    $res['fees_head']      = $fees_head;
    $res['fees_data']      = $data;
    $res['number_type']    = $number_type;
    $res['number_types']   = $number_types;
    $res['fees_details']   = $displayBreakoff;
    $res['previous_dues']  = $previousDues;

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
