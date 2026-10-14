<?php

namespace App\Models\placement;

class PlacementApplication extends PlacementModel
{
    protected $table = 'placement_application';

    public const STATUSES = [
        'applied'     => 'Applied',
        'shortlisted' => 'Shortlisted',
        'rejected'    => 'Rejected',
        'withdrawn'   => 'Withdrawn',
        'selected'    => 'Selected',
    ];

    /** Statuses that still count against the student's max-applications limit. */
    public const ACTIVE = ['applied', 'shortlisted', 'selected'];

    protected $attributes = ['status' => 'applied'];

    protected $casts = ['applied_at' => 'datetime', 'status_changed_at' => 'datetime'];

    public function drive()
    {
        return $this->belongsTo(PlacementDrive::class, 'drive_id');
    }

    public function role()
    {
        return $this->belongsTo(PlacementDriveRole::class, 'role_id');
    }
}
