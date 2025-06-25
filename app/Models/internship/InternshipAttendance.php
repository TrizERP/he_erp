<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'latitude',
        'longitude',
        'location_address',
        'check_in',
        'check_out'
    ];

    public function internshipStudent()
    {
        return $this->belongsTo(InternshipStudent::class);
    }
}