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
        Schema::table('nacc_part_a1', function (Blueprint $table) {
            //
            $table->string('assurance_file',500)->after('institute_assurance')->nullable();
            $table->string('conferred_status_file',500)->after('special_conferred_status')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nacc_part_a1', function (Blueprint $table) {
            //
        });
    }
};
