<?php

namespace App\Models\sms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsRemarkMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sms_remark_master';

    // Enable automatic timestamp management
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'title',
        'sort_order',
        'remark_status',
        'sub_institute_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
