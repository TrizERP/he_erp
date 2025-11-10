<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_id',
        'name',
        'start_time',
        'end_time'
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}