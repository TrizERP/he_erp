<?php

namespace App\Models\OBE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class addCourseCO extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbladd_course_co';
}
