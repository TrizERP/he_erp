<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementDrive;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Drive state machine: Draft -> Published -> Registration Open -> Registration Closed -> In Progress -> Completed (Cancelled from any open state). */
class DriveService
{
    public const TRANSITIONS = [
        'draft'               => ['published', 'cancelled'],
        'published'           => ['registration_open', 'draft', 'cancelled'],
        'registration_open'   => ['registration_closed', 'cancelled'],
        'registration_closed' => ['registration_open', 'in_progress', 'cancelled'],
        'in_progress'         => ['completed', 'cancelled'],
        'completed'           => [],
        'cancelled'           => [],
    ];

    protected AuditService $audit;
    protected EligibilityService $eligibility;
    protected NotificationService $notifier;

    public function __construct(AuditService $audit, EligibilityService $eligibility, NotificationService $notifier)
    {
        $this->audit = $audit;
        $this->eligibility = $eligibility;
        $this->notifier = $notifier;
    }

    public function allowedNext(PlacementDrive $drive): array
    {
        return self::TRANSITIONS[$drive->status] ?? [];
    }

    public function transition(PlacementDrive $drive, string $to, ?string $reason = null): PlacementDrive
    {
        $from = $drive->status;

        if (! in_array($to, $this->allowedNext($drive), true)) {
            throw ValidationException::withMessages(['status' => "A drive cannot move from {$from} to {$to}."]);
        }
        if ($to === 'cancelled' && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'A reason is required to cancel a drive.']);
        }
        if ($from === 'draft' && $to === 'published') {
            $this->assertPublishable($drive);
        }

        $drive = DB::transaction(function () use ($drive, $from, $to, $reason) {
            $drive->status = $to;
            if ($to === 'published') {
                $drive->published_at = now();
            }
            if ($to === 'cancelled') {
                $drive->cancel_reason = $reason;
            }
            $drive->save();

            // Eligibility is computed when the drive is published and refreshed when registration opens,
            // so late profile verifications and edits are picked up.
            if (in_array($to, ['published', 'registration_open'], true)) {
                $this->eligibility->computeForDrive($drive);
            }

            $this->audit->log('drive', $drive->id, 'status_change', ['status' => $from], ['status' => $to], $reason, $drive->sub_institute_id);

            return $drive;
        });

        // Notify after commit: a mail / WhatsApp problem must never undo the status change.
        if ($to === 'published') {
            $this->notifyEligible($drive, 'drive_published');
        }

        return $drive;
    }

    /** Tells every eligible student about the drive (event: drive_published | deadline_reminder). */
    public function notifyEligible(PlacementDrive $drive, string $event, bool $onlyNotApplied = false): int
    {
        $ids = DB::table('placement_drive_eligible_student')
            ->where('sub_institute_id', $drive->sub_institute_id)->where(['drive_id' => $drive->id, 'is_eligible' => 1])
            ->distinct()->pluck('student_id');

        if ($onlyNotApplied) {
            $applied = DB::table('placement_application')->where('drive_id', $drive->id)->whereNull('deleted_at')
                ->where('status', '!=', 'withdrawn')->pluck('student_id');
            $ids = $ids->diff($applied);
        }
        $company = PlacementCompany::withoutGlobalScopes()->find($drive->company_id);

        return $this->notifier->notify($event, $ids->values()->all(), [
            'company'  => $company->name ?? '',
            'date'     => $drive->drive_date?->format('d-m-Y') ?? 'to be announced',
            'venue'    => $drive->venue ?? 'to be announced',
            'deadline' => $drive->registration_deadline?->format('d-m-Y H:i') ?? '',
        ], 'drive', $drive->id, $drive->sub_institute_id);
    }

    /**
     * Scheduler: reminds eligible students who have not applied when the deadline is near.
     * Each drive is reminded once. Returns the number of drives reminded.
     */
    public function sendDeadlineReminders(): int
    {
        $settings = app(SettingService::class);
        $count = 0;

        $drives = PlacementDrive::withoutGlobalScopes()->where('status', 'registration_open')
            ->whereNotNull('registration_deadline')->where('registration_deadline', '>', now())->get();

        foreach ($drives as $drive) {
            $hours = (int) $settings->get('reminder_hours_before', $drive->sub_institute_id);
            if (! $settings->enabled('notify_deadline_reminder', $drive->sub_institute_id) || $drive->registration_deadline->gt(now()->addHours($hours))) {
                continue;
            }
            $already = DB::table('placement_notification_log')->where('sub_institute_id', $drive->sub_institute_id)
                ->where(['event_key' => 'deadline_reminder', 'related_type' => 'drive', 'related_id' => $drive->id])->exists();
            if ($already) {
                continue;
            }
            $this->notifyEligible($drive, 'deadline_reminder', true);
            $count++;
        }

        return $count;
    }

    protected function assertPublishable(PlacementDrive $drive): void
    {
        $errors = [];
        if (! $drive->roles()->exists()) {
            $errors[] = 'Add at least one role before publishing.';
        }
        if (! $drive->registration_deadline) {
            $errors[] = 'Set a registration deadline before publishing.';
        } elseif ($drive->registration_deadline->isPast()) {
            $errors[] = 'The registration deadline is in the past.';
        }
        $company = PlacementCompany::find($drive->company_id);
        if (! $company || $company->status === 'blacklisted') {
            $errors[] = 'The company is blacklisted or missing.';
        }
        if ($errors) {
            throw ValidationException::withMessages(['status' => $errors]);
        }
    }

    /**
     * Scheduler entry point (no session): closes registration for drives whose deadline passed.
     * Returns the number of drives closed.
     */
    public function closeExpiredRegistrations(): int
    {
        $closed = 0;
        $drives = PlacementDrive::withoutGlobalScopes()
            ->where('status', 'registration_open')
            ->whereNotNull('registration_deadline')
            ->where('registration_deadline', '<', now())
            ->get();

        foreach ($drives as $drive) {
            $drive->update(['status' => 'registration_closed']);
            $this->audit->log('drive', $drive->id, 'auto_close_registration', ['status' => 'registration_open'], ['status' => 'registration_closed'], 'Deadline passed', $drive->sub_institute_id);
            $closed++;
        }

        return $closed;
    }
}
