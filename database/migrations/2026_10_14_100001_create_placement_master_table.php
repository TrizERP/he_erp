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
        Schema::create('placement_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->string('master_type', 40)->index(); // industry|job_role|job_type|location|salary_band|package_category|skill|round_type|document_type
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->decimal('min_ctc', 12, 2)->nullable(); // salary_band / package_category (LPA)
            $table->decimal('max_ctc', 12, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->index(['sub_institute_id', 'master_type', 'status'], 'placement_master_lookup');
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
        Schema::dropIfExists('placement_master');
    }
};
