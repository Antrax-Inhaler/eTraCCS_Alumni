<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdvancementExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $reportType;
    
    public function __construct($data, $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }
    
    public function collection()
    {
        switch ($this->reportType) {
            case 'degrees':
                return $this->data['advancedDegrees'];
            case 'studies':
                return $this->data['advancedStudies'];
            case 'certifications':
                return $this->data['certifications'];
            case 'training':
                return collect($this->data['trainingStats']);
            default:
                return collect();
        }
    }
    
    public function headings(): array
    {
        switch ($this->reportType) {
            case 'degrees':
                return ['Name', 'Additional Degree', 'Institution', 'Year Earned'];
            case 'studies':
                return ['Name', 'Advanced Degree', 'Institution', 'Year Earned'];
            case 'certifications':
                return ['Name', 'Certification', 'Institution', 'Year Obtained'];
            case 'training':
                return ['Training Name', 'Participants', 'First Offered', 'Last Offered'];
            default:
                return [];
        }
    }
    
    public function map($item): array
    {
        switch ($this->reportType) {
            case 'degrees':
            case 'studies':
                return [
                    $item->user->first_name . ' ' . $item->user->last_name,
                    $item->degree_earned,
                    $item->institution,
                    $item->year_graduated
                ];
            case 'certifications':
                return [
                    $item->user->first_name . ' ' . $item->user->last_name,
                    $item->training_name,
                    $item->institution,
                    $item->year_attended
                ];
            case 'training':
                return [
                    $item->training_name,
                    $item->participant_count,
                    $item->earliest_year,
                    $item->latest_year
                ];
            default:
                return [];
        }
    }
}