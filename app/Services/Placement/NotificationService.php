<?php

namespace App\Services\Placement;

use App\Jobs\Placement\SendPlacementNotification;
use App\Models\placement\PlacementNotificationLog;
use App\Models\placement\PlacementTemplate;
use Illuminate\Support\Facades\DB;

/**
 * Renders templates and writes one placement_notification_log row per student and channel.
 * Email / WhatsApp rows are delivered by SendPlacementNotification jobs (queue them with
 * QUEUE_CONNECTION=database); in-app rows are delivered immediately and form the student inbox.
 *
 * Works from console / queue contexts: always pass $instituteId there (no session exists).
 */
class NotificationService
{
    protected SettingService $settings;

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Notify students about an event using the institute's active templates for that event.
     *
     * @param  int[]  $studentIds
     * @param  array  $vars  extra placeholders, e.g. ['company' => 'Acme', 'date' => '10-11-2026']
     * @return int number of log rows created
     */
    public function notify(string $event, array $studentIds, array $vars = [], ?string $relatedType = null, ?int $relatedId = null, $instituteId = null): int
    {
        $instituteId = $instituteId ?? session('sub_institute_id');
        if (! $studentIds || ! $this->settings->enabled('notify_'.$event, $instituteId)) {
            return 0;
        }

        $templates = PlacementTemplate::withoutGlobalScopes()
            ->where('sub_institute_id', $instituteId)->where('event_key', $event)->where('status', 1)->get();

        return $this->dispatch($templates, $studentIds, $vars, $event, $relatedType, $relatedId, $instituteId);
    }

    /** Send specific templates (bulk communication screen), ignoring the per-event toggle. */
    public function sendTemplates(array $templateIds, array $studentIds, array $vars = [], $instituteId = null): int
    {
        $instituteId = $instituteId ?? session('sub_institute_id');
        $templates = PlacementTemplate::withoutGlobalScopes()
            ->where('sub_institute_id', $instituteId)->whereIn('id', $templateIds)->where('status', 1)->get();

        return $this->dispatch($templates, $studentIds, $vars, null, null, null, $instituteId);
    }

    protected function dispatch($templates, array $studentIds, array $vars, ?string $event, ?string $relatedType, ?int $relatedId, $instituteId): int
    {
        $students = DB::table('tblstudent')
            ->where('sub_institute_id', $instituteId)->whereIn('id', $studentIds)
            ->selectRaw("id, email, mobile, CONCAT_WS(' ', first_name, last_name) AS name")
            ->get()->keyBy('id');

        $created = 0;
        foreach ($templates as $template) {
            if (! $this->settings->enabled('notify_'.$template->channel, $instituteId)) {
                continue;
            }
            foreach ($students as $student) {
                $contact = $template->channel === 'email' ? $student->email : ($template->channel === 'whatsapp' ? $student->mobile : 'in-app');
                $values = $vars + ['student_name' => $student->name];

                $log = PlacementNotificationLog::withoutGlobalScopes()->create([
                    'sub_institute_id' => $instituteId,
                    'student_id'       => $student->id,
                    'channel'          => $template->channel,
                    'event_key'        => $event ?? $template->event_key,
                    'recipient'        => $this->mask($template->channel, $contact),
                    'subject'          => $template->subject ? $this->render($template->subject, $values) : null,
                    'body'             => $this->render($template->body, $values),
                    'status'           => empty($contact) ? 'skipped' : ($template->channel === 'inapp' ? 'sent' : 'queued'),
                    'error'            => empty($contact) ? 'No '.$template->channel.' contact on the student record' : null,
                    'sent_at'          => $template->channel === 'inapp' ? now() : null,
                    'related_type'     => $relatedType,
                    'related_id'       => $relatedId,
                ]);
                $created++;

                if ($log->status === 'queued') {
                    SendPlacementNotification::dispatch($log->id);
                }
            }
        }

        return $created;
    }

    /** Replaces {placeholders}; unknown ones are left visible so a bad template is easy to spot. */
    public function render(string $text, array $values): string
    {
        $map = [];
        foreach ($values as $key => $value) {
            $map['{'.$key.'}'] = (string) $value;
        }

        return strtr($text, $map);
    }

    protected function mask(string $channel, ?string $contact): ?string
    {
        if (! $contact || $channel === 'inapp') {
            return $contact;
        }
        if ($channel === 'email' && str_contains($contact, '@')) {
            [$local, $domain] = explode('@', $contact, 2);

            return substr($local, 0, 2).str_repeat('*', max(strlen($local) - 2, 1)).'@'.$domain;
        }

        return str_repeat('*', max(strlen($contact) - 3, 0)).substr($contact, -3);
    }
}
