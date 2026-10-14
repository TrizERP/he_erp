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
        Schema::create('placement_training_attendance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('student_id')->index();
            $table->string('attendance', 10)->default('pending'); // pending|present|absent
            $table->decimal('score', 7,2)->nullable();
            $table->unsignedTinyInteger('rating')->nullable(); // mock interview, 1-5
            $table->text('feedback')->nullable();
            $table->unique(['training_id', 'student_id'], 'placement_training_att_unique');
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
        Schema::dropIfExists('placement_training_attendance');
    }
};
