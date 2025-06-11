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
        Schema::table('result_create_exam', function (Blueprint $table) {
            //
            $table->bigInteger('co_id')->nullable()->after('subject_id')->comment('comming from lo_category');
            $table->bigInteger('created_by')->nullable()->after('exam_date'); 
            $table->bigInteger('updated_by')->nullable()->after('created_by'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resul_create_exam', function (Blueprint $table) {
            //
            $table->dropColumn(['co_id','created_by','updated_by']);
        });
    }
};
