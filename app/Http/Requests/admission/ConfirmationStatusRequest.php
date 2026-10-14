<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['status' => 'required|in:on_hold,cancelled,pending', 'reason' => 'required|string|min:3|max:500'];
    }
}
