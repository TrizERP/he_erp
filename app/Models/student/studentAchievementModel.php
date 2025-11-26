<?php

namespace App\Models\student;

use Illuminate\Database\Eloquent\Model;

class studentAchievementModel extends Model
{
    protected $table = 'classwork_attachment';

    public $timestamps = false;

   protected $fillable = ['student_id','title','document_type','description','file_path'];

}
