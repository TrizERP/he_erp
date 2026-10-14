<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementSetting;

/**
 * Per-institute module settings (placement_setting key/value). Safe to call from console / queue
 * code: pass the institute id explicitly there, because no session exists.
 */
class SettingService
{
    public const DEFAULTS = [
        'notify_email'           => '1',
        'notify_whatsapp'        => '1',
        'notify_inapp'           => '1',
        'notify_drive_published' => '1',
        'notify_round_scheduled' => '1',
        'notify_round_result'    => '1',
        'notify_offer_issued'    => '1',
        'notify_offer_status'    => '1',
        'notify_deadline_reminder' => '1',
        'notify_announcement'    => '1',
        'reminder_hours_before'  => '24',
    ];

    /** Human labels for the settings screen. */
    public const LABELS = [
        'notify_email'           => 'Send email notifications',
        'notify_whatsapp'        => 'Send WhatsApp notifications',
        'notify_inapp'           => 'Send in-app notifications',
        'notify_drive_published' => 'Notify eligible students when a drive is published',
        'notify_round_scheduled' => 'Notify participants when a round is scheduled',
        'notify_round_result'    => 'Notify students when round results are published',
        'notify_offer_issued'    => 'Notify students when an offer is issued',
        'notify_offer_status'    => 'Notify students on offer status changes',
        'notify_deadline_reminder' => 'Send registration-deadline reminders',
        'notify_announcement'    => 'Push announcements through the notification channels',
        'reminder_hours_before'  => 'Hours before the deadline to send the reminder',
    ];

    protected array $cache = [];

    public function all($instituteId = null): array
    {
        $instituteId = $instituteId ?? session('sub_institute_id');
        if (! isset($this->cache[$instituteId])) {
            $stored = PlacementSetting::withoutGlobalScopes()
                ->where('sub_institute_id', $instituteId)->pluck('setting_value', 'setting_key')->all();
            $this->cache[$instituteId] = array_merge(self::DEFAULTS, $stored);
        }

        return $this->cache[$instituteId];
    }

    public function get(string $key, $instituteId = null)
    {
        return $this->all($instituteId)[$key] ?? null;
    }

    public function enabled(string $key, $instituteId = null): bool
    {
        return (string) $this->get($key, $instituteId) === '1';
    }

    public function set(string $key, $value, $instituteId = null): void
    {
        $instituteId = $instituteId ?? session('sub_institute_id');
        PlacementSetting::withoutGlobalScopes()->updateOrCreate(
            ['sub_institute_id' => $instituteId, 'setting_key' => $key],
            ['setting_value' => (string) $value]
        );
        unset($this->cache[$instituteId]);
    }
}
