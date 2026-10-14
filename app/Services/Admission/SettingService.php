<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;

/** Per-institute admission policy switches (admission_setting) over config('admission.settings') defaults. */
class SettingService
{
    private array $cache = [];

    public function all(int $instituteId): array
    {
        if (! isset($this->cache[$instituteId])) {
            $stored = DB::table('admission_setting')->where('sub_institute_id', $instituteId)->pluck('setting_value', 'setting_key')->all();
            $this->cache[$instituteId] = array_merge(config('admission.settings'), $stored);
        }

        return $this->cache[$instituteId];
    }

    public function get(int $instituteId, string $key): string
    {
        return (string) ($this->all($instituteId)[$key] ?? '');
    }

    public function flag(int $instituteId, string $key): bool
    {
        return $this->get($instituteId, $key) === '1';
    }

    public function save(int $instituteId, array $values): void
    {
        $defaults = config('admission.settings');
        $enums = [
            'fee_condition'           => ['none', 'any_payment', 'full'],
            'duplicate_inquiry_match' => ['none', 'mobile', 'email', 'mobile_name'],
        ];

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue; // unknown keys are ignored, never stored
            }
            $value = (string) $value;
            if (isset($enums[$key])) {
                if (! in_array($value, $enums[$key], true)) {
                    throw AdmissionException::because("Invalid value for {$key}.");
                }
            } else {
                $value = $value === '1' ? '1' : '0';
            }
            DB::table('admission_setting')->updateOrInsert(
                ['sub_institute_id' => $instituteId, 'setting_key' => $key],
                ['setting_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        unset($this->cache[$instituteId]);
    }
}
