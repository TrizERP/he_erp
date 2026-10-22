<?php

namespace Tests\Feature\Pbas;

use App\Services\Pbas\PbasEntries;
use Illuminate\Support\Facades\DB;

/**
 * Oracle test for the API Form. It runs the ORIGINAL pbas_facl.php (from the standalone app) against a fake
 * database that serves the same rows, runs the migrated screen on the same data, and compares the rendered
 * pages (HTML comments and whitespace aside). Skipped when the original source is not available:
 * set PBAS_ORIGINAL_DIR to the folder that holds pbas_facl.php.
 */
class PbasReportOracleTest extends PbasTestCase
{
    /** original table => [pbas entry slug] (same columns, minus faculty_id) */
    private const TABLES = [
        'lec_semi_tut_prac' => 'lectures', 'material_provided' => 'materials', 'paper_publication' => 'papers', 'exam' => 'exam-duties',
        'expert_lecture' => 'expert-lectures', 'book_author' => 'books', 'research_projects' => 'research-projects',
        'research_individual' => 'research-individuals', 'award' => 'awards', 'project_guide' => 'project-guides', 'sttp' => 'sttps',
        'admin' => 'admin-duties', 'event' => 'events', 'development_activities' => 'industrial-visits', 'membership' => 'memberships',
        'other' => 'community-activities',
    ];

