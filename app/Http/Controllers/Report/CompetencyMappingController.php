<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\EmployabilityIndex;
use App\Models\EducationalBackground;
use App\Models\RequiredCompetency;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\CompetencyMappingExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CompetencyMappingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'industry',
            'competency_search'
        ]);

        return Inertia::render('Reports/CompetencyMapping/Index', [
            'filters' => $filters,
            'reports' => $this->getReportData($filters)
        ]);
    }

    protected function getReportData(array $filters = [])
    {
        // Skills Utilization Report
        $skillsReport = EmployabilityIndex::selectRaw('
            competencies_learned,
            COUNT(*) as count,
            AVG(employability_score) as avg_score
        ')
        ->join('users', 'users.id', '=', 'employability_index.user_id')
        ->join('educational_backgrounds', 'educational_backgrounds.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->when($filters['competency_search'] ?? null, function ($query, $search) {
            $query->where('competencies_learned', 'like', '%'.$search.'%');
        })
        ->groupBy('competencies_learned')
        ->orderByDesc('count')
        ->limit(50)
        ->get();

        // Curriculum Relevance Report
        $curriculumReport = RequiredCompetency::selectRaw('
        required_competencies.industry,
        required_competencies.job_title,
        required_competencies.required_competencies,
        COUNT(employment_histories.id) as job_count,
        AVG(employability_index.employability_score) as avg_score
    ')
    ->leftJoin('employment_histories', function($join) {
        $join->on('employment_histories.industry', '=', 'required_competencies.industry')
             ->on('employment_histories.job_title', '=', 'required_competencies.job_title');
    })
    ->leftJoin('employability_index', 'employability_index.user_id', '=', 'employment_histories.user_id')
    ->when($filters['industry'] ?? null, function ($query, $industry) {
        $query->where('required_competencies.industry', 'like', '%'.$industry.'%');
    })
    ->when($filters['competency_search'] ?? null, function ($query, $search) {
        $query->where('required_competencies.required_competencies', 'like', '%'.$search.'%');
    })
    ->groupBy('required_competencies.industry', 'required_competencies.job_title', 'required_competencies.required_competencies') // Add required_competencies here
    ->orderByDesc('job_count')
    ->get();

        return [
            'skillsReport' => $skillsReport,
            'curriculumReport' => $curriculumReport,
            'degreePrograms' => EducationalBackground::distinct()->pluck('degree_earned'),
            'industries' => RequiredCompetency::distinct()->pluck('industry')
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'industry',
            'competency_search'
        ]);
    
        $data = $this->getReportData($filters);
    
        // Determine the export format (default is Excel)
        $format = $request->input('format', 'excel'); // 'excel' or 'pdf'
    
        if ($format === 'pdf') {
            // Generate PDF
            $pdf = Pdf::loadView('exports.competency-mapping-pdf', [
                'skillsReport' => $data['skillsReport'],
                'curriculumReport' => $data['curriculumReport'],
                'degreePrograms' => $data['degreePrograms'],
                'industries' => $data['industries']
            ]);
    
            return $pdf->download('competency-mapping-report.pdf');
        }
    
        // Default to Excel export
        return Excel::download(
            new CompetencyMappingExport($data),
            'competency-mapping-report.xlsx'
        );
    }
}