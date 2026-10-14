<?php

namespace App\Models\placement;

class PlacementDriveRound extends PlacementModel
{
    protected $table = 'placement_drive_round';

    protected $attributes = ['status' => 'scheduled', 'mode' => 'offline'];

    protected $casts = ['round_date' => 'date:Y-m-d', 'results_published_at' => 'datetime'];

    public function drive()
    {
        return $this->belongsTo(PlacementDrive::class, 'drive_id');
    }

    public function results()
    {
        return $this->hasMany(PlacementRoundResult::class, 'round_id');
    }

    public function isPublished(): bool
    {
        return $this->results_published_at !== null;
    }
}
