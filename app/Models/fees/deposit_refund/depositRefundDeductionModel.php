<?php

namespace App\Models\fees\deposit_refund;

use Illuminate\Database\Eloquent\Model;

class depositRefundDeductionModel extends Model
{
    protected $table = "deposit_refund_deduction";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'deposit_refund_id',
        'title',
        'amount',
        'remarks',
    ];
}
