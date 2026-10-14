<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        $instituteId = session('sub_institute_id');
        $id = $this->route('company');

        return [
            'name'           => ['required', 'string', 'max:200',
                Rule::unique('placement_company', 'name')->ignore($id)
                    ->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'industry_id'    => ['nullable', 'integer',
                Rule::exists('placement_master', 'id')->where('sub_institute_id', $instituteId)->where('master_type', 'industry')],
            'season_id'      => ['nullable', 'integer', Rule::exists('placement_season', 'id')->where('sub_institute_id', $instituteId)],
            'website'        => 'nullable|url|max:255',
            'hq_location'    => 'nullable|string|max:200',
            'address'        => 'nullable|string|max:1000',
            'mou_status'     => 'required|in:none,in_progress,signed,expired',
            'mou_valid_till' => 'nullable|date|required_if:mou_status,signed',
            'gst_no'         => 'nullable|string|max:30|alpha_num',
            'pan_no'         => 'nullable|string|max:20|alpha_num',
            'visit_history'  => 'nullable|string|max:3000',
            'logo'           => 'nullable|file|mimes:'.config('placement.upload.logo_mimes').'|max:'.config('placement.upload.logo_max_kb'),
        ];
    }
}
