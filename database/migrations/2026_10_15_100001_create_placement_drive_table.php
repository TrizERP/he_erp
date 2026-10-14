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
        Schema::create('placement_drive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('season_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('drive_mode', 20)->default('on_campus'); // on_campus|off_campus|pool|virtual
            $table->date('drive_date')->nullable()->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue', 255)->nullable();
            $table->dateTime('registration_deadline')->nullable()->index();
            $table->string('status', 25)->default('draft'); // draft|published|registration_open|registration_closed|in_progress|completed|cancelled
            $table->text('bond_details')->nullable();
            $table->string('jd_file', 255)->nullable();
            $table->text('cancel_reason')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->index(['sub_institute_id', 'status'], 'placement_drive_status');
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
        Schema::dropIfExists('placement_drive');
    }
};
