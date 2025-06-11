<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbluser_professional_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('designation')->nullable();
            $table->string('appointment_type')->nullable();
            $table->string('doctorate_degree')->nullable();
            $table->string('doctorate_degree_percentage')->nullable();
            $table->string('pg_degree')->nullable();
            $table->string('pg_degree_percentage')->nullable();
            $table->string('ug_degree')->nullable();
            $table->string('ug_degree_percentage')->nullable();
            $table->string('other_qualification')->nullable();
            $table->string('other_qualification_percentage')->nullable();
            $table->string('specification')->nullable();
            $table->integer('national_publication')->nullable();
            $table->integer('international_publication')->nullable();
            $table->integer('no_of_books_published')->nullable();
            $table->integer('no_of_patents')->nullable();
            $table->float('teaching_experience')->nullable();
            $table->float('total_work_experience')->nullable();
            $table->float('research_experience')->nullable();
            $table->integer('no_of_projects_guided')->nullable();
            $table->integer('no_of_doctorate_students_guided')->nullable();

            $table->unsignedBigInteger('sub_institute_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbluser_professional_details');
    }
};
