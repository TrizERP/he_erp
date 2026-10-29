<?php

namespace App\Http\Controllers\fees\deposit_refund;

use App\Http\Controllers\Controller;
use App\Models\fees\deposit_refund\depositRefundModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class depositRefundReportController extends Controller
{
    /**
     * Total deposit money the institute is holding today, standard/semester wise.
     */
    public function depositInHand(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');

        $collected = DB::table('fees_collect as fc')
            ->join('tblstudent as s', 's.id', '=', 'fc.student_id')
            ->join('tblstudent_enrollment as se', function ($join) {
                $join->whereRaw('se.student_id = s.id');
            })
            ->join('standard as st', 'st.id', '=', 'se.standard_id')
            ->selectRaw('st.id as standard_id, st.name as standard_name, SUM(fc.deposit) as collected')
            ->where('fc.sub_institute_id', $sub_institute_id)
            ->where('fc.is_deleted', 'N')
            ->where('se.syear', $syear)
            ->whereNull('se.end_date')
            ->groupBy('st.id', 'st.name')
            ->get()->keyBy('standard_id');

        $returned = DB::table('deposit_refund as dr')
            ->selectRaw('dr.standard_id, SUM(dr.final_refund_amount) as returned')
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.status', depositRefundModel::STATUS_PAID)
            ->groupBy('dr.standard_id')
            ->get()->keyBy('standard_id');

        $data = [];
        $totalInHand = 0;
        foreach ($collected as $standardId => $row) {
            $returnedAmt = (float)($returned[$standardId]->returned ?? 0);
            $inHand = (float)$row->collected - $returnedAmt;
            $data[] = [
                'standard_name' => $row->standard_name,
                'collected'     => $row->collected,
                'returned'      => $returnedAmt,
                'in_hand'       => $inHand,
            ];
            $totalInHand += $inHand;
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;
        $res['total_in_hand'] = $totalInHand;

        return is_mobile($type, "fees/deposit_refund/reports/deposit_in_hand", $res, "view");
    }

    /**
     * All refunds given in a chosen period, with who approved each one.
     */
    public function refundsGiven(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $query = DB::table('deposit_refund as dr')
            ->leftJoin('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->leftJoin('deposite_lagacy_students as ls', 'ls.id', '=', 'dr.legacy_student_id')
            ->leftJoin('tbluser as ah', 'ah.id', '=', 'dr.accounts_head_by')
            ->leftJoin('tbluser as pr', 'pr.id', '=', 'dr.principal_by')
            ->selectRaw("dr.*, 
                CASE WHEN dr.student_id IS NOT NULL THEN CONCAT_WS(' ',s.last_name,s.first_name,s.middle_name) ELSE ls.student_name END as student_name, 
                CASE WHEN dr.student_id IS NOT NULL THEN s.enrollment_no ELSE ls.enrollment_no END as enrollment_no,
                concat_ws(' ',ah.first_name,ah.last_name) as accounts_head_name,
                concat_ws(' ',pr.first_name,pr.last_name) as principal_name")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.status', depositRefundModel::STATUS_PAID);

        if ($from_date != '') {
            $query->whereDate('dr.paid_date', '>=', $from_date);
        }
        if ($to_date != '') {
            $query->whereDate('dr.paid_date', '<=', $to_date);
        }

        $data = $query->orderByDesc('dr.paid_date')->get()->toArray();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;
        $res['from_date'] = $from_date;
        $res['to_date'] = $to_date;
        $res['total_refunded'] = array_sum(array_column($data, 'final_refund_amount'));

        return is_mobile($type, "fees/deposit_refund/reports/refunds_given", $res, "view");
    }

    /**
     * Refunds waiting for someone's approval, with how many days they have been waiting.
     */
    public function pendingApprovals(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $approval_status = $request->input('approval_status');

        $query = DB::table('deposit_refund as dr')
            ->leftJoin('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->leftJoin('deposite_lagacy_students as ls', 'ls.id', '=', 'dr.legacy_student_id')
            ->join('deposit_refund_status_master as sm', 'sm.id', '=', 'dr.status')
            ->selectRaw("dr.*, 
                CASE WHEN dr.student_id IS NOT NULL THEN CONCAT_WS(' ',s.last_name,s.first_name,s.middle_name) ELSE ls.student_name END as student_name, 
                CASE WHEN dr.student_id IS NOT NULL THEN s.enrollment_no ELSE ls.enrollment_no END as enrollment_no,
                sm.title as status_title,
                DATEDIFF(NOW(), COALESCE(dr.accounts_head_date, dr.requested_date)) as days_pending")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->whereIn('dr.status', [
                depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
                depositRefundModel::STATUS_PENDING_PRINCIPAL,
                depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT,
            ]);

        if ($approval_status != '') {
            if ($approval_status == 'pending') {
                $query->whereIn('dr.status', [
                    depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD,
                    depositRefundModel::STATUS_PENDING_PRINCIPAL,
                ]);
            } elseif ($approval_status == 'approved') {
                $query->where('dr.status', depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT);
            }
        }

        if ($from_date != '') {
            $query->whereDate('dr.requested_date', '>=', $from_date);
        }
        if ($to_date != '') {
            $query->whereDate('dr.requested_date', '<=', $to_date);
        }

        $data = $query->orderByDesc('days_pending')->get()->toArray();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;
        $res['from_date'] = $from_date;
        $res['to_date'] = $to_date;
        $res['approval_status'] = $approval_status;

        return is_mobile($type, "fees/deposit_refund/reports/pending_approvals", $res, "view");
    }

    /**
     * Students who have left but have not collected their deposit.
     */
    public function unclaimedDeposits(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $data = [];

        // ERP left students
        $leftStudents = DB::table('tblstudent_enrollment as se')
            ->join('tblstudent as s', 's.id', '=', 'se.student_id')
            ->selectRaw("s.id as student_id, CONCAT_WS(' ',s.last_name,s.first_name,s.middle_name) as student_name,
                s.enrollment_no, s.mobile, MAX(se.end_date) as left_on")
            ->where('s.sub_institute_id', $sub_institute_id)
            ->whereNotNull('se.end_date')
            ->groupBy('s.id', 's.last_name', 's.first_name', 's.middle_name', 's.enrollment_no', 's.mobile')
            ->get();

        foreach ($leftStudents as $student) {
            $collected = (float)DB::table('fees_collect')
                ->where('student_id', $student->student_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('is_deleted', 'N')
                ->sum('deposit');

            if ($collected <= 0) {
                continue;
            }

            $returned = (float)depositRefundModel::where('student_id', $student->student_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('status', depositRefundModel::STATUS_PAID)
                ->sum('final_refund_amount');

            $balance = $collected - $returned;

            if ($balance > 0) {
                $data[] = [
                    'student_name'  => $student->student_name,
                    'enrollment_no' => $student->enrollment_no,
                    'mobile'        => $student->mobile,
                    'left_on'       => $student->left_on,
                    'collected'     => $collected,
                    'returned'      => $returned,
                    'balance'       => $balance,
                    'is_legacy'     => false,
                ];
            }
        }

        // Legacy students with deposit not fully refunded
        $legacyStudents = DB::table('deposite_lagacy_students')->get();
        foreach ($legacyStudents as $legacy) {
            $collected = (float)$legacy->security_deposit;
            if ($collected <= 0) {
                continue;
            }
            $returned = (float)depositRefundModel::where('legacy_student_id', $legacy->id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('status', depositRefundModel::STATUS_PAID)
                ->sum('final_refund_amount');
            $balance = $collected - $returned;
            if ($balance > 0) {
                $data[] = [
                    'student_name'  => $legacy->student_name,
                    'enrollment_no' => $legacy->enrollment_no,
                    'mobile'        => null,
                    'left_on'       => $legacy->received_date,
                    'collected'     => $collected,
                    'returned'      => $returned,
                    'balance'       => $balance,
                    'is_legacy'     => true,
                ];
            }
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;
        $res['total_unclaimed'] = array_sum(array_column($data, 'balance'));

        return is_mobile($type, "fees/deposit_refund/reports/unclaimed_deposits", $res, "view");
    }

    /**
     * One-page statement for a single student, which can be handed to the parent.
     */
    public function studentStatement(Request $request, $student_id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $student = null;
        if (is_string($student_id) && strpos($student_id, 'legacy_') === 0) {
            $legacyId = (int)str_replace('legacy_', '', $student_id);
            $legacy = DB::table('deposite_lagacy_students')->where('id', $legacyId)->first();
            if ($legacy) {
                $student = (object)[
                    'name' => $legacy->student_name,
                    'enrollment_no' => $legacy->enrollment_no,
                    'mobile' => null,
                    'is_legacy' => true,
                    'legacy_id' => $legacy->id,
                ];
            }
        } else {
            $student = DB::table('tblstudent')
                ->selectRaw("concat_ws(' ',last_name,first_name,middle_name) as name, enrollment_no, mobile")
                ->where('id', $student_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->first();
            if ($student) {
                $student = (object)array_merge((array)$student, ['is_legacy' => false]);
            }
        }

        if (!$student) {
            $res['status_code'] = 0;
            $res['message'] = "Student not found.";

            return is_mobile($type, "deposit_refund.index", $res, "redirect");
        }

        if (isset($student->is_legacy) && $student->is_legacy) {
            $collected = (float)DB::table('deposite_lagacy_students')->where('id', $student->legacy_id)->value('security_deposit');
            $refunds = depositRefundModel::where('legacy_student_id', $student->legacy_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('status', depositRefundModel::STATUS_PAID)
                ->orderBy('paid_date')
                ->get();
            $returned = (float)$refunds->sum('final_refund_amount');
            $receipts = collect([]);
        } else {
            $receipts = DB::table('fees_collect')
                ->selectRaw("receipt_no, receiptdate, deposit")
                ->where('student_id', $student_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('is_deleted', 'N')
                ->where('deposit', '>', 0)
                ->orderBy('receiptdate')
                ->get();

            $refunds = depositRefundModel::where('student_id', $student_id)
                ->where('sub_institute_id', $sub_institute_id)
                ->where('status', depositRefundModel::STATUS_PAID)
                ->orderBy('paid_date')
                ->get();

            $collected = (float)$receipts->sum('deposit');
            $returned = (float)$refunds->sum('final_refund_amount');
        }

        $data = [];
        foreach ($receipts as $r) {
            $data[] = ['type' => 'receipt', 'ref' => $r->receipt_no, 'date' => $r->receiptdate, 'collected' => $r->deposit, 'returned' => 0];
        }
        foreach ($refunds as $r) {
            $data[] = ['type' => 'refund', 'ref' => $r->voucher_no, 'date' => $r->paid_date, 'collected' => 0, 'returned' => $r->final_refund_amount];
        }
        usort($data, function($a,$b){ return strtotime($a['date']) <=> strtotime($b['date']); });

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['student'] = $student;
        $res['statement'] = $data;
        $res['collected'] = isset($collected) ? $collected : 0;
        $res['returned'] = isset($returned) ? $returned : 0;
        $res['balance'] = (isset($collected) ? $collected : 0) - (isset($returned) ? $returned : 0);

        return is_mobile($type, "fees/deposit_refund/reports/student_statement", $res, "view");
    }

    /**
     * How much the institute has recovered through deductions.
     */
    public function deductionsMade(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $query = DB::table('deposit_refund_deduction as drd')
            ->join('deposit_refund as dr', 'dr.id', '=', 'drd.deposit_refund_id')
            ->leftJoin('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->leftJoin('deposite_lagacy_students as ls', 'ls.id', '=', 'dr.legacy_student_id')
            ->selectRaw("drd.title, drd.amount, drd.remarks, dr.voucher_no, dr.paid_date,
                CASE WHEN dr.student_id IS NOT NULL THEN CONCAT_WS(' ',s.last_name,s.first_name,s.middle_name) ELSE ls.student_name END as student_name, 
                CASE WHEN dr.student_id IS NOT NULL THEN s.enrollment_no ELSE ls.enrollment_no END as enrollment_no")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.status', depositRefundModel::STATUS_PAID);

        if ($from_date != '') {
            $query->whereDate('dr.paid_date', '>=', $from_date);
        }
        if ($to_date != '') {
            $query->whereDate('dr.paid_date', '<=', $to_date);
        }

        $data = $query->orderByDesc('dr.paid_date')->get()->toArray();

        $byTitle = [];
        foreach ($data as $row) {
            $byTitle[$row->title] = ($byTitle[$row->title] ?? 0) + (float)$row->amount;
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;
        $res['by_title'] = $byTitle;
        $res['from_date'] = $from_date;
        $res['to_date'] = $to_date;
        $res['total_deductions'] = array_sum(array_column($data, 'amount'));

        return is_mobile($type, "fees/deposit_refund/reports/deductions_made", $res, "view");
    }
}
