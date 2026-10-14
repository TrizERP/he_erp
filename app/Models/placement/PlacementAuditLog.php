<?php

namespace App\Models\placement;

use Illuminate\Database\Eloquent\Model;

/** Append-only audit trail; deliberately not a PlacementModel (no soft deletes). */
class PlacementAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'placement_audit_log';
    protected $guarded = ['id'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
}
