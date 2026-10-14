<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

class PlacementContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:150',
            'designation'  => 'nullable|string|max:100',
            'contact_type' => 'required|in:hr,poc,other',
            'email'        => 'nullable|email|max:150|required_without:mobile',
            'mobile'       => 'nullable|string|max:30|regex:/^[0-9+\-\s()]{6,30}$/',
            'is_primary'   => 'nullable|boolean',
        ];
    }
}
