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
        Schema::create('tblco_po_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('grade_id')->nullable();
            $table->bigInteger('standard_id')->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->bigInteger('co_id')->nullable();
            $table->mediumText('po_json')->nullable();
            $table->bigInteger('sub_institute_id')->nullable();
            $table->bigInteger('syear')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblco_po_mapping_tablr');
    }
};
