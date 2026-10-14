<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admission workflow state that the legacy tables (admission_enquiry / admission_form /
 * admission_registration) do not hold. Additive only: no legacy table is altered.
 * Rows are keyed by the legacy admission_enquiry.id.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('admission_workflow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->bigInteger('enquiry_id');
            $table->integer('syear')->nullable();
            $table->bigInteger('standard_id')->nullable();

            $table->string('inquiry_status', 30)->default('new');          // new|contacted|follow_up|converted|closed
            $table->string('application_status', 30)->default('draft');    // draft|submitted|reopened
            $table->dateTime('submitted_at')->nullable();
            $table->bigInteger('submitted_by')->nullable();

            $table->string('eligibility_status', 30)->default('pending_review'); // pending_review|eligible|ineligible|needs_review
            $table->text('eligibility_reason')->nullable();
            $table->text('eligibility_values')->nullable();                // JSON snapshot of the values used
            $table->boolean('eligibility_override')->default(false);
            $table->string('eligibility_override_reason', 500)->nullable();
            $table->bigInteger('eligibility_by')->nullable();
            $table->dateTime('eligibility_at')->nullable();

            $table->string('selection_status', 30)->nullable();            // under_review|shortlisted|waitlisted|rejected|selected
            $table->string('selection_remarks', 500)->nullable();
            $table->bigInteger('selection_by')->nullable();
            $table->dateTime('selection_at')->nullable();

            $table->boolean('fee_waived')->default(false);
            $table->string('fee_waiver_reason', 500)->nullable();
            $table->bigInteger('fee_waived_by')->nullable();
            $table->dateTime('fee_waived_at')->nullable();

            $table->string('confirmation_status', 30)->default('pending'); // pending|confirmed|on_hold|cancelled
            $table->string('confirmation_note', 500)->nullable();
            $table->boolean('confirmation_override')->default(false);
            $table->string('confirmation_override_reason', 500)->nullable();
            $table->bigInteger('student_id')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->bigInteger('confirmed_by')->nullable();

            $table->timestamps();

            $table->unique(['sub_institute_id', 'enquiry_id'], 'adm_wf_unique_enquiry');
            $table->unique('student_id', 'adm_wf_unique_student'); // one student record per application
            $table->index(['sub_institute_id', 'syear', 'application_status'], 'adm_wf_app_idx');
            $table->index(['sub_institute_id', 'confirmation_status'], 'adm_wf_conf_idx');
        });

        Schema::create('admission_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->bigInteger('enquiry_id')->nullable();
            $table->string('entity_type', 40);
            $table->bigInteger('entity_id')->nullable();
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_override')->default(false);
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['enquiry_id', 'created_at'], 'adm_hist_enquiry_idx');
        });

        Schema::create('admission_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id');
            $table->string('setting_key', 60);
            $table->string('setting_value', 255)->nullable();
            $table->timestamps();

            $table->unique(['sub_institute_id', 'setting_key'], 'adm_setting_unique');
        });

        // Serialises enrollment-number generation per institute (SELECT ... FOR UPDATE inside the confirmation transaction).
        Schema::create('admission_enrollment_lock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_enrollment_lock');
        Schema::dropIfExists('admission_setting');
        Schema::dropIfExists('admission_history');
        Schema::dropIfExists('admission_workflow');
    }
};
