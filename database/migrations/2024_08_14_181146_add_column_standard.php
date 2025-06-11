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
        Schema::table('tblstudent_enrollment', function (Blueprint $table) {
            //
            $table->string('tution_fees',200)->nullable()->after('remarks');

        });

        Schema::table('standard', function (Blueprint $table) {
            //
            $table->date('sem_start_date')->nullable()->after('next_standard_id');
            $table->date('sem_end_date')->nullable()->after('sem_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tblstudent_enrollment', function (Blueprint $table) {
            //
            $table->dropColumn('tution_fees');

        });

        Schema::table('standard', function (Blueprint $table) {
            //
              $table->dropColumn('sem_start_date');
              $table->dropColumn('sem_end_date');
        });
    }
};
