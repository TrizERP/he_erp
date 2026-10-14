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
        Schema::create('placement_drive_round', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('drive_id')->index();
            $table->unsignedBigInteger('round_type_id')->nullable(); // placement_master (round_type)
            $table->string('name', 150);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('mode', 20)->default('offline'); // offline|online
            $table->date('round_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue', 255)->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled|completed|cancelled
            $table->tinyInteger('is_final')->default(0);
            $table->dateTime('results_published_at')->nullable();
            $table->index(['drive_id', 'sequence'], 'placement_round_order');
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
        Schema::dropIfExists('placement_drive_round');
    }
};
