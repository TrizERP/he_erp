<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

class AdmissionEligibilityRule extends Model
{
    public const OPERATORS = ['>=' => '>=', '>' => '>', '<=' => '<=', '<' => '<', '=' => '=', '!=' => '!=', 'in' => 'is one of (comma list)', 'filled' => 'is provided'];

    protected $table = 'admission_eligibility_rule';
    protected $fillable = ['sub_institute_id', 'standard_id', 'syear', 'label', 'field', 'operator', 'value', 'is_mandatory', 'status'];
    protected $casts = ['is_mandatory' => 'boolean', 'status' => 'boolean'];
}
