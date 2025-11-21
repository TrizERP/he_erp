<?php

namespace App\Models\student;

use Illuminate\Database\Eloquent\Model;

class studentAchievementModel extends Model
{
    protected $table = 'student_document_type';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'discription',
        'type',
        'file path'
    ];
}
