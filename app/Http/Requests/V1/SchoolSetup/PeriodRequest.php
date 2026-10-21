<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

/** A period and its timing groups (standards that share a start / end time). Times are HH:MM or HH:MM:SS. */
class PeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $time = ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'];

        return [
            'title' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
            'academic_section_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'used_for_attendance' => 'nullable|boolean',
            'groups' => 'nullable|array',
            'groups.*.standard_ids' => 'required|array|min:1',
            'groups.*.standard_ids.*' => 'integer',
            'groups.*.start_time' => $time,
            'groups.*.end_time' => $time,
        ];
    }

    public function messages(): array
    {
        return ['groups.*.start_time.regex' => 'Start Time must be a valid time (HH:MM).', 'groups.*.end_time.regex' => 'End Time must be a valid time (HH:MM).'];
    }

    public function attributes(): array
    {
        return ['title' => 'Title', 'short_name' => 'Short Name', 'sort_order' => 'Sort Order', 'groups.*.standard_ids' => 'Standard', 'groups.*.start_time' => 'Start Time', 'groups.*.end_time' => 'End Time'];
    }
}
