<?php

namespace App\Models\placement;

class PlacementResumeReview extends PlacementModel
{
    protected $table = 'placement_resume_review';

    protected $attributes = ['status' => 'requested'];

    protected $casts = ['requested_at' => 'datetime', 'reviewed_at' => 'datetime'];
}
