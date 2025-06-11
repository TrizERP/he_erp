<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('naac_part_a2', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('multidisciplinary_head',500)->nullable();
            $table->string('multidisciplinary_sub_head',500)->nullable();            
            $table->text('multidisciplinary_data')->nullable();
            $table->string('academic_bank_head',500)->nullable();
            $table->string('academic_bank_sub_head',500)->nullable();   
            $table->text('academic_bank_data')->nullable();
            $table->string('skill_development_head',500)->nullable();
            $table->string('skill_development_sub_head',500)->nullable();   
            $table->text('skill_development_data')->nullable();
            $table->string('appropriate_integration_head',500)->nullable();
            $table->string('appropriate_integration_sub_head',500)->nullable();   
            $table->text('appropriate_integration_data')->nullable(); 
            $table->string('focus_outcome_head',500)->nullable();
            $table->string('focus_outcome_sub_head',500)->nullable();   
            $table->text('focus_outcome_data')->nullable();
            $table->string('online_education_head',500)->nullable();
            $table->string('online_education_sub_head',500)->nullable();      
            $table->text('online_education_data')->nullable();   
            $table->bigInteger('sub_institute_id');         
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('naac_part_a2');
    }
};
