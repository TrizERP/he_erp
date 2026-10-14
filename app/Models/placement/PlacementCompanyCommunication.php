<?php

namespace App\Models\placement;

class PlacementCompanyCommunication extends PlacementModel
{
    protected $table = 'placement_company_communication';

    protected $casts = ['communicated_at' => 'datetime'];

}
