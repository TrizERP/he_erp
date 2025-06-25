<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_id',
        'student_id',
        'marks',
        'comments',
        'evaluated_by'
    ];

    public function internshipStudent()
    {
        return $this->belongsTo(InternshipStudent::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}