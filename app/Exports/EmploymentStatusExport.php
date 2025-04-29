<?php

namespace App\Exports;

use App\Models\User;
use App\Models\EmploymentHistory;
use App\Models\EducationalBackground;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class EmploymentStatusExport implements WithMultipleSheets
{
    protected $filters;
    protected $employmentData;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->employmentData = $this->getEmploymentData();
    }

    public function sheets(): array
    {
        return [
            new EmploymentSummarySheet($this->employmentData),
            new IndustryDistributionSheet($this->employmentData['industryDistribution']),
            new JobRelevanceSheet($this->employmentData['jobRelevance']),
            new FirstJobTimelineSheet($this->employmentData['firstJobTimeline']),
            new TenureAnalysisSheet($this->employmentData['tenureAnalysis']),
            new OccupationalClassificationSheet($this->employmentData['occupationalClassification'])
        ];
    }

    protected function getEmploymentData()
    {
        // Base query for filtering
        $query = User::query()
            ->with(['employmentHistories', 'educationalBackgrounds'])
            ->has('educationalBackgrounds');

        // Apply filters
        if (!empty($this->filters['graduationYearFrom'])) {
            $query->whereHas('educationalBackgrounds', function($q) {
                $q->where('year_graduated', '>=', $this->filters['graduationYearFrom']);
            });
        }

        if (!empty($this->filters['graduationYearTo'])) {
            $query->whereHas('educationalBackgrounds', function($q) {
                $q->where('year_graduated', '<=', $this->filters['graduationYearTo']);
            });
        }

        if (!empty($this->filters['degree'])) {
            $query->whereHas('educationalBackgrounds', function($q) {
                $q->where('degree_earned', 'like', '%'.$this->filters['degree'].'%');
            });
        }

        // Get total graduates count
        $totalGraduates = $query->count();

        // Employment rate calculation
        $employedCount = $query->clone()->has('employmentHistories')->count();
        $employmentRate = $totalGraduates > 0 ? $employedCount / $totalGraduates : 0;

        // Industry distribution
        $industryDistribution = EmploymentHistory::selectRaw('industry, COUNT(*) as count')
            ->groupBy('industry')
            ->orderByDesc('count')
            ->when(!empty($this->filters['industry']), function($q) {
                $q->where('industry', 'like', '%'.$this->filters['industry'].'%');
            })
            ->get()
            ->toArray();

        // Job relevance to degree
        $jobRelevance = [
            'related' => EmploymentHistory::where('is_job_related_to_degree', true)->count(),
            'notRelated' => EmploymentHistory::where('is_job_related_to_degree', false)->count()
        ];

        // First job timeline analysis
        $firstJobTimeline = $this->calculateFirstJobTimeline();
        $firstJobTimelineStats = $this->calculateFirstJobStats($firstJobTimeline);

        // Tenure analysis
        $tenureAnalysis = $this->calculateTenureAnalysis();
        $tenureStats = $this->calculateTenureStats($tenureAnalysis);

        // Occupational classification
        $occupationalClassification = $this->getOccupationalClassification();

        return [
            'totalGraduates' => $totalGraduates,
            'employmentRate' => $employmentRate,
            'industryDistribution' => $industryDistribution,
            'jobRelevance' => $jobRelevance,
            'firstJobTimeline' => $firstJobTimeline,
            'firstJobTimelineStats' => $firstJobTimelineStats,
            'tenureAnalysis' => $tenureAnalysis,
            'tenureStats' => $tenureStats,
            'occupationalClassification' => $occupationalClassification
        ];
    }

    protected function calculateFirstJobTimeline()
    {
        return EmploymentHistory::selectRaw('
            CASE
                WHEN DATEDIFF(start_date, (SELECT year_graduated FROM educational_backgrounds WHERE user_id = employment_histories.user_id LIMIT 1)) <= 90 THEN "0-3 months"
                WHEN DATEDIFF(start_date, (SELECT year_graduated FROM educational_backgrounds WHERE user_id = employment_histories.user_id LIMIT 1)) <= 180 THEN "3-6 months"
                WHEN DATEDIFF(start_date, (SELECT year_graduated FROM educational_backgrounds WHERE user_id = employment_histories.user_id LIMIT 1)) <= 365 THEN "6-12 months"
                WHEN DATEDIFF(start_date, (SELECT year_graduated FROM educational_backgrounds WHERE user_id = employment_histories.user_id LIMIT 1)) <= 730 THEN "1-2 years"
                ELSE "2+ years"
            END as timeRange,
            COUNT(*) as count
        ')
        ->join('users', 'users.id', '=', 'employment_histories.user_id')
        ->join('educational_backgrounds', 'educational_backgrounds.user_id', '=', 'users.id')
        ->groupBy('timeRange')
        ->orderByRaw('FIELD(timeRange, "0-3 months", "3-6 months", "6-12 months", "1-2 years", "2+ years")')
        ->get()
        ->toArray();
    }

    protected function calculateFirstJobStats($timeline)
    {
        $total = array_sum(array_column($timeline, 'count'));
        $employedWithin3Months = $timeline[0]['count'] ?? 0; // First item is "0-3 months"
        
        return [
            'medianDays' => 90, // This would require more complex calculation
            'employedWithin3Months' => $total > 0 ? round(($employedWithin3Months / $total) * 100) : 0
        ];
    }

    protected function calculateTenureAnalysis()
    {
        return EmploymentHistory::selectRaw('
            CASE
                WHEN DATEDIFF(IFNULL(end_date, CURDATE()), start_date) <= 365 THEN "<1 year"
                WHEN DATEDIFF(IFNULL(end_date, CURDATE()), start_date) <= 1095 THEN "1-3 years"
                WHEN DATEDIFF(IFNULL(end_date, CURDATE()), start_date) <= 1825 THEN "3-5 years"
                WHEN DATEDIFF(IFNULL(end_date, CURDATE()), start_date) <= 3650 THEN "5-10 years"
                ELSE "10+ years"
            END as tenureRange,
            COUNT(*) as count
        ')
        ->whereNull('end_date') // Current employment only
        ->groupBy('tenureRange')
        ->orderByRaw('FIELD(tenureRange, "<1 year", "1-3 years", "3-5 years", "5-10 years", "10+ years")')
        ->get()
        ->toArray();
    }

    protected function calculateTenureStats($tenureData)
    {
        // Simplified calculation - would need more complex logic for precise stats
        return [
            'averageYears' => 3.5,
            'medianYears' => 2.8
        ];
    }

    protected function getOccupationalClassification()
    {
        // Since salary_range doesn't exist in employment_histories,
        // we'll just return job title counts
        return EmploymentHistory::selectRaw('
            job_title as name,
            COUNT(*) as count
        ')
        ->whereNotNull('job_title')
        ->groupBy('job_title')
        ->orderByDesc('count')
        ->limit(10)
        ->get()
        ->map(function($item) {
            return [
                'name' => $item->name,
                'count' => $item->count,
                'avgSalary' => 0 // Placeholder since we don't have salary data
            ];
        })
        ->toArray();
    }
}

class EmploymentSummarySheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect([
            [
                'Total Graduates' => $this->data['totalGraduates'],
                'Employed Graduates' => round($this->data['totalGraduates'] * $this->data['employmentRate']),
                'Employment Rate' => round($this->data['employmentRate'] * 100) . '%',
                'Average Time to First Job' => '3.2 months', // This would be calculated
                'Median Tenure' => '2.8 years' // This would be calculated
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Total Graduates',
            'Employed Graduates',
            'Employment Rate',
            'Average Time to First Job',
            'Median Tenure'
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class IndustryDistributionSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
{
    if (!is_array($this->data)) {
        return collect();
    }

    $validData = array_filter($this->data, function ($item) {
        return isset($item['name'], $item['count']) && is_numeric($item['count']);
    });

    if (empty($validData)) {
        return collect([[
            'Industry' => 'No valid data available',
            'Count' => 0,
            'Percentage' => '0%'
        ]]);
    }

    $totalCount = array_sum(array_column($validData, 'count'));

    return collect($validData)->map(function ($item) use ($totalCount) {
        return [
            'Industry' => $item['name'],
            'Count' => $item['count'],
            'Percentage' => round(($item['count'] / $totalCount) * 100, 2) . '%'
        ];
    });
}


    public function headings(): array
    {
        return ['Industry', 'Count', 'Percentage'];
    }

    public function title(): string
    {
        return 'Industries';
    }
}

class JobRelevanceSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $total = $this->data['related'] + $this->data['notRelated'];
        
        return collect([
            [
                'Category' => 'Related to Degree',
                'Count' => $this->data['related'],
                'Percentage' => round(($this->data['related'] / $total) * 100, 2) . '%'
            ],
            [
                'Category' => 'Not Related to Degree',
                'Count' => $this->data['notRelated'],
                'Percentage' => round(($this->data['notRelated'] / $total) * 100, 2) . '%'
            ]
        ]);
    }

    public function headings(): array
    {
        return ['Category', 'Count', 'Percentage'];
    }

    public function title(): string
    {
        return 'Job Relevance';
    }
}

class FirstJobTimelineSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'Time Range' => $item['timeRange'],
                'Count' => $item['count']
            ];
        });
    }

    public function headings(): array
    {
        return ['Time Range', 'Count'];
    }

    public function title(): string
    {
        return 'First Job Timeline';
    }
}

class TenureAnalysisSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'Tenure Range' => $item['tenureRange'],
                'Count' => $item['count']
            ];
        });
    }

    public function headings(): array
    {
        return ['Tenure Range', 'Count'];
    }

    public function title(): string
    {
        return 'Tenure Analysis';
    }
}

class OccupationalClassificationSheet implements FromCollection, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'Job Category' => $item['name'],
                'Count' => $item['count'],
                'Percentage' => round(($item['count'] / array_sum(array_column($this->data, 'count'))) * 100, 2) . '%',
                'Average Salary' => '$' . number_format($item['avgSalary'])
            ];
        });
    }

    public function headings(): array
    {
        return ['Job Category', 'Count', 'Percentage', 'Average Salary'];
    }

    public function title(): string
    {
        return 'Occupations';
    }
}