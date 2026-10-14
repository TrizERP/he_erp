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
        Schema::create('placement_company_contact', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name', 150);
            $table->string('designation', 100)->nullable();
            $table->string('contact_type', 20)->default('hr'); // hr|poc|other
            $table->string('email', 150)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->tinyInteger('is_primary')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('placement_company_contact');
    }
};
