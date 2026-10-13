<?php

namespace App\Models\fees\deposit_refund;

use Illuminate\Database\Eloquent\Model;

class depositRefundLogModel extends Model
{
    protected $table = "deposit_refund_log";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'deposit_refund_id',
        'from_status',
        'to_status',
        'action',
        'remarks',
        'action_by',
        'action_date',
    ];
}
