<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

class PlacementSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|integer',
            'slot_start'     => 'required|date',
            'slot_end'       => 'required|date|after:slot_start',
            'panel'          => 'nullable|string|max:100',
            'venue'          => 'nullable|string|max:255',
        ];
    }
}
