<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_refund_status_master', function (Blueprint $table) {
            $table->comment('');
            $table->bigIncrements('id');
            $table->string('title', 100)->nullable();
            $table->integer('sort_order')->nullable();
        });

        DB::table('deposit_refund_status_master')->insert([
            ['id' => 1, 'title' => 'Pending Accounts Head Approval', 'sort_order' => 1],
            ['id' => 2, 'title' => 'Returned by Accounts Head',      'sort_order' => 2],
            ['id' => 3, 'title' => 'Pending Principal Approval',     'sort_order' => 3],
            ['id' => 4, 'title' => 'Returned by Principal',          'sort_order' => 4],
            ['id' => 5, 'title' => 'Approved - Pending Payment',     'sort_order' => 5],
            ['id' => 6, 'title' => 'Paid',                           'sort_order' => 6],
            ['id' => 7, 'title' => 'Cancelled',                      'sort_order' => 7],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_refund_status_master');
    }
};
