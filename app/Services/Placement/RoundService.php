<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveRound;
use App\Models\placement\PlacementRoundResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Selection rounds: participants, attendance, per-student results and publishing.
 * Round n's participants are the students who cleared round n-1 (published); round 1 takes the
 * shortlisted applicants. Publishing a round updates applications and notifies students.
 */
class RoundService
{
    protected AuditService $audit;
    protected NotificationService $notifier;

    public function __construct(AuditService $audit, NotificationService $notifier)
    {
        $this->audit = $audit;
        $this->notifier = $notifier;
    }

    public function create(PlacementDrive $drive, array $data): PlacementDriveRound
    {
        if (in_array($drive->status, ['completed', 'cancelled', 'draft'], true)) {
            throw ValidationException::withMessages(['round' => 'Rounds can be added once the drive is published and while it is not completed or cancelled.']);
        }

        return DB::transaction(function () use ($drive, $data) {
            $next = (int) PlacementDriveRound::where('drive_id', $drive->id)->max('sequence') + 1;
            $data['sequence'] = $data['sequence'] ?? $next;
            $round = PlacementDriveRound::create($data + ['drive_id' => $drive->id]);

            // At most one final round per drive.
            if (! empty($data['is_final'])) {
                PlacementDriveRound::where('drive_id', $drive->id)->where('id', '!=', $round->id)->update(['is_final' => 0]);
            }
            $this->audit->log('drive_round', $round->id, 'create', null, $data);

            return $round;
        });
    }

    public function previousRound(PlacementDriveRound $round): ?PlacementDriveRound
    {
        return PlacementDriveRound::where('drive_id', $round->drive_id)->where('sequence', '<', $round->sequence)
            ->where('status', '!=', 'cancelled')->orderByDesc('sequence')->first();
    }

    /** Application ids that take part in the round. */
    public function participantIds(PlacementDriveRound $round): array
    {
        $previous = $this->previousRound($round);

        if (! $previous) {
            return PlacementApplication::where('drive_id', $round->drive_id)->where('status', 'shortlisted')->pluck('id')->all();
        }
        if (! $previous->isPublished()) {
            throw ValidationException::withMessages(['round' => "Publish the results of \"{$previous->name}\" before starting this round."]);
        }

        return PlacementRoundResult::where('round_id', $previous->id)->where('result', 'cleared')->pluck('application_id')->all();
    }

    /** Creates the missing result rows for the round's participants (idempotent). */
    public function seed(PlacementDriveRound $round): int
    {
        $ids = $this->participantIds($round);
        $apps = PlacementApplication::whereIn('id', $ids)->get(['id', 'student_id']);
        $existing = PlacementRoundResult::where('round_id', $round->id)->pluck('application_id')->all();

        $created = 0;
        foreach ($apps as $app) {
            if (in_array($app->id, $existing, true)) {
                continue;
            }
            PlacementRoundResult::create([
                'round_id' => $round->id, 'drive_id' => $round->drive_id,
                'application_id' => $app->id, 'student_id' => $app->student_id,
            ]);
            $created++;
        }

        return $created;
    }

    /** @param array<int,string> $map application_id => present|absent */
    public function markAttendance(PlacementDriveRound $round, array $map, string $via = 'manual'): int
    {
        $this->assertOpen($round);
        $changed = 0;

        DB::transaction(function () use ($round, $map, $via, &$changed) {
            foreach (PlacementRoundResult::where('round_id', $round->id)->whereIn('application_id', array_keys($map))->get() as $row) {
                $value = $map[$row->application_id];
                if (! in_array($value, ['present', 'absent', 'pending'], true) || $row->attendance === $value) {
                    continue;
                }
                $row->attendance = $value;
                $row->attendance_via = $via;
                // An absent student cannot stay cleared.
                if ($value === 'absent' && $row->result === 'cleared') {
                    $row->result = 'pending';
                }
                $row->save();
                $changed++;
            }
        });

        return $changed;
    }

    /** @param array<int,array{result:string,score:?string,remarks:?string}> $rows keyed by application_id */
    public function recordResults(PlacementDriveRound $round, array $rows): int
    {
        $this->assertOpen($round);
        $changed = 0;
        $errors = [];

        DB::transaction(function () use ($round, $rows, &$changed, &$errors) {
            $results = PlacementRoundResult::where('round_id', $round->id)->whereIn('application_id', array_keys($rows))->get();
            foreach ($results as $row) {
                $in = $rows[$row->application_id];
                $result = $in['result'] ?? 'pending';
                if (! array_key_exists($result, PlacementRoundResult::RESULTS)) {
                    continue;
                }
                if ($result === 'cleared' && $row->attendance === 'absent') {
                    $errors[] = "Application #{$row->application_id} was absent and cannot be cleared.";
                    continue;
                }
                $row->result = $result;
                $row->score = ($in['score'] ?? '') === '' ? null : $in['score'];
                $row->remarks = $in['remarks'] ?? null;
                if ($row->isDirty()) {
                    $row->save();
                    $changed++;
                }
            }
        });

        if ($errors) {
            throw ValidationException::withMessages(['results' => $errors]);
        }

        return $changed;
    }

