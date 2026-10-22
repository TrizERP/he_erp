<?php

namespace Tests\Feature\Pbas;

use App\Services\Pbas\PbasScore;
use PHPUnit\Framework\TestCase;

/**
 * Hand-worked cases for the API-form scoring. Each expectation was derived from reading the original pbas_facl.php
 * (the oracle test runs that original code against the same data as an independent check).
 */
class PbasScoreTest extends TestCase
{
    protected function lecture(array $o = []): array
    {
        return $o + ['mode_teaching' => 'Lecture', 'ssasit_rank' => 9, 'gtu_rank' => 20, 'percent_class_parc' => 50];
    }

    protected function compute(array $data): array
    {
        return PbasScore::compute($data);
    }

    public function test_no_data_gives_blank_cells_and_zero_totals(): void
    {
        $r = $this->compute([]);
        $this->assertNull($r['sections']['lectures']['total']);
        $this->assertNull($r['sections']['materials']['total']);
        $this->assertNull($r['sections']['papers']['total']);
        $this->assertSame(0, $r['sections']['research_individuals']['total']);
        $this->assertSame(0, $r['sections']['memberships']['total']);
        $this->assertSame([0, 0, 0, 0, 0], [$r['c1'], $r['c2'], $r['c3'], $r['c4'], $r['total']]);
    }

    public function test_lecture_rank_marks_and_the_cap_of_ten(): void
    {
        // top rank (ssasit<=3 or gtu<=5) = 10 for results; 4-5 / 6-10 = 5; 11-15 = 2
        $this->assertSame(10, $this->compute(['lectures' => [$this->lecture(['ssasit_rank' => 3])]])['sections']['lectures']['total']);
        $this->assertSame(10, $this->compute(['lectures' => [$this->lecture(['gtu_rank' => 5])]])['sections']['lectures']['total']);
        $this->assertSame(5, $this->compute(['lectures' => [$this->lecture(['ssasit_rank' => 4])]])['sections']['lectures']['total']);
        $this->assertSame(5, $this->compute(['lectures' => [$this->lecture(['ssasit_rank' => 9, 'gtu_rank' => 10])]])['sections']['lectures']['total']);
        $this->assertSame(2, $this->compute(['lectures' => [$this->lecture(['gtu_rank' => 15])]])['sections']['lectures']['total']);
        $this->assertSame(0, $this->compute(['lectures' => [$this->lecture()]])['sections']['lectures']['total']);
        // many strong rows never exceed 10
        $this->assertSame(10, $this->compute(['lectures' => [$this->lecture(['gtu_rank' => 1]), $this->lecture(['gtu_rank' => 2]), $this->lecture(['gtu_rank' => 3])]])['sections']['lectures']['total']);
    }

    public function test_labs_and_tutorials_do_not_score_and_leave_the_cell_blank(): void
    {
        $r = $this->compute(['lectures' => [$this->lecture(['mode_teaching' => 'Lab', 'gtu_rank' => 1, 'percent_class_parc' => 95])]]);
        $this->assertNull($r['sections']['lectures']['total']);
        $this->assertNull($r['sections']['lectures']['total_b']);
    }

    public function test_class_percentage_must_be_over_80_and_at_most_100(): void
    {
        $b = fn (int $p) => $this->compute(['lectures' => [$this->lecture(['percent_class_parc' => $p])]])['sections']['lectures']['total_b'];
        $this->assertSame(0, $b(80));
        $this->assertSame(10, $b(81));
        $this->assertSame(10, $b(100));
        $this->assertSame(0, $b(101));
    }

    public function test_material_marks_two_each_capped_at_five(): void
    {
        $m = fn (string $type) => ['material_type' => $type];
        $this->assertSame(2, $this->compute(['materials' => [$m('PPT')]])['sections']['materials']['total']);
        $this->assertSame(4, $this->compute(['materials' => [$m('PPT'), $m('Question Banks')]])['sections']['materials']['total']);
        $this->assertSame(5, $this->compute(['materials' => [$m('PPT'), $m('Lecture Notes'), $m('Question Banks')]])['sections']['materials']['total']);
        $this->assertNull($this->compute(['materials' => [$m('')]])['sections']['materials']['total']);
    }

