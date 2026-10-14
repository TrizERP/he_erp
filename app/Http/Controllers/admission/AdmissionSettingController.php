<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\EligibilityRuleRequest;
use App\Http\Requests\admission\RequirementRequest;
use App\Http\Requests\admission\SettingsRequest;
use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionEligibilityRule;
use App\Services\Admission\AuditService;
use App\Services\Admission\SettingService;
use Illuminate\Support\Facades\DB;

/** Admission settings: policy switches, required-document rules and eligibility criteria (admin only). */
class AdmissionSettingController extends AdmissionController
{
    public function __construct(protected SettingService $settings, protected AuditService $audit)
    {
    }

    public function index()
    {
        $inst = $this->instituteId();

        return view('admission.process.settings', ['data' => [
            'settings'     => $this->settings->all($inst),
            'requirements' => AdmissionDocumentRequirement::where('sub_institute_id', $inst)->orderBy('sort_order')->orderBy('id')->get(),
            'rules'        => AdmissionEligibilityRule::where('sub_institute_id', $inst)->orderBy('id')->get(),
            'standards'    => DB::table('standard')->where('sub_institute_id', $inst)->orderBy('name')->get(['id', 'name']),
            'fields'       => config('admission.eligibility_fields'),
            'operators'    => AdmissionEligibilityRule::OPERATORS,
        ]]);
    }

    public function save(SettingsRequest $request)
    {
        $inst = $this->instituteId();
        $values = [];
        // unchecked checkboxes are absent from the request, which means "off"
        foreach (array_keys(config('admission.settings')) as $key) {
            $values[$key] = $request->input($key, in_array($key, ['fee_condition', 'duplicate_inquiry_match'], true) ? null : '0');
        }

        return $this->attempt(function () use ($inst, $values) {
            $before = $this->settings->all($inst);
            $this->settings->save($inst, $values);
            $this->audit->log($inst, null, 'settings', null, 'settings_changed', null, null, null, false, $before, $this->settings->all($inst));
        }, 'Admission settings saved.');
    }

    public function storeRequirement(RequirementRequest $request)
    {
        $inst = $this->instituteId();
        $row = AdmissionDocumentRequirement::create(['sub_institute_id' => $inst, 'status' => true] + $this->requirementData($request));
        $this->audit->log($inst, null, 'settings', $row->id, 'requirement_added', null, null, $row->name);

        return $this->flash(1, 'Required document added.');
    }

    public function updateRequirement(RequirementRequest $request, int $requirement)
    {
        $row = AdmissionDocumentRequirement::where('sub_institute_id', $this->instituteId())->findOrFail($requirement);
        $row->update($this->requirementData($request));
        $this->audit->log($this->instituteId(), null, 'settings', $row->id, 'requirement_updated', null, null, $row->name);

        return $this->flash(1, 'Required document updated.');
    }

    public function destroyRequirement(int $requirement)
    {
        // deactivated, not deleted: documents already uploaded against it keep their meaning
        $row = AdmissionDocumentRequirement::where('sub_institute_id', $this->instituteId())->findOrFail($requirement);
        $row->update(['status' => false]);
        $this->audit->log($this->instituteId(), null, 'settings', $row->id, 'requirement_deactivated', null, null, $row->name);

        return $this->flash(1, 'Required document deactivated.');
    }

    public function storeRule(EligibilityRuleRequest $request)
    {
        $inst = $this->instituteId();
        $row = AdmissionEligibilityRule::create(['sub_institute_id' => $inst, 'status' => true] + $this->ruleData($request));
        $this->audit->log($inst, null, 'settings', $row->id, 'eligibility_rule_added', null, null, $row->label, false, null, $row->only(['field', 'operator', 'value']));

        return $this->flash(1, 'Eligibility rule added.');
    }

    public function destroyRule(int $rule)
    {
        $row = AdmissionEligibilityRule::where('sub_institute_id', $this->instituteId())->findOrFail($rule);
        $row->update(['status' => false]);
        $this->audit->log($this->instituteId(), null, 'settings', $row->id, 'eligibility_rule_deactivated', null, null, $row->label);

        return $this->flash(1, 'Eligibility rule deactivated.');
    }

    protected function requirementData(RequirementRequest $request): array
    {
        return [
            'name'          => $request->input('name'),
            'standard_id'   => $request->integer('standard_id') ?: null,
            'syear'         => $request->integer('syear') ?: null,
            'category'      => $request->input('category') ?: null,
            'is_mandatory'  => $request->boolean('is_mandatory'),
            'allowed_mimes' => $request->input('allowed_mimes') ?: null,
            'max_kb'        => $request->integer('max_kb') ?: null,
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];
    }

    protected function ruleData(EligibilityRuleRequest $request): array
    {
        return [
            'label'        => $request->input('label'),
            'field'        => $request->input('field'),
            'operator'     => $request->input('operator'),
            'value'        => $request->input('value'),
            'standard_id'  => $request->integer('standard_id') ?: null,
            'syear'        => $request->integer('syear') ?: null,
            'is_mandatory' => $request->boolean('is_mandatory'),
        ];
    }
}
