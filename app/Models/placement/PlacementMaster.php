<?php

namespace App\Models\placement;

class PlacementMaster extends PlacementModel
{
    protected $table = 'placement_master';

    public const TYPES = [
        'industry'         => 'Industry Types',
        'job_role'         => 'Job Roles / Designations',
        'job_type'         => 'Job Types',
        'location'         => 'Employment Locations',
        'salary_band'      => 'Salary Bands',
        'package_category' => 'Package Categories',
        'skill'            => 'Skills',
        'round_type'       => 'Selection Round Types',
        'document_type'    => 'Document Types',
    ];

}
