<?php

namespace App\Models\placement;

class PlacementCoordinator extends PlacementModel
{
    protected $table = 'placement_coordinator';

    protected $casts = ['standard_ids' => 'array'];

}
