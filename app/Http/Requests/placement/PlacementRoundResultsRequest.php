<?php

namespace App\Http\Requests\placement;

use App\Models\placement\PlacementRoundResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Bulk save of attendance and/or results for one round: rows[application_id][attendance|result|score|remarks]. */
class PlacementRoundResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level placement.role middleware
    }

    public function rules(): array
    {
        return [
            'rows'                => 'required|array|max:1000',
            'rows.*.attendance'   => ['nullable', Rule::in(array_keys(PlacementRoundResult::ATTENDANCE))],
            'rows.*.result'       => ['nullable', Rule::in(array_keys(PlacementRoundResult::RESULTS))],
            'rows.*.score'        => 'nullable|numeric|min:0|max:100000',
            'rows.*.remarks'      => 'nullable|string|max:1000',
        ];
    }
}
