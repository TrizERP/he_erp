<?php

namespace App\Models\placement;

class PlacementDriveEligibility extends PlacementModel
{
    protected $table = 'placement_drive_eligibility';

    protected $casts = ['standard_ids' => 'array'];
}
