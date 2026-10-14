<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class InquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['status' => 'required|in:new,contacted,follow_up,closed', 'note' => 'nullable|string|max:500'];
    }
}
