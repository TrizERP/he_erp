<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

/** Append-only state-transition / audit trail. */
class AdmissionHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'admission_history';
    protected $guarded = ['id'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'is_override' => 'boolean'];
}
