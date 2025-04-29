<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployabilityIndex;
use App\Models\EducationalBackground;
use App\Models\EmploymentHistory;
use App\Models\Competency;
use App\Models\RequiredCompetency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class EmployabilityController extends Controller
{
    public function index(Request $request)
    {
        // Get filters from request
        $batchYear = $request->input('batch_year');
        $industry = $request->input('industry');
        
        // Base query for employability data
        $query = User::with([
            'employabilityIndex',
            'employmentHistories',
            'competencies',
            'educationalBackgrounds' => function($query) {
                $query->where('is_primary', true);
            }
        ])
        ->where('is_verified', true)
        ->whereHas('educationalBackgrounds', function($query) use ($batchYear) {
            if ($batchYear) {
                $query->where('year_graduated', $batchYear);
            }
        });

        // Apply industry filter if provided
        if ($industry) {
            $query->whereHas('employmentHistories', function($query) use ($industry) {
                $query->where('industry', $industry);
            });
        }

        $alumni = $query->get();

        // Calculate aggregate metrics
        $aggregates = [
            'average_score' => round($alumni->avg('employabilityIndex.employability_score'), 2),
            'employment_rate' => round(($alumni->filter(fn($user) => $user->employmentHistories->count() > 0)->count() / max(1, $alumni->count())) * 100, 2),
'related_field_rate' => round(
    ($alumni->filter(fn($user) => $user->employmentHistories->where('is_job_related_to_degree', true)->count())->count() 
    / max(1, $alumni->count())) * 100, 
    2
),
        ];

        // Get batch years for filter
        $batchYears = EducationalBackground::distinct('year_graduated')
            ->orderBy('year_graduated', 'desc')
            ->pluck('year_graduated');

        // Get industries for filter
        $industries = EmploymentHistory::distinct('industry')
            ->whereNotNull('industry')
            ->orderBy('industry')
            ->pluck('industry');

        // Comparative analysis data
        $comparativeData = $this->getComparativeData($batchYear, $industry);

        // Skills gap analysis
        $skillsGap = $this->getSkillsGapAnalysis($industry);

        return Inertia::render('Admin/Employability', [
            'alumni' => $alumni,
            'aggregates' => $aggregates,
            'comparativeData' => $comparativeData,
            'skillsGap' => $skillsGap,
            'filters' => [
                'batch_year' => $batchYear,
                'industry' => $industry
            ],
            'batchYears' => $batchYears,
            'industries' => $industries
        ]);
    }

    protected function getComparativeData($currentBatch, $currentIndustry)
    {
        // Compare batches if a specific batch is selected
        $batchComparison = [];
        if ($currentBatch) {
            $batchComparison = DB::table('educational_backgrounds as edu')
                ->join('users', 'users.id', '=', 'edu.user_id')
                ->leftJoin('employability_index', 'employability_index.user_id', '=', 'users.id')
                ->leftJoin('employment_histories', 'employment_histories.user_id', '=', 'users.id')
                ->where('edu.is_primary', true)
                ->where('users.is_verified', true)
                ->groupBy('edu.year_graduated')
                ->select(
                    'edu.year_graduated as batch',
                    DB::raw('AVG(employability_index.employability_score) as avg_score'),
                    DB::raw('COUNT(DISTINCT CASE WHEN employment_histories.id IS NOT NULL THEN users.id END) as employed_count'),
                    DB::raw('COUNT(DISTINCT users.id) as total_count')
                )
                ->orderBy('edu.year_graduated')
                ->get()
                ->map(function($item) {
                    return [
                        'batch' => $item->batch,
                        'avg_score' => round($item->avg_score, 2),
                        'employment_rate' => round(($item->employed_count / max(1, $item->total_count)) * 100, 2)
                    ];
                });
        }

        // Compare industries if a specific industry is selected
        $industryComparison = [];
        if ($currentIndustry) {
            $industryComparison = DB::table('employment_histories as emp')
                ->join('users', 'users.id', '=', 'emp.user_id')
                ->leftJoin('employability_index', 'employability_index.user_id', '=', 'users.id')
                ->where('users.is_verified', true)
                ->whereNotNull('emp.industry')
                ->groupBy('emp.industry')
                ->select(
                    'emp.industry',
                    DB::raw('AVG(employability_index.employability_score) as avg_score'),
                    DB::raw('COUNT(DISTINCT CASE WHEN emp.is_job_related_to_degree = 1 THEN users.id END) as related_field_count'),
                    DB::raw('COUNT(DISTINCT users.id) as total_count')
                )
                ->orderBy('emp.industry')
                ->get()
                ->map(function($item) {
                    return [
                        'industry' => $item->industry,
                        'avg_score' => round($item->avg_score, 2),
                        'related_field_rate' => round(($item->related_field_count / max(1, $item->total_count)) * 100, 2)
                    ];
                });
        }

        return [
            'batchComparison' => $batchComparison,
            'industryComparison' => $industryComparison
        ];
    }

    protected function getSkillsGapAnalysis($industry)
    {
        if (!$industry) {
            return [];
        }

        // Get required competencies for this industry
        $required = RequiredCompetency::where('industry', $industry)
            ->pluck('required_competencies')
            ->flatten()
            ->countBy()
            ->sortDesc();

        // Get possessed competencies from alumni in this industry
        $possessed = Competency::whereHas('user.employmentHistories', function($query) use ($industry) {
                $query->where('industry', $industry);
            })
            ->select('competency_name', DB::raw('COUNT(*) as count'))
            ->groupBy('competency_name')
            ->orderByDesc('count')
            ->pluck('count', 'competency_name');

        // Calculate gap
        $gap = [];
        foreach ($required as $skill => $reqCount) {
            $posCount = $possessed->get($skill, 0);
            $gap[$skill] = [
                'required' => $reqCount,
                'possessed' => $posCount,
                'gap' => max(0, $reqCount - $posCount)
            ];
        }

        return $gap;
    }
}