<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

class AdmissionDocument extends Model
{
    public const STATUSES = ['pending' => 'Pending', 'uploaded' => 'Uploaded', 'verified' => 'Verified', 'rejected' => 'Rejected', 'resubmission_required' => 'Resubmission Required'];

    protected $table = 'admission_document';
    // status, storage and verification columns are set by DocumentService only
    protected $fillable = ['sub_institute_id', 'enquiry_id', 'requirement_id', 'doc_name'];
    protected $casts = ['verified_at' => 'datetime'];
}