    /**
     * Makes the round's results visible, updates applications and notifies students.
     * Every participant needs a decision (cleared / rejected / on hold) and attendance.
     */
    public function publish(PlacementDriveRound $round): PlacementDriveRound
    {
        $this->assertOpen($round);
        $results = PlacementRoundResult::where('round_id', $round->id)->get();

        if ($results->isEmpty()) {
            throw ValidationException::withMessages(['round' => 'This round has no participants.']);
        }
        $undecided = $results->where('result', 'pending')->count();
        if ($undecided) {
            throw ValidationException::withMessages(['round' => "{$undecided} participant(s) still have no result."]);
        }

        $drive = PlacementDrive::with('company')->findOrFail($round->drive_id);

        DB::transaction(function () use ($round, $results, $drive) {
            $now = now();
            foreach ($results as $row) {
                $row->published_at = $now;
                $row->save();

                $application = PlacementApplication::find($row->application_id);
                if ($row->result === 'rejected' && $application && $application->status === 'shortlisted') {
                    $application->update(['status' => 'rejected', 'status_changed_at' => $now, 'remarks' => "Rejected in {$round->name}"]);
                } elseif ($row->result === 'cleared' && $round->is_final && $application) {
                    $application->update(['status' => 'selected', 'status_changed_at' => $now]);
                }
            }
            $round->update(['results_published_at' => $now, 'status' => 'completed']);
            $this->audit->log('drive_round', $round->id, 'publish_results', null, ['participants' => $results->count()]);
        });

        // Notify after commit so a mail failure can never roll back published results.
        foreach ($results->groupBy('result') as $result => $group) {
            $this->notifier->notify('round_result', $group->pluck('student_id')->all(), [
                'company' => $drive->company->name ?? '',
                'round'   => $round->name,
                'result'  => PlacementRoundResult::RESULTS[$result] ?? $result,
                'date'    => now()->format('d-m-Y'),
            ], 'round', $round->id);
        }

        return $round->fresh();
    }

    /** Tells participants about the round's date / venue. */
    public function notifySchedule(PlacementDriveRound $round): int
    {
        $drive = PlacementDrive::with('company')->findOrFail($round->drive_id);
        $studentIds = PlacementRoundResult::where('round_id', $round->id)->pluck('student_id')->all();

        return $this->notifier->notify('round_scheduled', $studentIds, [
            'company' => $drive->company->name ?? '',
            'round'   => $round->name,
            'date'    => $round->round_date?->format('d-m-Y') ?? 'to be announced',
            'venue'   => $round->venue ?? 'to be announced',
        ], 'round', $round->id);
    }

    /** Signed token students scan (QR) to mark themselves present. */
    public function attendanceToken(PlacementDriveRound $round): string
    {
        return substr(hash_hmac('sha256', 'round:'.$round->id.':'.$round->sub_institute_id.':'.$round->round_date, config('app.key')), 0, 32);
    }

    /** Student scans the round's QR. Only listed participants, only on the round day. */
    public function markAttendanceByQr(PlacementDriveRound $round, int $studentId, string $token): void
    {
        if (! hash_equals($this->attendanceToken($round), $token)) {
            throw ValidationException::withMessages(['attendance' => 'This attendance code is not valid.']);
        }
        if ($round->round_date && ! $round->round_date->isToday()) {
            throw ValidationException::withMessages(['attendance' => 'Attendance can only be marked on the day of the round.']);
        }
        $row = PlacementRoundResult::where(['round_id' => $round->id, 'student_id' => $studentId])->first();
        if (! $row) {
            throw ValidationException::withMessages(['attendance' => 'You are not a participant in this round.']);
        }
        $this->markAttendance($round, [$row->application_id => 'present'], 'qr');
    }

    protected function assertOpen(PlacementDriveRound $round): void
    {
        if ($round->isPublished()) {
            throw ValidationException::withMessages(['round' => 'Results for this round are already published.']);
        }
        if ($round->status === 'cancelled') {
            throw ValidationException::withMessages(['round' => 'This round was cancelled.']);
        }
    }
}
