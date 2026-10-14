<?php

namespace App\Models\placement;

class PlacementOffer extends PlacementModel
{
    public const STATUSES = [
        'offered'    => 'Offered',
        'accepted'   => 'Accepted',
        'rejected'   => 'Rejected',
        'revoked'    => 'Revoked',
        'joined'     => 'Joined',
        'not_joined' => 'Not joined',
    ];

    /** Statuses in which the student is holding the offer (counted by the one-offer rule). */
    public const HOLDING = ['offered', 'accepted', 'joined'];

    protected $table = 'placement_offer';

    protected $attributes = ['status' => 'offered'];

    protected $casts = [
        'offer_date'   => 'date:Y-m-d',
        'joining_date' => 'date:Y-m-d',
        'responded_at' => 'datetime',
        'verified_at'  => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(PlacementCompany::class, 'company_id');
    }

    public function drive()
    {
        return $this->belongsTo(PlacementDrive::class, 'drive_id');
    }
}
