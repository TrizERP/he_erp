<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class SelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['status' => 'required|in:under_review,shortlisted,waitlisted,rejected,selected', 'remarks' => 'nullable|string|max:500'];
    }
}
