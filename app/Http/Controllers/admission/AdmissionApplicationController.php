<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\EligibilityOverrideRequest;
use App\Http\Requests\admission\InquiryStatusRequest;
use App\Http\Requests\admission\ReasonRequest;
use App\Http\Requests\admission\SelectionRequest;
use App\Models\admission\AdmissionHistory;
use App\Models\admission\AdmissionOffer;
use App\Services\Admission\AdmissionAccess;
use App\Services\Admission\ApplicationQuery;
use App\Services\Admission\ConfirmationService;
use App\Services\Admission\DocumentService;
use App\Services\Admission\EligibilityService;
use App\Services\Admission\FeeService;
use App\Services\Admission\OfferService;
use App\Services\Admission\SelectionService;
use App\Services\Admission\SettingService;
use App\Services\Admission\StageService;
use App\Services\Admission\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Application review: list, the per-application workspace (timeline, documents, eligibility, selection,
 * offer, fee, confirmation checklist) and the inquiry/application/eligibility/selection actions.
 */
class AdmissionApplicationController extends AdmissionController
{
    public function __construct(
        protected WorkflowService $workflow,
        protected ApplicationQuery $queries,
        protected DocumentService $documents,
        protected EligibilityService $eligibility,
        protected SelectionService $selection,
        protected OfferService $offers,
        protected FeeService $fees,
        protected ConfirmationService $confirmation,
        protected SettingService $settings
    ) {
    }

    /** Application Review list, also reused (with a preset focus) by Merit/Selection, Confirmation and Admitted Students. */
    public function index(Request $request)
    {
        return $this->listing($request, 'Application Review', []);
    }

    public function merit(Request $request)
    {
        return $this->listing($request, 'Merit / Selection', ['application_status' => 'submitted'], 'selection');
    }

    public function confirmList(Request $request)
    {
        return $this->listing($request, 'Admission Confirmation', ['application_status' => 'submitted'], 'confirmation');
    }

    public function admitted(Request $request)
    {
        return $this->listing($request, 'Admitted Students', ['confirmation_status' => 'confirmed'], 'confirmation');
    }

