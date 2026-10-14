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
        Schema::create('placement_policy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('season_id')->nullable()->index();
            $table->string('name', 150);
            $table->tinyInteger('one_offer_rule')->default(0);
            $table->tinyInteger('dream_offer_rule')->default(0);
            $table->decimal('dream_min_ctc', 12, 2)->nullable(); // dream-offer holder may apply only above this CTC (LPA)
            $table->unsignedInteger('max_applications')->nullable();
            $table->unsignedInteger('max_active_backlogs')->nullable();
            $table->tinyInteger('block_blacklisted')->default(1);
            $table->unsignedInteger('debar_days')->nullable(); // debar period after an offer drop-out
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // principal approval
            $table->dateTime('approved_at')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('placement_policy');
    }
};
