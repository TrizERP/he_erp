<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

/** Academy (academic section) fields, as on the Blade "Add Masters" form. */
class AcademicSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // menu rights are enforced by the ApiPermission route middleware
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'shift' => 'nullable|string|max:255',
            'medium' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'Title', 'short_name' => 'Short Name', 'sort_order' => 'Sort Order', 'shift' => 'Shift', 'medium' => 'Medium'];
    }
}
