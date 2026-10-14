<?php

namespace App\Models\placement;

class PlacementPolicy extends PlacementModel
{
    protected $table = 'placement_policy';

    protected $casts = ['approved_at' => 'datetime'];

}
