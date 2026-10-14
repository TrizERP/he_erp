<?php

namespace App\Models\placement;

class PlacementTrainingAttendance extends PlacementPlainModel
{
    protected $table = 'placement_training_attendance';

    protected $attributes = ['attendance' => 'pending'];

    public function training()
    {
        return $this->belongsTo(PlacementTraining::class, 'training_id');
    }
}
