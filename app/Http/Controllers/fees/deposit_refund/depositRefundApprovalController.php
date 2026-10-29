<?php

namespace App\Http\Controllers\fees\deposit_refund;

use App\Http\Controllers\Controller;
use App\Models\fees\deposit_refund\depositRefundLogModel;
use App\Models\fees\deposit_refund\depositRefundModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class depositRefundApprovalController extends Controller
{
    /**
     * Requests waiting for Accounts Head approval.
     */
    public function accountsHeadQueue(Request $request)
    {
        $type = $request->input('type');
        $data = $this->getQueue($request, depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;

        return is_mobile($type, "fees/deposit_refund/accounts_head_queue", $res, "view");
    }

    /**
     * Accounts Head approves (moves to Principal) or returns a request (back to clerk).
     */
    public function accountsHeadAction(Request $request, $id = null)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');
        $decision = $request->input('decision'); // approve | return
        $remarks = $request->input('remarks');
        $bulk_ids = $request->input('bulk_ids', []);

        if (!empty($bulk_ids)) {
            if ($decision == 'return' && trim((string)$remarks) == '') {
                $res['status_code'] = 0;
                $res['message'] = "Please enter a remark explaining why the requests are being returned.";

                return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
            }

            $count = 0;
            foreach ($bulk_ids as $reqId) {
                $depositRefund = depositRefundModel::where('id', $reqId)
                    ->where('sub_institute_id', $sub_institute_id)
                    ->where('status', depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD)
                    ->first();

                if (!$depositRefund) {
                    continue;
                }

                $fromStatus = $depositRefund->status;
                $toStatus = $decision == 'approve'
                    ? depositRefundModel::STATUS_PENDING_PRINCIPAL
                    : depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD;

                $depositRefund->status = $toStatus;
                $depositRefund->accounts_head_by = $user_id;
                $depositRefund->accounts_head_remarks = $remarks;
                $depositRefund->accounts_head_date = date('Y-m-d H:i:s');
                $depositRefund->save();

                depositRefundLogModel::create([
                    'deposit_refund_id' => $depositRefund->id,
                    'from_status'       => $fromStatus,
                    'to_status'         => $toStatus,
                    'action'            => $decision == 'approve' ? 'Approved' : 'Returned',
                    'remarks'           => $remarks,
                    'action_by'         => $user_id,
                    'action_date'       => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }

            $res['status_code'] = 1;
            $res['message'] = $decision == 'approve'
                ? $count . " request(s) forwarded to Principal for approval."
                : $count . " request(s) returned to clerk.";

            return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
        }

        if (!$id) {
            $res['status_code'] = 0;
            $res['message'] = "Invalid request.";

            return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
        }

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', depositRefundModel::STATUS_PENDING_ACCOUNTS_HEAD)
            ->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "This request is no longer pending Accounts Head approval.";

            return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
        }

        if ($decision == 'return' && trim((string)$remarks) == '') {
            $res['status_code'] = 0;
            $res['message'] = "Please enter a remark explaining why the request is being returned.";

            return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
        }

        $fromStatus = $depositRefund->status;
        $toStatus = $decision == 'approve'
            ? depositRefundModel::STATUS_PENDING_PRINCIPAL
            : depositRefundModel::STATUS_RETURNED_BY_ACCOUNTS_HEAD;

        $depositRefund->status = $toStatus;
        $depositRefund->accounts_head_by = $user_id;
        $depositRefund->accounts_head_remarks = $remarks;
        $depositRefund->accounts_head_date = date('Y-m-d H:i:s');
        $depositRefund->save();

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => $fromStatus,
            'to_status'         => $toStatus,
            'action'            => $decision == 'approve' ? 'Approved' : 'Returned',
            'remarks'           => $remarks,
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $res['status_code'] = 1;
        $res['message'] = $decision == 'approve'
            ? "Request forwarded to Principal for approval."
            : "Request returned to clerk.";

        return is_mobile($type, "deposit_refund_accounts_head.index", $res, "redirect");
    }

    /**
     * Requests waiting for Principal approval.
     */
    public function principalQueue(Request $request)
    {
        $type = $request->input('type');
        $data = $this->getQueue($request, depositRefundModel::STATUS_PENDING_PRINCIPAL);

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;

        return is_mobile($type, "fees/deposit_refund/principal_queue", $res, "view");
    }

    /**
     * Principal gives final approval (moves to Approved - Pending Payment) or returns the request.
     */
    public function principalAction(Request $request, $id = null)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $user_id = $request->session()->get('user_id');
        $decision = $request->input('decision'); // approve | return
        $remarks = $request->input('remarks');
        $bulk_ids = $request->input('bulk_ids', []);

        if (!empty($bulk_ids)) {
            if ($decision == 'return' && trim((string)$remarks) == '') {
                $res['status_code'] = 0;
                $res['message'] = "Please enter a remark explaining why the requests are being returned.";

                return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
            }

            $count = 0;
            foreach ($bulk_ids as $reqId) {
                $depositRefund = depositRefundModel::where('id', $reqId)
                    ->where('sub_institute_id', $sub_institute_id)
                    ->where('status', depositRefundModel::STATUS_PENDING_PRINCIPAL)
                    ->first();

                if (!$depositRefund) {
                    continue;
                }

                $fromStatus = $depositRefund->status;
                $toStatus = $decision == 'approve'
                    ? depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT
                    : depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL;

                $depositRefund->status = $toStatus;
                $depositRefund->principal_by = $user_id;
                $depositRefund->principal_remarks = $remarks;
                $depositRefund->principal_date = date('Y-m-d H:i:s');
                $depositRefund->save();

                depositRefundLogModel::create([
                    'deposit_refund_id' => $depositRefund->id,
                    'from_status'       => $fromStatus,
                    'to_status'         => $toStatus,
                    'action'            => $decision == 'approve' ? 'Approved' : 'Returned',
                    'remarks'           => $remarks,
                    'action_by'         => $user_id,
                    'action_date'       => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }

            $res['status_code'] = 1;
            $res['message'] = $decision == 'approve'
                ? $count . " request(s) approved. Sent to cashier for payment."
                : $count . " request(s) returned to clerk.";

            return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
        }

        if (!$id) {
            $res['status_code'] = 0;
            $res['message'] = "Invalid request.";

            return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
        }

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', depositRefundModel::STATUS_PENDING_PRINCIPAL)
            ->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "This request is no longer pending Principal approval.";

            return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
        }

        if ($decision == 'return' && trim((string)$remarks) == '') {
            $res['status_code'] = 0;
            $res['message'] = "Please enter a remark explaining why the request is being returned.";

            return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
        }

        $fromStatus = $depositRefund->status;
        $toStatus = $decision == 'approve'
            ? depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT
            : depositRefundModel::STATUS_RETURNED_BY_PRINCIPAL;

        $depositRefund->status = $toStatus;
        $depositRefund->principal_by = $user_id;
        $depositRefund->principal_remarks = $remarks;
        $depositRefund->principal_date = date('Y-m-d H:i:s');
        $depositRefund->save();

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => $fromStatus,
            'to_status'         => $toStatus,
            'action'            => $decision == 'approve' ? 'Approved' : 'Returned',
            'remarks'           => $remarks,
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $res['status_code'] = 1;
        $res['message'] = $decision == 'approve'
            ? "Request approved. Sent to cashier for payment."
            : "Request returned to clerk.";

        return is_mobile($type, "deposit_refund_principal.index", $res, "redirect");
    }

    protected function getQueue(Request $request, $status)
    {
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');

        return DB::table('deposit_refund as dr')
            ->leftJoin('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->leftJoin('deposite_lagacy_students as ls', 'ls.id', '=', 'dr.legacy_student_id')
            ->leftJoin('standard as st', 'st.id', '=', 'dr.standard_id')
            ->leftJoin('division as d', 'd.id', '=', 'dr.section_id')
            ->selectRaw("dr.*, 
                CASE WHEN dr.student_id IS NOT NULL THEN CONCAT_WS(' ',s.last_name,s.first_name,s.middle_name) ELSE ls.student_name END as student_name, 
                CASE WHEN dr.student_id IS NOT NULL THEN s.enrollment_no ELSE ls.enrollment_no END as enrollment_no,
                CASE WHEN dr.student_id IS NOT NULL THEN concat_ws('/',st.name,d.name) ELSE CONCAT_WS(' / ',ls.semester,ls.student_quota) END as stddiv,
                DATEDIFF(NOW(), dr.requested_date) as days_pending")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.syear', $syear)
            ->where('dr.status', $status)
            ->orderBy('dr.requested_date')
            ->get()->toArray();
    }
}
