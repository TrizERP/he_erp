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
        Schema::table('attendance_student', function (Blueprint $table) {
            //
            $table->bigInteger('period_id')->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->bigInteger('timetable_id')->nullable();
            $table->string('attendance_type',25)->nullable();
            $table->integer('attendance_teacher_code')->nullable();
            $table->string('attendance_for',25)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_student', function (Blueprint $table) {
            // dropColumn
            $table->dropColumn('period_id')->nullable();
            $table->dropColumn('subject_id')->nullable();
            $table->dropColumn('timetable_id')->nullable();
            $table->dropColumn('attendance_type',25)->nullable();
            $table->dropColumn('attendance_teacher_code')->nullable();
            $table->dropColumn('attendance_for',25)->nullable();
        });
    }
};
