<?php

namespace App\Services\Admission;

use App\Models\admission\AdmissionHistory;

class AuditService
{
    public function log(
        int $instituteId,
        ?int $enquiryId,
        string $entityType,
        ?int $entityId,
        string $action,
        ?string $from = null,
        ?string $to = null,
        ?string $comments = null,
        bool $override = false,
        $old = null,
        $new = null
    ): AdmissionHistory {
        return AdmissionHistory::create([
            'sub_institute_id' => $instituteId,
            'enquiry_id'       => $enquiryId,
            'entity_type'      => $entityType,
            'entity_id'        => $entityId,
            'action'           => $action,
            'from_status'      => $from,
            'to_status'        => $to,
            'comments'         => $comments,
            'is_override'      => $override,
            'old_values'       => $old === null ? null : $this->mask($old),
            'new_values'       => $new === null ? null : $this->mask($new),
            'user_id'          => session()->get('user_id'),
            'ip_address'       => request()->ip(),
        ]);
    }

    /** Mask personal values so the trail never carries full contact / ID numbers. */
    protected function mask($values): array
    {
        $sensitive = config('admission.sensitive_keys', []);
        $values = (array) $values;

        foreach ($values as $key => $value) {
            if (in_array($key, $sensitive, true) && is_scalar($value) && $value !== '') {
                $value = (string) $value;
                $values[$key] = str_repeat('*', max(strlen($value) - 3, 0)).substr($value, -3);
            }
        }

        return $values;
    }
}
