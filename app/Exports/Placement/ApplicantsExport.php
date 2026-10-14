<?php

namespace App\Exports\Placement;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplicantsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return ['Enrollment No', 'Student', 'Course', 'Role', 'CGPA', 'Backlogs', 'Status', 'Applied On', 'Override reason'];
    }

    public function collection(): Collection
    {
        return $this->rows;
    }
}
