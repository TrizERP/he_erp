<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Institution-approved eligibility criteria. Nothing is seeded: with no rules configured,
        // evaluation returns "needs_review", never "eligible".
        Schema::create('admission_eligibility_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->bigInteger('standard_id')->nullable();
            $table->integer('syear')->nullable();
            $table->string('label', 120);
            $table->string('field', 60);          // whitelisted in config('admission.eligibility_fields')
            $table->string('operator', 10);       // >= > <= < = != in filled
            $table->string('value', 255)->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('admission_offer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->bigInteger('enquiry_id');
            $table->date('offer_date');
            $table->date('expiry_date');
            $table->bigInteger('standard_id')->nullable();
            $table->string('student_quota', 50)->nullable();   // seat category (matches student_quota used by enrollment)
            $table->string('status', 20)->default('issued');   // issued|accepted|declined|expired|withdrawn
            // 1 while the offer is live (issued/accepted), NULL afterwards. The unique index below makes
            // "one active offer per application" a database guarantee, not just an application check.
            $table->boolean('active')->nullable()->default(1);
            $table->dateTime('responded_at')->nullable();
            $table->string('notes', 500)->nullable();
            $table->bigInteger('issued_by')->nullable();
            $table->timestamps();

            $table->index(['enquiry_id', 'status'], 'adm_offer_enquiry_idx');
            $table->unique(['sub_institute_id', 'enquiry_id', 'active'], 'adm_offer_one_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_offer');
        Schema::dropIfExists('admission_eligibility_rule');
    }
};
