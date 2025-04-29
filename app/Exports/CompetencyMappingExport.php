<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompetencyMappingExport implements WithMultipleSheets
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function sheets(): array
    {
        return [
            new CompetencyMappingSheet($this->data['skillsReport'], 'Skills Utilization'),
            new CompetencyMappingSheet($this->data['curriculumReport'], 'Curriculum Relevance')
        ];
    }
}