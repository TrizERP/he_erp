<?php

namespace App\Models\placement;

use Illuminate\Database\Eloquent\Model;

/**
 * Materialised eligibility result per drive/role/student. Rebuilt wholesale by EligibilityService,
 * so it has no soft deletes. Callers must always filter by sub_institute_id.
 */
class PlacementDriveEligibleStudent extends Model
{
    public $timestamps = false;

    protected $table = 'placement_drive_eligible_student';
    protected $guarded = ['id'];
    protected $casts = ['reasons' => 'array', 'computed_at' => 'datetime'];
}
