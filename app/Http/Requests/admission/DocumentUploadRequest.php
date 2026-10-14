<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['requirement_id' => 'nullable|integer', 'doc_name' => 'nullable|string|max:120', 'file' => 'required|file'];
    }
}
