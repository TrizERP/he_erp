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
        Schema::create('internship_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->constrained('internships');
            $table->foreignId('student_id')->constrained();
            $table->string('status')->default('active');
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
        Schema::table('internship_student', function (Blueprint $table) {
            $table->unique(['internship_id', 'student_id'], 'internship_student_unique');
        });
        Schema::table('internship_student', function (Blueprint $table) {
            $table->foreign('internship_id')->references('id')->on('internships');
        });
        Schema::table('internship_student', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('tblstudent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internship_student');
    }
};
