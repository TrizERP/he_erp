<?php

namespace App\Services\Api\V1;

use Illuminate\Support\Facades\DB;

/**
 * UI wording per institute, the same rule as the Blade helper get_string(): the default strings
 * (app_language rows with sub_institute_id = 0) overridden by the institute's own active, non-empty rows.
 * e.g. "standard" is "Semester" for colleges.
 */
class LabelService
{
    /** @return array<string,string> string key => label */
    public function map(int $instituteId): array
    {
        $base = DB::table('app_language')->where('sub_institute_id', 0)->whereNotNull('string')
            ->pluck('value', 'string')->all();

        $own = DB::table('app_language')->where('sub_institute_id', $instituteId)->where('status', 1)
            ->whereNotNull('string')->whereNotNull('value')->where('value', '!=', '')
            ->pluck('value', 'string')->all();

        return array_filter(array_replace($base, $own), fn ($v) => $v !== null && $v !== '');
    }
}
