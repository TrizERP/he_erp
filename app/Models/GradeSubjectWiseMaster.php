<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class GradeSubjectWiseMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grade_subject_wise_master';
    public $timestamps = true;
    protected $fillable = [
        'standard_id',
        'grade_id',
        'subject',
        'title',
        'breakoff',
        'sort_order',
        'syear',
        'sub_institute_id',
        'term_id',
        'created_at',
    ];
}
