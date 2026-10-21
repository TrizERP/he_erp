<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:255'];
    }

    public function attributes(): array
    {
        return ['name' => 'Division'];
    }
}
