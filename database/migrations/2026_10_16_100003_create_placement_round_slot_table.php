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
        Schema::create('placement_round_slot', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('round_id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('student_id')->index();
            $table->dateTime('slot_start');
            $table->dateTime('slot_end');
            $table->string('panel', 100)->nullable();
            $table->string('venue', 255)->nullable();
            $table->index(['round_id', 'slot_start'], 'placement_slot_round');
            $table->index(['sub_institute_id', 'slot_start', 'slot_end'], 'placement_slot_time');
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
        Schema::dropIfExists('placement_round_slot');
    }
};
