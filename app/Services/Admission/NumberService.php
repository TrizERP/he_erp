<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Backend number generation for application (form) numbers and student enrollment numbers.
 *
 * Replaces the legacy `MAX(enrollment_no)+1` code, which (a) was not serialised, so two simultaneous
 * confirmations could receive the same number, (b) used CAST(... AS INT), which MySQL rejects, and
 * (c) in max_enrollment_no_new() returned the SQL text instead of a number.
 *
 * Every generator must run inside a DB transaction that has called lock(): the per-institute lock row is
 * taken with SELECT ... FOR UPDATE, so concurrent callers queue until the first one commits.
 * (SQLite ignores FOR UPDATE; real concurrency can only be exercised on MySQL.)
 *
 * Numbering rules preserved from the legacy code: institute 47 uses per-standard prefixes from
 * enrollment_prefix_master; every other institute uses max(numeric enrollment_no)+1.
 */
class NumberService
{
    public const PREFIX_INSTITUTE = 47;

    public function lock(int $instituteId): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('NumberService::lock() must be called inside a transaction.');
        }

        DB::table('admission_enrollment_lock')->insertOrIgnore([
            'sub_institute_id' => $instituteId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('admission_enrollment_lock')->where('sub_institute_id', $instituteId)->lockForUpdate()->first();
    }

    /** Integer type name for CAST() on the active driver. */
    public function intType(): string
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) ? 'UNSIGNED' : 'INTEGER';
    }

    /** SQL condition: the expression is a non-empty string of digits only (portable MySQL / SQLite). */
    protected function isDigits(string $expression): string
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            ? "{$expression} REGEXP '^[0-9]+$'"
            : "({$expression} <> '' AND {$expression} NOT GLOB '*[^0-9]*')";
    }

    public function nextFormNo(int $instituteId): string
    {
        $max = DB::table('admission_form')
            ->where('sub_institute_id', $instituteId)->whereRaw($this->isDigits('form_no'))
            ->selectRaw('MAX(CAST(form_no AS '.$this->intType().')) as m')
            ->value('m');

        return (string) (((int) $max) + 1);
    }

    /** Next free enrollment number for a standard, following the institute's existing numbering rule. */
    public function nextEnrollmentNo(int $instituteId, ?int $standardId): string
    {
        $cast = $this->intType();
        $students = fn () => DB::table('tblstudent')->where('sub_institute_id', $instituteId);

        $prefix = null;
        $otherPrefixes = [];
        if ($instituteId === self::PREFIX_INSTITUTE) {
            $rows = DB::table('enrollment_prefix_master')->where('sub_institute_id', $instituteId)->get();
            foreach ($rows as $row) {
                $standards = array_map('trim', explode(',', (string) $row->standards));
                if ($standardId !== null && in_array((string) $standardId, $standards, true) && ($row->prefix ?? '') !== '') {
                    $prefix = $row->prefix;
                } elseif (($row->prefix ?? '') !== '') {
                    $otherPrefixes[] = $row->prefix;
                }
            }
        }

        // Only purely numeric numbers take part in "max + 1": a value like "2024-legacy" must not be read as 2024.
        if ($prefix !== null) {
            $tail = 'SUBSTR(enrollment_no, '.(strlen($prefix) + 1).')';
            $max = $students()->where('enrollment_no', 'like', $prefix.'%')->whereRaw($this->isDigits($tail))
                ->selectRaw("MAX(CAST({$tail} AS {$cast})) as m")->value('m');
            $candidate = fn (int $n) => $prefix.$n;
        } else {
            $query = $students()->whereRaw($this->isDigits('enrollment_no'));
            foreach ($otherPrefixes as $other) {
                $query->where('enrollment_no', 'not like', $other.'%');
            }
            $max = $query->selectRaw("MAX(CAST(enrollment_no AS {$cast})) as m")->value('m');
            $candidate = fn (int $n) => (string) $n;
        }

        $n = ((int) $max) + 1;
        // Belt and braces: never hand out a number that is already taken.
        while ($students()->where('enrollment_no', $candidate($n))->exists()) {
            $n++;
        }

        return (string) $candidate($n);
    }
}
