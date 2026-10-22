<?php

namespace Tests\Feature\Pbas;

use Illuminate\Support\Facades\DB;

class PbasWorkflowTest extends PbasTestCase
{
    protected function profileBody(array $override = []): array
    {
        return $override + [
            'facultyname' => 'ASHA PATEL', 'fathername' => 'Ramesh Patel', 'department' => $this->deptCse, 'dateofjoining' => '2015-06-01',
            'designation' => 'Assistant Professor', 'payband' => '15600-39100 AGP 6000', 'promotiondate' => '2020-07-01',
            'address' => '12 Ring Road, Surat 395007', 'email' => 'asha@example.com', 'mobile' => '9999900001',
            'qualification' => 'NA', 'lwp' => '00',
        ];
    }

    // -- Part A profile -----------------------------------------------------------------------------------

    public function test_first_visit_prefills_the_form_from_the_erp_user_record(): void
    {
        $title = DB::table('hrms_job_titles')->insertGetId(['title' => 'Associate Professor']);
        $me = $this->makeUser('Lecturer', $this->deptMech, ['first_name' => 'Asha', 'last_name' => 'Patel', 'jobtitle_id' => $title, 'joined_date' => '2014-07-15 00:00:00']);

        $html = $this->as('Lecturer', $me)->get(route('pbas.profile.index'))->assertOk()
            ->assertSee('GENERAL INFORMATION')->assertSee('Personal-info')->assertSee('Info! Please write 00 in LWP if it is 0.')
            ->getContent();
        $this->assertStringContainsString('value="Asha Patel"', $html);
        $this->assertStringContainsString('value="Associate Professor"', $html);
        $this->assertStringContainsString('value="2014-07-15"', $html);
        $this->assertMatchesRegularExpression('/<option value="'.$this->deptMech.'"\s+selected/', $html);
    }

    public function test_department_dropdown_is_the_erp_department_master_of_the_institute(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.profile.index'))->getContent();
        $this->assertStringContainsString('Computer Engineering', $html);
        $this->assertStringContainsString('Mechanical Engineering', $html);
        $this->assertStringNotContainsString('Other Institute Dept', $html);
        $this->assertStringNotContainsString('Humanitites and Social Science', $html); // the original's fixed list is gone
    }

    public function test_saving_the_profile_inserts_then_updates(): void
    {
        $me = $this->makeUser();
        $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody())
            ->assertRedirect(route('pbas.profile.index'))->assertSessionHas('pbas_success', 'You data added successfully!');
        $this->assertSame(1, DB::table('pbas_profiles')->count());
        $row = DB::table('pbas_profiles')->first();
        $this->assertSame('ASHA PATEL', $row->facultyname);
        $this->assertSame($this->deptCse, (int) $row->department_id);
        $this->assertSame('00', $row->lwp);

