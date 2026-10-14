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
        Schema::create('placement_application', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('drive_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status', 20)->default('applied'); // applied|shortlisted|rejected|withdrawn|selected
            $table->dateTime('applied_at');
            $table->dateTime('status_changed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->text('override_reason')->nullable(); // TPO override of a policy block
            $table->unsignedBigInteger('override_by')->nullable();
            $table->unique(['drive_id', 'role_id', 'student_id'], 'placement_application_unique');
            $table->index(['student_id', 'status'], 'placement_application_student');
            $table->index(['drive_id', 'status'], 'placement_application_drive');
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
        Schema::dropIfExists('placement_application');
    }
};
