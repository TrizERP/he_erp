<?php

namespace App\Services\Pbas;

use Illuminate\Support\Facades\DB;

/**
 * Loads one faculty member's PBAS data and scores it (the original pbas_facl.php "Annual Self-Assessment form").
 * Like the original, every record of the member is included regardless of academic year.
 */
class PbasReportService
{
    public function build(int $instituteId, int $userId): array
    {
        $profile = DB::table('pbas_profiles')->where(['sub_institute_id' => $instituteId, 'user_id' => $userId])->first();
        if ($profile) {
            $profile = (array) $profile;
            $profile['department'] = $profile['department_id']
                ? (string) DB::table('hrms_departments')->where('id', $profile['department_id'])->value('department')
                : '';
        }

        $data = [];
        foreach (PbasEntries::all() as $slug => $def) {
            $data[str_replace('-', '_', $slug)] = DB::table($def['table'])
                ->where(['sub_institute_id' => $instituteId, 'user_id' => $userId])
                ->orderBy('id')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return ['profile' => $profile ?: null] + PbasScore::compute($data);
    }
}
