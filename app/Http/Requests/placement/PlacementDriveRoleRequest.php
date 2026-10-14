<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementDriveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        $master = fn (string $type) => Rule::exists('placement_master', 'id')
            ->where('sub_institute_id', session('sub_institute_id'))->where('master_type', $type)->whereNull('deleted_at');

        return [
            'title'          => 'required|string|max:150',
            'role_master_id' => ['nullable', 'integer', $master('job_role')],
            'job_type_id'    => ['nullable', 'integer', $master('job_type')],
            'ctc_min'        => 'nullable|numeric|min:0',
            'ctc_max'        => 'nullable|numeric|min:0|gte:ctc_min',
            'stipend'        => 'nullable|numeric|min:0',
            'location'       => 'nullable|string|max:150',
            'openings'       => 'nullable|integer|min:1',
        ];
    }
}
