<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['reason' => 'required|string|min:3|max:500', 'mode' => 'required|in:resubmit,reject'];
    }
}
