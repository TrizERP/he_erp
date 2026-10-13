<?php

namespace App\Http\Controllers\fees\deposit_refund;

use App\Http\Controllers\Controller;
use App\Http\Controllers\fees\fees_report\feesStatusController;
use App\Models\fees\deposit_refund\depositRefundDeductionModel;
use App\Models\fees\deposit_refund\depositRefundLogModel;
use App\Models\fees\deposit_refund\depositRefundModel;
use App\Models\student\tblstudentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class depositRefundController extends Controller
{
    /**
     * Search screen - clerk searches for a student.
     */
    public function index(Request $request)
    {
        $type = $request->input('type');

        $res['status_code'] = 1;
        $res['message'] = "Success";

        return is_mobile($type, "fees/deposit_refund/index", $res, "view");
    }

    /**
     * Run the search and list matching students with a link to their deposit account.
     */
    public function searchStudents(Request $request)
    {
        $type = $request->input('type');
        $grade = $request->input('grade');
        $standard = $request->input('standard');
        $division = $request->input('division');
        $enrollment_no = $request->input('enrollment_no');
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $marking_period_id = session()->get('term_id');

        $extraSearchArray = [];
        $extraSearchArrayRaw = " 1=1 ";

        if ($grade != '') {
            $extraSearchArray['tblstudent_enrollment.grade_id'] = $grade;
        }
        if ($standard != '') {
            $extraSearchArray['tblstudent_enrollment.standard_id'] = $standard;
        }
        if ($division != '') {
            $extraSearchArray['tblstudent_enrollment.section_id'] = $division;
        }
        if ($enrollment_no != '') {
            $extraSearchArray['tblstudent.enrollment_no'] = $enrollment_no;
        }

        $extraSearchArray['tblstudent_enrollment.syear'] = $syear;
        $extraSearchArray['tblstudent.sub_institute_id'] = $sub_institute_id;

        $studentData = tblstudentModel::selectRaw("tblstudent.id,CONCAT_WS(' ',tblstudent.first_name,tblstudent.middle_name,tblstudent.last_name) AS student_name,
            academic_section.title as grade,standard.name as standard_name,division.name as division_name,tblstudent.enrollment_no,tblstudent.mobile")
            ->join('tblstudent_enrollment', 'tblstudent.id', '=', 'tblstudent_enrollment.student_id')
            ->join('academic_section', 'academic_section.id', '=', 'tblstudent_enrollment.grade_id')
            ->join('standard', function ($join) use ($marking_period_id) {
                $join->on('standard.id', '=', 'tblstudent_enrollment.standard_id');
                if (!is_null($marking_period_id)) {
                    $join->where('standard.marking_period_id', $marking_period_id);
                }
            })
            ->join('division', 'division.id', '=', 'tblstudent_enrollment.section_id')
            ->where($extraSearchArray)
            ->whereRaw($extraSearchArrayRaw)
            ->orderByRaw('standard.sort_order, division.id, tblstudent.enrollment_no')
            ->get()
            ->toArray();

        if (count($studentData) == 0) {
            $res['status_code'] = 0;
            $res['message'] = "No student found, please check your search panel.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['student_data'] = $studentData;
        $res['grade_id'] = $grade;
        $res['standard_id'] = $standard;
        $res['division_id'] = $division;
        $res['enrollment_no'] = $enrollment_no;

        return is_mobile($type, "fees/deposit_refund/index", $res, "view");
    }

    /**
     * Show a single student's deposit account - collected / returned / balance + history.
     */
    public function depositAccount(Request $request, $student_id)
    {
        $type = $request->input('type');
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $stuData = $this->getStudentSnapshot($student_id, $syear, $sub_institute_id);

        if (!$stuData) {
            $res['status_code'] = 0;
            $res['message'] = "Student not found.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $ledger = $this->getDepositLedger($student_id, $sub_institute_id);

        $history = depositRefundModel::where([
            'student_id'        => $student_id,
            'sub_institute_id'  => $sub_institute_id,
        ])->orderByDesc('id')->get()->toArray();

        $hasActiveRequest = depositRefundModel::where('student_id', $student_id)
            ->where('sub_institute_id', $sub_institute_id)
            ->whereIn('status', [
                depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_PENDING_PRINCIPAL,
                depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL,
                depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT,
            ])->exists();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['stu_data'] = $stuData;
        $res['ledger'] = $ledger;
        $res['history'] = $history;
        $res['has_active_request'] = $hasActiveRequest;

        return is_mobile($type, "fees/deposit_refund/deposit_account", $res, "view");
    }

    /**
     * Show the refund request form for a student, pre-filled with account + dues snapshot.
     */
    public function create(Request $request, $student_id)
    {
        $type = $request->input('type');
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $stuData = $this->getStudentSnapshot($student_id, $syear, $sub_institute_id);

        if (!$stuData) {
            $res['status_code'] = 0;
            $res['message'] = "Student not found.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $ledger = $this->getDepositLedger($student_id, $sub_institute_id);

        if ($ledger['balance'] <= 0) {
            $res['status_code'] = 0;
            $res['message'] = "No deposit balance is available for refund for this student.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $hasActiveRequest = depositRefundModel::where('student_id', $student_id)
            ->where('sub_institute_id', $sub_institute_id)
            ->whereIn('status', [
                depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_PENDING_PRINCIPAL,
                depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL,
                depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT,
            ])->exists();

        if ($hasActiveRequest) {
            $res['status_code'] = 0;
            $res['message'] = "A refund request is already in progress for this student.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $unpaidFees = $this->calculateUnpaidFees($student_id, $stuData['grade_id'], $stuData['std_id'], $stuData['div_id']);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['stu_data'] = $stuData;
        $res['ledger'] = $ledger;
        $res['unpaid_fees'] = $unpaidFees;
        $res['bank_data'] = DB::table('bank_master')->get()->toArray();

        return is_mobile($type, "fees/deposit_refund/create", $res, "view");
    }

    /**
     * Save a new refund request and submit it for Accounts Head approval.
     */
    public function store(Request $request)
    {
        $type = $request->input('type');
        $syear = $request->session()->get('syear');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');

        $student_id = $request->input('student_id');
        $reason = trim((string)$request->input('reason'));
        $receiver_name = trim((string)$request->input('receiver_name'));
        $receiver_relation = $request->input('receiver_relation');
        $payment_mode = $request->input('payment_mode');
        $cheque_no = $request->input('cheque_no');
        $cheque_date = $request->input('cheque_date') ?: null;
        $bank_name = $request->input('bank_name');
        $bank_account_number = $request->input('bank_account_number');
        $ifsc_code = $request->input('ifsc_code');
        $clerk_remarks = $request->input('clerk_remarks');

        $deduction_titles = (array)$request->input('deduction_title', []);
        $deduction_amounts = (array)$request->input('deduction_amount', []);
        $deduction_remarks = (array)$request->input('deduction_remarks', []);

        if ($student_id == '' || $reason == '' || $receiver_name == '' || $payment_mode == '') {
            $res['status_code'] = 0;
            $res['message'] = "Please fill all mandatory fields.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        if ($payment_mode == 'Cheque' && ($cheque_no == '' || $cheque_date == null)) {
            $res['status_code'] = 0;
            $res['message'] = "Cheque number and date are mandatory for a cheque refund.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        if ($payment_mode == 'Bank Transfer' && ($bank_account_number == '' || $ifsc_code == '')) {
            $res['status_code'] = 0;
            $res['message'] = "Bank account number and IFSC are mandatory for a bank transfer refund.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $stuData = $this->getStudentSnapshot($student_id, $syear, $sub_institute_id);

        if (!$stuData) {
            $res['status_code'] = 0;
            $res['message'] = "Student not found.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $hasActiveRequest = depositRefundModel::where('student_id', $student_id)
            ->where('sub_institute_id', $sub_institute_id)
            ->whereIn('status', [
                depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_PENDING_PRINCIPAL,
                depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL,
                depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT,
            ])->exists();

        if ($hasActiveRequest) {
            $res['status_code'] = 0;
            $res['message'] = "A refund request is already in progress for this student.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $ledger = $this->getDepositLedger($student_id, $sub_institute_id);

        if ($ledger['balance'] <= 0) {
            $res['status_code'] = 0;
            $res['message'] = "No deposit balance is available for refund for this student.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $deductionTotal = 0;
        $deductions = [];
        foreach ($deduction_titles as $k => $title) {
            $title = trim((string)$title);
            $amount = (float)($deduction_amounts[$k] ?? 0);
            if ($title == '' || $amount <= 0) {
                continue;
            }
            $deductionTotal += $amount;
            $deductions[] = [
                'title'   => $title,
                'amount'  => $amount,
                'remarks' => $deduction_remarks[$k] ?? null,
            ];
        }

        $finalRefundAmount = $ledger['balance'] - $deductionTotal;

        if ($finalRefundAmount <= 0) {
            $res['status_code'] = 0;
            $res['message'] = "Final refund amount cannot be zero or negative. Please check the deductions entered.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        if ($finalRefundAmount > $ledger['balance']) {
            $res['status_code'] = 0;
            $res['message'] = "Refund amount cannot be greater than the deposit balance available.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        $supportingDocument = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $supportingDocument = 'DR-'.$student_id.'-'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('deposit_refund_docs'), $supportingDocument);
        }

        $unpaidFees = $this->calculateUnpaidFees($student_id, $stuData['grade_id'], $stuData['std_id'], $stuData['div_id']);

        $requestNoResult = DB::table('deposit_refund')
            ->selectRaw("IFNULL(MAX(CONVERT(SUBSTRING_INDEX(request_no,'/',-1), UNSIGNED)),0) AS rid")
            ->where('sub_institute_id', $sub_institute_id)
            ->where('syear', $syear)->get()->first();

        $requestNo = 'DR/'.$syear.'/'.($requestNoResult->rid + 1);

        $depositRefund = depositRefundModel::create([
            'sub_institute_id'         => $sub_institute_id,
            'syear'                    => $syear,
            'student_id'               => $student_id,
            'standard_id'              => $stuData['std_id'],
            'section_id'               => $stuData['div_id'],
            'request_no'               => $requestNo,
            'deposit_collected'        => $ledger['collected'],
            'deposit_returned_before'  => $ledger['returned'],
            'deposit_balance'          => $ledger['balance'],
            'unpaid_fees'              => $unpaidFees,
            'reason'                   => $reason,
            'deduction_total'          => $deductionTotal,
            'final_refund_amount'      => $finalRefundAmount,
            'receiver_name'            => $receiver_name,
            'receiver_relation'        => $receiver_relation,
            'payment_mode'             => $payment_mode,
            'cheque_no'                => $cheque_no,
            'cheque_date'              => $cheque_date,
            'bank_name'                => $bank_name,
            'bank_account_number'      => $bank_account_number,
            'ifsc_code'                => $ifsc_code,
            'supporting_document'      => $supportingDocument,
            'clerk_remarks'            => $clerk_remarks,
            'status'                   => depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
            'requested_by'             => $user_id,
            'requested_date'           => date('Y-m-d H:i:s'),
            'created_by'               => $user_id,
            'created_ip_address'       => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_date'             => date('Y-m-d H:i:s'),
        ]);

        foreach ($deductions as $deduction) {
            depositRefundDeductionModel::create([
                'deposit_refund_id' => $depositRefund->id,
                'title'             => $deduction['title'],
                'amount'            => $deduction['amount'],
                'remarks'           => $deduction['remarks'],
            ]);
        }

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => null,
            'to_status'         => depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
            'action'            => 'Submitted',
            'remarks'           => $clerk_remarks,
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $res['status_code'] = 1;
        $res['message'] = "Deposit refund request ".$requestNo." submitted for Accounts Head approval.";

        return is_mobile($type, "deposit_refund.index", $res, "redirect");
    }

    /**
     * Clerk's own submitted requests, so progress and returned requests can be tracked / resubmitted.
     */
    public function myRequests(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');

        $data = DB::table('deposit_refund as dr')
            ->join('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->join('deposit_refund_status_master as sm', 'sm.id', '=', 'dr.status')
            ->selectRaw("dr.*, concat_ws(' ',s.first_name,s.last_name) as student_name, s.enrollment_no, sm.title as status_title")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.syear', $syear)
            ->orderByDesc('dr.id')
            ->get()->toArray();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;

        return is_mobile($type, "fees/deposit_refund/my_requests", $res, "view");
    }

    /**
     * Resubmit a request that was returned by the Accounts Head or Principal, after corrections.
     */
    public function resubmit(Request $request, $id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->whereIn('status', [
                depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL,
            ])->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "This request cannot be resubmitted.";

            return is_mobile($type, "deposit_refund_my_requests.index", $res, "redirect");
        }

        $fromStatus = $depositRefund->status;
        $depositRefund->status = depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD;
        $depositRefund->save();

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => $fromStatus,
            'to_status'         => depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
            'action'            => 'Resubmitted',
            'remarks'           => $request->input('remarks'),
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $res['status_code'] = 1;
        $res['message'] = "Request resubmitted for Accounts Head approval.";

        return is_mobile($type, "deposit_refund_my_requests.index", $res, "redirect");
    }

    /**
     * Cancel a refund request. Never deleted - only cancelled, with a mandatory reason.
     */
    public function cancel(Request $request, $id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');
        $reason = trim((string)$request->input('cancellation_reason'));

        if ($reason == '') {
            $res['status_code'] = 0;
            $res['message'] = "Cancellation reason is mandatory.";

            return is_mobile($type, "deposit_refund_my_requests.index", $res, "redirect");
        }

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', '!=', depositRefundModel::STATUS_PAID)
            ->where('status', '!=', depositRefundModel::STATUS_CANCELLED)
            ->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "This request cannot be cancelled.";

            return is_mobile($type, "deposit_refund_my_requests.index", $res, "redirect");
        }

        $fromStatus = $depositRefund->status;
        $depositRefund->status = depositRefundModel::STATUS_CANCELLED;
        $depositRefund->cancelled_by = $user_id;
        $depositRefund->cancelled_date = date('Y-m-d H:i:s');
        $depositRefund->cancellation_reason = $reason;
        $depositRefund->save();

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => $fromStatus,
            'to_status'         => depositRefundModel::STATUS_CANCELLED,
            'action'            => 'Cancelled',
            'remarks'           => $reason,
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $res['status_code'] = 1;
        $res['message'] = "Request cancelled.";

        return is_mobile($type, "deposit_refund_my_requests.index", $res, "redirect");
    }

    /**
     * Snapshot of a student's identity/enrollment for display on the refund screens.
     */
    protected function getStudentSnapshot($student_id, $syear, $sub_institute_id)
    {
        $row = DB::table('tblstudent as s')
            ->join('tblstudent_enrollment as se', function ($join) {
                $join->whereRaw('se.student_id = s.id AND s.sub_institute_id = se.sub_institute_id');
            })
            ->join('academic_section as aa', 'aa.id', '=', 'se.grade_id')
            ->join('standard as st', 'st.id', '=', 'se.standard_id')
            ->join('division as d', function ($join) {
                $join->whereRaw('d.id = se.section_id AND d.sub_institute_id = se.sub_institute_id');
            })
            ->selectRaw("s.id as student_id, CONCAT_WS(' ',s.first_name,s.middle_name,s.last_name) AS name,
                s.father_name, s.enrollment_no, s.mobile, s.admission_date as admission,
                CONCAT_WS('/',st.name,d.name) AS stddiv, se.grade_id, se.standard_id as std_id, se.section_id as div_id")
            ->where('s.id', $student_id)
            ->where('se.syear', $syear)
            ->where('s.sub_institute_id', $sub_institute_id)
            ->whereNull('se.end_date')
            ->orderByDesc('se.id')
            ->first();

        return $row ? (array)$row : null;
    }

    /**
     * Deposit collected / returned / balance ledger for a student, across all years.
     */
    protected function getDepositLedger($student_id, $sub_institute_id)
    {
        $collected = (float)DB::table('fees_collect')
            ->where('student_id', $student_id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('is_deleted', 'N')
            ->sum('deposit');

        $returned = (float)depositRefundModel::where('student_id', $student_id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', depositRefundModel::STATUS_PAID)
            ->sum('final_refund_amount');

        return [
            'collected' => $collected,
            'returned'  => $returned,
            'balance'   => $collected - $returned,
        ];
    }

    /**
     * Reuse the institute's existing fee-dues engine (Fee Status report) to fetch a single
     * student's outstanding fees, so the deposit refund screen always matches what the
     * Fees Defaulter / Fee Status reports show.
     */
    protected function calculateUnpaidFees($student_id, $grade_id, $standard_id, $division_id)
    {
        try {
            $feesStatusController = new feesStatusController;
            $fakeRequest = new Request([
                'type'        => 'API',
                'grade'       => $grade_id,
                'standard'    => $standard_id,
                'division'    => $division_id,
                'fees_status' => 'unpaid',
            ]);
            $fakeRequest->setLaravelSession(session());

            $response = $feesStatusController->feesStatusReport($fakeRequest);
            $decoded = json_decode($response, true);
            $row = $decoded['fees_data'][$student_id] ?? null;

            if (!$row) {
                return 0.0;
            }

            $totalDue = (float)($row['total_due'] ?? 0);
            $payable = (float)($row['total_payable'] ?? 0);
            $paid = (float)($row['total_paid'] ?? 0);

            return round(max($totalDue, 0) + max($payable - $paid, 0), 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }
}
