<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

class PlacementFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'rating'   => 'required|numeric|min:1|max:5',
            'feedback' => 'nullable|string|max:3000',
        ];
    }
}
