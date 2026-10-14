<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementStudentProfile;
use App\Models\placement\PlacementTraining;
use App\Models\placement\PlacementTrainingAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Training, mock tests and mock interviews: batch assignment, attendance, scores and feedback,
 * plus the student readiness score.
 */
class TrainingService
{
    protected StudentLookup $students;
    protected AuditService $audit;

    public function __construct(StudentLookup $students, AuditService $audit)
    {
        $this->students = $students;
        $this->audit = $audit;
    }

    /**
     * Assigns registered students of the training's courses (empty = every course). Idempotent: existing
     * rows are kept so attendance / scores are never lost. Returns the number of students added.
     */
    public function assign(PlacementTraining $training): int
    {
        $ids = PlacementStudentProfile::where('registration_status', 'registered')->pluck('student_id')->all();
        if (! $ids) {
            return 0;
        }
        $standards = $training->standard_ids ? array_map('intval', $training->standard_ids) : null;
        $eligible = $this->students->students($ids, $standards)->keys()->all();
        $existing = PlacementTrainingAttendance::where('training_id', $training->id)->pluck('student_id')->all();

        $added = 0;
        foreach (array_diff($eligible, $existing) as $studentId) {
            PlacementTrainingAttendance::create(['training_id' => $training->id, 'student_id' => $studentId]);
            $added++;
        }

        return $added;
    }

    /** @param array<int,array{attendance?:string,score?:?string,rating?:?int,feedback?:?string}> $rows keyed by student id */
    public function record(PlacementTraining $training, array $rows, ?array $allowedStudentIds = null): int
    {
        if ($training->status === 'cancelled') {
            throw ValidationException::withMessages(['training' => 'This session was cancelled.']);
        }
        $changed = 0;

        DB::transaction(function () use ($training, $rows, $allowedStudentIds, &$changed) {
            foreach (PlacementTrainingAttendance::where('training_id', $training->id)->whereIn('student_id', array_keys($rows))->get() as $row) {
                if ($allowedStudentIds !== null && ! in_array($row->student_id, $allowedStudentIds, true)) {
                    continue;
                }
                $in = $rows[$row->student_id];
                if (isset($in['attendance'])) {
                    $row->attendance = $in['attendance'];
                }
                if (array_key_exists('score', $in)) {
                    $score = $in['score'] === '' ? null : (float) $in['score'];
                    if ($score !== null && $training->max_score !== null && $score > (float) $training->max_score) {
                        throw ValidationException::withMessages(['training' => "Score {$score} is above the maximum {$training->max_score}."]);
                    }
                    $row->score = $score;
                }
                if (array_key_exists('rating', $in)) {
                    $row->rating = $in['rating'] === '' ? null : (int) $in['rating'];
                }
                if (array_key_exists('feedback', $in)) {
                    $row->feedback = $in['feedback'];
                }
                if ($row->isDirty()) {
                    $row->save();
                    $changed++;
                }
            }
            if ($changed) {
                $this->audit->log('training', $training->id, 'record', null, ['rows' => $changed]);
            }
        });

        return $changed;
    }

    /**
     * Readiness score 0-100 from the evidence that exists: tests 40%, mock interviews 30%,
     * attendance 20%, profile completeness 10%. Missing components are left out and the rest re-weighted.
     * Returns null when there is no evidence at all.
     */
    public function readiness(int $studentId): ?array
    {
        $rows = DB::table('placement_training_attendance as ta')->join('placement_training as t', 't.id', '=', 'ta.training_id')
            ->where('ta.sub_institute_id', session('sub_institute_id'))->where('ta.student_id', $studentId)
            ->whereNull('t.deleted_at')->where('t.status', '!=', 'cancelled')
            ->get(['t.training_type', 't.max_score', 't.session_date', 'ta.attendance', 'ta.score', 'ta.rating']);

        $parts = [];

        $tests = $rows->whereIn('training_type', ['mock_test', 'aptitude', 'technical'])->filter(fn ($r) => $r->score !== null && $r->max_score > 0);
        if ($tests->isNotEmpty()) {
            $parts['tests'] = [40, $tests->avg(fn ($r) => $r->score / $r->max_score * 100)];
        }
        $interviews = $rows->where('training_type', 'mock_interview')->filter(fn ($r) => $r->rating !== null);
        if ($interviews->isNotEmpty()) {
            $parts['interviews'] = [30, $interviews->avg('rating') / 5 * 100];
        }
        $held = $rows->filter(fn ($r) => $r->session_date && $r->session_date <= now()->toDateString());
        if ($held->isNotEmpty()) {
            $parts['attendance'] = [20, $held->where('attendance', 'present')->count() / $held->count() * 100];
        }
        $completeness = PlacementStudentProfile::where('student_id', $studentId)->value('completeness');
        if ($completeness !== null && $parts) {
            $parts['profile'] = [10, (float) $completeness];
        }

        if (! $parts) {
            return null;
        }
        $weight = array_sum(array_column($parts, 0));
        $score = 0;
        foreach ($parts as [$w, $v]) {
            $score += $w * $v;
        }

        return [
            'score'      => (int) round($score / $weight),
            'components' => collect($parts)->map(fn ($p) => round($p[1]))->all(),
        ];
    }
}
