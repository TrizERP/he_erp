<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_paper', function (Blueprint $table) {
            $table->string('tag_name', 500)->nullable()->after('question_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_paper', function (Blueprint $table) {
            $table->dropColumn('tag_name');
        });
    }
};
