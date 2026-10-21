<?php

namespace App\Http\Requests\V1\SchoolSetup;

use Illuminate\Foundation\Http\FormRequest;

class DivisionPlannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => 'required|integer',
            'standard_id' => 'required|integer',
            'division_id' => 'required|integer',
            'capacity' => 'required|integer|min:1',
            'sem_start_date' => 'required|date_format:Y-m-d',
            'sem_end_date' => 'required|date_format:Y-m-d|after_or_equal:sem_start_date',
        ];
    }

    public function attributes(): array
    {
        return ['grade_id' => 'Grade', 'standard_id' => 'Standard', 'division_id' => 'Division', 'capacity' => 'Capacity', 'sem_start_date' => 'Sem start date', 'sem_end_date' => 'Sem end date'];
    }
}
