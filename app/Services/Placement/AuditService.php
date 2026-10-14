<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementAuditLog;

class AuditService
{
    public function log(string $entityType, ?int $entityId, string $action, $old = null, $new = null, ?string $reason = null, $instituteId = null): PlacementAuditLog
    {
        return PlacementAuditLog::create([
            'sub_institute_id' => $instituteId ?? session()->get('sub_institute_id') ?? 0,
            'entity_type'      => $entityType,
            'entity_id'        => $entityId,
            'action'           => $action,
            'reason'           => $reason,
            'old_values'       => $old === null ? null : $this->mask($old),
            'new_values'       => $new === null ? null : $this->mask($new),
            'user_id'          => session()->get('user_id'),
            'ip_address'       => request()->ip(),
        ]);
    }

    /** Old/new snapshots limited to the attributes that actually changed. */
    public function diff(array $original, array $changes): array
    {
        $old = $new = [];
        foreach ($changes as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at'], true)) {
                continue;
            }
            $old[$key] = $original[$key] ?? null;
            $new[$key] = $value;
        }

        return [$old, $new];
    }

    protected function mask($values): array
    {
        $sensitive = config('placement.sensitive_keys', []);
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
