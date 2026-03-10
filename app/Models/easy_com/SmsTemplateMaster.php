<?php

namespace App\Models\easy_com;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplateMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sms_template_master';

    // Enable automatic timestamp management
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'template_name',
        'template_id',
        'sender_id',
        'template_content',
        'sort_order',
        'status',
        'sub_institute_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
