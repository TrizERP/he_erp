<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementDriveRound;
use App\Models\placement\PlacementRoundResult;
use App\Models\placement\PlacementRoundSlot;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/** Interview slots with conflict detection (same student, or same panel, overlapping in time). */
class SlotService
{
    public function schedule(PlacementDriveRound $round, int $applicationId, Carbon $start, Carbon $end, ?string $panel = null, ?string $venue = null, ?int $ignoreSlotId = null): PlacementRoundSlot
    {
        $participant = PlacementRoundResult::where(['round_id' => $round->id, 'application_id' => $applicationId])->first();
        if (! $participant) {
            throw ValidationException::withMessages(['slot' => 'That applicant is not a participant in this round.']);
        }

        $conflicts = $this->conflicts($round, $participant->student_id, $start, $end, $panel, $ignoreSlotId);
        if ($conflicts) {
            throw ValidationException::withMessages(['slot' => $conflicts]);
        }

        return PlacementRoundSlot::updateOrCreate(
            ['round_id' => $round->id, 'application_id' => $applicationId],
            ['student_id' => $participant->student_id, 'slot_start' => $start, 'slot_end' => $end, 'panel' => $panel, 'venue' => $venue ?? $round->venue]
        );
    }

    /** @return string[] */
    public function conflicts(PlacementDriveRound $round, int $studentId, Carbon $start, Carbon $end, ?string $panel, ?int $ignoreSlotId = null): array
    {
        $errors = [];
        if ($end->lte($start)) {
            return ['The slot must end after it starts.'];
        }
        if ($round->round_date && ! $start->isSameDay($round->round_date)) {
            $errors[] = 'The slot must be on the round date ('.$round->round_date->format('d-m-Y').').';
        }

        $overlap = fn ($q) => $q->where('slot_start', '<', $end)->where('slot_end', '>', $start)
            ->when($ignoreSlotId, fn ($q) => $q->where('id', '!=', $ignoreSlotId));

        // The slot being re-scheduled for the same application in this round is replaced, not a conflict.
        $own = PlacementRoundSlot::where('round_id', $round->id)->where('student_id', $studentId)->pluck('id')->all();

        if ($overlap(PlacementRoundSlot::where('student_id', $studentId)->whereNotIn('id', $own))->exists()) {
            $errors[] = 'The student already has another interview slot overlapping this time.';
        }
        if ($panel && $overlap(PlacementRoundSlot::where('panel', $panel)->whereNotIn('id', $own))->exists()) {
            $errors[] = "Panel \"{$panel}\" is already booked for an overlapping slot.";
        }

        return $errors;
    }

    /**
     * Gives every participant without a slot the earliest conflict-free slot across the panels.
     * Returns the number of slots created.
     *
     * @param  string[]  $panels
     */
    public function autoSchedule(PlacementDriveRound $round, Carbon $firstStart, int $minutes, array $panels): int
    {
        $panels = array_values(array_filter($panels)) ?: ['Panel 1'];
        $pending = PlacementRoundResult::where('round_id', $round->id)
            ->whereNotIn('application_id', PlacementRoundSlot::where('round_id', $round->id)->pluck('application_id'))
            ->where('attendance', '!=', 'absent')->get();

        $created = 0;
        foreach ($pending as $row) {
            $start = $firstStart->copy();
            for ($i = 0; $i < 200; $i++) { // bounded search; 200 steps is far more than any real drive needs
                foreach ($panels as $panel) {
                    $end = $start->copy()->addMinutes($minutes);
                    if (! $this->conflicts($round, $row->student_id, $start, $end, $panel)) {
                        $this->schedule($round, $row->application_id, $start, $end, $panel);
                        $created++;
                        continue 3;
                    }
                }
                $start->addMinutes($minutes);
            }
        }

        return $created;
    }
}
