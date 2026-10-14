<?php

namespace Tests\Feature\Admission;

use App\Http\Middleware\LogRouteMiddleware;
use App\Http\Middleware\MenuMiddleware;
use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionHistory;
use App\Services\Admission\DocumentService;
use App\Services\Admission\OfferService;
use App\Services\Admission\WorkflowService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Drives the real routes. The real `session`, `check_permissions` and `admission.role` middleware run; only the
 * `menu` (sidebar builder) and `logRoute` (access log) middleware are switched off because they need the full ERP
 * schema, and the shared ERP header/sidebar/footer are replaced by empty stubs (tests/views/includes). The admission
 * screens themselves are rendered for real. Authorisation is therefore proven on the server, not by hidden menus.
 */
class HttpAccessTest extends AdmissionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([MenuMiddleware::class, LogRouteMiddleware::class]);
        $this->app['view']->getFinder()->prependLocation(base_path('tests/views'));
    }

    protected function as(string $profile, int $institute = self::INST)
    {
        return $this->withSession($this->sessionFor($profile, $institute));
    }

    protected function counts(): array
    {
        return [DB::table('tblstudent')->count(), DB::table('tblstudent_enrollment')->count()];
    }

    protected function realPdf(string $name = 'marks.pdf'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'adm');
        file_put_contents($path, "%PDF-1.4\n%test\n");

        return new UploadedFile($path, $name, null, null, true);
    }

    // -- rendering ---------------------------------------------------------------------------------------------

    public function test_every_screen_renders_for_an_administrator_including_rich_states(): void
    {
        Storage::fake(config('admission.disk'));
        $req = AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'ID proof', 'status' => true]);
        $id = $this->makeReadyApplication(['percentage' => '70'], ['amount' => 5000, 'payment_mode' => 'Cash', 'date_of_payment' => '2026-06-01']);
        app(DocumentService::class)->upload(self::INST, $id, $req->id, null, $this->realPdf());
        app(OfferService::class)->issue(self::INST, $id, ['offer_date' => now()->toDateString(), 'expiry_date' => now()->addWeek()->toDateString()]);
        \App\Models\admission\AdmissionEligibilityRule::create(['sub_institute_id' => self::INST, 'label' => 'Min %', 'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'status' => true]);

        $urls = [
            route('admission.dashboard.index'), route('admission.review.index'), route('admission.review.show', $id),
            route('admission.settings.index'), route('admission.documents.index'), route('admission.documents.index', ['status' => 'all']),
            route('admission.offers.index'), route('admission.fees.index'), route('admission.merit.index'),
            route('admission.confirm.index'), route('admission.admitted.index'),
        ];
        foreach (array_keys(\App\Services\Admission\ApplicationQuery::METRICS) as $metric) {
            $urls[] = route('admission.dashboard.drill', $metric);
        }
        foreach (array_keys(\App\Services\Admission\ReportService::TYPES) as $type) {
            $urls[] = route('admission.reports.index', ['type' => $type]);
        }

        foreach ($urls as $url) {
            $response = $this->as('Admin')->get($url);
            $this->assertSame(200, $response->getStatusCode(), "GET {$url}");
        }

        $this->as('Admin')->get(route('admission.review.show', $id))
            ->assertSee('Marksheet')->assertSee('Confirmation checklist')->assertSee('Mark accepted')->assertSee('Withdraw')->assertSee('History');
    }

    public function test_an_unknown_dashboard_metric_is_a_404(): void
    {
        $this->as('Admin')->get('/admission/process/dashboard/not-a-metric')->assertNotFound();
    }

    // -- authentication / authorisation ------------------------------------------------------------------------------

    public function test_a_guest_is_redirected_to_login_and_nothing_changes(): void
    {
        $id = $this->makeReadyApplication();
        session()->flush(); // setUp() logged a user into the shared test session; a guest has none

        $this->get(route('admission.dashboard.index'))->assertRedirect(route('home'));
        $this->post(route('admission.process.confirm', $id))->assertRedirect(route('home'));
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_a_teacher_has_no_admission_access_by_default(): void
    {
        $id = $this->makeReadyApplication();

        $this->as('Teacher')->get(route('admission.dashboard.index'))->assertForbidden();
        $this->as('Teacher')->get(route('admission.review.show', $id))->assertForbidden();
        $this->as('Teacher')->post(route('admission.process.confirm', $id))->assertForbidden();
        $this->as('Teacher')->post(route('admission.process.submit', $id))->assertForbidden();
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_an_unauthorised_user_confirming_is_refused_and_no_data_changes(): void
    {
        $id = $this->makeReadyApplication();

        foreach (['Accounts Operator', 'Admission Approver', 'Teacher'] as $profile) {
            $this->as($profile)->post(route('admission.process.confirm', $id))->assertForbidden();
        }

        $this->assertSame([0, 0], $this->counts());
        $this->assertSame('pending', app(WorkflowService::class)->ensure(self::INST, $id)->confirmation_status);
        $this->assertSame(0, AdmissionHistory::where('action', 'confirmed')->count());
    }

    public function test_accounts_can_see_fees_but_not_documents_or_academic_decisions(): void
    {
        $id = $this->makeReadyApplication();

        $this->as('Accounts Operator')->get(route('admission.fees.index'))->assertOk();
        $this->as('Accounts Operator')->get(route('admission.documents.index'))->assertForbidden();
        $this->as('Accounts Operator')->post(route('admission.process.selection', $id), ['status' => 'selected'])->assertForbidden();
        $this->as('Accounts Operator')->post(route('admission.process.eligibility_run', $id))->assertForbidden();
        $this->as('Accounts Operator')->post(route('admission.process.fee_waive', $id), ['reason' => 'x y z'])->assertForbidden();
    }

    public function test_an_approver_can_record_a_selection_decision_but_nothing_else(): void
    {
        $id = $this->makeReadyApplication();

        $this->as('Admission Approver')->post(route('admission.process.selection', $id), ['status' => 'shortlisted'])->assertRedirect();
        $this->assertSame('shortlisted', DB::table('admission_workflow')->where('enquiry_id', $id)->value('selection_status'));

        $this->as('Admission Approver')->post(route('admission.process.convert', $id))->assertForbidden();
        $this->as('Admission Approver')->post(route('admission.process.offer_issue', $id), ['offer_date' => '2026-07-01', 'expiry_date' => '2026-07-09'])->assertForbidden();
        $this->as('Admission Approver')->get(route('admission.settings.index'))->assertForbidden();
    }

    public function test_only_an_administrator_can_override_eligibility_or_open_settings(): void
    {
        $id = $this->makeReadyApplication();
        $payload = ['status' => 'eligible', 'reason' => 'Committee exception'];

        $this->as('Admission Officer')->post(route('admission.process.eligibility_override', $id), $payload)->assertForbidden();
        $this->as('Admission Officer')->get(route('admission.settings.index'))->assertForbidden();
        $this->as('Admission Officer')->post(route('admission.process.settings_save'), ['fee_condition' => 'full', 'duplicate_inquiry_match' => 'none'])->assertForbidden();
        $this->assertSame('pending_review', DB::table('admission_workflow')->where('enquiry_id', $id)->value('eligibility_status'));

        $this->as('Admin')->post(route('admission.process.eligibility_override', $id), $payload)->assertRedirect();
        $this->assertSame('eligible', DB::table('admission_workflow')->where('enquiry_id', $id)->value('eligibility_status'));
        $this->as('Admin')->get(route('admission.settings.index'))->assertOk();
    }

    public function test_the_menu_right_still_applies_on_top_of_the_role_for_menu_linked_screens(): void
    {
        $menuId = DB::table('tblmenumaster')->insertGetId(['status' => 1, 'link' => 'admission.dashboard.index']);

        // role allows (Admission Officer), but the profile has no menu right -> denied by check_permissions
        $this->as('Admission Officer')->get(route('admission.dashboard.index'))->assertForbidden();

        DB::table('tblgroupwise_rights')->insert(['menu_id' => $menuId, 'profile_id' => 99, 'sub_institute_id' => self::INST, 'can_view' => 1]);
        $this->as('Admission Officer')->get(route('admission.dashboard.index'))->assertOk();
    }

    // -- scope ---------------------------------------------------------------------------------------------------------

    public function test_another_institutes_application_is_neither_visible_nor_changeable(): void
    {
        $id = $this->makeEnquiry(['sub_institute_id' => self::OTHER_INST]);
        DB::table('admission_form')->insert(['enquiry_id' => (string) $id, 'form_no' => '1', 'admission_standard' => '1', 'sub_institute_id' => (string) self::OTHER_INST]);
        $this->makeRegistration($id, ['sub_institute_id' => self::OTHER_INST]);

        $this->as('Admin')->get(route('admission.review.show', $id))->assertNotFound();
        $this->as('Admin')->post(route('admission.process.confirm', $id))->assertNotFound();
        $this->as('Admin')->post(route('admission.process.convert', $id))->assertNotFound();
        $this->as('Admin')->post(route('admission.process.selection', $id), ['status' => 'selected'])->assertNotFound();
        $this->as('Admin')->post(route('admission.process.eligibility_override', $id), ['status' => 'eligible', 'reason' => 'whatever'])->assertNotFound();

        $this->assertSame([0, 0], $this->counts());
        $this->assertSame(0, DB::table('admission_workflow')->count(), 'no workflow row is even created for it');
    }

    public function test_lists_and_dashboard_never_include_another_institutes_rows(): void
    {
        $mine = $this->makeReadyApplication(['first_name' => 'Mine']);
        $this->makeEnquiry(['first_name' => 'Foreign', 'sub_institute_id' => self::OTHER_INST]);

        $this->as('Admin')->get(route('admission.review.index'))->assertSee('Mine')->assertDontSee('Foreign');
        $this->as('Admin')->get(route('admission.dashboard.drill', 'total_inquiries'))->assertSee('Mine')->assertDontSee('Foreign');
    }

    // -- end-to-end through HTTP ------------------------------------------------------------------------------------------

    public function test_an_officer_confirms_through_the_workspace_and_a_double_post_does_not_duplicate_the_student(): void
    {
        $id = $this->makeReadyApplication();

        $first = $this->as('Admission Officer')->from(route('admission.review.show', $id))->post(route('admission.process.confirm', $id));
        $first->assertRedirect(route('admission.review.show', $id));
        $this->assertSame(1, $first->getSession()->get('data')['status_code']);
        $this->assertStringContainsString('Admission confirmed', $first->getSession()->get('data')['message']);

        $second = $this->as('Admission Officer')->from(route('admission.review.show', $id))->post(route('admission.process.confirm', $id));
        $this->assertStringContainsString('no duplicate created', $second->getSession()->get('data')['message']);

        $this->assertSame([1, 1], $this->counts());
        $this->as('Admission Officer')->get(route('admission.admitted.index'))->assertSee('Student #');
    }

    public function test_a_blocked_confirmation_explains_why_and_creates_nothing(): void
    {
        $this->setSetting('fee_condition', 'full');
        $id = $this->makeReadyApplication([], ['amount' => 0]);

        $response = $this->as('Admission Officer')->from(route('admission.review.show', $id))->post(route('admission.process.confirm', $id));

        $data = $response->getSession()->get('data');
        $this->assertSame(0, $data['status_code']);
        $this->assertNotEmpty($data['details']);
        $this->assertSame([0, 0], $this->counts());
    }

    public function test_only_an_administrator_can_override_a_blocked_confirmation_over_http(): void
    {
        $this->setSetting('require_selection', '1');
        $id = $this->makeReadyApplication();
        $body = ['override_reason' => 'Approved by the principal'];

        $this->as('Admission Officer')->post(route('admission.process.confirm', $id), $body);
        $this->assertSame([0, 0], $this->counts());

        $this->as('Admin')->post(route('admission.process.confirm', $id), $body);
        $this->assertSame([1, 1], $this->counts());
        $this->assertTrue((bool) DB::table('admission_workflow')->where('enquiry_id', $id)->value('confirmation_override'));
    }

    public function test_the_legacy_confirm_route_is_routed_through_the_same_safe_service(): void
    {
        $id = $this->makeReadyApplication();

        $this->as('Admission Officer')->post(route('admission_student'), ['id' => $id])->assertRedirect(route('admission_registration.index'));
        $this->as('Admission Officer')->post(route('admission_student'), ['id' => $id])->assertRedirect(route('admission_registration.index'));

        $this->assertSame([1, 1], $this->counts(), 'a second legacy click must not duplicate the student');
    }

    public function test_the_legacy_enquiry_update_cannot_modify_another_institutes_record(): void
    {
        $foreign = $this->makeEnquiry(['first_name' => 'Original', 'sub_institute_id' => self::OTHER_INST]);
        $mine = $this->makeEnquiry(['first_name' => 'Original']);

        $this->as('Admission Officer')->put(route('admission_enquiry.update', $foreign), ['first_name' => 'Hacked']);
        $this->assertSame('Original', DB::table('admission_enquiry')->where('id', $foreign)->value('first_name'));

        $this->as('Admission Officer')->put(route('admission_enquiry.update', $mine), ['first_name' => 'Edited']);
        $this->assertSame('Edited', DB::table('admission_enquiry')->where('id', $mine)->value('first_name'));
    }

    // -- validation -----------------------------------------------------------------------------------------------------

    public function test_server_side_validation_rejects_bad_input_without_changing_data(): void
    {
        $id = $this->makeReadyApplication();

        $this->as('Admin')->post(route('admission.process.eligibility_override', $id), ['status' => 'eligible', 'reason' => ''])->assertSessionHasErrors('reason');
        $this->as('Admin')->post(route('admission.process.selection', $id), ['status' => 'astronaut'])->assertSessionHasErrors('status');
        $this->as('Admin')->post(route('admission.process.offer_issue', $id), ['offer_date' => '2026-07-10', 'expiry_date' => '2026-07-01'])->assertSessionHasErrors('expiry_date');
        $this->as('Admin')->post(route('admission.process.confirmation_status', $id), ['status' => 'cancelled', 'reason' => ''])->assertSessionHasErrors('reason');

        $this->assertSame('pending_review', DB::table('admission_workflow')->where('enquiry_id', $id)->value('eligibility_status'));
        $this->assertNull(DB::table('admission_workflow')->where('enquiry_id', $id)->value('selection_status'));
        $this->assertSame(0, DB::table('admission_offer')->count());
    }

    public function test_a_status_cannot_be_forced_through_the_request(): void
    {
        $id = $this->makeEnquiry();
        $this->as('Admission Officer')->post(route('admission.process.convert', $id), [
            'application_status' => 'submitted', 'confirmation_status' => 'confirmed', 'student_id' => 999, 'fee_waived' => 1,
        ]);

        $wf = DB::table('admission_workflow')->where('enquiry_id', $id)->first();
        $this->assertSame('draft', $wf->application_status);
        $this->assertSame('pending', $wf->confirmation_status);
        $this->assertNull($wf->student_id);
        $this->assertSame(0, (int) $wf->fee_waived);
    }

    // -- documents over HTTP ----------------------------------------------------------------------------------------------

    public function test_document_upload_validation_and_secure_download(): void
    {
        Storage::fake(config('admission.disk'));
        $req = AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true]);
        $id = $this->makeReadyApplication();

        // bad type: rejected with a message, nothing stored
        $bad = tempnam(sys_get_temp_dir(), 'adm');
        file_put_contents($bad, 'MZ');
        $response = $this->as('Admission Officer')->from(route('admission.review.show', $id))
            ->post(route('admission.process.document_upload', $id), ['requirement_id' => $req->id, 'file' => new UploadedFile($bad, 'x.exe', null, null, true)]);
        $this->assertSame(0, $response->getSession()->get('data')['status_code']);
        $this->assertSame([], Storage::disk(config('admission.disk'))->allFiles());

        // good upload
        $this->as('Admission Officer')->post(route('admission.process.document_upload', $id), ['requirement_id' => $req->id, 'file' => $this->realPdf()])->assertRedirect();
        $doc = DB::table('admission_document')->where('enquiry_id', $id)->first();
        $this->assertSame('uploaded', $doc->status);

        // authorised download streams the file privately
        $download = $this->as('Admission Officer')->get(route('admission.process.document_download', $doc->id));
        $download->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('inline', $download->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $download->headers->get('Cache-Control'));

        // other institute and fee-only role cannot fetch it
        $this->as('Admin', self::OTHER_INST)->get(route('admission.process.document_download', $doc->id))->assertNotFound();
        $this->as('Accounts Operator')->get(route('admission.process.document_download', $doc->id))->assertForbidden();
        // and the stored path is not reachable as a public URL
        $this->assertStringNotContainsString('public', $doc->storage_path);
    }

    public function test_rejecting_a_document_needs_a_reason_and_saves_it(): void
    {
        Storage::fake(config('admission.disk'));
        $req = AdmissionDocumentRequirement::create(['sub_institute_id' => self::INST, 'name' => 'Marksheet', 'is_mandatory' => true, 'status' => true]);
        $id = $this->makeReadyApplication();
        $this->as('Admission Officer')->post(route('admission.process.document_upload', $id), ['requirement_id' => $req->id, 'file' => $this->realPdf()]);
        $docId = DB::table('admission_document')->where('enquiry_id', $id)->value('id');

        $this->as('Admission Officer')->post(route('admission.process.document_reject', $docId), ['reason' => '', 'mode' => 'resubmit'])->assertSessionHasErrors('reason');
        $this->assertSame('uploaded', DB::table('admission_document')->where('id', $docId)->value('status'));

        $this->as('Admission Officer')->post(route('admission.process.document_reject', $docId), ['reason' => 'Unreadable scan', 'mode' => 'resubmit']);
        $row = DB::table('admission_document')->where('id', $docId)->first();
        $this->assertSame('resubmission_required', $row->status);
        $this->assertSame('Unreadable scan', $row->rejection_reason);
        $this->as('Admission Officer')->get(route('admission.review.show', $id))->assertSee('Unreadable scan');
    }

    // -- fee receipts ---------------------------------------------------------------------------------------------------------

    public function test_a_stored_fee_receipt_can_be_reprinted_safely_and_only_within_the_institute(): void
    {
        $id = $this->makeEnquiry(['receipt_id' => 17, 'receipt_html' => '<table><tr><td>RECEIPT-ENQ-17 <script>alert(1)</script></td></tr></table>']);
        $this->makeForm($id, ['receipt_id' => '5', 'receipt_html' => '<b>RECEIPT-FORM-5</b>']);
        $this->makeRegistration($id);
        $none = $this->makeReadyApplication();

        $enquiry = $this->as('Accounts Operator')->get(route('admission.process.receipt', [$id, 'enquiry']));
        $enquiry->assertOk()->assertSee('RECEIPT-ENQ-17', false)->assertSee('Print receipt');
        $csp = $enquiry->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'none'", $csp);
        $this->assertMatchesRegularExpression("/script-src 'nonce-[A-Za-z0-9]{24}'/", $csp, 'only our own print script may run, never the stored HTML');
        $this->assertStringContainsString('no-store', $enquiry->headers->get('Cache-Control'));

        $this->as('Admission Officer')->get(route('admission.process.receipt', [$id, 'form']))->assertOk()->assertSee('RECEIPT-FORM-5', false);

        // not generated -> clear 404; unknown kind -> 404; other institute -> 404; other roles -> 403
        $this->as('Admission Officer')->get(route('admission.process.receipt', [$none, 'enquiry']))->assertNotFound();
        $this->as('Admission Officer')->get('/admission/process/applications/'.$id.'/receipt/whatever')->assertNotFound();
        $this->as('Admin', self::OTHER_INST)->get(route('admission.process.receipt', [$id, 'enquiry']))->assertNotFound();
        $this->as('Teacher')->get(route('admission.process.receipt', [$id, 'enquiry']))->assertForbidden();
        $this->as('Admission Approver')->get(route('admission.process.receipt', [$id, 'enquiry']))->assertForbidden();
    }

    public function test_the_registration_payment_gets_a_printable_receipt_built_from_the_recorded_payment(): void
    {
        $paid = $this->makeReadyApplication(['first_name' => 'Riya', 'last_name' => 'Pandya', 'father_name' => 'Mr Pandya'],
            ['amount' => 12000, 'payment_mode' => 'Cash', 'date_of_payment' => '2026-10-14']);
        $unpaid = $this->makeReadyApplication();
        $regId = DB::table('admission_registration')->where('enquiry_id', $paid)->value('id');

        $page = $this->as('Accounts Operator')->get(route('admission.process.receipt', [$paid, 'registration']));
        $page->assertOk()->assertSee('ADMISSION FEE RECEIPT')->assertSee('Riya Pandya')->assertSee('Mr Pandya')
            ->assertSee('12,000.00')->assertSee('Cash')->assertSee('14 Oct 2026')->assertSee('ADM-'.str_pad((string) $regId, 6, '0', STR_PAD_LEFT));

        // never a receipt for a payment that was not recorded; scoped to the institute; same role rules
        $this->as('Admission Officer')->get(route('admission.process.receipt', [$unpaid, 'registration']))->assertNotFound();
        $this->as('Admin', self::OTHER_INST)->get(route('admission.process.receipt', [$paid, 'registration']))->assertNotFound();
        $this->as('Teacher')->get(route('admission.process.receipt', [$paid, 'registration']))->assertForbidden();

        // the button is on both screens, only for the paid applicant
        $this->as('Admin')->get(route('admission.review.show', $paid))->assertSee('Admission fee receipt (12,000.00)')
            ->assertSee(route('admission.process.receipt', [$paid, 'registration']), false);
        $this->as('Admin')->get(route('admission.fees.index'))->assertSee(route('admission.process.receipt', [$paid, 'registration']), false)
            ->assertDontSee(route('admission.process.receipt', [$unpaid, 'registration']), false);
    }

    public function test_receipt_buttons_appear_on_the_application_and_fee_screens_only_when_a_receipt_exists(): void
    {
        $with = $this->makeEnquiry(['receipt_id' => 3, 'receipt_html' => '<b>R</b>', 'first_name' => 'Withreceipt']);
        $this->makeForm($with);
        $this->makeRegistration($with);
        $without = $this->makeReadyApplication(['first_name' => 'Noreceipt']);

        $this->as('Admin')->get(route('admission.review.show', $with))->assertSee('Inquiry fee receipt #3')->assertDontSee('No receipt yet');
        $this->as('Admin')->get(route('admission.review.show', $without))->assertSee('No receipt yet')->assertDontSee('Admission fee receipt (');
        $this->as('Admin')->get(route('admission.fees.index'))->assertSee(route('admission.process.receipt', [$with, 'enquiry']), false)
            ->assertDontSee(route('admission.process.receipt', [$without, 'enquiry']), false);
    }

    // -- reports / export ---------------------------------------------------------------------------------------------------

    public function test_reports_export_to_excel_with_the_same_scoping_and_are_gated(): void
    {
        $this->makeReadyApplication();

        $this->as('Admission Approver')->get(route('admission.process.report_export', 'confirmed'))->assertOk();
        $this->as('Teacher')->get(route('admission.process.report_export', 'confirmed'))->assertForbidden();
        $this->as('Admin')->get(route('admission.process.report_export', 'nonsense'))->assertStatus(302); // unknown report: flash, not a 500
    }

    // -- settings -----------------------------------------------------------------------------------------------------------

    public function test_settings_save_requirements_and_rules_are_scoped_validated_and_audited(): void
    {
        $this->as('Admin')->post(route('admission.process.settings_save'), ['require_eligibility' => '1', 'fee_condition' => 'full', 'duplicate_inquiry_match' => 'mobile'])->assertRedirect();
        $this->assertSame('1', DB::table('admission_setting')->where('sub_institute_id', self::INST)->where('setting_key', 'require_eligibility')->value('setting_value'));
        $this->assertSame('0', DB::table('admission_setting')->where('setting_key', 'require_selection')->value('setting_value'), 'unchecked box = off');
        $this->as('Admin')->post(route('admission.process.settings_save'), ['fee_condition' => 'bogus', 'duplicate_inquiry_match' => 'mobile'])->assertSessionHasErrors('fee_condition');

        $this->as('Admin')->post(route('admission.process.requirement_add'), ['name' => 'Marksheet', 'is_mandatory' => '1'])->assertRedirect();
        $this->as('Admin')->post(route('admission.process.rule_add'), ['label' => 'Min %', 'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'is_mandatory' => '1'])->assertRedirect();
        $this->as('Admin')->post(route('admission.process.rule_add'), ['label' => 'Bad', 'field' => 'password', 'operator' => '>=', 'value' => '1'])->assertSessionHasErrors('field');
        $this->as('Admin')->post(route('admission.process.rule_add'), ['label' => 'No value', 'field' => 'percentage', 'operator' => '>='])->assertSessionHasErrors('value');

        $this->assertSame(1, DB::table('admission_document_requirement')->where('sub_institute_id', self::INST)->count());
        $this->assertSame(1, DB::table('admission_eligibility_rule')->where('sub_institute_id', self::INST)->count());
        $this->assertGreaterThanOrEqual(3, AdmissionHistory::where('entity_type', 'settings')->count());

        // another institute's admin cannot deactivate this institute's rule
        $ruleId = DB::table('admission_eligibility_rule')->value('id');
        $this->as('Admin', self::OTHER_INST)->delete(route('admission.process.rule_remove', $ruleId))->assertNotFound();
        $this->assertSame(1, (int) DB::table('admission_eligibility_rule')->where('id', $ruleId)->value('status'));
    }

    // -- duplicate inquiry (legacy store, configurable) ------------------------------------------------------------------------

    public function test_the_duplicate_inquiry_check_is_off_by_default_and_a_warning_when_enabled(): void
    {
        $service = app(\App\Services\Admission\DuplicateInquiryService::class);
        $this->makeEnquiry(['mobile' => '9000000001', 'enquiry_no' => 'ENQ-OLD']);

        $this->assertNull($service->warning(self::INST, ['mobile' => '9000000001']), 'default is legacy behaviour');

        $this->setSetting('duplicate_inquiry_match', 'mobile');
        $service = app(\App\Services\Admission\DuplicateInquiryService::class);
        $warning = $service->warning(self::INST, ['mobile' => '9000000001']);
        $this->assertStringContainsString('ENQ-OLD', $warning);
        $this->assertNull($service->warning(self::INST, ['mobile' => '9000000001']), 'the same details submitted again are accepted');
        $this->assertNull($service->warning(self::INST, ['mobile' => '9111111111']), 'a different mobile is not a duplicate');
        $this->assertNull($service->warning(self::OTHER_INST, ['mobile' => '9000000001']), 'matching is per institute');
    }
}
