<?php

namespace App\Models\placement;

class PlacementDrive extends PlacementModel
{
    protected $table = 'placement_drive';

    public const STATUSES = [
        'draft'               => 'Draft',
        'published'           => 'Published',
        'registration_open'   => 'Registration Open',
        'registration_closed' => 'Registration Closed',
        'in_progress'         => 'In Progress',
        'completed'           => 'Completed',
        'cancelled'           => 'Cancelled',
    ];

    public const MODES = ['on_campus' => 'On-campus', 'off_campus' => 'Off-campus', 'pool' => 'Pool', 'virtual' => 'Virtual'];

    protected $attributes = ['status' => 'draft', 'drive_mode' => 'on_campus'];

    protected $casts = [
        'drive_date'            => 'date:Y-m-d',
        'registration_deadline' => 'datetime',
        'published_at'          => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(PlacementCompany::class, 'company_id');
    }

    public function roles()
    {
        return $this->hasMany(PlacementDriveRole::class, 'drive_id');
    }

    public function eligibilities()
    {
        return $this->hasMany(PlacementDriveEligibility::class, 'drive_id');
    }

    public function applications()
    {
        return $this->hasMany(PlacementApplication::class, 'drive_id');
    }

    public function isRegistrationOpen(): bool
    {
        return $this->status === 'registration_open'
            && (! $this->registration_deadline || $this->registration_deadline->isFuture());
    }
}
