<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnlineFieldsToFeesRefundTable extends Migration
{
    public function up()
    {
        Schema::table('fees_refund', function (Blueprint $table) {
            $table->string('account_no', 50)->nullable()->after('bank_branch');
            $table->string('ifsc_code', 20)->nullable()->after('account_no');
        });
    }

    public function down()
    {
        Schema::table('fees_refund', function (Blueprint $table) {
            $table->dropColumn(['account_no', 'ifsc_code']);
        });
    }
}
