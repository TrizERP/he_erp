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
        Schema::create('internship_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->constrained('internships');
            // For signed BIGINT(10)
            $table->bigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('tblstudent');
            $table->integer('marks');
            $table->text('comments')->nullable();
            $table->integer('evaluated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('internship_marks', function (Blueprint $table) {
            $table->unique(['internship_id', 'student_id'], 'internship_marks_unique');
        });
        Schema::table('internship_marks', function (Blueprint $table) {
            $table->foreign('internship_id')->references('id')->on('internships');
        });
        Schema::table('internship_marks', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('tblstudent');
        });
        Schema::table('internship_marks', function (Blueprint $table) {
            $table->index(['internship_id', 'student_id'], 'internship_marks_index');
        });
        Schema::table('internship_marks', function (Blueprint $table) {
            $table->index(['marks'], 'internship_marks_marks_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internship_marks');
    }
};
