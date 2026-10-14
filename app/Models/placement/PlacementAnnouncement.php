<?php

namespace App\Models\placement;

class PlacementAnnouncement extends PlacementModel
{
    protected $table = 'placement_announcement';

    protected $casts = ['standard_ids' => 'array', 'publish_at' => 'datetime:Y-m-d\TH:i', 'expires_at' => 'datetime:Y-m-d\TH:i'];
}
