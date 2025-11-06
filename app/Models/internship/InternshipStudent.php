<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipStudent extends Model
{
    use HasFactory;

    protected $table = 'internship_student';

    protected $fillable = [
        'internship_id',
        'student_id',
        'status',
        'feedback'
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function marks()
    {
        return $this->hasMany(InternshipMark::class, 'student_id');
    }

    public function attendance()
    {
        return $this->hasMany(InternshipAttendance::class, 'student_id');
    }
}