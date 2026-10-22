<?php

namespace App\Services\Pbas;

use Illuminate\Support\Facades\DB;

/**
 * Dropdown sources. The original app hard-coded these lists (one academic year, twelve departments); the ERP
 * already owns both, so PBAS reads them instead of keeping copies:
 *   - academic years: the `academic_year` table via the session list set at login (same source as every other module)
 *   - departments:    `hrms_departments` (same table the HRMS module's department dropdown uses)
 */
class PbasLookups
{
    /** @return array<int, array{0:string,1:string}> [value, label] pairs, e.g. ['2025-2026', '2025-2026'] */
    public static function years(): array
    {
        $current = (int) session()->get('syear');
        $years = [];
        foreach ((array) session()->get('academicYears', []) as $row) {
            $y = (int) (is_object($row) ? ($row->syear ?? 0) : ($row['syear'] ?? 0));
            if ($y > 0) {
                $years[$y] = true;
            }
        }
        if ($current > 0) {
            $years[$current] = true;
        }
        $years = array_keys($years);
        sort($years);

        return array_map(fn ($y) => [self::label($y), self::label($y)], $years);
    }

    public static function label(int $syear): string
    {
        return $syear.'-'.($syear + 1);
    }

    /** The academic year the forms should preselect (session year, like every other ERP screen). */
    public static function currentYear(): string
    {
        $current = (int) session()->get('syear');

        return $current > 0 ? self::label($current) : '';
    }

    /** @return array<int, string> hrms_departments.id => department name */
    public static function departments(int $instituteId): array
    {
        return DB::table('hrms_departments')
            ->where('sub_institute_id', $instituteId)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('department')
            ->pluck('department', 'id')
            ->all();
    }
}
