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
        Schema::create('tbluser_salary_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('pay_scale')->nullable();
            $table->date('increment_date')->nullable();
            $table->string('salary_mode')->nullable();
            $table->string('basic')->nullable();
            $table->string('grade_pay')->nullable();
            $table->string('basic_pay')->nullable();
            $table->string('da')->nullable();
            $table->string('da_percentage')->nullable();
            $table->string('cla')->nullable();
            $table->string('hra')->nullable();
            $table->string('hra_percentage')->nullable();
            $table->string('vehicle_allowances')->nullable();
            $table->string('medical_allowances')->nullable();
            $table->string('other_allowances')->nullable();
            $table->string('gross_salary')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('pf_number')->nullable();
            $table->unsignedBigInteger('sub_institute_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbluser_salary_details');
    }
};
