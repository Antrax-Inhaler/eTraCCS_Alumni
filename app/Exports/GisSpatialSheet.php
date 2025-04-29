<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GisSpatialSheet implements FromArray, WithTitle, WithHeadings
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
        if ($this->title === 'Alumni Heatmap') {
            return ['Latitude', 'Longitude', 'Country', 'City', 'Alumni Count'];
        }
        
        if ($this->title === 'Industry Clusters') {
            return ['Latitude', 'Longitude', 'Industry', 'Employee Count'];
        }
        
        return ['Origin Campus', 'Destination City', 'Country', 'Alumni Count'];
    }
    
    public function title(): string
    {
        return $this->title;
    }
}