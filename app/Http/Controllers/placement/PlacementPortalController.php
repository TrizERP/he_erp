<?php

namespace App\Http\Controllers\placement;

use App\Http\Controllers\Controller;
use App\Models\placement\PlacementApplication;
use App\Models\placement\PlacementCompany;
use App\Models\placement\PlacementCompanyRequirement;
use App\Models\placement\PlacementDrive;
use App\Models\placement\PlacementDriveRound;
use App\Models\placement\PlacementRoundResult;
use App\Services\Placement\AuditService;
use App\Services\Placement\RoundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

/**
 * Recruiter portal (no ERP login). Access is a time-limited signed link the TPO generates for one drive
 * (applicants + round results) or one company (post a requirement). Recruiters see only what they need to
 * decide - never contact details - and can only record round results, which the TPO then publishes.
 * There is no session here, so every query is keyed explicitly by the link's drive / company.
 */
class PlacementPortalController extends Controller
{
    // ------------------------------------------------------------ TPO side: generate links

    public function driveLink(Request $request, AuditService $audit, $drive)
    {
        $drive = PlacementDrive::findOrFail($drive);
        $days = max(1, min(30, (int) $request->input('days', 14)));
        $url = URL::temporarySignedRoute('placement.portal.drive', now()->addDays($days), ['drive' => $drive->id]);
        $audit->log('drive', $drive->id, 'portal_link', null, ['valid_days' => $days]);

        return redirect()->route('placement.drives.show', $drive->id)
            ->with('data', ['status_code' => 1, 'message' => "Recruiter link (valid {$days} days): {$url}"]);
    }

    public function companyLink(Request $request, AuditService $audit, $company)
    {
        $company = PlacementCompany::findOrFail($company);
        $url = URL::temporarySignedRoute('placement.portal.company', now()->addDays(14), ['company' => $company->id]);
        $audit->log('company', $company->id, 'portal_link', null, ['valid_days' => 14]);

        return redirect()->route('placement.companies.show', $company->id)
            ->with('data', ['status_code' => 1, 'message' => "Recruiter link (valid 14 days): {$url}"]);
    }

    // ------------------------------------------------------------ recruiter side

    public function drive(Request $request, $drive)
    {
        $drive = PlacementDrive::withoutGlobalScopes()->findOrFail($drive);
        $institute = $drive->sub_institute_id;

        $applicants = DB::table('placement_application as a')
            ->join('tblstudent as s', 's.id', '=', 'a.student_id')
            ->join('placement_drive_role as r', 'r.id', '=', 'a.role_id')
            ->leftJoin('placement_student_profile as p', fn ($j) => $j->on('p.student_id', '=', 'a.student_id')->on('p.sub_institute_id', '=', 'a.sub_institute_id'))
            ->leftJoin('tblstudent_enrollment as e', fn ($j) => $j->on('e.student_id', '=', 's.id')->where('e.syear', DB::table('school_setup')->where('Id', $institute)->value('syear')))
            ->leftJoin('standard as st', 'st.id', '=', 'e.standard_id')
            ->where('a.sub_institute_id', $institute)->where('a.drive_id', $drive->id)->whereNull('a.deleted_at')
            ->whereIn('a.status', ['applied', 'shortlisted', 'selected'])
            ->orderBy('s.first_name')
            ->get(['a.id', 'a.status', 'r.title as role', 'st.name as course', 'p.cgpa', 'p.resume_file', 'p.id as profile_id',
                DB::raw("CONCAT_WS(' ', s.first_name, s.last_name) as name")]);

        $skills = DB::table('placement_student_skill')->where('sub_institute_id', $institute)->whereIn('profile_id', $applicants->pluck('profile_id')->filter())
            ->whereNull('deleted_at')->get(['profile_id', 'skill_name'])->groupBy('profile_id');

        $rounds = PlacementDriveRound::withoutGlobalScopes()->where('sub_institute_id', $institute)->where('drive_id', $drive->id)->orderBy('sequence')->get();
        $results = PlacementRoundResult::withoutGlobalScopes()->where('sub_institute_id', $institute)->whereIn('round_id', $rounds->pluck('id'))->get()->groupBy('round_id');
        $names = $applicants->keyBy('id');

        return view('placement.portal.drive', ['data' => [
            'drive'      => $drive,
            'company'    => PlacementCompany::withoutGlobalScopes()->find($drive->company_id),
            'applicants' => $applicants->map(function ($a) use ($skills) {
                $a->skills = ($skills[$a->profile_id] ?? collect())->pluck('skill_name')->implode(', ');
                $a->resume_url = $a->resume_file ? Storage::disk(config('placement.disk'))->url($a->resume_file) : null;

                return $a;
            }),
            'rounds'     => $rounds,
            'results'    => $results,
            'names'      => $names,
            'post_url'   => URL::temporarySignedRoute('placement.portal.results', now()->addHours(4), ['drive' => $drive->id]),
        ]]);
    }

    public function results(Request $request, AuditService $audit, RoundService $rounds, $drive)
    {
        $drive = PlacementDrive::withoutGlobalScopes()->findOrFail($drive);
        $data = $request->validate([
            'round_id'        => 'required|integer',
            'rows'            => 'required|array|max:1000',
            'rows.*.result'   => ['nullable', Rule::in(['pending', 'cleared', 'rejected', 'on_hold'])],
            'rows.*.score'    => 'nullable|numeric|min:0|max:100000',
            'rows.*.remarks'  => 'nullable|string|max:1000',
        ]);

        $round = PlacementDriveRound::withoutGlobalScopes()->where('drive_id', $drive->id)->findOrFail($data['round_id']);
        $changed = $rounds->recordResults($round, array_filter($data['rows'], fn ($r) => ! empty($r['result'])));
        $audit->log('drive_round', $round->id, 'recruiter_results', null, ['rows' => $changed], 'Recorded through the recruiter portal', $drive->sub_institute_id);

        return back()->with('done', "{$changed} result(s) saved. The placement cell will publish them.");
    }

    public function company(Request $request, $company)
    {
        $company = PlacementCompany::withoutGlobalScopes()->findOrFail($company);

        return view('placement.portal.company', ['data' => [
            'company'  => $company,
            'post_url' => URL::temporarySignedRoute('placement.portal.requirement', now()->addHours(4), ['company' => $company->id]),
        ]]);
    }

    public function requirement(Request $request, AuditService $audit, $company)
    {
        $company = PlacementCompany::withoutGlobalScopes()->findOrFail($company);
        $data = $request->validate([
            'contact_name'  => 'required|string|max:150',
            'contact_email' => 'required|email|max:150',
            'title'         => 'required|string|max:200',
            'description'   => 'nullable|string|max:4000',
            'openings'      => 'nullable|integer|min:1|max:100000',
            'ctc_range'     => 'nullable|string|max:80',
            'skills'        => 'nullable|string|max:500',
        ]);

        $req = PlacementCompanyRequirement::withoutGlobalScopes()->create($data + ['sub_institute_id' => $company->sub_institute_id, 'company_id' => $company->id, 'status' => 'pending']);
        $audit->log('company_requirement', $req->id, 'submitted', null, ['company_id' => $company->id, 'title' => $req->title], 'Submitted through the recruiter portal', $company->sub_institute_id);

        return back()->with('done', 'Thank you. Your requirement was sent to the placement cell for review.');
    }
}
