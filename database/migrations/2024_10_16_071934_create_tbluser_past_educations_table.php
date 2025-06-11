<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbluser_past_educations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('degree')->nullable();
            $table->string('medium')->nullable();
            $table->string('university_name')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('main_subject')->nullable();
            $table->string('secondary_subject')->nullable();
            $table->string('percentage')->nullable();
            $table->float('cpi')->nullable();
            $table->float('cgpa')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('sub_institute_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbluser_past_educations');
    }
};
