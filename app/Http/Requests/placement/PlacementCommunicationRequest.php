<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'contact_id'      => ['nullable', 'integer',
                Rule::exists('placement_company_contact', 'id')
                    ->where('company_id', $this->route('company'))
                    ->where('sub_institute_id', session('sub_institute_id'))->whereNull('deleted_at')],
            'mode'            => 'required|in:email,call,meeting,visit,other',
            'subject'         => 'nullable|string|max:255',
            'notes'           => 'required|string|max:3000',
            'communicated_at' => 'required|date',
            'follow_up_date'  => 'nullable|date|after_or_equal:communicated_at',
        ];
    }
}
