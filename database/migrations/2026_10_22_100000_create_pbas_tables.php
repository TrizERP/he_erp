<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PBAS (Performance Based Appraisal System) tables. The original standalone app keyed everything on a
 * free-text `faculty_id` (the employee id typed at its own login); here every row belongs to an ERP user
 * (tbluser.id) inside one institute (sub_institute_id). Column names follow the original tables so the
 * API score form reads the same data.
 *
 *   php artisan migrate --path=database/migrations/2026_10_22_100000_create_pbas_tables.php
 */
class CreatePbasTables extends Migration
{
    /** @var array<string, callable> table => column builder (ownership columns are added for every table) */
    protected function entryTables(): array
    {
        $s = fn (Blueprint $t, string ...$cols) => array_map(fn ($c) => $t->string($c, 255)->default(''), $cols);

        return [
            'pbas_lectures' => function (Blueprint $t) use ($s) {          // lec_semi_tut_prac
                $s($t, 'year', 'semester', 'subject', 'mode_teaching');
                foreach (['class_alloted_weekly', 'class_conducted_weekly', 'percent_class_parc', 'gtu_percentage', 'ssasit_percentage', 'gtu_rank', 'ssasit_rank'] as $c) {
                    $t->integer($c)->default(0);
                }
            },
            'pbas_materials' => fn (Blueprint $t) => $s($t, 'year', 'subject', 'material_prepared', 'material_type', 'quantity', 'resources'), // material_provided
            'pbas_papers' => fn (Blueprint $t) => $s($t, 'year', 'title', 'published_in', 'journal_detail', 'isbn_no', 'reviewed', 'co_author', 'main_author'), // paper_publication
            'pbas_exam_duties' => function (Blueprint $t) use ($s) {      // exam
                $s($t, 'year', 'exam_duty_type', 'number_of_duties');
                $t->integer('percent_carried')->default(0);
            },
            'pbas_expert_lectures' => fn (Blueprint $t) => $s($t, 'year', 'topic', 'level', 'date'), // expert_lecture
            'pbas_books' => fn (Blueprint $t) => $s($t, 'year', 'publication_type', 'book_title', 'isbn_no', 'author_type'), // book_author
            'pbas_research_projects' => fn (Blueprint $t) => $s($t, 'year', 'title', 'agency', 'period', 'amount'),
            'pbas_research_individuals' => fn (Blueprint $t) => $s($t, 'year', 'enrollment_number', 'enrollment_year', 'thesis_submitted', 'degree_awarded'), // research_individual
            'pbas_project_guides' => fn (Blueprint $t) => $s($t, 'year', 'project_type', 'number', 'guide_type'),
            'pbas_sttps' => fn (Blueprint $t) => $s($t, 'year', 'program', 'title', 'organizer', 'date', 'day_number'),
            'pbas_awards' => fn (Blueprint $t) => $s($t, 'year', 'name', 'givenby', 'date'),
            'pbas_admin_duties' => fn (Blueprint $t) => $s($t, 'year', 'post', 'com_name'), // admin
            'pbas_events' => fn (Blueprint $t) => $s($t, 'year', 'event_name', 'level', 'post'),
            'pbas_industrial_visits' => fn (Blueprint $t) => $s($t, 'year', 'activity', 'name', 'responsibilities'), // development_activities
            'pbas_memberships' => fn (Blueprint $t) => $s($t, 'year', 'professionalbody_name', 'membership_duration', 'membership_id'),
            'pbas_community_activities' => fn (Blueprint $t) => $s($t, 'year', 'name', 'worked_as', 'date'), // other
            'pbas_industrial_trainings' => fn (Blueprint $t) => $s($t, 'year', 'name', 'date', 'day_number'), // ind_training
        ];
    }

    public function up()
    {
        foreach ($this->entryTables() as $table => $columns) {
            if (Schema::hasTable($table)) {
                continue;
            }
            Schema::create($table, function (Blueprint $t) use ($columns) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('sub_institute_id')->index();
                $t->unsignedBigInteger('user_id')->index();
                $columns($t);
                $t->timestamps();
            });
        }

        // personal (Part A general information)
        if (! Schema::hasTable('pbas_profiles')) {
            Schema::create('pbas_profiles', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('sub_institute_id');
                $t->unsignedBigInteger('user_id');
                $t->string('facultyname', 255)->default('');
                $t->string('fathername', 255)->default('');
                $t->unsignedBigInteger('department_id')->nullable();   // hrms_departments.id
                $t->string('dateofjoining', 20)->default('');
                $t->string('designation', 255)->default('');
                $t->string('payband', 255)->default('');
                $t->string('promotiondate', 20)->default('');
                $t->string('address', 500)->default('');
                $t->string('email', 255)->default('');
                $t->string('mobile', 50)->default('');
                $t->string('extraqualification', 255)->nullable();
                $t->string('lwp', 20)->default('');
                $t->timestamps();
                $t->unique(['sub_institute_id', 'user_id'], 'pbas_profiles_user_unique');
            });
        }

        // flag: 1 = data locked by HOD / Principal
        if (! Schema::hasTable('pbas_locks')) {
            Schema::create('pbas_locks', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('sub_institute_id');
                $t->unsignedBigInteger('user_id');
                $t->unsignedTinyInteger('flag')->default(0);
                $t->unsignedBigInteger('changed_by')->nullable();
                $t->timestamps();
                $t->unique(['sub_institute_id', 'user_id'], 'pbas_locks_user_unique');
            });
        }

        // acr: Principal's Annual Confidential Report marks
        if (! Schema::hasTable('pbas_acr')) {
            Schema::create('pbas_acr', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('sub_institute_id');
                $t->unsignedBigInteger('user_id');
                $t->string('year', 20);
                $t->string('punctuality', 20)->default('');
                $t->string('innovative', 20)->default('');
                $t->string('dedication', 20)->default('');
                $t->string('inp', 20)->default('');
                $t->string('performance', 20)->default('');
                $t->unsignedBigInteger('entered_by')->nullable();
                $t->timestamps();
                $t->unique(['sub_institute_id', 'user_id', 'year'], 'pbas_acr_user_year_unique');
            });
        }

        // comment: HOD comments on a faculty member's data
        if (! Schema::hasTable('pbas_comments')) {
            Schema::create('pbas_comments', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('sub_institute_id');
                $t->unsignedBigInteger('user_id');
                $t->string('year', 20);
                $t->text('comment');
                $t->unsignedBigInteger('commented_by')->nullable();
                $t->timestamps();
                $t->index(['sub_institute_id', 'user_id'], 'pbas_comments_user_idx');
            });
        }
    }

    public function down()
    {
        foreach (array_merge(array_keys($this->entryTables()), ['pbas_profiles', 'pbas_locks', 'pbas_acr', 'pbas_comments']) as $table) {
            Schema::dropIfExists($table);
        }
    }
}
