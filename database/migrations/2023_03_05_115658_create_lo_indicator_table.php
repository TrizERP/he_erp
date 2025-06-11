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
        Schema::create('lo_indicator', function (Blueprint $table) {
            $table->comment('');
            $table->bigIncrements('id');
            $table->integer('grade_id')->nullable();
            $table->integer('standard_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->integer('chapter_id')->nullable();
            $table->integer('lomaster_id')->nullable();
            $table->string('indicator', 250)->nullable();
            $table->integer('availability')->nullable();
            $table->integer('show_hide')->nullable();
            $table->integer('sort_order')->nullable();
            $table->integer('syear')->nullable();
            $table->integer('sub_institute_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lo_indicator');
    }
};
