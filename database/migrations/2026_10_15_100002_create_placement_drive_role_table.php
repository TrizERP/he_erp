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
        Schema::create('placement_drive_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('drive_id')->index();
            $table->unsignedBigInteger('role_master_id')->nullable(); // placement_master (job_role)
            $table->string('title', 150);
            $table->unsignedBigInteger('job_type_id')->nullable(); // placement_master (job_type)
            $table->decimal('ctc_min', 8, 2)->nullable(); // LPA
            $table->decimal('ctc_max', 8, 2)->nullable();
            $table->decimal('stipend', 10, 2)->nullable(); // per month, internships
            $table->string('location', 150)->nullable();
            $table->unsignedInteger('openings')->nullable();
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
        Schema::dropIfExists('placement_drive_role');
    }
};
