<?php
// app/Exports/GraduateProfileExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GraduateProfileExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function collection()
    {
        return collect($this->data['graduates']->items());
    }
    
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Degree',
            'Year Graduated',
            'Campus',
            'Institution'
        ];
    }
    
    public function map($graduate): array
    {
        return [
            $graduate->user->first_name . ' ' . ($graduate->user->middle_initial ? $graduate->user->middle_initial . ' ' : '') . $graduate->user->last_name,
            $graduate->user->email,
            $graduate->degree_earned,
            $graduate->year_graduated,
            $graduate->campus,
            $graduate->institution
        ];
    }
}