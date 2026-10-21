<?php

namespace App\Http\Resources\V1\SchoolSetup;

use Illuminate\Http\Resources\Json\JsonResource;

/** A subject row; `standard_name` comes from the joined standard. */
class SubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'standard_id' => $this->standard_id === null ? null : (int) $this->standard_id,
            'standard_name' => $this->standard_name ?? null,
            'subject_name' => $this->subject_name,
            'subject_code' => $this->subject_code,
            'short_name' => $this->short_name,
            'subject_type' => $this->subject_type,
            'major' => $this->subject_type === 'Major',
        ];
    }
}
