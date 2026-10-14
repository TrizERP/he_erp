<?php

namespace App\Http\Requests\placement;

use App\Models\placement\PlacementApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'application_ids'   => 'required|array|min:1|max:500',
            'application_ids.*' => ['integer', Rule::exists('placement_application', 'id')
                ->where('drive_id', $this->route('drive'))->where('sub_institute_id', session('sub_institute_id'))],
            'status'            => ['required', Rule::in(['shortlisted', 'rejected', 'applied'])],
            'remarks'           => 'nullable|string|max:1000',
            'override_reason'   => 'nullable|string|max:1000',
        ];
    }
}
