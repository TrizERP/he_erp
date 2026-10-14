<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('placement_student_profile', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('student_id'); // tblstudent.id
            $table->string('registration_status', 20)->default('not_registered'); // not_registered|registered|opted_out
            $table->dateTime('undertaking_accepted_at')->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('tenth_percent', 5, 2)->nullable();
            $table->decimal('twelfth_percent', 5, 2)->nullable();
            $table->unsignedInteger('active_backlogs')->default(0);
            $table->unsignedSmallInteger('passing_year')->nullable();
            $table->json('projects')->nullable();
            $table->json('certifications')->nullable();
            $table->json('achievements')->nullable();
            $table->json('preferences')->nullable(); // preferred locations / job types
            $table->string('resume_file', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->string('verification_status', 20)->default('pending'); // pending|verified|rejected
            $table->text('verification_remarks')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->unsignedTinyInteger('completeness')->default(0);
            $table->tinyInteger('is_placed')->default(0);
            $table->date('debarred_until')->nullable();
            $table->string('debar_reason', 255)->nullable();
            $table->unique(['sub_institute_id', 'student_id'], 'placement_profile_student');
            $table->index(['sub_institute_id', 'registration_status', 'verification_status'], 'placement_profile_state');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('placement_student_profile');
    }
};
