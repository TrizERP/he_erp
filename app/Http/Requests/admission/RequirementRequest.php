<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class RequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:120', 'standard_id' => 'nullable|integer', 'syear' => 'nullable|integer', 'category' => 'nullable|string|max:50', 'is_mandatory' => 'nullable|boolean', 'allowed_mimes' => 'nullable|string|max:100', 'max_kb' => 'nullable|integer|min:10|max:10240', 'sort_order' => 'nullable|integer'];
    }
}
