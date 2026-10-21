<?php

namespace App\Http\Resources\V1\SchoolSetup;

use Illuminate\Http\Resources\Json\JsonResource;

class AcademicSectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'short_name' => $this->short_name,
            'sort_order' => $this->sort_order === null ? null : (int) $this->sort_order,
            'shift' => $this->shift,
            'medium' => $this->medium,
        ];
    }
}