    private string $originalDir;
    private string $work;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDir = rtrim((string) (getenv('PBAS_ORIGINAL_DIR') ?: 'C:/Users/Asus/Downloads/pbas/pbas'), '/\\');
        if (! is_file($this->originalDir.'/pbas_facl.php')) {
            $this->markTestSkipped('Original pbas_facl.php not found (set PBAS_ORIGINAL_DIR).');
        }
        $this->work = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pbas_oracle_'.uniqid();
        mkdir($this->work.DIRECTORY_SEPARATOR.'sessions', 0777, true);
        copy($this->originalDir.'/pbas_facl.php', $this->work.'/pbas_facl.php');
        file_put_contents($this->work.'/dbcontroller.php', <<<'PHP'
<?php
// fake of the original DBController: serves rows from a JSON file, keyed by table name
class DBController {
    private function rows($query) {
        $data = json_decode(file_get_contents(getenv('PBAS_ORACLE_DATA')), true);
        preg_match('/FROM `(\w+)`/i', $query, $m);
        return $data[$m[1]] ?? [];
    }
    function numRows($query) { return count($this->rows($query)); }
    function runQuery($query) { $r = $this->rows($query); return $r ?: null; }
}
PHP);
        file_put_contents($this->work.'/run.php', <<<'PHP'
<?php
session_id('oracle');
chdir(__DIR__);
require __DIR__.'/pbas_facl.php';
PHP);
        file_put_contents($this->work.'/sessions/sess_oracle', 'username|s:1:"1";');
    }

    protected function tearDown(): void
    {
        if (isset($this->work) && is_dir($this->work)) {
            foreach (glob($this->work.'/{,sessions/}*', GLOB_BRACE) as $f) {
                is_file($f) && unlink($f);
            }
            @rmdir($this->work.'/sessions');
            @rmdir($this->work);
        }
        parent::tearDown();
    }

    /** Run the original page; returns its HTML. */
    private function original(array $data): string
    {
        file_put_contents($this->work.'/data.json', json_encode($data));
        putenv('PBAS_ORACLE_DATA='.$this->work.DIRECTORY_SEPARATOR.'data.json');
        $cmd = sprintf(
            '%s -d session.save_path=%s -d session.use_cookies=0 -d session.use_strict_mode=0 -d display_errors=0 -d error_reporting=0 %s',
            escapeshellarg(PHP_BINARY), escapeshellarg($this->work.DIRECTORY_SEPARATOR.'sessions'), escapeshellarg($this->work.DIRECTORY_SEPARATOR.'run.php')
        );

        return (string) shell_exec($cmd);
    }

    /** Load the same rows into the PBAS tables and render the migrated page. */
    private function migrated(array $data): string
    {
        $uid = $this->makeUser('Lecturer', $this->deptCse);
        $p = $data['personal'][0];
        DB::table('pbas_profiles')->insert([
            'sub_institute_id' => self::INST, 'user_id' => $uid, 'facultyname' => $p['facultyname'], 'fathername' => $p['fathername'],
            'department_id' => $this->deptCse, 'dateofjoining' => $p['dateofjoining'], 'designation' => $p['designation'], 'payband' => $p['payband'],
            'promotiondate' => $p['promotiondate'], 'address' => $p['address'], 'email' => $p['email'], 'mobile' => $p['mobile'],
            'extraqualification' => $p['extraqualification'], 'lwp' => $p['lwp'],
        ]);
        foreach (self::TABLES as $original => $slug) {
            foreach ($data[$original] ?? [] as $row) {
                DB::table(PbasEntries::find($slug)['table'])->insert($row + ['sub_institute_id' => self::INST, 'user_id' => $uid]);
            }
        }

        return $this->as('Lecturer', $uid)->get(route('pbas.report.index'))->assertOk()->getContent();
    }

    /** HTML comments, whitespace and the whitespace between tags do not matter to the browser. */
    private function normalise(string $html): string
    {
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    private function assertSamePage(array $data): void
    {
        $original = $this->normalise($this->original($data));
        $this->assertStringContainsString('Annual Self-Assessment form', $original, 'the original page did not run');
        $this->assertSame($original, $this->normalise($this->migrated($data)));
    }

    // -- datasets -----------------------------------------------------------------------------------------

    private function personal(): array
    {
        return [[
            'username' => '1', 'facultyname' => 'ASHA PATEL', 'fathername' => 'Ramesh Patel', 'department' => 'Computer Engineering',
            'dateofjoining' => '2015-06-01', 'designation' => 'Assistant Professor', 'payband' => 'Band 15600 AGP 6000',
            'promotiondate' => '2020-07-01', 'address' => '12 Ring Road Surat 395007', 'email' => 'asha@example.com', 'mobile' => '9999900001',
            'extraqualification' => 'NA', 'lwp' => '00',
        ]];
    }

    /** One row for a table, using real option values (and sometimes values outside them), strings like mysqli returns. */
    private function row(string $original, int $seed, bool $stray = false): array
    {
        mt_srand($seed);
        $def = PbasEntries::find(self::TABLES[$original]);
        $row = [];
        foreach ($def['fields'] as $field) {
            $column = $field['column'] ?? $field['name'];
            if ($field['type'] === 'year') {
                $row[$column] = '2025-2026';
            } elseif (in_array($field['type'], ['select', 'radio'], true)) {
                $values = array_column($field['options'], 0);
                $row[$column] = $stray && mt_rand(0, 4) === 0 ? 'Something else' : $values[mt_rand(0, count($values) - 1)];
            } elseif (! empty($field['int'])) {
                $row[$column] = (string) mt_rand(0, 100);
            } elseif ($column === 'enrollment_year') {
                $row[$column] = (string) mt_rand(2010, 2023);
            } elseif (in_array($column, ['day_number', 'number', 'responsibilities', 'number_of_duties', 'co_author'], true)) {
                $row[$column] = (string) mt_rand(1, 6);
            } elseif ($column === 'amount') {
                $row[$column] = (string) mt_rand(1, 2);
            } else {
                $row[$column] = 'Text'.mt_rand(1, 999);
            }
        }
        if ($original === 'lec_semi_tut_prac') {
            $row['gtu_rank'] = (string) mt_rand(0, 20);
            $row['ssasit_rank'] = (string) mt_rand(0, 8);
            $row['percent_class_parc'] = (string) mt_rand(60, 100);
        }

        return $row;
    }

    private function dataset(int $seed, int $maxRows, bool $stray = true): array
    {
        mt_srand($seed);
        $data = ['personal' => $this->personal()];
        foreach (array_keys(self::TABLES) as $table) {
            $n = mt_rand(0, $maxRows);
            for ($i = 0; $i < $n; $i++) {
                $data[$table][] = $this->row($table, $seed * 100 + $i * 7 + strlen($table), $stray);
            }
        }

        return $data;
    }

    // -- tests --------------------------------------------------------------------------------------------

    public function test_page_with_no_activity_data(): void
    {
        $this->assertSamePage(['personal' => $this->personal()]);
    }

    public function test_every_table_with_exactly_one_row(): void
    {
        $data = ['personal' => $this->personal()];
        foreach (array_keys(self::TABLES) as $i => $table) {
            $data[$table] = [$this->row($table, 500 + $i)];
        }
        $this->assertSamePage($data);
    }

    /** @dataProvider seeds */
    public function test_random_data_renders_identically(int $seed): void
    {
        $this->assertSamePage($this->dataset($seed, 4));
    }

    public static function seeds(): array
    {
        return array_combine(array_map(fn ($s) => "seed {$s}", range(1, 25)), array_map(fn ($s) => [$s], range(1, 25)));
    }

    public function test_heavy_data_hits_every_cap(): void
    {
        $this->assertSamePage($this->dataset(777, 9, false));
    }
}
