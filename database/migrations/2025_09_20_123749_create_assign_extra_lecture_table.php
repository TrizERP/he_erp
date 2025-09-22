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
        Schema::create('assign_extra_lecture', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('teacher_id')->nullable();
            $table->bigInteger('grade_id')->nullable();
            $table->bigInteger('standard_id')->nullable();
            $table->bigInteger('section_id')->nullable();
            $table->date('extra_date')->nullable();
            $table->string('type',15)->nullable()->comment('lecture,lab,tutorial');
            $table->bigInteger('batch_id')->nullable();
            $table->string('lecture_no',10)->nullable()->comment('like 1,2,3,4,5');
            $table->bigInteger('sub_institute_id')->nullable();
            $table->string('syear',10)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        // added column in attendance_student table
        Schema::table('attendance_student', function (Blueprint $table) {
            $table->string('lecture_no')->after('attendance_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assign_extra_lecture');
        // added column in attendance_student table
        Schema::table('attendance_student', function (Blueprint $table) {
            $table->dropColumn(['lecture_no']);
        });
    }
};
