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
        Schema::create('deposite_lagacy_students', function (Blueprint $table) {
            $table->id();
            $table->integer('admission_year')->nullable();
            $table->string('enrollment_no')->nullable();
            $table->string('student_name')->nullable();
            $table->string('semester')->nullable();
            $table->string('student_quota')->nullable();
            $table->string('reciept_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('cheque_dd_no')->nullable();
            $table->date('cheque_dd_date')->nullable();
            $table->string('payment_mode')->nullable();
            $table->date('received_date')->nullable();
            $table->decimal('security_deposit', 10, 2)->nullable()->default(0);
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
        Schema::dropIfExists('deposite_lagacy_students');
    }
};
