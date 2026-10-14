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
        Schema::create('placement_round_result', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('round_id');
            $table->unsignedBigInteger('drive_id')->index();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('student_id')->index();
            $table->string('attendance', 10)->default('pending'); // pending|present|absent
            $table->string('attendance_via', 10)->nullable(); // manual|qr
            $table->string('result', 10)->default('pending'); // pending|cleared|rejected|on_hold
            $table->decimal('score', 6, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->unique(['round_id', 'application_id'], 'placement_round_result_unique');
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
        Schema::dropIfExists('placement_round_result');
    }
};
