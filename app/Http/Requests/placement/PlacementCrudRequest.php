<?php

namespace App\Http\Requests\placement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared Form Request for the config-driven placement CRUD screens: the rules come from the
 * routed controller, so each screen keeps its validation next to its field definitions.
 * Access is enforced by the placement.role route middleware.
 */
class PlacementCrudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $controller = $this->route()->getController();
        $id = $this->route('id') ? (int) $this->route('id') : null;

        return $controller->rules($this, $id);
    }
}
