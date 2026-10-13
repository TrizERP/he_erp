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
        Schema::create('deposit_refund_deduction', function (Blueprint $table) {
            $table->comment('Itemised deductions made out of a deposit refund, e.g. breakage charge, library fine');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('deposit_refund_id')->nullable()->index('deposit_refund_id');
            $table->string('title', 150)->nullable();
            $table->decimal('amount', 10, 2)->nullable()->default(0);
            $table->string('remarks', 255)->nullable();

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
        Schema::dropIfExists('deposit_refund_deduction');
    }
};
