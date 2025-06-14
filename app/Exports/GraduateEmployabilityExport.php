<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Campus;

class GraduateEmployabilityExport implements WithMultipleSheets
{
    protected $graduates;
    protected $batchYear;
    protected $campus;

    public function __construct($graduates, $batchYear = null, $campus = null)
    {
        $this->graduates = $graduates;
        $this->batchYear = $batchYear;
        $this->campus = $campus;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Group graduates by year_graduated
        $groupedGraduates = $this->graduates->groupBy('year_graduated');
        
        foreach ($groupedGraduates as $year => $graduates) {
            $sheets[] = new GraduateEmployabilitySheet($year, $graduates, $this->campus);
        }
        
        return $sheets;
    }
}