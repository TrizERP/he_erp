<?php

namespace App\Services\Pbas;

/**
 * The scoring rules of the original PBAS "API Form" (pbas_facl.php), moved out of the page into a pure function.
 * It is a line-for-line port, including its quirks, so existing faculty see the same marks:
 *   - a mark is `null` (blank cell) until a row contributes to it, exactly like the original's un-initialised sums;
 *   - the project-guide row mark carries over from the previous row when a row earns nothing;
 *   - research-work marks use a fixed reference year (2023);
 *   - research-project marks compare the stored "1"/"2" option value against 100000, so both options score 5.
 * Categories are capped at 25 / 20 / 10 / 10 and the grand total is the sum of the capped values.
 */
class PbasScore
{
    /** Reference year the original hard-coded in the research-work formula. */
    public const RESEARCH_REFERENCE_YEAR = 2023;

    /**
     * @param  array<string, array<int, array>>  $data  section => rows (associative arrays); missing sections count as empty
     * @return array{sections: array<string, array{rows: array, total: mixed}>, c1: int|float, c2: int|float, c3: int|float, c4: int|float, total: int|float}
     */
    public static function compute(array $data): array
    {
        $rows = fn (string $section) => array_values($data[$section] ?? []);
        $n = fn ($v) => is_numeric($v) ? $v + 0 : 0;
        $sections = [];

        // 1.1 Lectures, seminars, tutorials, practicals
        $c11a = null;
        $c11b = null;
        $out = [];
        foreach ($rows('lectures') as $row) {
            if ($row['mode_teaching'] == 'Lecture') {
                if ($row['ssasit_rank'] <= 3 || $row['gtu_rank'] <= 5) {
                    $c11a = $c11a + 10;
                } elseif ($row['ssasit_rank'] >= 4 && $row['ssasit_rank'] <= 5 || $row['gtu_rank'] > 5 && $row['gtu_rank'] <= 10) {
                    $c11a = $c11a + 5;
                } elseif ($row['gtu_rank'] > 10 && $row['gtu_rank'] <= 15) {
                    $c11a = $c11a + 2;
                } else {
                    $c11a = $c11a + 0;
                }

                if ((int) $row['percent_class_parc'] <= 100 && (int) $row['percent_class_parc'] > 80) {
                    $c11b = $c11b + 10;
                } else {
                    $c11b = $c11b + 0;
                }
            }
            if ($c11a >= 10) {
                $c11a = 10;
            }
            if ($c11b >= 10) {
                $c11b = 10;
            }
            $out[] = $row;
        }
        $sections['lectures'] = ['rows' => $out, 'total' => $c11a, 'total_b' => $c11b];

        // 1.2 Reading / instructional material
        $c12 = null;
        $out = [];
        foreach ($rows('materials') as $row) {
            if (in_array($row['material_type'], ['Lecture Notes', 'PPT', 'Question Banks', 'Others', 'LectandPPT'], true)) {
                $c12 = $c12 + 2;
            }
            if ($c12 >= 5) {
                $c12 = 5;
            }
            $out[] = $row;
        }
        $sections['materials'] = ['rows' => $out, 'total' => $c12];

        // 3a Published / presented papers
        $c3a = null;
        $out = [];
        foreach ($rows('papers') as $row) {
            if ($row['published_in'] == 'Scopus Index Journal') {
                $cp = 5;
            } elseif ($row['published_in'] == 'UGC Approved Journal') {
                $cp = 5;
            } elseif ($row['published_in'] == 'Presentation in Conference/seminar') {
                $cp = 2;
            } else {
                $cp = 0;
            }
            $c3a = $c3a + $cp;
            $out[] = $row + ['mark' => $cp];
        }
        $sections['papers'] = ['rows' => $out, 'total' => $c3a];

        // examination duties: listed only (the original page read columns the table does not have, so it shows blanks)
        $out = [];
        foreach ($rows('exam_duties') as $row) {
            $out[] = $row;
        }
        $sections['exam_duties'] = ['rows' => $out, 'total' => null];

        // 3b expert lectures
        $c3b2 = null;
        $out = [];
        foreach ($rows('expert_lectures') as $row) {
            if ($row['level'] == 'State Level/National Level') {
                $c22 = 5;
            } elseif ($row['level'] == 'SSASIT Level') {
                $c22 = 2;
            } else {
                $c22 = 0;
            }
            $c3b2 = $c3b2 + $c22;
            $out[] = $row + ['mark' => $c22];
        }
        $sections['expert_lectures'] = ['rows' => $out, 'total' => $c3b2];

        // 3 articles / chapters in books
        $baut = null;
        $out = [];
        foreach ($rows('books') as $row) {
            if ($row['author_type'] == 'Soul Author') {
                $c23 = 5;
            } elseif ($row['author_type'] == 'Co-Author') {
                $c23 = 2;
            } else {
                $c23 = 0;
            }
            $baut = $baut + $c23;
            $out[] = $row + ['mark' => $c23];
        }
        $sections['books'] = ['rows' => $out, 'total' => $baut];

        // 3c research projects / consultancies
        $c3c2 = null;
        $out = [];
        foreach ($rows('research_projects') as $row) {
            if ($row['amount'] >= 0 && $row['amount'] <= 100000) {
                $c24 = 5;
            } elseif ($row['amount'] > 100000) {
                $c24 = 10;
            } else {
                $c24 = 0;
            }
            $c3c2 = $c3c2 + $c24;
            $out[] = $row + ['mark' => $c24];
        }
        $sections['research_projects'] = ['rows' => $out, 'total' => $c3c2];

        // research work (enrolment based)
        $c25 = 0;
        $out = [];
        foreach ($rows('research_individuals') as $row) {
            $yc = (self::RESEARCH_REFERENCE_YEAR - $n($row['enrollment_year'])) * 3;
            $c25 = $c25 + $yc;
            $out[] = $row + ['mark' => $yc];
        }
        $sections['research_individuals'] = ['rows' => $out, 'total' => $c25];

        // awards
        $awrd = 0;
        $out = [];
        foreach ($rows('awards') as $row) {
            $c26 = 5;
            $awrd = $awrd + $c26;
            $out[] = $row + ['mark' => $c26];
        }
        $sections['awards'] = ['rows' => $out, 'total' => $awrd];

        // projects guided (the row mark carries over from the previous row when nothing matches, as in the original)
        $c3d = null;
        $c27 = 0;
        $out = [];
        foreach ($rows('project_guides') as $row) {
            if ($row['project_type'] == 'UG Final Year Project' && $row['guide_type'] == 'Main Guide') {
                $c3d = $c3d + 3;
                $c27 = 3;
            } elseif ($row['project_type'] == 'DE Project' && $row['guide_type'] == 'Main Guide') {
                $c3d = $c3d + 1;
                $c27 = 1;
            } elseif ($row['project_type'] == 'PG Project' && $row['guide_type'] == 'Main Guide') {
                $c3d = $c3d + 3;
                $c27 = 3;
            } elseif ($row['project_type'] == 'Ph.D project' && $row['guide_type'] == 'Main Guide') {
                $c3d = $c3d + 5;
                $c27 = 5;
            } elseif (($row['project_type'] == 'DE Project' || $row['project_type'] == 'PG Project') && $row['guide_type'] == 'Co-Guide') {
                $c3d = $c3d + 2;
                $c27 = 2;
            } else {
                $c3d = $c3d + 0;
            }
            $out[] = $row + ['mark' => $c27];
        }
        $sections['project_guides'] = ['rows' => $out, 'total' => $c3d];

        // STTP / FDP: marks are the number of days
        $tstp = 0;
        $out = [];
        foreach ($rows('sttps') as $row) {
            $stp = $row['day_number'];
            $tstp = $tstp + $n($stp);
            $out[] = $row + ['mark' => $stp];
        }
        $sections['sttps'] = ['rows' => $out, 'total' => $tstp];

        // 2.1 administrative duties (capped at 15)
        $c21 = null;
        $out = [];
        foreach ($rows('admin_duties') as $row) {
            if ($row['post'] == 'HOD/Dean') {
                $cp = 5;
            } elseif ($row['post'] == 'Co-Dean' || $row['post'] == 'Class coordinator') {
                $cp = 3;
            } elseif ($row['post'] == 'Committee Member' || $row['post'] == 'Lab Incharge') {
                $cp = 2;
            } else {
                $cp = 0;
            }
            $c21 = $c21 + $cp;
            if ($c21 >= 15) {
                $c21 = 15;
            }
            $out[] = $row + ['mark' => $cp];
        }
        $sections['admin_duties'] = ['rows' => $out, 'total' => $c21];

        // 2.2 events
        $et = null;
        $out = [];
        $eventRows = $rows('events');
        if ($eventRows) {
            $et = 0;
        }
        foreach ($eventRows as $row) {
            $ce22 = 0;
            if ($row['level'] == 'GTU/State Level/National Level' && $row['post'] == 'Coordinator') {
                $ce22 = $ce22 + 5;
                $et = $et + $ce22;
            } elseif ($row['level'] == 'GTU/State Level/National Level' && $row['post'] == 'Member') {
                $ce22 = $ce22 + 2;
                $et = $et + $ce22;
            } elseif ($row['level'] == 'SSASIT Level' && $row['post'] == 'Coordinator') {
                $ce22 = $ce22 + 2;
                $et = $et + $ce22;
            } elseif ($row['level'] == 'SSASIT Level' && $row['post'] == 'Member') {
                $ce22 = $ce22 + 1;
                $et = $et + $ce22;
            } elseif ($row['level'] == 'Department Level' && $row['post'] == 'Coordinator') {
                $ce22 = $ce22 + 2;
                $et = $et + $ce22;
            } elseif ($row['level'] == 'Department Level' && $row['post'] == 'Member') {
                $ce22 = $ce22 + 1;
                $et = $et + $ce22;
            }
            $out[] = $row + ['mark' => $ce22];
        }
        $sections['events'] = ['rows' => $out, 'total' => $et];

        // 4 industrial visits / training (the form posts a trailing space in one option; the request is trimmed)
        $tdact = 0;
        $out = [];
        foreach ($rows('industrial_visits') as $row) {
            $activity = trim((string) $row['activity']);
            if ($activity === 'Industrial Visit with students') {
                $dact = 3;
            } elseif ($activity === 'Industrial Visit/Training') {
                $dact = 2;
            } else {
                $dact = 0;
            }
            $tdact = $dact + $tdact;
            $out[] = $row + ['mark' => $dact];
        }
        $sections['industrial_visits'] = ['rows' => $out, 'total' => $tdact];

        // 4 professional membership: 3 per membership, the shown total is the last row's mark (max 5)
        $pdev = 0;
        $t42b = 0;
        $out = [];
        foreach ($rows('memberships') as $row) {
            $pdev = 3;
            $t42b = $t42b + $pdev;
            $out[] = $row + ['mark' => $pdev];
        }
        $shown = $pdev >= 5 ? 5 : $pdev;
        $sections['memberships'] = ['rows' => $out, 'total' => $shown];

        // 4 community development
        $tbdc = 0;
        $out = [];
        foreach ($rows('community_activities') as $row) {
            if ($row['worked_as'] == 'Blood Donation Camp organizer') {
                $bdc = 2;
            } elseif ($row['name'] == 'Blood Donation Camp') {
                $bdc = 1;
            } else {
                $bdc = 2;
            }
            $tbdc += $bdc;
            $out[] = $row + ['mark' => $bdc];
        }
        $sections['community_activities'] = ['rows' => $out, 'total' => null];

        $c1 = ($c11a + $c11b + $c12);
        $c2 = ($c3a + $c3b2 + $baut + $c3c2 + $c25 + $awrd + $c3d + $tstp);
        $c3 = ($c21 + $et);
        $c4 = $tdact + $t42b + $tbdc;

        if ($c1 > 25) {
            $c1 = 25;
        }
        if ($c2 > 20) {
            $c2 = 20;
        }
        if ($c3 > 10) {
            $c3 = 10;
        }
        if ($c4 > 10) {
            $c4 = 10;
        }

        return ['sections' => $sections, 'c1' => $c1, 'c2' => $c2, 'c3' => $c3, 'c4' => $c4, 'total' => $c1 + $c2 + $c3 + $c4];
    }
}
