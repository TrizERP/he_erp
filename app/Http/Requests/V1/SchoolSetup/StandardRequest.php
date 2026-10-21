<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

/** Standard (a "Semester" for colleges) fields from the Blade "Add Masters" form. */
class StandardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'medium' => 'nullable|string|max:255',
            'course_duration' => 'nullable|string|max:255',
            'next_grade_id' => 'nullable|integer',
            'next_standard_id' => 'nullable|integer',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Name', 'short_name' => 'Short Name', 'sort_order' => 'Sort Order', 'medium' => 'Medium', 'course_duration' => 'Course Duration'];
    }
}
