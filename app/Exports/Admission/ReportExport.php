<?php

namespace App\Exports\Admission;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(protected string $title, protected array $headings, protected array $rows)
    {
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
        return mb_substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $this->title), 0, 31);
    }
}
