<?php

namespace App\Models\placement;

class PlacementOutcome extends PlacementModel
{
    public const TYPES = [
        'higher_studies'       => 'Higher studies',
        'entrepreneurship'     => 'Entrepreneurship',
        'off_campus_placement' => 'Off-campus placement',
        'other'                => 'Other',
    ];

    protected $table = 'placement_outcome_other';

    protected $casts = ['outcome_date' => 'date:Y-m-d'];
}
