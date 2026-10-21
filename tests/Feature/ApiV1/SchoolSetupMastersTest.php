<?php

namespace Tests\Feature\ApiV1;

use Illuminate\Support\Facades\DB;

/** Module 1a: setup menu, labels and the four "Add Masters" resources (academy, standard, division, subject). */
class SchoolSetupMastersTest extends ApiV1TestCase
{
    private const BASE = '/api/v1/school-setup';

    // ---------------------------------------------------------------- setup menu + labels

    public function test_setup_menu_lists_master_screens_for_admin_and_keeps_them_out_of_the_sidebar(): void
    {
        $me = $this->as(10)->getJson('/api/v1/auth/me')->assertOk();
        $setup = $me->json('data.setup_menu');

        $this->assertSame(['Institute (Master)'], array_column($setup, 'name')); // heading granted to Admin (menu id 37)
        $screen = $setup[0]['children'][0];
        $this->assertSame('Create Subject', $screen['name']);
        $this->assertSame('/school_setup/subject_master', $screen['path']);        // same path as the Blade route
        $this->assertSame(['view' => true, 'add' => true, 'edit' => true, 'delete' => true], $screen['permissions']);
        $this->assertSame([], $me->json('data.menu'));                           // MASTER menus are not sidebar entries
    }

    public function test_labels_use_default_strings_overridden_by_the_institute(): void
    {
        DB::table('app_language')->insert([
            ['string' => 'standard', 'value' => 'Standard', 'sub_institute_id' => 0, 'status' => 1],
            ['string' => 'division', 'value' => 'Division', 'sub_institute_id' => 0, 'status' => 1],
            ['string' => 'standard', 'value' => 'Semester', 'sub_institute_id' => 1, 'status' => 1],   // institute 1 override
            ['string' => 'division', 'value' => 'Batch', 'sub_institute_id' => 1, 'status' => 0],      // inactive: ignored
            ['string' => 'standard', 'value' => 'Class', 'sub_institute_id' => 2, 'status' => 1],      // other institute
        ]);

        $this->as(10)->getJson('/api/v1/labels')->assertOk()
            ->assertJsonPath('data.standard', 'Semester')->assertJsonPath('data.division', 'Division');
        $this->as(12)->getJson('/api/v1/labels')->assertJsonPath('data.standard', 'Class');
    }

    // ---------------------------------------------------------------- rights

    public function test_rights_are_enforced_per_action(): void
    {
        $this->seedStandards();
        $this->as(11)->getJson(self::BASE.'/divisions')->assertOk();                                   // teacher can view
        $this->as(11)->postJson(self::BASE.'/divisions', ['name' => 'A'])->assertStatus(403)->assertJson(['success' => false]);
        $this->as(11)->deleteJson(self::BASE.'/subjects/1')->assertStatus(403);
        $this->as(13)->getJson(self::BASE.'/divisions')->assertStatus(403);                            // no rights row at all
        $this->defaultHeaders = [];
        $this->getJson(self::BASE.'/divisions')->assertStatus(401);                                     // no token
    }

    // ---------------------------------------------------------------- academy

