<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class OfferIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['offer_date' => 'required|date', 'expiry_date' => 'required|date|after_or_equal:offer_date', 'standard_id' => 'nullable|integer', 'student_quota' => 'nullable|string|max:50', 'notes' => 'nullable|string|max:500'];
    }
}