    protected function listing(Request $request, string $title, array $preset, string $focus = 'application')
    {
        $inst = $this->instituteId();
        $this->workflow->syncMissing($inst);
        $this->offers->expireDue($inst);

        $filters = array_merge($request->only(['q', 'application_status', 'eligibility_status', 'selection_status', 'confirmation_status']), $preset);
        $query = $this->queries->listColumns($this->queries->base($inst, $this->syear()));
        foreach (['application_status', 'eligibility_status', 'selection_status', 'confirmation_status'] as $column) {
            $query->when(! empty($filters[$column]), fn ($q) => $q->where('w.'.$column, $filters[$column]));
        }
        $query->when(! empty($filters['q']), function ($q) use ($filters) {
            $term = '%'.trim($filters['q']).'%';
            $q->where(fn ($w) => $w->where('ae.first_name', 'like', $term)->orWhere('ae.last_name', 'like', $term)
                ->orWhere('ae.mobile', 'like', $term)->orWhere('ae.enquiry_no', 'like', $term));
        });

        return view('admission.process.list', ['data' => [
            'title'   => $title,
            'rows'    => $query->orderByDesc('ae.id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'preset'  => $preset,
            'focus'   => $focus,
        ]]);
    }

    /** Per-application workspace. */
    public function show(int $enquiry)
    {
        $inst = $this->instituteId();
        $syear = (int) ($this->syear() ?? 0);
        $applicant = $this->workflow->applicantOrFail($inst, $enquiry);
        $this->offers->expireDue($inst, $enquiry);
        $wf = $this->workflow->ensure($inst, $enquiry);

        $standardId = $wf->standard_id;
        $res = [
            'applicant'   => $applicant,
            'workflow'    => $wf,
            'standard'    => $standardId ? DB::table('standard')->where('id', $standardId)->where('sub_institute_id', $inst)->value('name') : null,
            'standards'   => DB::table('standard')->where('sub_institute_id', $inst)->orderBy('name')->get(['id', 'name']),
            'documents'   => $this->documents->documentsFor($inst, $enquiry),
            'requirements' => $this->documents->requirementsFor($inst, $enquiry),
            'gaps'        => $wf->application_status === 'submitted' ? [] : $this->workflow->submissionGaps($inst, $enquiry),
            'evaluation'  => $this->eligibility->evaluate($inst, $enquiry),
            'offers'      => AdmissionOffer::where('sub_institute_id', $inst)->where('enquiry_id', $enquiry)->orderByDesc('id')->get(),
            'fee'         => $this->fees->summary($inst, $enquiry, $syear),
            'checklist'   => $this->confirmation->checklist($inst, $enquiry, $syear),
            'history'     => AdmissionHistory::where('sub_institute_id', $inst)->where('enquiry_id', $enquiry)->orderByDesc('id')->limit(100)->get(),
            'settings'    => $this->settings->all($inst),
            'student'     => $wf->student_id ? DB::table('tblstudent')->where('id', $wf->student_id)->where('sub_institute_id', $inst)->first(['id', 'enrollment_no', 'first_name', 'last_name']) : null,
            'can'         => collect(array_keys(AdmissionAccess::CAPABILITIES))->mapWithKeys(fn ($c) => [$c => AdmissionAccess::can($c)])->all(),
        ];

        // plain-language progress: where is it now, what to do next
        $stageService = app(StageService::class);
        $offers = $res['offers'];
        $res['stage'] = $stageService->describe([
            'form_count' => $applicant['form'] ? 1 : 0, 'application_status' => $wf->application_status,
            'eligibility_status' => $wf->eligibility_status, 'selection_status' => $wf->selection_status,
            'confirmation_status' => $wf->confirmation_status, 'student_id' => $wf->student_id,
            'docs_to_verify' => $res['documents']->where('status', 'uploaded')->count(),
            'last_offer' => optional($offers->first())->status,
        ]);
        $res['steps'] = $stageService->steps([
            'form' => (bool) $applicant['form'], 'submitted' => $wf->application_status === 'submitted',
            'docs_ok' => (bool) $applicant['form'] && ! $this->documents->unverifiedMandatory($inst, $enquiry),
            'eligible' => $wf->eligibility_status === 'eligible', 'selected' => $wf->selection_status === 'selected',
            'offer_accepted' => $offers->contains('status', 'accepted'),
            'fee_ok' => in_array($res['fee']['status'], ['paid', 'waived'], true), 'admitted' => $wf->confirmation_status === 'confirmed',
        ]);

        return view('admission.process.show', ['data' => $res]);
    }

    public function convert(int $enquiry)
    {
        return $this->attempt(fn () => $this->workflow->convert($this->instituteId(), $enquiry), 'Application form is ready.');
    }

    public function submit(int $enquiry)
    {
        return $this->attempt(fn () => $this->workflow->submit($this->instituteId(), $enquiry), 'Application submitted.');
    }

    public function reopen(ReasonRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->workflow->reopen($this->instituteId(), $enquiry, $request->input('reason')), 'Application reopened for correction.');
    }

    public function inquiryStatus(InquiryStatusRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->workflow->setInquiryStatus($this->instituteId(), $enquiry, $request->input('status'), $request->input('note')), 'Inquiry status updated.');
    }

    public function evaluateEligibility(Request $request, int $enquiry)
    {
        // resetting a manual override is itself an override-level action
        $reset = $request->boolean('reset_override') && AdmissionAccess::can('override');

        return $this->attempt(fn () => $this->eligibility->run($this->instituteId(), $enquiry, $reset), 'Eligibility evaluated.');
    }

    public function overrideEligibility(EligibilityOverrideRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->eligibility->override($this->instituteId(), $enquiry, $request->input('status'), $request->input('reason')), 'Manual eligibility decision recorded and audited.');
    }

    public function decideSelection(SelectionRequest $request, int $enquiry)
    {
        return $this->attempt(fn () => $this->selection->decide($this->instituteId(), $enquiry, $request->input('status'), $request->input('remarks')), 'Selection decision recorded.');
    }
}
