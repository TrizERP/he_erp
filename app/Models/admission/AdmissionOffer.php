<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

class AdmissionOffer extends Model
{
    public const STATUSES = ['issued' => 'Offer Issued', 'accepted' => 'Accepted', 'declined' => 'Declined', 'expired' => 'Expired', 'withdrawn' => 'Withdrawn'];

    protected $table = 'admission_offer';
    protected $fillable = ['sub_institute_id', 'enquiry_id', 'offer_date', 'expiry_date', 'standard_id', 'student_quota', 'notes'];
    protected $casts = ['responded_at' => 'datetime', 'active' => 'boolean'];
}
