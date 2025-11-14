<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'company_id',
        'is_active'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'internship_student')
                    ->withPivot('status', 'feedback')
                    ->withTimestamps();
    }

    public function shifts()
    {
        return $this->hasMany(InternshipShift::class);
    }

    public function holidays()
    {
        return $this->hasMany(InternshipHoliday::class);
    }
}
