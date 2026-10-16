<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // One row per student per year: the waiver shown (read-only) on the fees collect page.
        Schema::create('fees_waiver', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->integer('syear');
            $table->bigInteger('student_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('remarks', 255)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamp('applied_at')->nullable(); // set once the waiver is used in a fees receipt
            $table->timestamps();
            $table->unique(['sub_institute_id', 'syear', 'student_id'], 'fees_waiver_student_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fees_waiver');
    }
};
