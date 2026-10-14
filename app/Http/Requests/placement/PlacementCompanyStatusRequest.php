<?php

namespace App\Http\Requests\placement;

use App\Models\placement\PlacementCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementCompanyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(PlacementCompany::STATUSES))],
            'reason' => 'nullable|string|max:1000|required_if:status,blacklisted',
        ];
    }
}
