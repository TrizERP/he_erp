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
        Schema::create('placement_outcome_other', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('outcome_type', 25); // higher_studies|entrepreneurship|off_campus_placement|other
            $table->string('organization', 200)->nullable(); // institute / venture / employer
            $table->string('program_role', 200)->nullable(); // course / venture / designation
            $table->string('country', 80)->nullable();
            $table->date('outcome_date')->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('verified')->default(0);
            $table->index(['sub_institute_id', 'outcome_type'], 'placement_outcome_type');
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
        Schema::dropIfExists('placement_outcome_other');
    }
};
