<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementComposeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        $instituteId = session('sub_institute_id');

        return [
            'audience'       => 'required|in:registered,verified,drive_eligible,drive_applicants,drive_shortlisted',
            'drive_id'       => ['nullable', 'integer', 'required_if:audience,drive_eligible,drive_applicants,drive_shortlisted',
                Rule::exists('placement_drive', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'standard_ids'   => 'nullable|array',
            'standard_ids.*' => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', $instituteId)],
            'template_ids'   => 'required|array|min:1',
            'template_ids.*' => ['integer', Rule::exists('placement_template', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
        ];
    }
}
