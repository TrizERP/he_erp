<?php

namespace App\Models\placement;

class PlacementStudentProfile extends PlacementModel
{
    protected $table = 'placement_student_profile';

    // verification / placement state is only changed through the services
    protected $guarded = [
        'id', 'sub_institute_id', 'created_by', 'student_id', 'registration_status', 'undertaking_accepted_at',
        'verification_status', 'verification_remarks', 'verified_by', 'verified_at', 'completeness',
        'is_placed', 'debarred_until', 'debar_reason',
    ];

    protected $attributes = ['registration_status' => 'not_registered', 'verification_status' => 'pending', 'active_backlogs' => 0];

    protected $casts = [
        'projects'                => 'array',
        'certifications'          => 'array',
        'achievements'            => 'array',
        'preferences'             => 'array',
        'undertaking_accepted_at' => 'datetime',
        'verified_at'             => 'datetime',
        'debarred_until'          => 'date',
    ];

    public function skills()
    {
        return $this->hasMany(PlacementStudentSkill::class, 'profile_id');
    }

    public function isDebarred(): bool
    {
        return $this->debarred_until && $this->debarred_until->isFuture();
    }

    /** Registered and verified: the minimum to see drives and apply. */
    public function isActive(): bool
    {
        return $this->registration_status === 'registered' && $this->verification_status === 'verified';
    }
}
