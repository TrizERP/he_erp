<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class OfferRespondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['decision' => 'required|in:accept,decline', 'notes' => 'nullable|string|max:500'];
    }
}
