<?php

namespace App\Models\placement;

class PlacementCompanyCommunication extends PlacementModel
{
    protected $table = 'placement_company_communication';

    protected $casts = ['communicated_at' => 'datetime'];

    public function company()
    {
        return $this->belongsTo(PlacementCompany::class, 'company_id');
    }
}
