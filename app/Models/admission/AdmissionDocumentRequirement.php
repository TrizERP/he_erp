<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

class AdmissionDocumentRequirement extends Model
{
    protected $table = 'admission_document_requirement';
    protected $fillable = ['sub_institute_id', 'name', 'standard_id', 'syear', 'category', 'is_mandatory', 'allowed_mimes', 'max_kb', 'sort_order', 'status'];
    protected $casts = ['is_mandatory' => 'boolean', 'status' => 'boolean'];
}
