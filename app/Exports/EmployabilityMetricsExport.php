<?php

namespace App\Exports;

use App\Models\EducationalBackground;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployabilityMetricsExport implements WithMultipleSheets
{
    protected $metrics;

    public function __construct(array $metrics)
    {
        $this->metrics = $metrics;
    }

    public function sheets(): array
    {
        return [
            new EmployabilityIndexSheet($this->metrics['indexReport']),
            new DepartmentPerformanceSheet($this->metrics['departmentPerformance']),
            new TrendAnalysisSheet($this->metrics['trendAnalysis'])
        ];
    }
}

class EmployabilityIndexSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect([[
            'Average Score' => $this->data->avg_score ?? 0,
            'Total Graduates' => $this->data->count ?? 0
        ]]);
    }

    public function headings(): array
    {
        return ['Average Employability Score', 'Total Graduates Assessed'];
    }

    public function title(): string
    {
        return 'Employability Index';
    }
}

class DepartmentPerformanceSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Degree Program' => $item->degree_earned,
                'Average Score' => round($item->avg_score, 2),
                'Graduate Count' => $item->count
            ];
        });
    }

    public function headings(): array
    {
        return ['Degree Program', 'Average Score', 'Graduate Count'];
    }

    public function title(): string
    {
        return 'Department Performance';
    }
}

class TrendAnalysisSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Graduation Year' => $item->year_graduated,
                'Average Score' => round($item->avg_score, 2),
                'Graduate Count' => $item->count
            ];
        });
    }

    public function headings(): array
    {
        return ['Graduation Year', 'Average Score', 'Graduate Count'];
    }

    public function title(): string
    {
        return 'Trend Analysis';
    }
}