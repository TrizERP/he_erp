<?php

namespace App\Models\placement;

class PlacementTraining extends PlacementModel
{
    public const TYPES = [
        'aptitude'       => 'Aptitude',
        'soft_skills'    => 'Soft skills',
        'mock_interview' => 'Mock interview',
        'mock_test'      => 'Mock test',
        'technical'      => 'Technical',
        'other'          => 'Other',
    ];

    protected $table = 'placement_training';

    protected $attributes = ['status' => 'scheduled', 'training_type' => 'aptitude'];

    protected $casts = ['session_date' => 'date:Y-m-d', 'standard_ids' => 'array'];

    public function attendance()
    {
        return $this->hasMany(PlacementTrainingAttendance::class, 'training_id');
    }
}
