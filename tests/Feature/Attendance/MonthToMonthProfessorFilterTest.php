<?php

namespace Tests\Feature\Attendance;

use App\Http\Controllers\attendance\monthwiseAttendanceReportController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The optional "Professor" dropdown of the Subject Month to Month report: the AJAX list must contain only
 * professors who actually took attendance for that subject / standard / division, within the user's institute
 * and academic year. In-memory SQLite only (the guard aborts otherwise).
 */
class MonthToMonthProfessorFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $c = DB::connection();
        if ($c->getDriverName() !== 'sqlite' || $c->getDatabaseName() !== ':memory:') {
            $this->fail('Refusing to run outside in-memory SQLite.');
        }
        // SQLite has no CONCAT_WS in older builds; the query is written for MySQL
        $c->getPdo()->sqliteCreateFunction('CONCAT_WS', function ($sep, ...$parts) {
            return implode($sep, array_filter($parts, fn ($p) => $p !== null && $p !== ''));
        });

        Schema::create('tbluser', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('first_name');
            $t->string('middle_name')->nullable();
            $t->string('last_name');
            $t->integer('sub_institute_id')->default(0);
        });
        Schema::create('attendance_student', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->integer('syear')->nullable();
            $t->integer('student_id')->nullable();
            $t->integer('teacher_id')->nullable();
            $t->integer('standard_id')->nullable();
            $t->integer('section_id')->nullable();
            $t->integer('subject_id')->nullable();
            $t->integer('sub_institute_id')->nullable();
        });

        DB::table('tbluser')->insert([
            ['id' => 11, 'first_name' => 'Meena', 'middle_name' => null, 'last_name' => 'Shah', 'sub_institute_id' => 1],
            ['id' => 12, 'first_name' => 'Amit', 'middle_name' => 'K', 'last_name' => 'Patel', 'sub_institute_id' => 1],
            ['id' => 13, 'first_name' => 'Other', 'middle_name' => null, 'last_name' => 'Subject', 'sub_institute_id' => 1],
            ['id' => 21, 'first_name' => 'Foreign', 'middle_name' => null, 'last_name' => 'Institute', 'sub_institute_id' => 2],
        ]);
        $row = fn ($teacher, $o = []) => $o + ['syear' => 2026, 'student_id' => 1, 'teacher_id' => $teacher, 'standard_id' => 5, 'section_id' => 3, 'subject_id' => 9, 'sub_institute_id' => 1];
        DB::table('attendance_student')->insert([
            $row(11), $row(11, ['student_id' => 2]), $row(11, ['student_id' => 3]),   // same professor, many students: listed once
            $row(12),
            $row(13, ['subject_id' => 10]),                                          // different subject
            $row(13, ['section_id' => 4]),                                           // different division
            $row(13, ['syear' => 2025]),                                             // different year
            $row(21, ['sub_institute_id' => 2]),                                     // different institute
        ]);

        session()->put(['sub_institute_id' => 1, 'syear' => 2026]);
    }

    protected function professors(array $query)
    {
        return app(monthwiseAttendanceReportController::class)->professors(Request::create('/x', 'GET', $query))->getData(true);
    }

    public function test_only_professors_who_took_that_subjects_attendance_are_listed_once_each_and_sorted(): void
    {
        $list = $this->professors(['standard_id' => 5, 'division_id' => 3, 'subject_id' => 9]);

        $this->assertSame([12, 11], array_column($list, 'id'), 'Amit then Meena (sorted by first name), each once');
        $this->assertSame(['Amit K Patel', 'Meena Shah'], array_column($list, 'name'));
    }

    public function test_a_subject_nobody_has_taken_gives_an_empty_list_so_the_dropdown_stays_on_all(): void
    {
        $this->assertSame([], $this->professors(['standard_id' => 5, 'division_id' => 3, 'subject_id' => 777]));
        $this->assertSame([], $this->professors([]), 'missing parameters never leak everything');
    }

    public function test_the_list_never_crosses_institute_or_academic_year(): void
    {
        $this->assertNotContains(21, array_column($this->professors(['standard_id' => 5, 'division_id' => 3, 'subject_id' => 9]), 'id'));

        session()->put(['sub_institute_id' => 2]);
        $this->assertSame([21], array_column($this->professors(['standard_id' => 5, 'division_id' => 3, 'subject_id' => 9]), 'id'));

        session()->put(['sub_institute_id' => 1, 'syear' => 2025]);
        $this->assertSame([13], array_column($this->professors(['standard_id' => 5, 'division_id' => 3, 'subject_id' => 9]), 'id'));
    }
}
