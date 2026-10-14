<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

class PlacementOfferTransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'status'          => 'required|in:accepted,rejected,revoked,joined,not_joined',
            'reason'          => 'nullable|string|max:1000|required_if:status,rejected,revoked,not_joined',
            'override_reason' => 'nullable|string|min:10|max:1000',
        ];
    }
}
