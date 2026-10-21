<?php

namespace App\Http\Resources\V1\SchoolSetup;

use Illuminate\Http\Resources\Json\JsonResource;

class DivisionResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => (int) $this->id, 'name' => $this->name];
    }
}
