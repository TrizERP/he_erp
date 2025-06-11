<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbladd_course_co', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('academic_year',255)->nullable();
            $table->string('programme',255)->nullable();
            $table->string('semester',255)->nullable();
            $table->string('course',255)->nullable();
            $table->string('course_code',150)->nullable();
            $table->string('course_code_nba',150)->nullable();
            $table->string('course_coordinator',150)->nullable();
            $table->tinytext('subject_teachers',255)->nullable();
            $table->string('subject_types',10)->nullable()->comment('Tutorial, Lecture, Lab');
            $table->integer('no_student')->nullable();
            $table->bigInteger('sub_institute_id')->nullable();
            $table->bigInteger('syear')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
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
        Schema::dropIfExists('tbladd_course_co');
    }
};
