<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementRoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:150',
            'round_type_id' => ['nullable', 'integer', Rule::exists('placement_master', 'id')
                ->where('sub_institute_id', session('sub_institute_id'))->where('master_type', 'round_type')->whereNull('deleted_at')],
            'mode'          => 'required|in:offline,online',
            'round_date'    => 'nullable|date',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i|after:start_time',
            'venue'         => 'nullable|string|max:255',
            'is_final'      => 'nullable|boolean',
        ];
    }
}
