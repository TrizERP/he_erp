<?php

namespace App\Models\placement;

class PlacementEligibilityTemplate extends PlacementModel
{
    protected $table = 'placement_eligibility_template';

    protected $casts = ['standard_ids' => 'array'];

}
