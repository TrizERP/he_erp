<?php

namespace App\Http\Resources\V1\SchoolSetup;

use Illuminate\Http\Resources\Json\JsonResource;

/** sub_std_map row joined with the subject and standard names. Yes/'' flags become booleans. */
class SubStdMapResource extends JsonResource
{
    public function toArray($request): array
    {
        $image = trim((string) ($this->display_image ?? ''), '/');

        return [
            'id' => (int) $this->id,
            'standard_id' => (int) $this->standard_id,
            'standard_name' => $this->standard_name ?? null,
            'subject_id' => (int) $this->subject_id,
            'subject_name' => $this->subject_name ?? null,
            'subject_code' => $this->subject_code ?? null,
            'display_name' => $this->display_name,
            'allow_grades' => $this->allow_grades === 'Yes',
            'elective_subject' => $this->elective_subject === 'Yes',
            'allow_content' => $this->allow_content === 'Yes',
            'subject_category' => $this->subject_category,
            'sort_order' => $this->sort_order === null || $this->sort_order === '' ? null : (int) $this->sort_order,
            'add_content' => $this->add_content,
            'display_image' => $image === '' ? null : '/'.$image,
            'display_image_url' => $image === '' ? null : asset('storage/'.$image),
        ];
    }
}
