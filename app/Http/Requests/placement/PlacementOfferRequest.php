<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    /** PPO conversions identify the student by enrollment number. */
    protected function prepareForValidation(): void
    {
        if ($this->filled('enrollment_no')) {
            $this->merge(['student_id' => \Illuminate\Support\Facades\DB::table('tblstudent')
                ->where('sub_institute_id', session('sub_institute_id'))->where('enrollment_no', $this->enrollment_no)->value('id')]);
        }
    }

    public function rules(): array
    {
        $instituteId = session('sub_institute_id');
        $isPpo = $this->boolean('is_ppo');

        return [
            'is_ppo'          => 'nullable|boolean',
            // drive offer: pick the application; PPO: pick the student and company
            'application_id'  => [Rule::requiredIf(! $isPpo), 'nullable', 'integer',
                Rule::exists('placement_application', 'id')->where('sub_institute_id', $instituteId)],
            'student_id'      => [Rule::requiredIf($isPpo), 'nullable', 'integer', Rule::exists('tblstudent', 'id')->where('sub_institute_id', $instituteId)],
            'company_id'      => [Rule::requiredIf($isPpo), 'nullable', 'integer', Rule::exists('placement_company', 'id')->where('sub_institute_id', $instituteId)->whereNull('deleted_at')],
            'internship_id'   => 'nullable|integer',
            'role_title'      => 'required|string|max:150',
            'ctc_fixed'       => 'nullable|numeric|min:0|max:100000',
            'ctc_variable'    => 'nullable|numeric|min:0|max:100000',
            'joining_bonus'   => 'nullable|numeric|min:0|max:100000',
            'location'        => 'nullable|string|max:150',
            'offer_date'      => 'nullable|date',
            'joining_date'    => 'nullable|date|after_or_equal:offer_date',
            'bond_details'    => 'nullable|string|max:3000',
            'offer_letter'    => 'nullable|file|mimes:pdf|max:'.config('placement.upload.doc_max_kb'),
            'override_reason' => 'nullable|string|min:10|max:1000',
        ];
    }
}
