<?php

namespace Database\Seeders;

use App\Models\admission\AdmissionDocumentRequirement;
use App\Models\admission\AdmissionEligibilityRule;
use App\Services\Admission\ConfirmationService;
use App\Services\Admission\DocumentService;
use App\Services\Admission\EligibilityService;
use App\Services\Admission\OfferService;
use App\Services\Admission\SelectionService;
use App\Services\Admission\WorkflowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Presentation data for the Admission workflow: about 16 applicants spread across every stage.
 * Run on a DEV / STAGING copy only:
 *
 *   php artisan db:seed --class=AdmissionDemoSeeder
 *
 * Optional environment variables: DEMO_INSTITUTE (sub_institute_id, default: first in school_setup),
 * DEMO_SYEAR (academic year, default: the year already used by the institute's students, else current year).
 *
 * Everything it creates is recognisable (enquiry remarks "DEMO DATA", requirement / rule labels "[DEMO] ...") and is
 * removed by `php artisan db:seed --class=AdmissionDemoCleanupSeeder`. Safe to re-run: it does nothing if demo data
 * already exists. It also creates ONE real tblstudent row (the confirmed applicant); the cleanup removes it too.
 */
class AdmissionDemoSeeder extends Seeder
{
    protected int $inst;
    protected int $syear;
    protected int $n = 0;
    protected bool $shortMobile = false;
    protected array $standards = [];

    public function run()
    {
        $this->inst = (int) (env('DEMO_INSTITUTE') ?: DB::table('school_setup')->min('Id'));
        if (! $this->inst) {
            $this->command?->error('No institute found in school_setup.');

            return;
        }
        if (DB::table('admission_enquiry')->where('sub_institute_id', $this->inst)->where('remarks', 'DEMO DATA')->exists()) {
            $this->command?->warn('Demo data already exists for this institute. Run AdmissionDemoCleanupSeeder first to rebuild it.');

            return;
        }
        $this->standards = DB::table('standard')->where('sub_institute_id', $this->inst)->orderBy('id')->limit(2)->pluck('id')->all();
        if (! $this->standards) {
            $this->command?->error("Institute {$this->inst} has no rows in `standard` (programmes). Create a programme first.");

            return;
        }
        // Default to the current calendar year (what the ERP year selector normally shows); set DEMO_SYEAR to override.
        $this->syear = (int) (env('DEMO_SYEAR') ?: date('Y'));
        $quotas = DB::table('student_quota')->where('sub_institute_id', $this->inst)->orderBy('id')->get(['id', 'title']);
        $this->quotaId = $quotas->first(fn ($q) => stripos($q->title, 'general') !== false)->id ?? ($quotas->first()->id ?? null);
        $this->shortMobile = $this->mobileIsInteger();
        session()->put(['user_id' => 1, 'sub_institute_id' => $this->inst, 'syear' => $this->syear, 'term_id' => 1]);

        DB::transaction(function () {
            $this->configure();
            $this->applicants();
        });

        $this->command?->info("Demo admission data created for institute {$this->inst}, academic year {$this->syear}. Select that year in the ERP header to see it.");
        if (! $this->quotaId) {
            $this->command?->warn('No student_quota rows exist for this institute, so the confirmed example could not be created.');
        }
    }

    protected function configure(): void
    {
        $marks = AdmissionDocumentRequirement::create(['sub_institute_id' => $this->inst, 'name' => '[DEMO] Previous marksheet', 'is_mandatory' => true, 'status' => true, 'sort_order' => 1]);
        AdmissionDocumentRequirement::create(['sub_institute_id' => $this->inst, 'name' => '[DEMO] Identity proof', 'is_mandatory' => false, 'status' => true, 'sort_order' => 2]);
        AdmissionEligibilityRule::create([
            'sub_institute_id' => $this->inst, 'label' => '[DEMO] Minimum previous percentage (illustrative only, replace with approved criteria)',
            'field' => 'percentage', 'operator' => '>=', 'value' => '50', 'is_mandatory' => true, 'status' => true,
        ]);
        $this->marksId = $marks->id;
    }

    protected int $marksId = 0;
    protected $quotaId = null;

    protected function applicants(): void
    {
        $workflow = app(WorkflowService::class);
        $docs = app(DocumentService::class);
        $elig = app(EligibilityService::class);
        $select = app(SelectionService::class);
        $offers = app(OfferService::class);
        $offer = ['offer_date' => now()->toDateString(), 'expiry_date' => now()->addDays(10)->toDateString()];

        // 1-2: inquiries only
        $a = $this->enquiry('Aarav', 'Shah', '72');
        $b = $this->enquiry('Diya', 'Mehta', '81');
        $workflow->setInquiryStatus($this->inst, $b, 'contacted', 'Called, will visit campus');

        // 3-4: draft applications (form created, not submitted)
        foreach ([['Kabir', 'Joshi', '66'], ['Meera', 'Desai', '58']] as [$f, $l, $p]) {
            $id = $this->enquiry($f, $l, $p);
            $workflow->convert($this->inst, $id);
        }

        // 5-6: submitted, documents waiting for verification
        foreach ([['Ishaan', 'Trivedi', '74'], ['Anaya', 'Parekh', '69']] as [$f, $l, $p]) {
            $id = $this->submitted($f, $l, $p);
            $this->documentStatus($id, 'uploaded');
        }

        // 7: a document sent back for resubmission
        $id = $this->submitted('Vihaan', 'Rana', '77');
        $this->documentStatus($id, 'resubmission_required', 'Scan is blurred, please upload a clear copy');

        // 8: verified documents, eligible
        $id = $this->submitted('Saanvi', 'Gandhi', '88');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);

        // 9: ineligible (below the illustrative 50%)
        $id = $this->submitted('Rohan', 'Vyas', '41');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);

        // 10: needs review (percentage not provided)
        $id = $this->submitted('Tanvi', 'Bhatt', null);
        $this->documentStatus($id, 'uploaded');
        $elig->run($this->inst, $id);

        // 11: shortlisted
        $id = $this->submitted('Dev', 'Kapadia', '84');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'shortlisted', 'Good academic record');

        // 12: rejected
        $id = $this->submitted('Nisha', 'Modi', '47');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'rejected', 'Does not meet the criteria');

        // 13: selected, offer issued and awaiting response
        $id = $this->submitted('Yash', 'Soni', '91');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'selected', 'Top of the merit review');
        $offers->issue($this->inst, $id, $offer + ['student_quota' => 'General']);

        // 14: offer declined
        $id = $this->submitted('Pooja', 'Sheth', '79');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'selected', null);
        $o = $offers->issue($this->inst, $id, $offer);
        $offers->respond($this->inst, $o->id, false, 'Joined another college');

        // 15: offer accepted, part of the fee paid, ready to confirm
        $id = $this->submitted('Krish', 'Doshi', '86', 5000, 'Online');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'selected', null);
        $o = $offers->issue($this->inst, $id, $offer + ['student_quota' => 'General']);
        $offers->respond($this->inst, $o->id, true);

        // 16: fully processed and confirmed (creates one student record)
        $id = $this->submitted('Riya', 'Pandya', '93', 12000, 'Cash');
        $this->documentStatus($id, 'verified');
        $elig->run($this->inst, $id);
        $select->decide($this->inst, $id, 'selected', null);
        $o = $offers->issue($this->inst, $id, $offer);
        $offers->respond($this->inst, $o->id, true);
        try {
            app(ConfirmationService::class)->confirm($this->inst, $id, $this->syear, 1);
        } catch (\Throwable $e) {
            $this->command?->warn('The confirmed example was not created: '.$e->getMessage());
        }
    }

    /** An application that is registered, has its documents rows and is submitted. */
    protected function submitted(string $first, string $last, ?string $percentage, int $paid = 0, string $mode = ''): int
    {
        $workflow = app(WorkflowService::class);
        $id = $this->enquiry($first, $last, $percentage);
        $workflow->convert($this->inst, $id);

        $documents = app(DocumentService::class)->documentsFor($this->inst, $id);
        DB::table('admission_document')->whereIn('id', $documents->pluck('id'))->where('requirement_id', $this->marksId)
            ->update(['status' => 'uploaded', 'original_filename' => 'demo-marksheet.pdf', 'version' => 1]); // metadata only, no real file
        $workflow->submit($this->inst, $id);

        DB::table('admission_registration')->insert([
            'enquiry_id' => $id, 'enquiry_no' => $this->n, 'admission_date' => now()->toDateString(), 'admission_division' => null, 'student_quota' => $this->quotaId,
            'amount' => $paid ?: null, 'payment_mode' => $paid ? $mode : null, 'date_of_payment' => $paid ? now()->toDateString() : null,
            'sub_institute_id' => $this->inst, 'created_by' => 1,
        ]);

        return $id;
    }

    protected function documentStatus(int $enquiryId, string $status, ?string $reason = null): void
    {
        DB::table('admission_document')->where('enquiry_id', $enquiryId)->where('requirement_id', $this->marksId)->update([
            'status' => $status, 'rejection_reason' => $reason,
            'verified_by' => in_array($status, ['verified', 'resubmission_required'], true) ? 1 : null,
            'verified_at' => in_array($status, ['verified', 'resubmission_required'], true) ? now() : null,
        ]);
    }

    protected function enquiry(string $first, string $last, ?string $percentage): int
    {
        $this->n++;
        $mobile = $this->shortMobile ? (string) (900000000 + $this->n * 1111) : (string) (9800000000 + $this->n * 1111);

        return (int) DB::table('admission_enquiry')->insertGetId([
            'enquiry_no' => 900000 + $this->n, 'first_name' => $first, 'last_name' => $last, 'gender' => $this->n % 2 ? 'Male' : 'Female',
            'mobile' => $mobile, 'email' => strtolower($first.'.'.$last).'@example.com', 'syear' => $this->syear,
            'admission_standard' => $this->standards[$this->n % count($this->standards)], 'source_of_enquiry' => ['Website', 'Walk-in', 'Referral', 'Social media'][$this->n % 4],
            'percentage' => $percentage, 'father_name' => 'Demo Parent '.$last, 'date_of_birth' => '2007-0'.(1 + $this->n % 9).'-1'.($this->n % 9),
            'remarks' => 'DEMO DATA', 'created_by' => 1, 'sub_institute_id' => $this->inst,
        ]);
    }

    /** The legacy `mobile` column is declared as a 32-bit integer in the original migration. */
    protected function mobileIsInteger(): bool
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return false;
        }
        $type = DB::selectOne('SELECT DATA_TYPE AS t FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', ['admission_enquiry', 'mobile']);

        return in_array(strtolower($type->t ?? ''), ['int', 'integer', 'mediumint', 'smallint'], true);
    }
}
