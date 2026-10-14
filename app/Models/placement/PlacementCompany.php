<?php

namespace App\Models\placement;

class PlacementCompany extends PlacementModel
{
    protected $table = 'placement_company';

    public const STATUSES = [
        'prospect'    => 'Prospect',
        'invited'     => 'Invited',
        'confirmed'   => 'Confirmed',
        'visited'     => 'Visited',
        'blacklisted' => 'Blacklisted',
    ];

    public const MOU_STATUSES = ['none' => 'None', 'in_progress' => 'In progress', 'signed' => 'Signed', 'expired' => 'Expired'];

    protected $attributes = ['status' => 'prospect', 'mou_status' => 'none'];

    protected $hidden = ['gst_no', 'pan_no'];

    public function contacts()
    {
        return $this->hasMany(PlacementCompanyContact::class, 'company_id');
    }

    public function communications()
    {
        return $this->hasMany(PlacementCompanyCommunication::class, 'company_id');
    }

}
