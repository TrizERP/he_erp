<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_id',
        'title',
        'date',
        'description'
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}