    public function test_academic_section_create_list_validate_and_duplicate(): void
    {
        $this->as(10)->postJson(self::BASE.'/academic-sections', ['title' => 'Degree', 'short_name' => 'DEG', 'sort_order' => 2, 'shift' => 'Morning', 'medium' => 'English'])
            ->assertStatus(201)->assertJsonPath('message', 'Academic Section Added Successfully !')->assertJsonPath('data.title', 'Degree');
        $this->assertDatabaseHas('academic_section', ['title' => 'Degree', 'sub_institute_id' => 1, 'shift' => 'Morning']);

        $this->as(10)->postJson(self::BASE.'/academic-sections', ['title' => 'Degree'])
            ->assertStatus(422)->assertJsonPath('errors.title.0', 'Academic Section Already Exists !');
        $this->as(12)->postJson(self::BASE.'/academic-sections', ['title' => 'Degree'])->assertStatus(201); // other institute may reuse it

        $this->as(10)->postJson(self::BASE.'/academic-sections', ['short_name' => 'x', 'sort_order' => 'abc'])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['title', 'sort_order']]);

        $list = $this->as(10)->getJson(self::BASE.'/academic-sections')->assertOk();
        $this->assertSame(1, $list->json('meta.total'));                                                // only this institute's rows
        $this->assertSame('DEG', $list->json('data.0.short_name'));
    }

    // ---------------------------------------------------------------- standard

    public function test_standard_is_created_in_the_current_term_with_all_fields(): void
    {
        $this->seedStandards();
        $this->as(10)->postJson(self::BASE.'/standards', [
            'grade_id' => 1, 'name' => 'Sem 5', 'short_name' => 'S5', 'sort_order' => 5, 'medium' => 'English',
            'course_duration' => '6 months', 'next_grade_id' => 1, 'next_standard_id' => 2,
        ])->assertStatus(201)->assertJsonPath('message', 'Standard Added Successfully !')->assertJsonPath('data.name', 'Sem 5');

        $this->assertDatabaseHas('standard', ['name' => 'Sem 5', 'sub_institute_id' => 1, 'marking_period_id' => 1, 'next_standard_id' => 2, 'course_duration' => '6 months']);
        $this->as(10, ['X-Term-Id' => '2'])->postJson(self::BASE.'/standards', ['name' => 'Sem 6'])->assertStatus(201);
        $this->assertDatabaseHas('standard', ['name' => 'Sem 6', 'marking_period_id' => 2]);

        $this->as(10)->postJson(self::BASE.'/standards', ['short_name' => 'x'])->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_standard_list_is_term_scoped_filterable_searchable_and_sortable(): void
    {
        $this->seedStandards();

        $term1 = $this->as(10)->getJson(self::BASE.'/standards')->assertOk();
        $this->assertSame(['Sem 1', 'Sem 3'], array_column($term1->json('data'), 'name'));             // term 1 only, by sort_order
        $this->assertSame('Degree', $term1->json('data.0.grade_title'));                               // joined academic section

        $all = $this->as(10)->getJson(self::BASE.'/standards?filters[all_terms]=1')->assertOk();
        $this->assertSame(3, $all->json('meta.total'));                                                // other institute excluded

        $this->assertSame(['Sem 2'], array_column($this->as(10)->getJson(self::BASE.'/standards?filters[all_terms]=1&filters[grade_id]=2')->json('data'), 'name'));
        $this->assertSame(['Sem 3'], array_column($this->as(10)->getJson(self::BASE.'/standards?search=S3')->json('data'), 'name'));
        $this->assertSame(['Sem 3', 'Sem 1'], array_column($this->as(10)->getJson(self::BASE.'/standards?sort=-name')->json('data'), 'name'));
        $this->as(10)->getJson(self::BASE.'/standards?sort=password;drop')->assertOk();                // unknown sort field falls back safely
    }

    // ---------------------------------------------------------------- division

    public function test_division_create_and_duplicate(): void
    {
        $this->as(10)->postJson(self::BASE.'/divisions', ['name' => 'A'])->assertStatus(201)->assertJsonPath('message', 'Division Added Successfully');
        $this->as(10)->postJson(self::BASE.'/divisions', ['name' => 'A'])->assertStatus(422)->assertJsonPath('errors.name.0', 'Division Already Exists');
        $this->as(10)->postJson(self::BASE.'/divisions', [])->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
        $this->as(12)->postJson(self::BASE.'/divisions', ['name' => 'A'])->assertStatus(201);
        $this->assertSame(1, $this->as(10)->getJson(self::BASE.'/divisions')->json('meta.total'));
    }

    // ---------------------------------------------------------------- subject

    public function test_subject_create_with_major_flag_and_term(): void
    {
        $this->seedStandards();
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Maths', 'subject_code' => 'M1', 'short_name' => 'MT', 'major' => true])
            ->assertStatus(201)->assertJsonPath('message', 'Subject Added Successfully')
            ->assertJsonPath('data.major', true)->assertJsonPath('data.standard_name', 'Sem 1');
        $this->assertDatabaseHas('subject', ['subject_name' => 'Maths', 'subject_type' => 'Major', 'marking_period_id' => 1, 'sub_institute_id' => 1, 'status' => '1']);

        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Art'])->assertStatus(201)->assertJsonPath('data.major', false);
    }

    public function test_subject_rules_duplicate_required_standard_and_tenant(): void
    {
        $this->seedStandards();
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Maths'])->assertStatus(201);

        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'maths'])                // case-insensitive
            ->assertStatus(422)->assertJsonPath('errors.subject_name.0', 'This subject already exists for the selected Standard.');
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 2, 'subject_name' => 'Maths'])->assertStatus(201); // other standard is fine

        $this->as(10)->postJson(self::BASE.'/subjects', ['subject_name' => 'Physics'])
            ->assertStatus(422)->assertJsonPath('errors.standard_id.0', 'Please select a Standard.');
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 4, 'subject_name' => 'Physics'])                // standard of institute 2
            ->assertStatus(422)->assertJsonPath('errors.standard_id.0', 'Please select a Standard.');
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1])->assertStatus(422)->assertJsonStructure(['errors' => ['subject_name']]);
    }

    public function test_subject_update_delete_and_list(): void
    {
        $this->seedStandards();
        $a = $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Maths'])->json('data.id');
        $b = $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Physics', 'major' => true])->json('data.id');

        $this->as(10)->putJson(self::BASE."/subjects/$a", ['standard_id' => 2, 'subject_name' => 'Maths II', 'major' => true])
            ->assertOk()->assertJsonPath('message', 'Subject Updated Successfully')->assertJsonPath('data.standard_name', 'Sem 3')->assertJsonPath('data.major', true);
        $this->as(10)->putJson(self::BASE."/subjects/$b", ['standard_id' => 2, 'subject_name' => 'maths ii'])->assertStatus(422);  // clashes with the updated one
        $this->as(10)->putJson(self::BASE."/subjects/$b", ['standard_id' => 1, 'subject_name' => 'PHYSICS'])->assertOk();          // keeping its own name is fine
        $this->as(12)->putJson(self::BASE."/subjects/$a", ['standard_id' => 4, 'subject_name' => 'Hacked'])->assertStatus(404);      // other institute

        $list = $this->as(10)->getJson(self::BASE.'/subjects')->assertOk();
        $this->assertSame(['Maths II', 'PHYSICS'], array_column($list->json('data'), 'subject_name'));
        $this->assertSame(['Maths II'], array_column($this->as(10)->getJson(self::BASE.'/subjects?filters[standard_id]=2')->json('data'), 'subject_name'));
        $this->assertSame(['PHYSICS'], array_column($this->as(10)->getJson(self::BASE.'/subjects?search=phys')->json('data'), 'subject_name'));

        $this->as(12)->deleteJson(self::BASE."/subjects/$a")->assertStatus(404);
        $this->as(10)->deleteJson(self::BASE."/subjects/$a")->assertOk()->assertJsonPath('message', 'Subject Deleted Successfully');
        $this->assertDatabaseMissing('subject', ['id' => $a]);
    }

    public function test_subject_list_is_term_scoped(): void
    {
        $this->seedStandards();
        $this->as(10)->postJson(self::BASE.'/subjects', ['standard_id' => 1, 'subject_name' => 'Term1 subject'])->assertStatus(201);
        $this->as(10, ['X-Term-Id' => '2'])->postJson(self::BASE.'/subjects', ['standard_id' => 3, 'subject_name' => 'Term2 subject'])->assertStatus(201);

        $this->assertSame(['Term1 subject'], array_column($this->as(10)->getJson(self::BASE.'/subjects')->json('data'), 'subject_name'));
        $this->assertSame(['Term2 subject'], array_column($this->as(10, ['X-Term-Id' => '2'])->getJson(self::BASE.'/subjects')->json('data'), 'subject_name'));
    }

    public function test_pagination_meta_and_per_page_cap(): void
    {
        foreach (range(1, 30) as $i) {
            DB::table('division')->insert(['name' => sprintf('D%02d', $i), 'sub_institute_id' => 1]);
        }
        $p1 = $this->as(10)->getJson(self::BASE.'/divisions?per_page=10&page=2')->assertOk();
        $this->assertSame(['page' => 2, 'per_page' => 10, 'total' => 30], $p1->json('meta'));
        $this->assertSame('D11', $p1->json('data.0.name'));
        $this->assertSame(100, $this->as(10)->getJson(self::BASE.'/divisions?per_page=5000')->json('meta.per_page'));
    }
}
