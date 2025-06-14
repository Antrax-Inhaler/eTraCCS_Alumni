<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GraduateEmployabilitySheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $year;
    protected $graduates;
    protected $campus;
    
    public function __construct($year, $graduates, $campus = null)
    {
        $this->year = $year;
        $this->graduates = $graduates;
        $this->campus = $campus;
    }
    
    public function collection()
    {
        return $this->graduates->map(function ($graduate, $index) {
            return [
                $index + 1,
                $graduate['year_graduated'] . '-' . (intval($graduate['year_graduated']) + 1),
                $graduate['first_name'],
                $graduate['middle_initial'],
                $graduate['last_name'],
                $graduate['gender'],
                $graduate['company'] ? $graduate['company']['name'] : ($graduate['institution'] ?? 'N/A'),
                $graduate['company'] ? ($graduate['company_city'] . ', ' . $graduate['company_country']) : 'N/A',
                $graduate['job_title'] ?? 'N/A',
                $graduate['first_job_year'] ?? 'N/A'
            ];
        });
    }
    
    public function title(): string
    {
        return 'AY ' . $this->year . '-' . (intval($this->year) + 1);
    }
    
    public function headings(): array
    {
        // Default header (when no campus is selected)
        $campusName = 'Main Campus';
        $campusAddress = 'Alcate, Victoria, Oriental Mindoro';
        
        if ($this->campus) {
            $campusName = $this->campus->campus_name;
            $campusAddress = $this->campus->campus_address;
        }

        return [
            ['Republic of the Philippines'],
            ['Mindoro State University'],
            [$campusName], // Dynamic campus name
            [$campusAddress], // Dynamic campus address
            ['EMPLOYABILITY OF GRADUATES'],
            ['A.Y. ' . $this->year . '-' . (intval($this->year) + 1)],
            [''],
            ['Bachelor of Science in Information Technology'],
            [''],
            [
                'No.',
                'Year Graduated',
                'First Name',
                'Middle Name',
                'Last Name',
                'Gender',
                'Company/Institution',
                'Address of Employment',
                'Position',
                'Year Employed'
            ]
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Merge header/title rows
        $sheet->mergeCells('A1:J1'); // Republic of the Philippines
        $sheet->mergeCells('A2:J2'); // Mindoro State University
        $sheet->mergeCells('A3:J3'); // Campus Name
        $sheet->mergeCells('A4:J4'); // Campus Address
        $sheet->mergeCells('A5:J5'); // EMPLOYABILITY OF GRADUATES
        $sheet->mergeCells('A6:J6'); // A.Y. 20xx-20xx
        $sheet->mergeCells('A7:J7'); // (blank row)
        $sheet->mergeCells('A8:J8'); // Bachelor of Science...
        $sheet->mergeCells('A9:J9'); // (blank row)

        // Center align all the header text
        $sheet->getStyle('A1:J9')->getAlignment()->setHorizontal('center');

        // Bold only specific rows
        $sheet->getStyle('A5')->getFont()->setBold(true); // EMPLOYABILITY OF GRADUATES
        $sheet->getStyle('A6')->getFont()->setBold(true); // A.Y. xxxx-xxxx
        $sheet->getStyle('A8')->getFont()->setBold(true); // Course
        $sheet->getStyle('A10:J10')->getFont()->setBold(true); // Table header

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);   // No.
        $sheet->getColumnDimension('B')->setWidth(15);  // Year Graduated
        $sheet->getColumnDimension('C')->setWidth(15);  // First Name
        $sheet->getColumnDimension('D')->setWidth(15);  // Middle Name
        $sheet->getColumnDimension('E')->setWidth(15);  // Last Name
        $sheet->getColumnDimension('F')->setWidth(10);  // Gender
        $sheet->getColumnDimension('G')->setWidth(25);  // Company/Institution
        $sheet->getColumnDimension('H')->setWidth(25);  // Address
        $sheet->getColumnDimension('I')->setWidth(20);  // Position
        $sheet->getColumnDimension('J')->setWidth(15);  // Year Employed

        // Freeze the table header row
        $sheet->freezePane('A11');
    }
}