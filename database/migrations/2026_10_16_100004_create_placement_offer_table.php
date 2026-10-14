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
        Schema::create('placement_offer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('application_id')->nullable()->index(); // null for PPO conversions
            $table->unsignedBigInteger('drive_id')->nullable()->index();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('season_id')->nullable()->index(); // season the placement counts towards
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('role_title', 150);
            $table->decimal('ctc_fixed', 10, 2)->default(0); // LPA
            $table->decimal('ctc_variable', 10, 2)->default(0);
            $table->decimal('joining_bonus', 10, 2)->default(0);
            $table->decimal('ctc_total', 10, 2)->default(0)->index(); // fixed + variable
            $table->string('location', 150)->nullable();
            $table->date('offer_date')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('offer_letter', 255)->nullable();
            $table->text('bond_details')->nullable();
            $table->string('status', 15)->default('offered'); // offered|accepted|rejected|revoked|joined|not_joined
            $table->tinyInteger('is_ppo')->default(0);
            $table->unsignedBigInteger('internship_id')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->text('status_reason')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // TPO verification of the acceptance
            $table->dateTime('verified_at')->nullable();
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('override_by')->nullable();
            $table->index(['sub_institute_id', 'status'], 'placement_offer_status');
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
        Schema::dropIfExists('placement_offer');
    }
};
