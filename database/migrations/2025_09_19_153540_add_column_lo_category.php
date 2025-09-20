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
        Schema::table('lo_category', function (Blueprint $table) {
            //
            $table->string('short_code')->after('title')->nullable();
            $table->bigInteger('updated_by')->nullable()->after('created_by'); 
            $table->timestamp('updated_at')->nullable()->after('created_at'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lo_category', function (Blueprint $table) {
            $table->dropColumn(['short_code','updated_by','updated_at']);
        });
    }
};
