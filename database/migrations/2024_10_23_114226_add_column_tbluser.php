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
        Schema::table('tbluser', function (Blueprint $table) {
            //
            $table->string('aadhar_card',50)->after('status')->nullable();
            $table->string('pan_card',50)->after('aadhar_card')->nullable();
            $table->integer('category')->after('pan_card')->nullable();
            $table->integer('bloodgroup')->after('category')->nullable();
            $table->string('marital_status',10)->after('bloodgroup')->nullable();
            $table->integer('religion')->after('marital_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbluser', function (Blueprint $table) {
            //
            $table->dropColumn('aadhar_card');
            $table->dropColumn('pan_card');
            $table->dropColumn('category');
            $table->dropColumn('bloodgroup');
            $table->dropColumn('marital_status');
            $table->dropColumn('religion');
        });
    }
};
