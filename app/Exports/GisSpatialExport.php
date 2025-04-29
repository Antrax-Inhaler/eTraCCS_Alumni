<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GisSpatialExport implements WithMultipleSheets
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function sheets(): array
    {
        return [
            new GisSpatialSheet($this->data['alumniHeatmap'], 'Alumni Heatmap'),
            new GisSpatialSheet($this->data['industryClusters'], 'Industry Clusters'),
            new GisSpatialSheet($this->data['migrationPatterns'], 'Migration Patterns')
        ];
    }
}