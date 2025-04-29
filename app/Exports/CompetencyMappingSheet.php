<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CompetencyMappingSheet implements FromArray, WithTitle, WithHeadings
{
    protected $data;
    protected $title;
    
    public function __construct($data, $title)
    {
        $this->data = $data;
        $this->title = $title;
    }
    
    public function array(): array
    {
        return $this->data->toArray();
    }
    
    public function headings(): array
    {
        if ($this->title === 'Skills Utilization') {
            return ['Competencies Learned', 'Number of Graduates', 'Average Score'];
        }
        
        return ['Industry', 'Job Title', 'Required Competencies', 'Job Count', 'Average Score'];
    }
    
    public function title(): string
    {
        return $this->title;
    }
}