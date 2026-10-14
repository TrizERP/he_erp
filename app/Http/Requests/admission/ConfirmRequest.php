<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['override_reason' => 'nullable|string|max:500'];
    }
}
