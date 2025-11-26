<?php

namespace App\Models\student;

use Illuminate\Database\Eloquent\Model;

class studentAchievementModel extends Model
{
    protected $table = 'classwork_attachment';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'type',
        'description',
        'title',
        'file_path',
        'sub_institute_id',
        'created_by'
    ];
}