        $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody(['payband' => 'New Band', 'promotiondate' => '2024-01-01']))
            ->assertSessionHas('pbas_updated', 'Data Updated sucessfully');
        $row = DB::table('pbas_profiles')->first();
        $this->assertSame(1, DB::table('pbas_profiles')->count());
        $this->assertSame('New Band', $row->payband);          // the original wrote promotiondate into payband here (bug, not kept)
        $this->assertSame('2024-01-01', $row->promotiondate);

        $this->as('Lecturer', $me)->get(route('pbas.profile.index'))->assertSee('value="New Band"', false)->assertSee('Data Updated sucessfully')
            ->assertSessionMissing('pbas_updated');
    }

    public function test_profile_requires_every_field_and_a_valid_department(): void
    {
        $me = $this->makeUser();
        foreach (array_keys($this->profileBody()) as $field) {
            $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody([$field => '']))
                ->assertSessionHas('pbas_error', 'All Fields are required');
        }
        $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody(['lwp' => '0']))->assertSessionHas('pbas_error'); // "0" is empty(), hence "write 00"
        $other = DB::table('hrms_departments')->where('sub_institute_id', self::OTHER_INST)->value('id');
        $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody(['department' => $other]))->assertSessionHas('pbas_error');
        $this->assertSame(0, DB::table('pbas_profiles')->count());
    }

    // -- HOD: lock / unlock / comment ---------------------------------------------------------------------

    public function test_hod_sees_only_their_own_department_and_can_lock_and_unlock(): void
    {
        $hod = $this->makeUser('HOD', $this->deptCse, ['first_name' => 'Hema', 'last_name' => 'Head']);
        $mine = $this->makeUser('Lecturer', $this->deptCse, ['first_name' => 'Mine', 'last_name' => 'Faculty']);
        $theirs = $this->makeUser('Lecturer', $this->deptMech, ['first_name' => 'Theirs', 'last_name' => 'Faculty']);
        $student = $this->makeUser('Student', $this->deptCse, ['first_name' => 'Stu', 'last_name' => 'Dent']);
        $inactive = $this->makeUser('Lecturer', $this->deptCse, ['first_name' => 'Gone', 'last_name' => 'Away', 'status' => 0]);

        $html = $this->as('HOD', $hod)->get(route('pbas.hod.index'))->assertOk()
            ->assertSee('Lock/Unlock  Data :  Computer Engineering', false)->assertSee('VIEW/LOCK FACULTY DATA')->getContent();
        $this->assertStringContainsString('Mine Faculty', $html);
        $this->assertStringContainsString('Hema Head', $html);
        $this->assertStringNotContainsString('Theirs Faculty', $html);
        $this->assertStringNotContainsString('Stu Dent', $html);
        $this->assertStringNotContainsString('Gone Away', $html);
        $this->assertStringNotContainsString('Data Already locked', $html);

        $this->as('HOD', $hod)->post(route('pbas.hod.lock'), ['facultyid' => $mine, 'facultyname' => 'Mine Faculty'])
            ->assertRedirect(route('pbas.hod.index'))->assertSessionHas('pbas_locked', 'Data Locked Succesfully for : Mine Faculty');
        $this->assertSame(1, (int) DB::table('pbas_locks')->where('user_id', $mine)->value('flag'));
        $this->as('HOD', $hod)->get(route('pbas.hod.index'))->assertSee('Data Already locked...');

        $this->as('HOD', $hod)->post(route('pbas.hod.unlock'), ['facultyid' => $mine, 'facultyname' => 'Mine Faculty'])
            ->assertSessionHas('pbas_unlocked', 'Data Un-Locked Succesfully for : Mine Faculty');
        $this->assertSame(0, (int) DB::table('pbas_locks')->where('user_id', $mine)->value('flag'));
        $this->assertSame(1, DB::table('pbas_locks')->count()); // updated in place, not duplicated
        $this->as('HOD', $hod)->get(route('pbas.hod.index'))->assertSee('data is Not Locked Yet!!!');
    }

    public function test_hod_cannot_touch_another_department_and_faculty_cannot_use_hod_screens(): void
    {
        $hod = $this->makeUser('HOD', $this->deptCse);
        $theirs = $this->makeUser('Lecturer', $this->deptMech);
        $mine = $this->makeUser('Lecturer', $this->deptCse);

        $this->as('HOD', $hod)->post(route('pbas.hod.lock'), ['facultyid' => $theirs])->assertForbidden();
        $this->as('HOD', $hod)->get(route('pbas.hod.comment', ['username' => $theirs, 'year' => '2025-2026']))->assertForbidden();
        $this->as('HOD', $hod)->post(route('pbas.hod.comment.store'), ['username' => $theirs, 'year' => '2025-2026', 'commentsta' => 'x'])->assertForbidden();
        $this->as('HOD', $hod)->get(route('pbas.report.show', $theirs))->assertForbidden();
        $this->assertSame(0, DB::table('pbas_locks')->count());

        $this->as('Lecturer', $mine)->get(route('pbas.hod.index'))->assertForbidden();
        $this->as('Lecturer', $mine)->post(route('pbas.hod.lock'), ['facultyid' => $mine])->assertForbidden();
        $this->as('Lecturer', $mine)->get(route('pbas.principal.index'))->assertForbidden();
        $this->as('HOD', $hod)->get(route('pbas.principal.index'))->assertForbidden();
    }

    public function test_hod_comments_are_saved_validated_and_listed(): void
    {
        $hod = $this->makeUser('HOD');
        $fac = $this->makeUser('Lecturer');
        $y = $this->currentYear();

        $this->as('HOD', $hod)->get(route('pbas.hod.comment', ['username' => $fac, 'year' => $y]))->assertOk()
            ->assertSee('LOCK/UNLOCK/COMMENT DATA')->assertSee('Write Your Comments here')->assertSee('Back to Lock Faculty Data');

        $this->as('HOD', $hod)->post(route('pbas.hod.comment.store'), ['username' => $fac, 'year' => $y, 'commentsta' => ''])
            ->assertSessionHas('pbas_error', 'All Fields are required');
        $this->assertSame(0, DB::table('pbas_comments')->count());

        $this->as('HOD', $hod)->post(route('pbas.hod.comment.store'), ['username' => $fac, 'year' => $y, 'commentsta' => 'Good progress'])
            ->assertRedirect(route('pbas.hod.comment', ['username' => $fac, 'year' => $y]))->assertSessionHas('pbas_success', 'successfully eneterd comment!');
        $this->as('HOD', $hod)->get(route('pbas.hod.comment', ['username' => $fac, 'year' => $y]))->assertSee('Good progress');
        $this->as('HOD', $hod)->get(route('pbas.hod.index'))->assertSee('value="Good progress"', false);
    }

    public function test_principal_and_admin_can_manage_any_department(): void
    {
        $principal = $this->makeUser('Principal');
        $mech = $this->makeUser('Lecturer', $this->deptMech);
        $this->as('Principal', $principal)->post(route('pbas.hod.lock'), ['facultyid' => $mech])->assertSessionHas('pbas_locked');
        $this->as('Admin')->post(route('pbas.hod.unlock'), ['facultyid' => $mech])->assertSessionHas('pbas_unlocked');
    }

    // -- Principal: department list and ACR ---------------------------------------------------------------

    public function test_principal_lists_departments_and_faculty_with_comments_lock_and_acr_status(): void
    {
        $principal = $this->makeUser('Principal');
        $fac = $this->makeUser('Lecturer', $this->deptCse, ['first_name' => 'Fay', 'last_name' => 'Culty']);
        DB::table('pbas_comments')->insert([
            ['sub_institute_id' => self::INST, 'user_id' => $fac, 'year' => $this->currentYear(), 'comment' => 'First note'],
            ['sub_institute_id' => self::INST, 'user_id' => $fac, 'year' => $this->currentYear(), 'comment' => 'Second note'],
        ]);
        DB::table('pbas_locks')->insert(['sub_institute_id' => self::INST, 'user_id' => $fac, 'flag' => 1]);

        $home = $this->as('Principal', $principal)->get(route('pbas.principal.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Computer Engineering', $home);
        $this->assertStringContainsString('Mechanical Engineering', $home);
        $this->assertStringNotContainsString('Fay Culty', $home);

        $html = $this->as('Principal', $principal)->get(route('pbas.principal.index', ['e' => $this->deptCse]))->assertOk()
            ->assertSee('VIEW DATA/ ADD ACR  :  Computer Engineering For Year :'.$this->currentYear(), false)
            ->assertSee('Fay Culty')->assertSee('Data Already locked...')->assertSee('ACR not entered')->getContent();
        $this->assertStringContainsString("1 First note\n2 Second note", $html);
        $this->assertStringContainsString('Add ACR Data', $html);
    }

    public function test_acr_marks_are_inserted_then_updated_for_the_session_year(): void
    {
        $principal = $this->makeUser('Principal');
        $fac = $this->makeUser('Lecturer', $this->deptCse, ['first_name' => 'Fay', 'last_name' => 'Culty']);
        $body = ['facultyid' => $fac, 'moveback' => $this->deptCse, 'punctuality' => '1', 'innovative' => '1.5', 'dedication' => '1.5', 'inp' => '2', 'performance' => '2'];

        $this->as('Principal', $principal)->get(route('pbas.principal.acr', ['facultyid' => $fac, 'moveback' => $this->deptCse]))->assertOk()
            ->assertSee('Insert/Update Data of  ACR for : Fay Culty', false)->assertSee('Punctuality')->assertSee('Innovative Approach')
            ->assertSee('Committed to work')->assertSee('Obedient')->assertSee('5. Overall Performance')->assertSee('Add/Update ACR Marks')
            ->assertSee('Move Back to Faculty List');

        $this->as('Principal', $principal)->post(route('pbas.principal.acr.store'), $body)
            ->assertSessionHas('pbas_success', 'Data inserted sucessfully for : Fay Culty for year '.$this->currentYear());
        $this->as('Principal', $principal)->post(route('pbas.principal.acr.store'), ['performance' => '3', 'innovative' => '0'] + $body)
            ->assertSessionHas('pbas_success', 'Data updated Succesfully for : Fay Culty for year '.$this->currentYear());

        $this->assertSame(1, DB::table('pbas_acr')->count());
        $row = DB::table('pbas_acr')->first();
        $this->assertSame($this->currentYear(), $row->year);
        $this->assertSame('3', $row->performance);
        $this->assertSame('0', $row->innovative); // a zero mark is accepted
        $this->as('Principal', $principal)->get(route('pbas.principal.index', ['e' => $this->deptCse]))->assertSee('ACR entered');
    }

    public function test_acr_requires_every_mark_and_refuses_other_roles(): void
    {
        $principal = $this->makeUser('Principal');
        $fac = $this->makeUser('Lecturer');
        $body = ['facultyid' => $fac, 'punctuality' => '1', 'innovative' => '1', 'dedication' => '1', 'inp' => '1', 'performance' => '1'];
        foreach (['punctuality', 'innovative', 'dedication', 'inp', 'performance'] as $field) {
            $this->as('Principal', $principal)->post(route('pbas.principal.acr.store'), [$field => '  '] + $body)->assertSessionHas('pbas_error', 'All Fields are required');
        }
        $this->assertSame(0, DB::table('pbas_acr')->count());
        $this->as('HOD')->post(route('pbas.principal.acr.store'), $body)->assertForbidden();
        $this->as('Lecturer', $fac)->post(route('pbas.principal.acr.store'), $body)->assertForbidden();
    }

    // -- API form access ----------------------------------------------------------------------------------

    public function test_api_form_asks_for_part_a_first_then_renders(): void
    {
        $me = $this->makeUser();
        $this->as('Lecturer', $me)->get(route('pbas.report.index'))->assertRedirect(route('pbas.profile.index'))->assertSessionHas('pbas_error');

        $this->as('Lecturer', $me)->post(route('pbas.profile.store'), $this->profileBody());
        $this->as('Lecturer', $me)->get(route('pbas.report.index'))->assertOk()
            ->assertSee('Annual Self-Assessment form', false)->assertSee('ASHA PATEL')->assertSee('Computer Engineering')
            ->assertSee('From June '.self::SYEAR.' TO May '.(self::SYEAR + 1), false);
    }

    public function test_hod_opens_the_api_form_of_a_department_member_only(): void
    {
        $hod = $this->makeUser('HOD', $this->deptCse);
        $mine = $this->makeUser('Lecturer', $this->deptCse);
        $this->as('Lecturer', $mine)->post(route('pbas.profile.store'), $this->profileBody(['facultyname' => 'MINE PERSON']));

        $this->as('HOD', $hod)->get(route('pbas.report.show', $mine))->assertOk()->assertSee('MINE PERSON');
        $this->as('Lecturer', $mine)->get(route('pbas.report.show', $mine))->assertForbidden(); // faculty use the own-data route
    }
}
