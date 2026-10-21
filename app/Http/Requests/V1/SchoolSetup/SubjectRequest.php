<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

/** Subject fields. `major` replaces the Blade checkbox that sends subject_type = "Major". */
class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'standard_id' => 'required|integer',
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'major' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return ['standard_id.required' => 'Please select a Standard.'];
    }

    public function attributes(): array
    {
        return ['subject_name' => 'Subject Name', 'subject_code' => 'Subject Code', 'short_name' => 'Short Name'];
    }
}