    public function test_paper_expert_book_and_award_marks(): void
    {
        $r = $this->compute([
            'papers' => [['published_in' => 'Scopus Index Journal'], ['published_in' => 'UGC Approved Journal'], ['published_in' => 'Presentation in Conference/seminar'], ['published_in' => 'Other']],
            'expert_lectures' => [['level' => 'State Level/National Level'], ['level' => 'SSASIT Level']],
            'books' => [['author_type' => 'Soul Author'], ['author_type' => 'Co-Author']],
            'awards' => [[], []],
        ]);
        $this->assertSame([5, 5, 2, 0], array_column($r['sections']['papers']['rows'], 'mark'));
        $this->assertSame(12, $r['sections']['papers']['total']);
        $this->assertSame([5, 2], array_column($r['sections']['expert_lectures']['rows'], 'mark'));
        $this->assertSame([5, 2], array_column($r['sections']['books']['rows'], 'mark'));
        $this->assertSame(10, $r['sections']['awards']['total']);
    }

    public function test_research_project_amount_quirk_is_preserved(): void
    {
        // the form stores "1" / "2"; the original compares them with 100000, so both score 5
        $r = $this->compute(['research_projects' => [['amount' => '1'], ['amount' => '2'], ['amount' => '250000']]]);
        $this->assertSame([5, 5, 10], array_column($r['sections']['research_projects']['rows'], 'mark'));
    }

    public function test_research_work_uses_the_fixed_reference_year(): void
    {
        $r = $this->compute(['research_individuals' => [['enrollment_year' => '2018'], ['enrollment_year' => '2020']]]);
        $this->assertSame([15, 9], array_column($r['sections']['research_individuals']['rows'], 'mark'));
        $this->assertSame(24, $r['sections']['research_individuals']['total']);
    }

    public function test_project_guidance_marks_and_the_row_carry_over(): void
    {
        $g = fn (string $type, string $guide) => ['project_type' => $type, 'guide_type' => $guide];
        $r = $this->compute(['project_guides' => [
            $g('UG Final Year Project', 'Main Guide'), // 3
            $g('DE Project', 'Main Guide'),            // 1
            $g('PG Project', 'Main Guide'),            // 3
            $g('Ph.D project', 'Main Guide'),          // 5
            $g('PG Project', 'Co-Guide'),              // 2
            $g('UG Final Year Project', 'Co-Guide'),   // 0 earned: the displayed mark carries over from the row above
        ]]);
        $this->assertSame([3, 1, 3, 5, 2, 2], array_column($r['sections']['project_guides']['rows'], 'mark'));
        $this->assertSame(14, $r['sections']['project_guides']['total']);
    }

    public function test_sttp_marks_are_the_days_attended(): void
    {
        $r = $this->compute(['sttps' => [['day_number' => '3'], ['day_number' => '5']]]);
        $this->assertSame(['3', '5'], array_column($r['sections']['sttps']['rows'], 'mark'));
        $this->assertSame(8, $r['sections']['sttps']['total']);
    }

    public function test_admin_duty_marks_and_the_cap_of_fifteen(): void
    {
        $a = fn (string $post) => ['post' => $post];
        $r = $this->compute(['admin_duties' => [$a('HOD/Dean'), $a('Co-Dean'), $a('Class coordinator'), $a('Committee Member'), $a('Lab Incharge'), $a('Unknown')]]);
        $this->assertSame([5, 3, 3, 2, 2, 0], array_column($r['sections']['admin_duties']['rows'], 'mark'));
        $this->assertSame(15, $r['sections']['admin_duties']['total']); // 5+3+3+2+2 = 15
        $many = $this->compute(['admin_duties' => array_fill(0, 5, $a('HOD/Dean'))]);
        $this->assertSame(15, $many['sections']['admin_duties']['total']);
    }

