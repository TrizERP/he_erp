<?php

namespace App\Exports\Placement;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected string $title;
    protected array $headings;
    protected array $rows;

    public function __construct(string $title, array $headings, array $rows)
    {
        $this->title = $title;
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        // Excel sheet titles are limited to 31 characters and cannot contain these symbols.
        return mb_substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $this->title), 0, 31);
    }
}
