<?php

namespace App\Models\placement;

class PlacementRoundResult extends PlacementPlainModel
{
    public const ATTENDANCE = ['pending' => 'Pending', 'present' => 'Present', 'absent' => 'Absent'];
    public const RESULTS = ['pending' => 'Pending', 'cleared' => 'Cleared', 'rejected' => 'Rejected', 'on_hold' => 'On hold'];

    protected $table = 'placement_round_result';

    protected $attributes = ['attendance' => 'pending', 'result' => 'pending'];

    protected $casts = ['published_at' => 'datetime'];

    public function round()
    {
        return $this->belongsTo(PlacementDriveRound::class, 'round_id');
    }

    public function application()
    {
        return $this->belongsTo(PlacementApplication::class, 'application_id');
    }
}
