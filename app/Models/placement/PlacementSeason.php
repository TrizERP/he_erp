<?php

namespace App\Models\placement;

class PlacementSeason extends PlacementModel
{
    protected $table = 'placement_season';

    protected $casts = ['start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d'];

}
