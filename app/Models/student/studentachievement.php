<?php

namespace App\Models\student;

use Illuminate\Database\Eloquent\Model;

class studentAchievementModel extends Model
{
    protected $table = 'tblstudent_achievement';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'discription',
        'type',
        'file path'
    ];
}
