<?php

namespace App\Models\placement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Base for every placement_* table. Adds soft deletes, institute scoping and
 * created_by / sub_institute_id stamping.
 *
 * The tenant scope applies whenever the session carries a sub_institute_id. Console / queue
 * code has no session, so it must filter and stamp sub_institute_id itself.
 */
abstract class PlacementModel extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'sub_institute_id', 'created_by'];

    protected static function booted()
    {
        static::addGlobalScope('placement_tenant', function (Builder $builder) {
            $instituteId = static::sessionInstituteId();
            if ($instituteId !== null) {
                $builder->where($builder->getModel()->getTable().'.sub_institute_id', $instituteId);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->sub_institute_id)) {
                $model->sub_institute_id = static::sessionInstituteId() ?? 0;
            }
            if (empty($model->created_by)) {
                $model->created_by = session()->get('user_id');
            }
        });
    }

    protected static function sessionInstituteId()
    {
        $id = session()->get('sub_institute_id');

        return $id === null || $id === '' ? null : $id;
    }
}
