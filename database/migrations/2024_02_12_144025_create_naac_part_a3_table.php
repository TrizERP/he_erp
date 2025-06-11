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
        Schema::create('naac_part_a3', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('program',100)->nullable();
            $table->string('student_1',100)->nullable();
            $table->string('student_2',100)->nullable();
            $table->string('student_3',100)->nullable();
            $table->string('academic_1',100)->nullable();
            $table->string('academic_2',100)->nullable();
            $table->string('academic_3',100)->nullable();
            $table->string('institution_1',100)->nullable();
            $table->string('institution_2',100)->nullable();
            $table->string('institution_3',100)->nullable();
            $table->string('institution_4',100)->nullable();
            $table->bigInteger('sub_institute_id');               
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
        Schema::dropIfExists('naac_part_a3');
    }
};
