<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\EmployabilityIndex;
use App\Models\EducationalBackground;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\EmployabilityMetricsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployabilityMetricsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'campus'
        ]);

        return Inertia::render('Reports/EmployabilityMetrics/Index', [
            'filters' => $filters,
            'metrics' => $this->getMetricsData($filters)
        ]);
    }

    protected function getMetricsData(array $filters = [])
    {
        // Employability Index Report
        $indexReport = EmployabilityIndex::selectRaw('
            AVG(employability_score) as avg_score,
            COUNT(*) as count
        ')
        ->with('user.educationalBackgrounds')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->whereHas('user.educationalBackgrounds', function ($q) use ($year) {
                $q->where('year_graduated', '>=', $year);
            });
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->whereHas('user.educationalBackgrounds', function ($q) use ($year) {
                $q->where('year_graduated', '<=', $year);
            });
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->whereHas('user.educationalBackgrounds', function ($q) use ($degree) {
                $q->where('degree_earned', 'like', '%'.$degree.'%');
            });
        })
        ->when($filters['campus'] ?? null, function ($query, $campus) {
            $query->whereHas('user.educationalBackgrounds', function ($q) use ($campus) {
                $q->where('campus', 'like', '%'.$campus.'%');
            });
        })
        ->first();

        // Department Performance Report
        $departmentPerformance = EducationalBackground::selectRaw('
            degree_earned,
            AVG(employability_index.employability_score) as avg_score,
            COUNT(*) as count
        ')
        ->join('users', 'users.id', '=', 'educational_backgrounds.user_id')
        ->join('employability_index', 'employability_index.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->when($filters['campus'] ?? null, function ($query, $campus) {
            $query->where('campus', 'like', '%'.$campus.'%');
        })
        ->groupBy('degree_earned')
        ->orderByDesc('avg_score')
        ->get();

        // Trend Analysis
        $trendAnalysis = EducationalBackground::selectRaw('
            year_graduated,
            AVG(employability_index.employability_score) as avg_score,
            COUNT(*) as count
        ')
        ->join('users', 'users.id', '=', 'educational_backgrounds.user_id')
        ->join('employability_index', 'employability_index.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->when($filters['campus'] ?? null, function ($query, $campus) {
            $query->where('campus', 'like', '%'.$campus.'%');
        })
        ->groupBy('year_graduated')
        ->orderBy('year_graduated')
        ->get();

        return [
            'indexReport' => $indexReport,
            'departmentPerformance' => $departmentPerformance,
            'trendAnalysis' => $trendAnalysis
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'campus',
            'format'
        ]);
    
        $metrics = $this->getMetricsData($filters);
    
        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('exports.employability-metrics', [
                'metrics' => $metrics // Explicitly pass the metrics variable
            ]);
            return $pdf->download('employability-metrics-report.pdf');
        }
    
        return Excel::download(
            new EmployabilityMetricsExport($metrics),
            'employability-metrics-report.xlsx'
        );
    }
}