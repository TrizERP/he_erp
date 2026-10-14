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
        Schema::create('placement_company_communication', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('mode', 20)->default('email'); // email|call|meeting|visit|other
            $table->string('subject', 255)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('communicated_at');
            $table->date('follow_up_date')->nullable()->index();
            $table->tinyInteger('follow_up_done')->default(0);
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
        Schema::dropIfExists('placement_company_communication');
    }
};
