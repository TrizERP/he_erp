<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

class PlacementVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'status'  => 'required|in:verified,rejected',
            'remarks' => 'nullable|string|max:1000|required_if:status,rejected',
        ];
    }
}
