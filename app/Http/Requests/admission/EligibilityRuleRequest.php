<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class EligibilityRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['label' => 'required|string|max:120', 'field' => 'required|in:'.implode(',', array_keys(config('admission.eligibility_fields'))), 'operator' => 'required|in:>=,>,<=,<,=,!=,in,filled', 'value' => 'nullable|string|max:255|required_unless:operator,filled', 'standard_id' => 'nullable|integer', 'syear' => 'nullable|integer', 'is_mandatory' => 'nullable|boolean'];
    }
}
