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
        Schema::create('deposit_refund', function (Blueprint $table) {
            $table->comment('Security / caution deposit refund requests raised against a student deposit account');
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->nullable();
            $table->integer('syear')->nullable();
            $table->bigInteger('student_id')->nullable()->index('student_id');
            $table->bigInteger('standard_id')->nullable();
            $table->bigInteger('section_id')->nullable();

            $table->string('request_no', 50)->nullable()->comment('assigned on submit, e.g. DR/2026/12');
            $table->string('voucher_no', 50)->nullable()->comment('assigned on payment, e.g. 2026/12');

            $table->decimal('deposit_collected', 10, 2)->nullable()->default(0)->comment('snapshot: total deposit collected till date');
            $table->decimal('deposit_returned_before', 10, 2)->nullable()->default(0)->comment('snapshot: total deposit already paid out before this request');
            $table->decimal('deposit_balance', 10, 2)->nullable()->default(0)->comment('snapshot: collected - returned_before');
            $table->decimal('unpaid_fees', 10, 2)->nullable()->default(0)->comment('snapshot: outstanding fees at the time of request');

            $table->mediumText('reason')->nullable();
            $table->decimal('deduction_total', 10, 2)->nullable()->default(0);
            $table->decimal('final_refund_amount', 10, 2)->nullable()->default(0);

            $table->string('receiver_name', 150)->nullable();
            $table->string('receiver_relation', 100)->nullable();

            $table->string('payment_mode', 50)->nullable()->comment('Cheque / Cash / Bank Transfer');
            $table->string('cheque_no', 50)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('ifsc_code', 20)->nullable();

            $table->string('supporting_document', 255)->nullable();
            $table->mediumText('clerk_remarks')->nullable();

            $table->unsignedBigInteger('status')->nullable()->default(1)->index('status');

            $table->bigInteger('requested_by')->nullable();
            $table->dateTime('requested_date')->nullable();

            $table->bigInteger('accounts_head_by')->nullable();
            $table->mediumText('accounts_head_remarks')->nullable();
            $table->dateTime('accounts_head_date')->nullable();

            $table->bigInteger('principal_by')->nullable();
            $table->mediumText('principal_remarks')->nullable();
            $table->dateTime('principal_date')->nullable();

            $table->bigInteger('paid_by')->nullable();
            $table->dateTime('paid_date')->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->mediumText('voucher_html')->nullable();

            $table->bigInteger('cancelled_by')->nullable();
            $table->dateTime('cancelled_date')->nullable();
            $table->mediumText('cancellation_reason')->nullable();

            $table->string('sms_sent', 1)->nullable()->default('N');

            $table->string('created_by', 50)->nullable();
            $table->string('created_ip_address', 150)->nullable();
            $table->dateTime('created_date')->nullable();

            $table->foreign('student_id')->references('id')->on('tblstudent')
                ->onDelete('set null')->onUpdate('set null');
            $table->foreign('status')->references('id')->on('deposit_refund_status_master')
                ->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_refund');
    }
};
