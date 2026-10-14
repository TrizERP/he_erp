<?php

namespace App\Models\placement;

class PlacementDriveRole extends PlacementModel
{
    protected $table = 'placement_drive_role';

    public function drive()
    {
        return $this->belongsTo(PlacementDrive::class, 'drive_id');
    }

    /** Highest CTC on offer in LPA (used by the dream-offer rule). */
    public function topCtc(): float
    {
        return (float) ($this->ctc_max ?? $this->ctc_min ?? 0);
    }
}
