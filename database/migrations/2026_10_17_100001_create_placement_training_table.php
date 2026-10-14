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
        Schema::create('placement_training', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->string('title', 200);
            $table->string('training_type', 20)->default('aptitude'); // aptitude|soft_skills|mock_interview|mock_test|technical|other
            $table->string('trainer', 150)->nullable();
            $table->date('session_date')->nullable()->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue', 255)->nullable();
            $table->decimal('max_score', 7, 2)->nullable();
            $table->json('standard_ids')->nullable(); // batch assignment: courses whose registered students attend
            $table->text('description')->nullable();
            $table->string('status', 15)->default('scheduled'); // scheduled|completed|cancelled
            $table->index(['sub_institute_id', 'training_type'], 'placement_training_type');
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
        Schema::dropIfExists('placement_training');
    }
};
