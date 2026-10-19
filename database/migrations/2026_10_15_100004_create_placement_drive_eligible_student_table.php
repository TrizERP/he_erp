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
        Schema::create('placement_drive_eligible_student', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('drive_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->tinyInteger('is_eligible')->default(0);
            $table->longText('reasons')->nullable(); // why not eligible
            $table->dateTime('computed_at');
            $table->unique(['drive_id', 'role_id', 'student_id'], 'placement_eligible_unique');
            $table->index(['student_id', 'is_eligible'], 'placement_eligible_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('placement_drive_eligible_student');
    }
};
