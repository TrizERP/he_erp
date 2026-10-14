<?php

namespace App\Models\placement;

class PlacementCompanyRequirement extends PlacementModel
{
    protected $table = 'placement_company_requirement';

    protected $attributes = ['status' => 'pending'];

    public function company()
    {
        return $this->belongsTo(PlacementCompany::class, 'company_id');
    }
}
