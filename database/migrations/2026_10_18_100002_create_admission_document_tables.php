<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Configurable required-document rules. NULL standard_id / syear / category = applies to all.
        Schema::create('admission_document_requirement', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->string('name', 120);
            $table->bigInteger('standard_id')->nullable();
            $table->integer('syear')->nullable();
            $table->string('category', 50)->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->string('allowed_mimes', 100)->nullable();
            $table->integer('max_kb')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('admission_document', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sub_institute_id')->index();
            $table->bigInteger('enquiry_id');
            $table->bigInteger('requirement_id')->nullable();
            $table->string('doc_name', 120);
            $table->string('status', 30)->default('pending'); // pending|uploaded|verified|rejected|resubmission_required
            $table->string('storage_path', 500)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->string('rejection_reason', 500)->nullable();
            $table->bigInteger('uploaded_by')->nullable();
            $table->bigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['enquiry_id', 'status'], 'adm_doc_enquiry_idx');
            // one row per requirement per application; ad-hoc documents (NULL requirement) are not constrained
            $table->unique(['enquiry_id', 'requirement_id'], 'adm_doc_unique_requirement');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_document');
        Schema::dropIfExists('admission_document_requirement');
    }
};
