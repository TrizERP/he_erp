<?php

namespace Tests\Feature\ApiV1;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Module 1b: lookups, Semester Division Mapping, Subject Semester Mapping, Batches, Division Planner and Periods.
 * Standards: 1 "Sem 1", 2 "Sem 3" (term 1), 3 "Sem 2" (term 2), all institute 1; 4 belongs to institute 2.
 */
class SchoolSetupMappingsTest extends ApiV1TestCase
{
    private const B = '/api/v1/school-setup';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStandards();
        DB::table('division')->insert([
            ['id' => 1, 'name' => 'A', 'sub_institute_id' => 1],
            ['id' => 2, 'name' => 'B', 'sub_institute_id' => 1],
            ['id' => 3, 'name' => 'X', 'sub_institute_id' => 2],
        ]);
    }

    private function subject(string $name, int $standard = 1, int $term = 1, int $inst = 1): int
    {
        return DB::table('subject')->insertGetId(['subject_name' => $name, 'subject_code' => strtoupper(substr($name, 0, 3)), 'standard_id' => $standard, 'sub_institute_id' => $inst, 'marking_period_id' => $term, 'status' => '1']);
    }

    // ---------------------------------------------------------------- lookups

    public function test_lookups_are_scoped_and_need_only_a_login(): void
    {
        $this->as(13)->getJson('/api/v1/lookups/standards')->assertOk();                                  // user with no screen rights
        $this->assertSame(['Sem 1', 'Sem 3'], array_column($this->as(10)->getJson('/api/v1/lookups/standards')->json('data'), 'name'));
        $this->assertSame(3, count($this->as(10)->getJson('/api/v1/lookups/standards?all_terms=1')->json('data')));
        $this->assertSame(['Sem 2'], array_column($this->as(10)->getJson('/api/v1/lookups/standards?all_terms=1&grade_id=2')->json('data'), 'name'));

        DB::table('std_div_map')->insert(['standard_id' => 1, 'division_id' => 2, 'sub_institute_id' => 1]);
        $this->assertSame(['A', 'B'], array_column($this->as(10)->getJson('/api/v1/lookups/divisions')->json('data'), 'name'));
        $this->assertSame(['B'], array_column($this->as(10)->getJson('/api/v1/lookups/divisions?standard_id=1')->json('data'), 'name')); // only mapped divisions

        $this->subject('Maths', 1, 1);
        $this->subject('Old', 1, 2);
        $this->assertSame(['Maths'], array_column($this->as(10)->getJson('/api/v1/lookups/subjects?standard_id=1')->json('data'), 'subject_name'));
        $this->assertSame(['Maths', 'Old'], array_column($this->as(10)->getJson('/api/v1/lookups/subjects?standard_id=1&all_terms=1')->json('data'), 'subject_name'));

        $this->assertSame(['Degree', 'Diploma'], array_column($this->as(10)->getJson('/api/v1/lookups/academic-sections')->json('data'), 'title'));
        $this->assertSame(['SEMESTER-1', 'SEMESTER-2'], array_column($this->as(10)->getJson('/api/v1/lookups/academic-years')->json('data'), 'title'));

        foreach ([['Own', 1, '1'], ['Shared', 0, '1'], ['Other', 2, '1'], ['Off', 1, '0']] as [$name, $inst, $status]) {
            DB::table('lms_content_category')->insert(['category_name' => $name, 'sub_institute_id' => $inst, 'status' => $status]);
        }
        $this->assertSame(['Own', 'Shared'], array_column($this->as(10)->getJson('/api/v1/lookups/content-categories')->json('data'), 'category_name'));
    }

    // ---------------------------------------------------------------- semester division mapping

    public function test_std_div_map_shows_everything_and_save_replaces_the_mapping(): void
    {
        DB::table('std_div_map')->insert([['standard_id' => 1, 'division_id' => 1, 'sub_institute_id' => 1], ['standard_id' => 4, 'division_id' => 3, 'sub_institute_id' => 2]]);

        $show = $this->as(10)->getJson(self::B.'/std-div-map')->assertOk();
        $this->assertSame(['Sem 1', 'Sem 3', 'Sem 2'], array_column($show->json('data.standards'), 'name'));      // all terms, as Blade
        $this->assertSame(['A', 'B'], array_column($show->json('data.divisions'), 'name'));
        $this->assertSame([1], $show->json('data.mapping.1'));

        $this->as(10)->postJson(self::B.'/std-div-map', ['mapping' => ['1' => [2], '2' => [1, 2]]])
            ->assertOk()->assertJsonPath('message', 'Division Mapped Successfully.')->assertJsonPath('data.mapping.2', [1, 2]);
        $this->assertSame([[1, 2], [2, 1], [2, 2]], DB::table('std_div_map')->where('sub_institute_id', 1)->orderBy('standard_id')->orderBy('division_id')->get(['standard_id', 'division_id'])->map(fn ($r) => [(int) $r->standard_id, (int) $r->division_id])->all());
        $this->assertSame(1, DB::table('std_div_map')->where('sub_institute_id', 2)->count());                     // other institute untouched

        $this->as(10)->postJson(self::B.'/std-div-map', ['mapping' => []])->assertOk();                          // clearing is allowed
        $this->assertSame(0, DB::table('std_div_map')->where('sub_institute_id', 1)->count());
    }

    public function test_std_div_map_rejects_foreign_ids_and_enforces_rights(): void
    {
        $this->as(10)->postJson(self::B.'/std-div-map', ['mapping' => ['4' => [1]]])->assertStatus(422);            // standard of institute 2
        $this->as(10)->postJson(self::B.'/std-div-map', ['mapping' => ['1' => [3]]])->assertStatus(422);            // division of institute 2
        $this->as(10)->postJson(self::B.'/std-div-map', [])->assertStatus(422);
        $this->as(11)->getJson(self::B.'/std-div-map')->assertOk();
        $this->as(11)->postJson(self::B.'/std-div-map', ['mapping' => []])->assertStatus(403);
        $this->as(13)->getJson(self::B.'/std-div-map')->assertStatus(403);
        $this->assertSame(0, DB::table('std_div_map')->where('sub_institute_id', 1)->count());
    }

    // ---------------------------------------------------------------- subject semester mapping

    public function test_sub_std_map_create_is_an_upsert_with_flags_and_image(): void
    {
        Storage::fake('local');
        $sub = $this->subject('Maths');
        $body = ['standard_id' => 1, 'subject_id' => $sub, 'display_name' => 'Mathematics', 'allow_grades' => '1', 'elective_subject' => '0', 'allow_content' => '1', 'subject_category' => 'Core', 'sort_order' => '3', 'add_content' => 'chapterwise'];

        $r = $this->as(10)->post(self::B.'/sub-std-maps', $body + ['display_image' => UploadedFile::fake()->image('cover.png')], ['Accept' => 'application/json'])
            ->assertStatus(201)->assertJsonPath('message', 'Subject-Standard Mapping Added Successfully')
            ->assertJsonPath('data.allow_grades', true)->assertJsonPath('data.elective_subject', false)->assertJsonPath('data.subject_name', 'Maths')->assertJsonPath('data.standard_name', 'Sem 1');
        $id = $r->json('data.id');
        $this->assertDatabaseHas('sub_std_map', ['id' => $id, 'allow_grades' => 'Yes', 'elective_subject' => '', 'sort_order' => 3, 'add_content' => 'chapterwise']);
        $image = DB::table('sub_std_map')->where('id', $id)->value('display_image');
        $this->assertStringStartsWith('/SubStdMapping/SubStdMap_', $image);
        Storage::disk('local')->assertExists('public'.$image);

        // posting the same standard + subject again updates that row and keeps the image
        $again = $this->as(10)->postJson(self::B.'/sub-std-maps', ['display_name' => 'Maths v2'] + $body)->assertStatus(201);
        $this->assertSame($id, $again->json('data.id'));
        $this->assertSame(1, DB::table('sub_std_map')->count());
        $this->assertSame($image, DB::table('sub_std_map')->where('id', $id)->value('display_image'));
        $this->assertSame('Maths v2', DB::table('sub_std_map')->where('id', $id)->value('display_name'));
    }

    public function test_sub_std_map_validation_and_tenant_rules(): void
    {
        $sub = $this->subject('Maths');
        $foreign = $this->subject('Theirs', 4, 1, 2);
        $ok = ['standard_id' => 1, 'subject_id' => $sub, 'display_name' => 'M'];

        $this->as(10)->postJson(self::B.'/sub-std-maps', ['standard_id' => 1, 'subject_id' => $sub])->assertStatus(422)->assertJsonStructure(['errors' => ['display_name']]);
        $this->as(10)->postJson(self::B.'/sub-std-maps', $ok + ['add_content' => 'weekly'])->assertStatus(422)->assertJsonStructure(['errors' => ['add_content']]);
        $this->as(10)->post(self::B.'/sub-std-maps', $ok + ['display_image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422)->assertJsonStructure(['errors' => ['display_image']]);
        $this->as(10)->postJson(self::B.'/sub-std-maps', ['standard_id' => 4, 'subject_id' => $sub, 'display_name' => 'M'])->assertStatus(422)->assertJsonPath('errors.standard_id.0', 'Please select a Standard.');
        $this->as(10)->postJson(self::B.'/sub-std-maps', ['standard_id' => 1, 'subject_id' => $foreign, 'display_name' => 'M'])->assertStatus(422)->assertJsonPath('errors.subject_id.0', 'Please select a Subject.');
        $this->as(11)->postJson(self::B.'/sub-std-maps', $ok)->assertStatus(403);
        $this->assertSame(0, DB::table('sub_std_map')->count());
    }

    public function test_sub_std_map_update_list_filter_and_delete_dependency(): void
    {
        $a = $this->subject('Maths');
        $b = $this->subject('Physics');
        $m1 = $this->as(10)->postJson(self::B.'/sub-std-maps', ['standard_id' => 1, 'subject_id' => $a, 'display_name' => 'Maths'])->json('data.id');
        $m2 = $this->as(10)->postJson(self::B.'/sub-std-maps', ['standard_id' => 2, 'subject_id' => $b, 'display_name' => 'Physics', 'sort_order' => 1])->json('data.id');

        // update through the multipart-friendly POST + _method=PUT
        $this->as(10)->postJson(self::B."/sub-std-maps/$m1", ['_method' => 'PUT', 'standard_id' => 1, 'subject_id' => $a, 'display_name' => 'Maths II', 'elective_subject' => true])
            ->assertOk()->assertJsonPath('message', 'Subject-Standard Mapping Updated Successfully')->assertJsonPath('data.display_name', 'Maths II')->assertJsonPath('data.elective_subject', true);
        $this->as(10)->putJson(self::B."/sub-std-maps/$m1", ['standard_id' => 2, 'subject_id' => $b, 'display_name' => 'x'])->assertStatus(422)->assertJsonStructure(['errors' => ['subject_id']]); // duplicate of m2
        $this->as(12)->putJson(self::B."/sub-std-maps/$m1", ['standard_id' => 4, 'subject_id' => $a, 'display_name' => 'x'])->assertStatus(404);

        $list = $this->as(10)->getJson(self::B.'/sub-std-maps')->assertOk();
        $this->assertSame(['Maths II', 'Physics'], array_column($list->json('data'), 'display_name'));
        $this->assertSame(['Physics'], array_column($this->as(10)->getJson(self::B.'/sub-std-maps?filters[standard_id]=2')->json('data'), 'display_name'));
        $this->assertSame(['Physics'], array_column($this->as(10)->getJson(self::B.'/sub-std-maps?search=phys')->json('data'), 'display_name'));
        $this->as(10)->getJson(self::B."/sub-std-maps/$m1")->assertOk()->assertJsonPath('data.id', $m1);
        $this->as(12)->getJson(self::B."/sub-std-maps/$m1")->assertStatus(404);

        // chapters exist for subject + standard in this year -> deletion is refused
        DB::table('chapter_master')->insert(['syear' => $this->syear, 'sub_institute_id' => 1, 'subject_id' => $a, 'standard_id' => 1, 'chapter_name' => 'Ch 1']);
        $this->as(10)->deleteJson(self::B."/sub-std-maps/$m1")->assertStatus(409)->assertJson(['success' => false, 'message' => 'You cannot delete Mapping.Mapping is having dependencies in Other Module']);
        $this->assertDatabaseHas('sub_std_map', ['id' => $m1]);
        $this->as(12)->deleteJson(self::B."/sub-std-maps/$m2")->assertStatus(404);
        $this->as(11)->deleteJson(self::B."/sub-std-maps/$m2")->assertStatus(403);
        $this->as(10)->deleteJson(self::B."/sub-std-maps/$m2")->assertOk()->assertJsonPath('message', 'Subject-Standard Mapping Deleted Successfully');
        $this->assertDatabaseMissing('sub_std_map', ['id' => $m2]);
    }

    // ---------------------------------------------------------------- batches

    public function test_batch_create_skips_duplicates_and_lists_groups(): void
    {
        $r = $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1, 'titles' => ['B1', 'b1', '  B2  ', '']])->assertStatus(201)->assertJsonPath('message', 'Batch Added Successfully');
        $this->assertSame(['B1', 'B2'], $r->json('data.created'));                                         // repeats and blanks dropped
        $this->assertDatabaseHas('batch', ['title' => 'B1', 'standard_id' => 1, 'division_id' => 1, 'sub_institute_id' => 1, 'syear' => $this->syear]);

        $r = $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1, 'titles' => ['b2', 'B3']])->assertStatus(201);
        $this->assertSame(['B3'], $r->json('data.created'));
        $this->assertSame(['b2'], $r->json('data.skipped'));
        $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1, 'titles' => ['B1']])->assertStatus(422)->assertJsonPath('errors.titles.0', 'Batch Already Exist');
        $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 2, 'titles' => ['B1']])->assertStatus(201);   // same title, other division

        DB::table('batch')->insert(['title' => 'OLD', 'standard_id' => 1, 'division_id' => 1, 'sub_institute_id' => 1, 'syear' => $this->syear - 1]); // other year
        DB::table('batch')->insert(['title' => 'THEIRS', 'standard_id' => 4, 'division_id' => 3, 'sub_institute_id' => 2, 'syear' => $this->syear]);

        $list = $this->as(10)->getJson(self::B.'/batches')->assertOk();
        $this->assertSame(2, $list->json('meta.total'));
        $this->assertSame(['B1', 'B2', 'B3'], $list->json('data.0.titles'));
        $this->assertSame(['standard_name' => 'Sem 1', 'division_name' => 'A', 'batch_count' => 3], array_intersect_key($list->json('data.0'), array_flip(['standard_name', 'division_name', 'batch_count'])));

        $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 4, 'division_id' => 1, 'titles' => ['X']])->assertStatus(422);        // foreign standard
        $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1])->assertStatus(422);                              // no titles
        $this->as(11)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1, 'titles' => ['Z']])->assertStatus(403);
    }

    public function test_batch_group_update_rename_and_delete(): void
    {
        $this->as(10)->postJson(self::B.'/batches', ['standard_id' => 1, 'division_id' => 1, 'titles' => ['B1', 'B2']])->assertStatus(201);
        $group = $this->as(10)->getJson(self::B.'/batches/group?standard_id=1&division_id=1')->assertOk();
        [$one, $two] = array_column($group->json('data.batches'), 'id');

        $this->as(10)->putJson(self::B.'/batches/group', ['standard_id' => 1, 'division_id' => 1, 'edit' => [(string) $one => 'First'], 'new' => ['B9', 'b2']])
            ->assertOk()->assertJsonPath('message', 'Batch Updated Successfully');
        $this->assertSame(['First', 'B2', 'B9'], DB::table('batch')->where('sub_institute_id', 1)->orderBy('id')->pluck('title')->all()); // renamed in place, b2 not duplicated
        $this->assertSame($one, (int) DB::table('batch')->where('title', 'First')->value('id'));

        $this->as(10)->putJson(self::B.'/batches/group', ['standard_id' => 1, 'division_id' => 1, 'edit' => [(string) $two => 'first']])->assertStatus(422)->assertJsonStructure(['errors']);
        $this->as(10)->putJson(self::B.'/batches/group', ['standard_id' => 1, 'division_id' => 1, 'edit' => [(string) $two => '   ']])->assertStatus(422);
        $this->as(10)->putJson(self::B.'/batches/group', ['standard_id' => 1, 'division_id' => 2, 'edit' => [(string) $two => 'Moved']])->assertStatus(422); // id is not in that group
        $this->as(11)->putJson(self::B.'/batches/group', ['standard_id' => 1, 'division_id' => 1])->assertStatus(403);

        $this->as(12)->deleteJson(self::B."/batches/$two")->assertStatus(404);
        $this->as(10)->deleteJson(self::B."/batches/$two")->assertOk();
        $this->assertSame(['First', 'B9'], DB::table('batch')->orderBy('id')->pluck('title')->all());
        $this->as(10)->deleteJson(self::B.'/batches/group?standard_id=1&division_id=1')->assertOk()->assertJsonPath('message', 'Batch Deleted Successfully');
        $this->assertSame(0, DB::table('batch')->count());
        $this->as(10)->getJson(self::B.'/batches/group?standard_id=1&division_id=1')->assertStatus(404);
    }

    // ---------------------------------------------------------------- division planner

    public function test_division_planner_crud_validation_and_scope(): void
    {
        $body = ['grade_id' => 1, 'standard_id' => 1, 'division_id' => 1, 'capacity' => 60, 'sem_start_date' => '2026-07-01', 'sem_end_date' => '2026-11-20'];
        $r = $this->as(10)->postJson(self::B.'/division-planner', $body)->assertStatus(201)->assertJsonPath('message', 'Division Planner Added Successfully')
            ->assertJsonPath('data.capacity', 60)->assertJsonPath('data.standard_name', 'Sem 1')->assertJsonPath('data.division_name', 'A')->assertJsonPath('data.academic_section_name', 'Degree');
        $id = $r->json('data.id');
        $this->assertDatabaseHas('division_capacity_master', ['id' => $id, 'syear' => $this->syear, 'sub_institute_id' => 1, 'created_by' => 10]);

        $this->as(10)->putJson(self::B."/division-planner/$id", ['capacity' => 75, 'sem_end_date' => '2026-12-01'] + $body)->assertOk()->assertJsonPath('data.capacity', 75)->assertJsonPath('message', 'Division Planner Updated Successfully');
        $this->assertDatabaseHas('division_capacity_master', ['id' => $id, 'updated_by' => 10, 'sem_end_date' => '2026-12-01']);

        $this->as(10)->postJson(self::B.'/division-planner', ['capacity' => 0, 'sem_start_date' => '01/07/2026'] + $body)->assertStatus(422)->assertJsonStructure(['errors' => ['capacity', 'sem_start_date']]);
        $this->as(10)->postJson(self::B.'/division-planner', ['sem_start_date' => '2026-07-10', 'sem_end_date' => '2026-07-01'] + $body)->assertStatus(422)->assertJsonStructure(['errors' => ['sem_end_date']]);
        $this->as(10)->postJson(self::B.'/division-planner', ['standard_id' => 4] + $body)->assertStatus(422)->assertJsonPath('errors.standard_id.0', 'Please select a Standard.');
        $this->as(10)->postJson(self::B.'/division-planner', [])->assertStatus(422)->assertJsonStructure(['errors' => ['grade_id', 'standard_id', 'division_id', 'capacity', 'sem_start_date', 'sem_end_date']]);

        DB::table('division_capacity_master')->insert(['syear' => $this->syear - 1, 'sub_institute_id' => 1, 'grade_id' => 1, 'standard_id' => 1, 'division_id' => 1, 'capacity' => '10']); // other year
        $list = $this->as(10)->getJson(self::B.'/division-planner')->assertOk();
        $this->assertSame(1, $list->json('meta.total'));
        $this->assertSame(['Sem 1'], array_column($this->as(10)->getJson(self::B.'/division-planner?search=sem')->json('data'), 'standard_name'));

        $this->as(12)->putJson(self::B."/division-planner/$id", ['grade_id' => 3, 'standard_id' => 4, 'division_id' => 3, 'capacity' => 1, 'sem_start_date' => '2026-01-01', 'sem_end_date' => '2026-02-01'])->assertStatus(404);
        $this->as(11)->deleteJson(self::B."/division-planner/$id")->assertStatus(403);
        $this->as(12)->deleteJson(self::B."/division-planner/$id")->assertStatus(404);
        $this->as(10)->deleteJson(self::B."/division-planner/$id")->assertOk()->assertJsonPath('message', 'Division Planner Deleted Successfully');
        $this->assertDatabaseMissing('division_capacity_master', ['id' => $id]);
    }

    // ---------------------------------------------------------------- periods

    private function periodBody(array $over = []): array
    {
        return $over + [
            'title' => 'Period-1', 'short_name' => 'P-1', 'sort_order' => 1, 'academic_section_id' => 1, 'academic_year_id' => 1, 'used_for_attendance' => true,
            'groups' => [['standard_ids' => [1, 2], 'start_time' => '08:00', 'end_time' => '08:45'], ['standard_ids' => [3], 'start_time' => '09:00:00', 'end_time' => '10:30']],
        ];
    }

    public function test_period_create_writes_details_with_length_and_lists_times(): void
    {
        $r = $this->as(10)->postJson(self::B.'/periods', $this->periodBody())->assertStatus(201)->assertJsonPath('message', 'Period Added Successfully');
        $id = $r->json('data.id');

        $this->assertDatabaseHas('period', ['id' => $id, 'title' => 'Period-1', 'used_for_attendance' => 'Yes', 'start_time' => '08:00:00', 'sub_institute_id' => 1, 'marking_period_id' => 1]);
        $details = DB::table('period_details')->where('period_id', $id)->orderBy('standard_id')->get();
        $this->assertSame([1, 2, 3], $details->pluck('standard_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame([45.0, 45.0, 90.0], $details->pluck('length')->map(fn ($v) => (float) $v)->all());              // minutes
        $this->assertSame('09:00:00', $details[2]->start_time);

        $list = $this->as(10)->getJson(self::B.'/periods')->assertOk();
        $this->assertTrue($list->json('data.0.used_for_attendance'));
        $this->assertSame(['08:00', '09:00'], $list->json('data.0.start_times'));
        $this->assertSame(['08:45', '10:30'], $list->json('data.0.end_times'));

        $show = $this->as(10)->getJson(self::B."/periods/$id")->assertOk();
        $this->assertSame([['standard_ids' => [1, 2], 'start_time' => '08:00', 'end_time' => '08:45'], ['standard_ids' => [3], 'start_time' => '09:00', 'end_time' => '10:30']], $show->json('data.groups'));
        $this->assertSame(1, $show->json('data.academic_section_id'));
    }

    public function test_period_rules_title_groups_times_and_rights(): void
    {
        $this->as(10)->postJson(self::B.'/periods', $this->periodBody())->assertStatus(201);

        $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'period-1']))->assertStatus(422)->assertJsonPath('errors.title.0', 'Period Already Exist');
        $this->as(12)->postJson(self::B.'/periods', $this->periodBody(['groups' => [], 'academic_section_id' => 3, 'academic_year_id' => null]))->assertStatus(201);                      // other institute may reuse the title
        $dup = $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'P2', 'groups' => [['standard_ids' => [1], 'start_time' => '08:00', 'end_time' => '09:00'], ['standard_ids' => [1], 'start_time' => '10:00', 'end_time' => '11:00']]]))
            ->assertStatus(422);
        $this->assertSame('A standard can only be in one timing group.', $dup->json('errors')['groups.1.standard_ids'][0]);   // error key is the field path
        $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'P3', 'groups' => [['standard_ids' => [4], 'start_time' => '08:00', 'end_time' => '09:00']]]))->assertStatus(422);   // foreign standard
        $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'P4', 'groups' => [['standard_ids' => [1], 'start_time' => '8am', 'end_time' => '25:00']]]))->assertStatus(422)->assertJsonStructure(['errors']);
        $this->as(10)->postJson(self::B.'/periods', ['title' => 'P5'])->assertStatus(422)->assertJsonStructure(['errors' => ['short_name', 'sort_order']]);
        $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'P6', 'academic_section_id' => 3]))->assertStatus(422)->assertJsonStructure(['errors' => ['academic_section_id']]);
        $this->as(11)->postJson(self::B.'/periods', $this->periodBody(['title' => 'P7']))->assertStatus(403);
        $this->assertSame(['Period-1', 'Period-1'], DB::table('period')->orderBy('id')->pluck('title')->all());
    }

    public function test_period_update_replaces_groups_allows_rename_and_delete_cleans_up(): void
    {
        $id = $this->as(10)->postJson(self::B.'/periods', $this->periodBody())->json('data.id');
        $other = $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => 'Period-2', 'sort_order' => 2, 'groups' => []]))->json('data.id');

        // rename, drop standard 2 and 3, change the time of standard 1, add standard 3 back in a new group
        $this->as(10)->putJson(self::B."/periods/$id", $this->periodBody(['title' => 'Morning', 'used_for_attendance' => false, 'groups' => [['standard_ids' => [1], 'start_time' => '08:15', 'end_time' => '09:00']]]))
            ->assertOk()->assertJsonPath('message', 'Period Updated Successfully');
        $this->assertDatabaseHas('period', ['id' => $id, 'title' => 'Morning', 'used_for_attendance' => '']);
        $rows = DB::table('period_details')->where('period_id', $id)->get();
        $this->assertSame([1], $rows->pluck('standard_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(['08:15:00', 45.0], [$rows[0]->start_time, (float) $rows[0]->length]);
        $this->assertSame(10, (int) $rows[0]->updated_by);

        $this->as(10)->putJson(self::B."/periods/$id", $this->periodBody(['title' => 'period-2']))->assertStatus(422)->assertJsonPath('errors.title.0', 'Period Already Exist'); // another period's title
        $this->as(10)->putJson(self::B."/periods/$id", $this->periodBody(['title' => 'MORNING']))->assertOk();                                                                    // own title in other case
        $this->as(12)->putJson(self::B."/periods/$id", $this->periodBody(['title' => 'Hijack', 'groups' => []]))->assertStatus(404);
        $this->as(11)->putJson(self::B."/periods/$id", $this->periodBody())->assertStatus(403);

        $this->as(12)->deleteJson(self::B."/periods/$id")->assertStatus(404);
        $this->as(11)->deleteJson(self::B."/periods/$id")->assertStatus(403);
        $this->as(10)->deleteJson(self::B."/periods/$id")->assertOk()->assertJsonPath('message', 'Period Deleted Successfully');
        $this->assertDatabaseMissing('period', ['id' => $id]);
        $this->assertSame(0, DB::table('period_details')->where('period_id', $id)->count());
        $this->assertDatabaseHas('period', ['id' => $other]);
        $this->as(10)->getJson(self::B."/periods/$id")->assertStatus(404);
    }

    public function test_period_list_search_sort_and_pagination(): void
    {
        foreach ([3 => 'Lunch', 1 => 'Assembly', 2 => 'Maths'] as $order => $title) {
            $this->as(10)->postJson(self::B.'/periods', $this->periodBody(['title' => $title, 'short_name' => substr($title, 0, 2), 'sort_order' => $order, 'groups' => []]))->assertStatus(201);
        }
        $this->assertSame(['Assembly', 'Maths', 'Lunch'], array_column($this->as(10)->getJson(self::B.'/periods')->json('data'), 'title'));      // by sort_order
        $this->assertSame(['Maths'], array_column($this->as(10)->getJson(self::B.'/periods?search=mat')->json('data'), 'title'));
        $this->assertSame(['Maths', 'Lunch', 'Assembly'], array_column($this->as(10)->getJson(self::B.'/periods?sort=-title')->json('data'), 'title'));
        $page = $this->as(10)->getJson(self::B.'/periods?per_page=2&page=2')->assertOk();
        $this->assertSame(['page' => 2, 'per_page' => 2, 'total' => 3], $page->json('meta'));
        $this->assertSame(['Lunch'], array_column($page->json('data'), 'title'));
        $this->assertSame([], $this->as(10)->getJson(self::B.'/periods?search=mat')->json('data.0.start_times'));                                  // no groups -> no times
    }
}
