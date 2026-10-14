<?php

namespace App\Http\Requests\admission;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level admission.role middleware
    }

    public function rules(): array
    {
        return ['require_documents_verified' => 'nullable|in:0,1', 'require_eligibility' => 'nullable|in:0,1', 'require_selection' => 'nullable|in:0,1', 'require_offer_accepted' => 'nullable|in:0,1', 'allow_fee_waiver' => 'nullable|in:0,1', 'fee_condition' => 'required|in:none,any_payment,full', 'duplicate_inquiry_match' => 'required|in:none,mobile,email,mobile_name'];
    }
}
