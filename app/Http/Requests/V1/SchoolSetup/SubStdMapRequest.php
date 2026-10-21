<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

/** Subject <-> Standard mapping (multipart: it can carry the display image). Checkboxes arrive as true/false. */
class SubStdMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'standard_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'display_name' => 'required|string|max:255',
            'allow_grades' => 'nullable|boolean',
            'elective_subject' => 'nullable|boolean',
            'allow_content' => 'nullable|boolean',
            'subject_category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'add_content' => 'nullable|in:chapterwise,topicwise',
            'display_image' => 'nullable|image|max:5120',
        ];
    }

    public function attributes(): array
    {
        return ['display_name' => 'Display Name', 'sort_order' => 'Sort Order', 'subject_id' => 'Subject', 'standard_id' => 'Standard', 'display_image' => 'Display Image'];
    }
}
