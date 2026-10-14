<?php

namespace App\Models\placement;

class PlacementRoundSlot extends PlacementModel
{
    protected $table = 'placement_round_slot';

    protected $casts = ['slot_start' => 'datetime', 'slot_end' => 'datetime'];
}
