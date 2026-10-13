<?php

namespace App\Http\Controllers\fees\deposit_refund;

use App\Http\Controllers\Controller;
use App\Http\Controllers\easy_com\send_sms_parents\send_sms_parents_controller;
use App\Http\Controllers\fees\other_fees_collect\other_fees_collect_controller;
use App\Models\fees\deposit_refund\depositRefundDeductionModel;
use App\Models\fees\deposit_refund\depositRefundLogModel;
use App\Models\fees\deposit_refund\depositRefundModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\is_mobile;

class depositRefundPaymentController extends Controller
{
    /**
     * Requests approved by the Principal and waiting for the cashier to pay out.
     */
    public function queue(Request $request)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');

        $data = DB::table('deposit_refund as dr')
            ->join('tblstudent as s', 's.id', '=', 'dr.student_id')
            ->leftJoin('standard as st', 'st.id', '=', 'dr.standard_id')
            ->leftJoin('division as d', 'd.id', '=', 'dr.section_id')
            ->selectRaw("dr.*, concat_ws(' ',s.first_name,s.last_name) as student_name, s.enrollment_no,
                concat_ws('/',st.name,d.name) as stddiv")
            ->where('dr.sub_institute_id', $sub_institute_id)
            ->where('dr.syear', $syear)
            ->where('dr.status', depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT)
            ->orderBy('dr.principal_date')
            ->get()->toArray();

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['data'] = $data;

        return is_mobile($type, "fees/deposit_refund/payment_queue", $res, "view");
    }

    /**
     * Cashier confirms payment: assigns the voucher number, builds the printable voucher,
     * marks the request Paid and sends the parent an SMS confirmation.
     */
    public function pay(Request $request, $id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');
        $syear = $request->session()->get('syear');
        $user_id = $request->session()->get('user_id');
        $payment_reference = $request->input('payment_reference');

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT)
            ->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "This request is not ready for payment.";