    public function test_event_marks_by_level_and_post(): void
    {
        $e = fn (string $l, string $p) => ['level' => $l, 'post' => $p];
        $r = $this->compute(['events' => [
            $e('GTU/State Level/National Level', 'Coordinator'), $e('GTU/State Level/National Level', 'Member'),
            $e('SSASIT Level', 'Coordinator'), $e('SSASIT Level', 'Member'),
            $e('Department Level', 'Coordinator'), $e('Department Level', 'Member'), $e('Other', 'Member'),
        ]]);
        $this->assertSame([5, 2, 2, 1, 2, 1, 0], array_column($r['sections']['events']['rows'], 'mark'));
        $this->assertSame(13, $r['sections']['events']['total']);
        $this->assertNull($this->compute([])['sections']['events']['total']);
    }

    public function test_industrial_visit_membership_and_community_marks(): void
    {
        $r = $this->compute([
            'industrial_visits' => [['activity' => 'Industrial Visit with students '], ['activity' => 'Industrial Visit with students'], ['activity' => 'Industrial Visit/Training'], ['activity' => 'x']],
            'memberships' => [[], []],
            'community_activities' => [
                ['worked_as' => 'Blood Donation Camp organizer', 'name' => 'Blood Donation Camp'],
                ['worked_as' => 'Yoga class', 'name' => 'Blood Donation Camp'],
                ['worked_as' => 'Yoga class', 'name' => 'Tree Plantation'],
            ],
        ]);
        $this->assertSame([3, 3, 2, 0], array_column($r['sections']['industrial_visits']['rows'], 'mark')); // trailing space is trimmed by the request
        $this->assertSame(8, $r['sections']['industrial_visits']['total']);
        $this->assertSame([3, 3], array_column($r['sections']['memberships']['rows'], 'mark'));
        $this->assertSame(3, $r['sections']['memberships']['total']); // the shown total is the last row's mark, not the sum
        $this->assertSame([2, 1, 2], array_column($r['sections']['community_activities']['rows'], 'mark'));
    }

    public function test_category_caps_and_grand_total(): void
    {
        $lectures = array_fill(0, 3, $this->lecture(['gtu_rank' => 1, 'percent_class_parc' => 90]));   // 10 + 10
        $r = $this->compute([
            'lectures' => $lectures,
            'materials' => array_fill(0, 3, ['material_type' => 'PPT']),                                  // 5  => c1 = 25
            'papers' => array_fill(0, 6, ['published_in' => 'Scopus Index Journal']),                     // 30 => c2 capped at 20
            'admin_duties' => array_fill(0, 4, ['post' => 'HOD/Dean']),                                   // 15
            'events' => array_fill(0, 3, ['level' => 'GTU/State Level/National Level', 'post' => 'Coordinator']), // 15 => c3 capped at 10
            'industrial_visits' => array_fill(0, 5, ['activity' => 'Industrial Visit with students']),     // 15 => c4 capped at 10
        ]);
        $this->assertSame([25, 20, 10, 10, 65], [$r['c1'], $r['c2'], $r['c3'], $r['c4'], $r['total']]);
    }

    public function test_totals_below_the_caps_are_plain_sums(): void
    {
        $r = $this->compute([
            'lectures' => [$this->lecture(['gtu_rank' => 7, 'percent_class_parc' => 85])],             // 5 + 10
            'materials' => [['material_type' => 'PPT']],                                                // 2          => c1 = 17
            'papers' => [['published_in' => 'Presentation in Conference/seminar']],                    // 2
            'awards' => [[]],                                                                           // 5          => c2 = 7
            'admin_duties' => [['post' => 'Lab Incharge']],                                             // 2
            'events' => [['level' => 'SSASIT Level', 'post' => 'Member']],                              // 1          => c3 = 3
            'memberships' => [[]],                                                                      // 3
            'community_activities' => [['worked_as' => 'Yoga class', 'name' => 'Blood Donation Camp']], // 1          => c4 = 4
        ]);
        $this->assertSame([17, 7, 3, 4, 31], [$r['c1'], $r['c2'], $r['c3'], $r['c4'], $r['total']]);
    }
}
