<?php

namespace App\Models\placement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tenant-scoped base for placement tables that have no soft deletes / created_by
 * (results, notification log, settings). Same institute scoping as PlacementModel.
 */
abstract class PlacementPlainModel extends Model
{
    protected $guarded = ['id', 'sub_institute_id'];

    protected static function booted()
    {
        static::addGlobalScope('placement_tenant', function (Builder $builder) {
            $id = session()->get('sub_institute_id');
            if ($id !== null && $id !== '') {
                $builder->where($builder->getModel()->getTable().'.sub_institute_id', $id);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->sub_institute_id)) {
                $model->sub_institute_id = session()->get('sub_institute_id') ?? 0;
            }
        });
    }
}