            return is_mobile($type, "deposit_refund_payment.index", $res, "redirect");
        }

        $stuData = DB::table('tblstudent')
            ->selectRaw("concat_ws(' ',first_name,middle_name,last_name) as name, enrollment_no, mobile")
            ->where('id', $depositRefund->student_id)
            ->first();

        $voucherNoResult = DB::table('deposit_refund')
            ->selectRaw("IFNULL(MAX(CONVERT(SUBSTRING_INDEX(voucher_no,'/',-1), UNSIGNED)),0) AS vid")
            ->where('sub_institute_id', $sub_institute_id)
            ->where('syear', $syear)->get()->first();

        $voucherNo = $syear.'/'.($voucherNoResult->vid + 1);

        $deductions = depositRefundDeductionModel::where('deposit_refund_id', $depositRefund->id)->get();

        $voucherHtml = $this->buildVoucherHtml($depositRefund, $stuData, $voucherNo, $deductions);

        $depositRefund->status = depositRefundModel::STATUS_PAID;
        $depositRefund->voucher_no = $voucherNo;
        $depositRefund->paid_by = $user_id;
        $depositRefund->paid_date = date('Y-m-d H:i:s');
        $depositRefund->payment_reference = $payment_reference;
        $depositRefund->voucher_html = $voucherHtml;
        $depositRefund->save();

        depositRefundLogModel::create([
            'deposit_refund_id' => $depositRefund->id,
            'from_status'       => depositRefundModel::STATUS_APPROVED_PENDING_PAYMENT,
            'to_status'         => depositRefundModel::STATUS_PAID,
            'action'            => 'Paid',
            'remarks'           => $payment_reference,
            'action_by'         => $user_id,
            'action_date'       => date('Y-m-d H:i:s'),
        ]);

        $this->sendRefundSms($depositRefund, $stuData, $voucherNo, $sub_institute_id, $syear);

        $res['status_code'] = 1;
        $res['message'] = "Deposit refund voucher ".$voucherNo." generated successfully.";
        $res['voucher_html'] = $voucherHtml;
        $res['deposit_refund_id'] = $depositRefund->id;

        return is_mobile($type, "fees/deposit_refund/voucher_view", $res, "view");
    }

    /**
     * Reprint a previously issued voucher.
     */
    public function printVoucher(Request $request, $id)
    {
        $type = $request->input('type');
        $sub_institute_id = $request->session()->get('sub_institute_id');

        $depositRefund = depositRefundModel::where('id', $id)
            ->where('sub_institute_id', $sub_institute_id)
            ->where('status', depositRefundModel::STATUS_PAID)
            ->first();

        if (!$depositRefund) {
            $res['status_code'] = 0;
            $res['message'] = "Voucher not found.";

            return is_mobile($type, "deposit_refund_payment.index", $res, "redirect");
        }

        $res['status_code'] = 1;
        $res['message'] = "Success";
        $res['voucher_html'] = $depositRefund->voucher_html;
        $res['deposit_refund_id'] = $depositRefund->id;

        return is_mobile($type, "fees/deposit_refund/voucher_view", $res, "view");
    }

    protected function buildVoucherHtml($depositRefund, $stuData, $voucherNo, $deductions)
    {
        $style = '<style type="text/css">
            body { background: #ffffff; }
            table.deposit-voucher { border-collapse: inherit !important; }
            .deposit-voucher { border: 1px solid #888; overflow: hidden }
            .particulars { border-collapse: collapse !important; }
            .particulars td { border: 1px solid #888; border-collapse: inherit !important; }
            .deposit-voucher td { font-family: Arial, Helvetica, sans-serif !important; padding: 6px 8px; font-size: 13px }
            .double-border { border-bottom: 1px double #000; border-width: 3px; }
            .sc-hd { font-size: 22px; font-weight: 700; font-family: Arial, Helvetica, sans-serif !important }
            .receipt-hd { border: 1px solid #000; padding: 5px 15px; margin-top: 15px }
        </style>';

        $other_fees_controller = new other_fees_collect_controller;
        $amountInWords = ucwords($other_fees_controller->convert_number_to_words((int)$depositRefund->final_refund_amount));
        $amountInWordsStr = $amountInWords != '' ? 'Rupees '.$amountInWords.' Only' : '';

        $html = '<br><table class="deposit-voucher" style="margin:0 auto;" width="80%"><tbody>';
        $html .= '<tr class="double-border"><td colspan="4" align="center"><span class="sc-hd">Deposit Refund Voucher</span></td></tr>';
        $html .= '<tr><td colspan="2">Voucher No. : <b>'.$voucherNo.'</b></td><td colspan="2" align="right">Date : <b>'.date('d-m-Y').'</b></td></tr>';
        $html .= '<tr><td colspan="2">Request No. : <b>'.$depositRefund->request_no.'</b></td><td colspan="2" align="right">Gr.No. : <b>'.$stuData->enrollment_no.'</b></td></tr>';
        $html .= '<tr><td colspan="4">Student Name : <b>'.$stuData->name.'</b></td></tr>';
        $html .= '<tr><td colspan="4">Received By : <b>'.$depositRefund->receiver_name.($depositRefund->receiver_relation ? ' ('.$depositRefund->receiver_relation.')' : '').'</b></td></tr>';

        $html .= '<tr><td colspan="4"><table class="particulars" width="100%" border="0">';
        $html .= '<tr><td><b>Particulars</b></td><td align="right"><b>Amount (Rs.)</b></td></tr>';
        $html .= '<tr><td>Deposit Collected</td><td align="right">'.number_format($depositRefund->deposit_collected, 2).'</td></tr>';
        $html .= '<tr><td>Deposit Already Returned</td><td align="right">'.number_format($depositRefund->deposit_returned_before, 2).'</td></tr>';
        $html .= '<tr><td>Deposit Balance</td><td align="right">'.number_format($depositRefund->deposit_balance, 2).'</td></tr>';

        foreach ($deductions as $deduction) {
            $html .= '<tr><td>Less: '.$deduction->title.'</td><td align="right">-'.number_format($deduction->amount, 2).'</td></tr>';
        }

        $html .= '<tr><td><b>Final Refund Amount</b></td><td align="right"><b>'.number_format($depositRefund->final_refund_amount, 2).'</b></td></tr>';
        $html .= '</table></td></tr>';

        $html .= '<tr><td colspan="4">In Words : <b>'.$amountInWordsStr.'</b></td></tr>';
        $html .= '<tr><td colspan="4">Payment Mode : <b>'.$depositRefund->payment_mode.'</b>';
        if ($depositRefund->payment_mode == 'Cheque') {
            $html .= ' | Cheque No : <b>'.$depositRefund->cheque_no.'</b> | Cheque Date : <b>'.$depositRefund->cheque_date.'</b>';
        } elseif ($depositRefund->payment_mode == 'Bank Transfer') {
            $html .= ' | A/C No : <b>'.$depositRefund->bank_account_number.'</b> | IFSC : <b>'.$depositRefund->ifsc_code.'</b>';
        }
        $html .= '</td></tr>';

        $html .= '<tr><td colspan="3"><b>THIS IS A COMPUTER GENERATED VOUCHER.</b></td><td align="center">'.session()->get('name').'<br>Signature</td></tr>';
        $html .= '</tbody></table>';

        return $style.$html;
    }

    protected function sendRefundSms($depositRefund, $stuData, $voucherNo, $sub_institute_id, $syear)
    {
        try {
            if (empty($stuData->mobile)) {
                return;
            }

            $smsController = new send_sms_parents_controller;
            $msg = "Dear Parent, a security deposit refund of Rs.".number_format($depositRefund->final_refund_amount, 2).
                " for ".$stuData->name." (".$stuData->enrollment_no.") has been processed vide voucher no ".$voucherNo.". - School";

            $response = $smsController->sendSMS($stuData->mobile, $msg, $sub_institute_id);
            $messageId = ($response['error'] ?? 1) == 0 ? ($response['message'] ?? null) : null;

            $smsController->saveParentLog($depositRefund->student_id, $msg, $stuData->mobile, $sub_institute_id, $syear, $messageId);

            $depositRefund->sms_sent = 'Y';
            $depositRefund->save();
        } catch (\Throwable $e) {
            // SMS failures must never block a refund payment that has already been recorded.
        }
    }
}
