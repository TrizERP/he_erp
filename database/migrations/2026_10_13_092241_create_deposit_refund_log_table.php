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
        Schema::create('deposit_refund_log', function (Blueprint $table) {
            $table->comment('Full audit trail of every action taken on a deposit refund request - nothing is ever deleted, only logged');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('deposit_refund_id')->nullable()->index('deposit_refund_id');
            $table->bigInteger('from_status')->nullable();
            $table->bigInteger('to_status')->nullable();
            $table->string('action', 50)->nullable()->comment('Submitted / Approved / Returned / Paid / Cancelled / Resubmitted');
            $table->mediumText('remarks')->nullable();
            $table->bigInteger('action_by')->nullable();
            $table->dateTime('action_date')->nullable();

            $table->foreign('deposit_refund_id')->references('id')->on('deposit_refund')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_refund_log');
    }
};
