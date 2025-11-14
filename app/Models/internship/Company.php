<?php

namespace App\Models\internship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'internship_companies';

    protected $fillable = [
        'name',
        'industry',
        'address',
        'contact_email',
        'contact_phone',
        'requirements'
    ];

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}
