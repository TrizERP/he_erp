<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware; the profile is always the session student's
    }

    public function rules(): array
    {
        return [
            'cgpa'            => 'nullable|numeric|min:0|max:10',
            'tenth_percent'   => 'nullable|numeric|min:0|max:100',
            'twelfth_percent' => 'nullable|numeric|min:0|max:100',
            'active_backlogs' => 'required|integer|min:0|max:100',
            'passing_year'    => 'nullable|integer|min:2000|max:2100',
            'linkedin_url'    => 'nullable|url|max:255',
            'github_url'      => 'nullable|url|max:255',
            'portfolio_url'   => 'nullable|url|max:255',
            'resume'          => 'nullable|file|mimes:pdf,doc,docx|max:'.config('placement.upload.doc_max_kb'),

            'skills'               => 'nullable|array|max:30',
            'skills.*.name'        => 'nullable|string|max:100',
            'skills.*.level'       => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],

            'projects'             => 'nullable|array|max:10',
            'projects.*.title'     => 'nullable|string|max:150',
            'projects.*.description' => 'nullable|string|max:1000',
            'certifications'       => 'nullable|array|max:10',
            'certifications.*.title' => 'nullable|string|max:150',
            'certifications.*.issuer' => 'nullable|string|max:150',
            'achievements'         => 'nullable|string|max:2000',
            'preferred_locations'  => 'nullable|string|max:255',
            'preferred_job_types'  => 'nullable|array',
            'preferred_job_types.*' => 'string|max:50',
        ];
    }
}
