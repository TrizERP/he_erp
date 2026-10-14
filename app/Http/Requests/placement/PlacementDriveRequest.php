<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementDriveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        $instituteId = session('sub_institute_id');

        return [
            'company_id'            => ['required', 'integer', Rule::exists('placement_company', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'season_id'             => ['nullable', 'integer', Rule::exists('placement_season', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'title'                 => 'required|string|max:200',
            'description'           => 'nullable|string|max:5000',
            'drive_mode'            => 'required|in:on_campus,off_campus,pool,virtual',
            'drive_date'            => 'nullable|date',
            'start_time'            => 'nullable|date_format:H:i',
            'end_time'              => 'nullable|date_format:H:i|after:start_time',
            'venue'                 => 'nullable|string|max:255|required_if:drive_mode,on_campus',
            'registration_deadline' => 'nullable|date|before_or_equal:drive_date',
            'bond_details'          => 'nullable|string|max:3000',
            'jd_file'               => 'nullable|file|mimes:'.config('placement.upload.doc_mimes').'|max:'.config('placement.upload.doc_max_kb'),
        ];
    }
}
