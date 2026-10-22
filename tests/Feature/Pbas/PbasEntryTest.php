<?php

namespace Tests\Feature\Pbas;

use App\Services\Pbas\PbasEntries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PbasEntryTest extends PbasTestCase
{
    public static function slugs(): array
    {
        $out = [];
        foreach (array_keys(PbasEntries::all()) as $slug) {
            $out[$slug] = [$slug];
        }

        return $out;
    }

    /** A valid POST body for an entry, built from its definition. */
    protected function payload(string $slug, array $override = []): array
    {
        $body = [];
        foreach (PbasEntries::find($slug)['fields'] as $field) {
            $body[$field['name']] = match ($field['type']) {
                'year' => $this->currentYear(),
                'select', 'radio' => $field['options'][0][0],
                'number' => '5',
                default => ! empty($field['int']) ? '7' : 'Sample '.$field['name'],
            };
        }

        return $override + $body;
    }

    // -- definition integrity -----------------------------------------------------------------------------

    /** @dataProvider slugs */
    public function test_every_definition_matches_its_table(string $slug): void
    {
        $def = PbasEntries::find($slug);
        $this->assertTrue(Schema::hasTable($def['table']), "table {$def['table']}");
        foreach ($def['fields'] as $field) {
            $this->assertTrue(Schema::hasColumn($def['table'], $field['column'] ?? $field['name']), "{$slug}: column for field {$field['name']}");
        }
        foreach ($def['columns'] as $header => $column) {
            $this->assertTrue(Schema::hasColumn($def['table'], $column), "{$slug}: list column {$column}");
        }
        $this->assertTrue(\Route::has("pbas.{$slug}.index") && \Route::has("pbas.{$slug}.store") && \Route::has("pbas.{$slug}.destroy"));
    }

    public function test_the_original_pages_are_all_present(): void
    {
        // 15 sidebar pages + the two the original sidebar had commented out (exam duties, industrial training)
        $this->assertCount(17, PbasEntries::all());
        $this->assertCount(15, collect(PbasEntries::all())->filter(fn ($d) => $d['group'])->all());
        $this->assertSame(['teaching', 'departmental', 'institute', 'society'], array_keys(PbasEntries::menuGroups()));
        $this->assertCount(2, PbasEntries::menuGroups()['teaching']);
        $this->assertCount(8, PbasEntries::menuGroups()['departmental']);
        $this->assertCount(2, PbasEntries::menuGroups()['institute']);
        $this->assertCount(3, PbasEntries::menuGroups()['society']);
    }

    // -- screens ------------------------------------------------------------------------------------------

    /** @dataProvider slugs */
    public function test_screen_renders_with_the_original_labels_and_columns(string $slug): void
    {
        $def = PbasEntries::find($slug);
        $response = $this->as('Lecturer')->get(route("pbas.{$slug}.index"));
        $response->assertOk()->assertSee($def['h1'], false)->assertSee(trim($def['h5']), false)->assertSee('Add Record')->assertSee('Delete');
        foreach ($def['fields'] as $field) {
            $response->assertSee(e($field['label']), false);
            if (! empty($field['placeholder'])) {
                $response->assertSee(e($field['placeholder']), false);
            }
        }
        foreach (array_keys($def['columns']) as $header) {
            $response->assertSee(trim($header), false);
        }
    }

    public function test_year_dropdown_comes_from_the_erp_academic_years_and_preselects_the_session_year(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.lectures.index'))->assertOk()->getContent();
        $this->assertStringContainsString('<option value="2024-2025" >', preg_replace('/\s+/', ' ', $html));
        $this->assertMatchesRegularExpression('/<option value="2025-2026"\s+selected/', $html);
    }

    public function test_semester_and_mode_dropdowns_match_the_original(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.lectures.index'))->getContent();
        foreach (['1st Semester', '2nd Semester', '3rd Semester', '4th Semester', '5th Semester', '6th Semester', '7th Semester', '8th Semester', 'Lecture', 'Lab', 'Tutorial'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('name="gturank"', $html);
        $this->assertStringContainsString('type="number"', $html);
    }

    public function test_material_screen_uses_the_yearm_field_and_radio_groups(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.materials.index'))->getContent();
        $this->assertStringContainsString('name="yearm"', $html);
        $this->assertSame(2, substr_count($html, 'name="material_prepared"'));
        $this->assertSame(3, substr_count($html, 'name="material_type"'));
    }

    // -- adding -------------------------------------------------------------------------------------------

    /** @dataProvider slugs */
    public function test_adding_a_record_stores_it_for_the_logged_in_user_only(string $slug): void
    {
        $def = PbasEntries::find($slug);
        $me = $this->makeUser();
        $other = $this->makeUser();

        $this->as('Lecturer', $me)->post(route("pbas.{$slug}.store"), $this->payload($slug))
            ->assertRedirect(route("pbas.{$slug}.index"))
            ->assertSessionHas('pbas_success', 'successfully eneterd data!');

        $row = DB::table($def['table'])->first();
        $this->assertSame(1, DB::table($def['table'])->count());
        $this->assertSame($me, (int) $row->user_id);
        $this->assertSame(self::INST, (int) $row->sub_institute_id);
        foreach ($def['fields'] as $field) {
            $expected = $this->payload($slug)[$field['name']];
            $this->assertEquals($expected, $row->{$field['column'] ?? $field['name']}, "{$slug}.{$field['name']}");
        }

        // the list shows the row to its owner and to nobody else
        $this->as('Lecturer', $me)->get(route("pbas.{$slug}.index"))->assertSee($row->year);
        $this->assertSame(0, DB::table($def['table'])->where('user_id', $other)->count());
    }

    /** @dataProvider slugs */
    public function test_any_empty_posted_field_rejects_the_whole_form(string $slug): void
    {
        foreach (PbasEntries::find($slug)['fields'] as $field) {
            if ($field['type'] === 'radio') {
                continue; // an untouched radio group is not posted at all (see its own test)
            }
            $this->as('Lecturer', $this->makeUser())->post(route("pbas.{$slug}.store"), $this->payload($slug, [$field['name'] => '']))
                ->assertRedirect(route("pbas.{$slug}.index"))
                ->assertSessionHas('pbas_error', 'All Fields are required');
        }
        $this->assertSame(0, DB::table(PbasEntries::find($slug)['table'])->count());
    }

    public function test_zero_counts_as_empty_like_the_original(): void
    {
        $this->as('Lecturer')->post(route('pbas.lectures.store'), $this->payload('lectures', ['gturank' => '0']))
            ->assertSessionHas('pbas_error', 'All Fields are required');
        $this->assertSame(0, DB::table('pbas_lectures')->count());
    }

    public function test_unticked_radio_groups_do_not_block_the_form_and_are_stored_empty(): void
    {
        $body = $this->payload('materials');
        unset($body['material_prepared'], $body['material_type']);
        $this->as('Lecturer')->post(route('pbas.materials.store'), $body)->assertSessionHas('pbas_success');
        $row = DB::table('pbas_materials')->first();
        $this->assertSame('', $row->material_prepared);
        $this->assertSame('', $row->material_type);
    }

    public function test_non_numeric_text_in_an_integer_column_is_a_graceful_error(): void
    {
        $this->as('Lecturer')->post(route('pbas.lectures.store'), $this->payload('lectures', ['gtu_percentage' => 'abc']))
            ->assertSessionHas('pbas_error', 'Problem in insertion. Try Again!');
        $this->assertSame(0, DB::table('pbas_lectures')->count());
    }

    public function test_posted_values_are_kept_after_a_validation_error(): void
    {
        $html = $this->as('Lecturer')->from(route('pbas.awards.index'))
            ->followingRedirects()
            ->post(route('pbas.awards.store'), $this->payload('awards', ['givenby' => '', 'name' => 'Best Teacher']))
            ->getContent();
        $this->assertStringContainsString('All Fields are required', $html);
        $this->assertStringContainsString('value="Best Teacher"', $html);
    }

    public function test_the_industrial_visit_option_keeps_the_original_trailing_space_value(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.industrial-visits.index'))->getContent();
        $this->assertStringContainsString('value="Industrial Visit with students "', $html);
    }

    public function test_the_duplicate_yoga_option_of_the_original_is_kept(): void
    {
        $html = $this->as('Lecturer')->get(route('pbas.community-activities.index'))->getContent();
        $this->assertSame(3, substr_count($html, 'value="Yoga class"'));
    }

    // -- deleting -----------------------------------------------------------------------------------------

    public function test_delete_removes_only_the_selected_rows_of_the_logged_in_user(): void
    {
        $me = $this->makeUser();
        $other = $this->makeUser();
        $this->as('Lecturer', $me)->post(route('pbas.awards.store'), $this->payload('awards'));
        $this->as('Lecturer', $me)->post(route('pbas.awards.store'), $this->payload('awards'));
        $this->as('Lecturer', $other)->post(route('pbas.awards.store'), $this->payload('awards'));
        [$mine1, $mine2, $theirs] = DB::table('pbas_awards')->orderBy('id')->pluck('id')->all();

        $this->as('Lecturer', $me)->delete(route('pbas.awards.destroy'), ['usersdaaward' => [$mine1, $theirs]])
            ->assertRedirect(route('pbas.awards.index'));

        $this->assertEqualsCanonicalizing([$mine2, $theirs], DB::table('pbas_awards')->pluck('id')->all());
    }

    public function test_delete_with_nothing_selected_is_harmless(): void
    {
        $this->as('Lecturer')->post(route('pbas.awards.store'), $this->payload('awards'));
        $this->as('Lecturer')->delete(route('pbas.awards.destroy'))->assertRedirect(route('pbas.awards.index'));
        $this->assertSame(1, DB::table('pbas_awards')->count());
    }

    public function test_rows_are_isolated_between_institutes(): void
    {
        $other = $this->makeUser('Lecturer', null, [], self::OTHER_INST);
        $this->as('Lecturer', $other, self::OTHER_INST)->post(route('pbas.awards.store'), $this->payload('awards'));
        $id = DB::table('pbas_awards')->value('id');

        // same user id in the first institute cannot see or delete it
        $this->as('Lecturer', $other, self::INST)->delete(route('pbas.awards.destroy'), ['usersdaaward' => [$id]]);
        $this->assertSame(1, DB::table('pbas_awards')->count());
    }

    // -- locking ------------------------------------------------------------------------------------------

    public function test_a_locked_member_cannot_add_or_delete_and_the_form_is_disabled(): void
    {
        $me = $this->makeUser();
        $this->as('Lecturer', $me)->post(route('pbas.awards.store'), $this->payload('awards'));
        DB::table('pbas_locks')->insert(['sub_institute_id' => self::INST, 'user_id' => $me, 'flag' => 1]);

        $this->as('Lecturer', $me)->post(route('pbas.awards.store'), $this->payload('awards'))->assertSessionHas('pbas_error');
        $this->as('Lecturer', $me)->delete(route('pbas.awards.destroy'), ['usersdaaward' => [DB::table('pbas_awards')->value('id')]])->assertSessionHas('pbas_error');
        $this->assertSame(1, DB::table('pbas_awards')->count());
        $this->as('Lecturer', $me)->get(route('pbas.awards.index'))->assertSeeInOrder(['<fieldset', 'disabled="disabled"'], false);

        DB::table('pbas_locks')->where('user_id', $me)->update(['flag' => 0]);
        $this->as('Lecturer', $me)->post(route('pbas.awards.store'), $this->payload('awards'))->assertSessionHas('pbas_success');
        $this->assertSame(2, DB::table('pbas_awards')->count());
    }

    // -- access -------------------------------------------------------------------------------------------

    public function test_anonymous_visitors_are_sent_to_login(): void
    {
        $this->get(route('pbas.lectures.index'))->assertRedirect(route('home'));
        $this->post(route('pbas.lectures.store'), [])->assertRedirect(route('home'));
    }

    public function test_students_and_non_staff_profiles_are_refused(): void
    {
        $this->as('Student')->get(route('pbas.lectures.index'))->assertForbidden();
        $this->as('Student')->post(route('pbas.lectures.store'), $this->payload('lectures'))->assertForbidden();
    }

    public function test_a_profile_without_menu_rights_cannot_use_the_screens(): void
    {
        // 'Accountant' is staff (pbas.role passes) but the seeder gave it no rights: check_permissions refuses
        $this->as('Accountant')->get(route('pbas.lectures.index'))->assertForbidden();
    }

    public function test_unknown_entry_slug_is_404(): void
    {
        $this->as('Admin')->get('/pbas/entry/not-a-screen')->assertNotFound();
    }
}
