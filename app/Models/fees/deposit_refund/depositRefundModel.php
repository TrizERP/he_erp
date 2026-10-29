<?php

namespace App\Models\fees\deposit_refund;

use Illuminate\Database\Eloquent\Model;

class depositRefundModel extends Model
{
    protected $table = "deposit_refund";
    public $timestamps = false;

    const STATUS_PENDING_ACCOUNTS_HEAD = 1;
    const STATUS_RETURNED_BY_ACCOUNTS_HEAD = 2;
    const STATUS_PENDING_PRINCIPAL = 3;
    const STATUS_RETURNED_BY_PRINCIPAL = 4;
    const STATUS_APPROVED_PENDING_PAYMENT = 5;
    const STATUS_PAID = 6;
    const STATUS_CANCELLED = 7;

    protected $fillable = [
        'id',
        'sub_institute_id',
        'syear',
        'student_id',
        'legacy_student_id',
        'standard_id',
        'section_id',
        'request_no',
        'voucher_no',
        'deposit_collected',
        'deposit_returned_before',
        'deposit_balance',
        'unpaid_fees',
        'reason',
        'deduction_total',
        'final_refund_amount',
        'receiver_name',
        'receiver_relation',
        'payment_mode',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'supporting_document',
        'clerk_remarks',
        'status',
        'requested_by',
        'requested_date',
        'accounts_head_by',
        'accounts_head_remarks',
        'accounts_head_date',
        'principal_by',
        'principal_remarks',
        'principal_date',
        'paid_by',
        'paid_date',
        'payment_reference',
        'voucher_html',
        'cancelled_by',
        'cancelled_date',
        'cancellation_reason',
        'sms_sent',
        'created_by',
        'created_ip_address',
        'created_date',
    ];
}
