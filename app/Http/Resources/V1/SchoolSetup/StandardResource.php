<?php

namespace App\Http\Resources\V1\SchoolSetup;

use Illuminate\Http\Resources\Json\JsonResource;

/** A standard row; `grade_title` / `grade_short_name` come from the joined academic section. */
class StandardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'grade_id' => $this->grade_id === null ? null : (int) $this->grade_id,
            'grade_title' => $this->grade_title ?? null,
            'grade_short_name' => $this->grade_short_name ?? null,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'sort_order' => $this->sort_order === null ? null : (int) $this->sort_order,
            'medium' => $this->medium,
            'course_duration' => $this->course_duration,
            'next_grade_id' => $this->next_grade_id === null ? null : (int) $this->next_grade_id,
            'next_standard_id' => $this->next_standard_id === null ? null : (int) $this->next_standard_id,
        ];
    }
}
