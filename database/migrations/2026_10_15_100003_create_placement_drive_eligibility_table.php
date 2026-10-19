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
        Schema::create('placement_drive_eligibility', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('drive_id')->index();
            $table->unsignedBigInteger('role_id')->nullable()->index(); // null = applies to every role of the drive
            $table->unsignedBigInteger('template_id')->nullable(); // placement_eligibility_template it was copied from
            $table->decimal('min_cgpa', 4, 2)->nullable();
            $table->decimal('min_10th_percent', 5, 2)->nullable();
            $table->decimal('min_12th_percent', 5, 2)->nullable();
            $table->unsignedInteger('max_active_backlogs')->nullable();
            $table->longText('standard_ids')->nullable();
            $table->unsignedSmallInteger('passing_year')->nullable();
            $table->string('gender', 20)->nullable();
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
        Schema::dropIfExists('placement_drive_eligibility');
    }
};
