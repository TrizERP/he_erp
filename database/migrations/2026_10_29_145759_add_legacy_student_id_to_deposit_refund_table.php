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
        Schema::table('deposit_refund', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_student_id')->nullable()->after('student_id');
        });
        
        Schema::table('deposit_refund', function (Blueprint $table) {
            $table->foreign('legacy_student_id')
                ->references('id')
                ->on('deposite_lagacy_students')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposit_refund', function (Blueprint $table) {
            $table->dropForeign(['legacy_student_id']);
            $table->dropColumn('legacy_student_id');
        });
    }
};
