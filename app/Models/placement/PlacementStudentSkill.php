<?php

namespace App\Models\placement;

class PlacementStudentSkill extends PlacementModel
{
    protected $table = 'placement_student_skill';

    public const LEVELS = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
}
