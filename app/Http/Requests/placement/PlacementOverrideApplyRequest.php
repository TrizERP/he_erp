<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementOverrideApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'enrollment_no'   => ['required', 'string', 'max:50',
                Rule::exists('tblstudent', 'enrollment_no')->where('sub_institute_id', session('sub_institute_id'))],
            'role_id'         => ['required', 'integer', Rule::exists('placement_drive_role', 'id')
                ->where('drive_id', $this->route('drive'))->where('sub_institute_id', session('sub_institute_id'))->whereNull('deleted_at')],
            'override_reason' => 'required|string|min:10|max:1000',
        ];
    }
}
