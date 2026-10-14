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
        Schema::create('placement_student_skill', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('profile_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('skill_id')->nullable(); // placement_master (skill)
            $table->string('skill_name', 100);
            $table->string('level', 20)->default('intermediate'); // beginner|intermediate|advanced
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
        Schema::dropIfExists('placement_student_skill');
    }
};
