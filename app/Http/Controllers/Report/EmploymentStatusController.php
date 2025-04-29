<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\EducationalBackground;
use App\Models\EmploymentHistory;
use App\Models\User;
use App\Exports\EmploymentStatusExport;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmploymentStatusController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'graduationYearFrom',
            'graduationYearTo',
            'degree',
            'industry'
        ]);

        return Inertia::render('Reports/EmploymentStatus/Index', [
            'filters' => $filters,
            'employmentData' => $this->getEmploymentData($filters)
        ]);
    }

    protected function getEmploymentData(array $filters = [])
    {
        // Base query for employed graduates
        $employedQuery = User::has('employmentHistories')
            ->with(['employmentHistories', 'educationalBackgrounds'])
            ->when($filters['graduationYearFrom'] ?? null, function ($query, $year) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($year) {
                    $q->where('year_graduated', '>=', $year);
                });
            })
            ->when($filters['graduationYearTo'] ?? null, function ($query, $year) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($year) {
                    $q->where('year_graduated', '<=', $year);
                });
            })
            ->when($filters['degree'] ?? null, function ($query, $degree) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($degree) {
                    $q->where('degree_earned', 'like', '%'.$degree.'%');
                });
            });

        // Total graduates count (for employment rate calculation)
        $totalGraduates = User::has('educationalBackgrounds')
            ->when($filters['graduationYearFrom'] ?? null, function ($query, $year) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($year) {
                    $q->where('year_graduated', '>=', $year);
                });
            })
            ->when($filters['graduationYearTo'] ?? null, function ($query, $year) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($year) {
                    $q->where('year_graduated', '<=', $year);
                });
            })
            ->when($filters['degree'] ?? null, function ($query, $degree) {
                $query->whereHas('educationalBackgrounds', function ($q) use ($degree) {
                    $q->where('degree_earned', 'like', '%'.$degree.'%');
                });
            })
            ->count();

        // Current employment data
        $employedCount = $employedQuery->count();
        $employmentRate = $totalGraduates > 0 ? $employedCount / $totalGraduates : 0;

        // Industry distribution
        $industryDistribution = EmploymentHistory::selectRaw('industry, COUNT(*) as count')
            ->when($filters['industry'] ?? null, function ($query, $industry) {
                $query->where('industry', 'like', '%'.$industry.'%');
            })
            ->groupBy('industry')
            ->orderByDesc('count')
            ->get();

        // Job relevance to degree
        $jobRelevance = [
            'related' => EmploymentHistory::where('is_job_related_to_degree', true)->count(),
            'notRelated' => EmploymentHistory::where('is_job_related_to_degree', false)->count()
        ];

        // First job timeline analysis
        $firstJobTimeline = $this->getFirstJobTimeline($filters);
        $firstJobTimelineStats = $this->calculateFirstJobStats($firstJobTimeline);

        // Tenure analysis
        $tenureAnalysis = $this->getTenureAnalysis($filters);
        $tenureStats = $this->calculateTenureStats($tenureAnalysis);

        // Occupational classification
        $occupationalClassification = $this->getOccupationalClassification($filters);

        return [
            'employmentRate' => $employmentRate,
            'industryDistribution' => $industryDistribution,
            'jobRelevance' => $jobRelevance,
            'firstJobTimeline' => $firstJobTimeline,
            'firstJobTimelineStats' => $firstJobTimelineStats,
            'tenureAnalysis' => $tenureAnalysis,
            'tenureStats' => $tenureStats,
            'occupationalClassification' => $occupationalClassification,
            'totalGraduates' => $totalGraduates
        ];
    }

    protected function getFirstJobTimeline($filters)
    {
        // This would be replaced with actual query logic
        return [
            ['timeRange' => '0-3 months', 'count' => 120],
            ['timeRange' => '3-6 months', 'count' => 85],
            ['timeRange' => '6-12 months', 'count' => 45],
            ['timeRange' => '1-2 years', 'count' => 30],
            ['timeRange' => '2+ years', 'count' => 20]
        ];
    }

    protected function calculateFirstJobStats($timeline)
    {
        // This would be replaced with actual calculation logic
        return [
            'medianDays' => 90,
            'employedWithin3Months' => 60
        ];
    }

    protected function getTenureAnalysis($filters)
    {
        // This would be replaced with actual query logic
        return [
            ['tenureRange' => '<1 year', 'count' => 50],
            ['tenureRange' => '1-3 years', 'count' => 120],
            ['tenureRange' => '3-5 years', 'count' => 80],
            ['tenureRange' => '5-10 years', 'count' => 45],
            ['tenureRange' => '10+ years', 'count' => 25]
        ];
    }

    protected function calculateTenureStats($tenureData)
    {
        // This would be replaced with actual calculation logic
        return [
            'averageYears' => 3.5,
            'medianYears' => 2.8
        ];
    }

    protected function getOccupationalClassification($filters)
    {
        // This would be replaced with actual query logic
        return [
            ['name' => 'Information Technology', 'count' => 150, 'avgSalary' => 75000],
            ['name' => 'Business/Finance', 'count' => 120, 'avgSalary' => 68000],
            ['name' => 'Engineering', 'count' => 90, 'avgSalary' => 82000],
            ['name' => 'Healthcare', 'count' => 60, 'avgSalary' => 70000],
            ['name' => 'Education', 'count' => 45, 'avgSalary' => 55000],
            ['name' => 'Other', 'count' => 35, 'avgSalary' => 50000]
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'graduationYearFrom',
            'graduationYearTo',
            'degree',
            'industry'
        ]);

        if ($request->format === 'excel') {
            return Excel::download(new EmploymentStatusExport($filters), 'employment-status-report.xlsx');
        }
        
        $data = $this->getEmploymentData($filters);
        $pdf = Pdf::loadView('exports.employment-status', $data);
        return $pdf->download('employment-status-report.pdf');
    }
}