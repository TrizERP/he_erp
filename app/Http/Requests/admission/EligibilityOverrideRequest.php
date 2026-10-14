<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class EligibilityOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['status' => 'required|in:eligible,ineligible,needs_review', 'reason' => 'required|string|min:5|max:500'];
    }
}
