<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubjectWiseMaster extends Model
{
    use HasFactory;

    protected $table = 'grade_subject_wise_master';

    protected $fillable = [
        'subject',
        'title',
        'breakoff',
        'sort_order',
        'spear',
        'sub_institute_id',
        'term_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
