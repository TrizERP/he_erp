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
        Schema::create('placement_company', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->string('name', 200);
            $table->unsignedBigInteger('industry_id')->nullable()->index(); // placement_master (industry)
            $table->string('website', 255)->nullable();
            $table->string('hq_location', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('mou_status', 30)->default('none'); // none|in_progress|signed|expired
            $table->date('mou_valid_till')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('gst_no', 30)->nullable();
            $table->string('pan_no', 20)->nullable();
            $table->string('status', 20)->default('prospect'); // prospect|invited|confirmed|visited|blacklisted
            $table->text('blacklist_reason')->nullable();
            $table->text('visit_history')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedBigInteger('season_id')->nullable()->index();
            $table->index(['sub_institute_id', 'status'], 'placement_company_status');
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
        Schema::dropIfExists('placement_company');
    }
};
