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
        Schema::table('tblstudent', function (Blueprint $table) {
            //
            $table->string('pass_year',50)->nullable()->after('admission_date');
            $table->string('cgpa',50)->nullable()->after('pass_year');

        });

       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tblstudent', function (Blueprint $table) {
            //
            $table->dropColumn('pass_year');
            $table->dropColumn('cgpa');

        });

       
    }
};
