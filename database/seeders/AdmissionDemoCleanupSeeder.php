<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Removes everything AdmissionDemoSeeder created (and only that): enquiries with remarks "DEMO DATA" and their
 * workflow / forms / registrations / documents / offers / history, the demo student record(s), and the "[DEMO]"
 * document requirements and eligibility rules.
 *
 *   php artisan db:seed --class=AdmissionDemoCleanupSeeder
 */
class AdmissionDemoCleanupSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $ids = DB::table('admission_enquiry')->where('remarks', 'DEMO DATA')->pluck('id')->all();

            if ($ids) {
                $students = DB::table('tblstudent')->whereIn('admission_id', array_map('strval', $ids))->pluck('id')->all();
                if ($students) {
                    DB::table('tblstudent_enrollment')->whereIn('student_id', $students)->delete();
                    DB::table('tblstudent')->whereIn('id', $students)->delete();
                }
                foreach (['admission_history', 'admission_document', 'admission_offer', 'admission_workflow', 'admission_registration'] as $table) {
                    DB::table($table)->whereIn('enquiry_id', $ids)->delete();
                }
                DB::table('admission_form')->whereIn('enquiry_id', array_map('strval', $ids))->delete();
                DB::table('admission_enquiry')->whereIn('id', $ids)->delete();
            }

            DB::table('admission_document_requirement')->where('name', 'like', '[DEMO]%')->delete();
            DB::table('admission_eligibility_rule')->where('label', 'like', '[DEMO]%')->delete();
        });

        $this->command?->info('Demo admission data removed.');
    }
}
