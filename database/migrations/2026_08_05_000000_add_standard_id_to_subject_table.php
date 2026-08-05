<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subject', function (Blueprint $table) {
            if (!Schema::hasColumn('subject', 'standard_id')) {
                $table->bigInteger('standard_id')->nullable()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('subject', function (Blueprint $table) {
            if (Schema::hasColumn('subject', 'standard_id')) {
                $table->dropColumn('standard_id');
            }
        });
    }
};
