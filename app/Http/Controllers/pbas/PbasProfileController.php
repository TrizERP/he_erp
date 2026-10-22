<?php

namespace App\Http\Controllers\pbas;

use App\Http\Controllers\Controller;
use App\Services\Pbas\PbasAccess;
use App\Services\Pbas\PbasLookups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Part A "General Information" (the original update_profile.php, also the dashboard). The saved copy lives in
 * pbas_profiles; until the faculty member saves it once, the form is pre-filled from their ERP user record.
 */
class PbasProfileController extends Controller
{
    protected const FIELDS = [
        'facultyname', 'fathername', 'department', 'dateofjoining', 'designation', 'payband',
        'promotiondate', 'address', 'email', 'mobile', 'qualification', 'lwp',
    ];

    public function index()
    {
        $institute = PbasAccess::instituteId();
        $saved = DB::table('pbas_profiles')->where(['sub_institute_id' => $institute, 'user_id' => PbasAccess::userId()])->first();

        return view('pbas.profile', [
            'saved' => $saved !== null,
            'profile' => $saved ? (array) $saved : $this->defaultsFromUser($institute),
            'departments' => PbasLookups::departments($institute),
        ]);
    }

    public function store(Request $request)
    {
        $institute = PbasAccess::instituteId();
        $uid = PbasAccess::userId();

        // original rule: every posted field must be non-empty() ("00" is how a zero LWP is entered)
        foreach (self::FIELDS as $field) {
            if (empty($request->input($field))) {
                return redirect()->route('pbas.profile.index')->withInput()->with('pbas_error', 'All Fields are required');
            }
        }
        $departments = PbasLookups::departments($institute);
        if (! array_key_exists((int) $request->input('department'), $departments)) {
            return redirect()->route('pbas.profile.index')->withInput()->with('pbas_error', 'All Fields are required');
        }

        $values = [
            'facultyname' => (string) $request->input('facultyname'),
            'fathername' => (string) $request->input('fathername'),
            'department_id' => (int) $request->input('department'),
            'dateofjoining' => (string) $request->input('dateofjoining'),
            'designation' => (string) $request->input('designation'),
            'payband' => (string) $request->input('payband'),
            'promotiondate' => (string) $request->input('promotiondate'),
            'address' => (string) $request->input('address'),
            'email' => (string) $request->input('email'),
            'mobile' => (string) $request->input('mobile'),
            'extraqualification' => (string) $request->input('qualification'),
            'lwp' => (string) $request->input('lwp'),
        ];

        $key = ['sub_institute_id' => $institute, 'user_id' => $uid];
        $exists = DB::table('pbas_profiles')->where($key)->exists();
        if ($exists) {
            DB::table('pbas_profiles')->where($key)->update($values + ['updated_at' => now()]);
            $flash = ['pbas_updated' => 'Data Updated sucessfully'];
        } else {
            DB::table('pbas_profiles')->insert($key + $values + ['created_at' => now(), 'updated_at' => now()]);
            $flash = ['pbas_success' => 'You data added successfully!'];
        }

        return redirect()->route('pbas.profile.index')->with($flash);
    }

    /** Pre-fill for a first visit: name, joining date, designation (HRMS job title), department, contact. */
    protected function defaultsFromUser(int $institute): array
    {
        $user = DB::table('tbluser')->where(['sub_institute_id' => $institute, 'id' => PbasAccess::userId()])->first();
        if (! $user) {
            return [];
        }
        $title = $user->jobtitle_id
            ? DB::table('hrms_job_titles')->where('id', $user->jobtitle_id)->value('title')
            : null;
        $address = trim(implode(' ', array_filter([$user->address ?? null, $user->address_2 ?? null, $user->city ?? null, $user->pincode ?? null])));

        return [
            'facultyname' => trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name]))),
            'department_id' => $user->department_id,
            'dateofjoining' => $user->joined_date ? substr((string) $user->joined_date, 0, 10) : '2013-01-02', // original form default
            'designation' => (string) ($title ?? ''),
            'address' => $address,
            'email' => (string) $user->email,
            'mobile' => (string) $user->mobile,
        ];
    }
}
