<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementDriveEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        $instituteId = session('sub_institute_id');

        return [
            // empty = criteria apply to every role of the drive
            'role_id'             => ['nullable', 'integer',
                Rule::exists('placement_drive_role', 'id')->where('drive_id', $this->route('drive'))->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'template_id'         => ['nullable', 'integer', Rule::exists('placement_eligibility_template', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'min_cgpa'            => 'nullable|numeric|min:0|max:10',
            'min_10th_percent'    => 'nullable|numeric|min:0|max:100',
            'min_12th_percent'    => 'nullable|numeric|min:0|max:100',
            'max_active_backlogs' => 'nullable|integer|min:0',
            'passing_year'        => 'nullable|integer|min:2000|max:2100',
            'gender'              => 'nullable|in:male,female',
            'standard_ids'        => 'nullable|array',
            'standard_ids.*'      => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', $instituteId)],
        ];
    }
}
