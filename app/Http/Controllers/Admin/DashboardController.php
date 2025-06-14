<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmploymentHistory;
use App\Models\EducationalBackground;
use App\Models\AlumniLocation;
use App\Models\ContentItem;
use App\Models\JobHuntingExperience;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filters from request
        $genderFilter = $request->input('gender');
        $salaryFilter = $request->input('salary_range');

        // Base queries with filters
        $userQuery = User::query();
        $employmentQuery = EmploymentHistory::query();
        $jobHuntingQuery = JobHuntingExperience::query();

        if ($genderFilter) {
            $userQuery->where('gender', $genderFilter);
            $employmentQuery->whereHas('user', function($q) use ($genderFilter) {
                $q->where('gender', $genderFilter);
            });
            $jobHuntingQuery->whereHas('user', function($q) use ($genderFilter) {
                $q->where('gender', $genderFilter);
            });
        }

        if ($salaryFilter) {
            $jobHuntingQuery->where('starting_salary', $salaryFilter);
            $employmentQuery->where('current_salary', str_replace('_', '-', $salaryFilter));
        }

        // Core statistics with filters
        $totalAlumni = $userQuery->count();
        $activeUsers = $userQuery->clone()->where('last_seen_at', '>', now()->subMonth())->count();
        $newRegistrations = $userQuery->clone()->where('created_at', '>', now()->subMonth())->count();
        
        // Employment status calculations
        $employedCount = $employmentQuery->clone()->where(function($query) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>', now());
        })->count();
        
        $employmentRate = $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100, 1) : 0;

        // Growth calculations
        $alumniGrowth = $this->calculateGrowth(
            $userQuery->clone()->where('created_at', '<', now()->subYear())->count(),
            $totalAlumni
        );
        
        $activeGrowth = $this->calculateGrowth(
            $userQuery->clone()->where('last_seen_at', '>', now()->subMonths(2))
                ->where('last_seen_at', '<', now()->subMonth())
                ->count(),
            $activeUsers
        );
        
        $registrationGrowth = $this->calculateGrowth(
            $userQuery->clone()->where('created_at', '>', now()->subMonths(2))
                ->where('created_at', '<', now()->subMonth())
                ->count(),
            $newRegistrations
        );

        // Demographic data with filters
        $genderData = $this->getGenderDistribution();
        $graduationYearData = $this->getGraduationYearDistribution($genderFilter);
        $educationalProfileData = $this->getEducationalProfileData($genderFilter);
        $bsitReasonsData = $this->getBSITReasonsData($genderFilter);
        $graduateStudiesData = $this->getGraduateStudiesData($genderFilter);
        $jobHuntingData = $this->getJobHuntingData($genderFilter, $salaryFilter);
        $startingSalaryData = $this->getStartingSalaryData($genderFilter);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalAlumni' => $totalAlumni,
                'activeUsers' => $activeUsers,
                'employmentRate' => $employmentRate,
                'newRegistrations' => $newRegistrations,
                'alumniGrowth' => $alumniGrowth,
                'activeGrowth' => $activeGrowth,
                'registrationGrowth' => $registrationGrowth,
            ],
            'demographics' => [
                'gender' => $genderData,
                'graduationYears' => $graduationYearData,
                'educationalProfiles' => $educationalProfileData,
                'bsitReasons' => $bsitReasonsData,
                'graduateStudies' => $graduateStudiesData,
                'jobHunting' => $jobHuntingData,
                'startingSalaries' => $startingSalaryData,
            ],
            'filters' => [
                'gender' => $genderFilter,
                'salary_range' => $salaryFilter
            ]
        ]);
    }
    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getGenderDistribution()
    {
        $genderCounts = User::selectRaw('gender, count(*) as count')
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item['gender'] => $item['count']];
            });

        return [
            'labels' => ['Male', 'Female'],
            'data' => [
                $genderCounts->get('Male', 0),
                $genderCounts->get('Female', 0)
            ],
            'colors' => ['#3490dc', '#f66d9b']
        ];
    }

    private function getGraduationYearDistribution()
    {
        $years = EducationalBackground::where('is_primary', 1)
            ->whereNotNull('year_graduated')
            ->selectRaw('year_graduated, count(*) as count')
            ->groupBy('year_graduated')
            ->orderBy('year_graduated')
            ->get();

        return [
            'labels' => $years->pluck('year_graduated'),
            'data' => $years->pluck('count'),
            'color' => '#6574cd'
        ];
    }

    private function getEducationalProfileData()
    {
        $profiles = EducationalBackground::where('is_primary', 1)
            ->selectRaw('degree_earned, count(*) as count')
            ->groupBy('degree_earned')
            ->orderByDesc('count')
            ->get();

        return [
            'labels' => $profiles->pluck('degree_earned'),
            'data' => $profiles->pluck('count'),
            'colors' => ['#4dc0b5', '#f6993f', '#e3342f', '#9561e2']
        ];
    }

    private function getBSITReasonsData()
    {
        // This would need to be implemented based on how you store reasons for taking BSIT
        // For now, I'll provide a placeholder structure
        $reasons = [
            'Career opportunities' => 45,
            'Personal interest' => 30,
            'Family influence' => 15,
            'Other' => 10
        ];

        return [
            'labels' => array_keys($reasons),
            'data' => array_values($reasons),
            'colors' => ['#3490dc', '#f66d9b', '#6cb2eb', '#ffed4a']
        ];
    }

    private function getGraduateStudiesData()
    {
        $graduateStudies = EducationalBackground::where('is_graduate_study', 1)
            ->selectRaw('degree_earned, count(*) as count')
            ->groupBy('degree_earned')
            ->orderByDesc('count')
            ->get();

        $currentlyStudying = EducationalBackground::where('currently_studying', 1)
            ->where('is_graduate_study', 1)
            ->count();

        return [
            'degrees' => [
                'labels' => $graduateStudies->pluck('degree_earned'),
                'data' => $graduateStudies->pluck('count'),
                'color' => '#38c172'
            ],
            'currentlyStudying' => $currentlyStudying,
            'reasons' => $this->getGraduateStudyReasons()
        ];
    }

    private function getGraduateStudyReasons()
    {
        $reasons = EducationalBackground::whereNotNull('reason_for_study')
            ->selectRaw('reason_for_study, count(*) as count')
            ->groupBy('reason_for_study')
            ->orderByDesc('count')
            ->get();

        return [
            'labels' => $reasons->pluck('reason_for_study'),
            'data' => $reasons->pluck('count'),
            'colors' => ['#6cb2eb', '#ffed4a', '#4dc0b5', '#f6993f']
        ];
    }

    

     private function getStartingSalaryData($genderFilter = null)
    {
        $query = JobHuntingExperience::query();
        
        if ($genderFilter) {
            $query->whereHas('user', function($q) use ($genderFilter) {
                $q->where('gender', $genderFilter);
            });
        }

        $salaries = $query->selectRaw('starting_salary, count(*) as count')
            ->groupBy('starting_salary')
            ->orderByRaw("FIELD(starting_salary, 'below_10k', '10k_15k', '15k_20k', '20k_30k', 'above_30k')")
            ->get();

        return [
            'labels' => $salaries->pluck('starting_salary'),
            'data' => $salaries->pluck('count'),
            'color' => '#9561e2'
        ];
    }

    private function getJobHuntingData($genderFilter = null, $salaryFilter = null)
    {
        $query = JobHuntingExperience::query();
        
        if ($genderFilter) {
            $query->whereHas('user', function($q) use ($genderFilter) {
                $q->where('gender', $genderFilter);
            });
        }

        if ($salaryFilter) {
            $query->where('starting_salary', $salaryFilter);
        }

        $timeToJob = $query->clone()->selectRaw('time_to_first_job, count(*) as count')
            ->groupBy('time_to_first_job')
            ->get();

        $difficulties = $query->clone()->selectRaw('difficulties')
            ->get()
            ->flatMap(function($item) {
                return explode(',', str_replace(['"', '[', ']'], '', $item->difficulties));
            })
            ->countBy();

        return [
            'timeToJob' => [
                'labels' => $timeToJob->pluck('time_to_first_job'),
                'data' => $timeToJob->pluck('count'),
                'color' => '#f66d9b'
            ],
            'difficulties' => [
                'labels' => $difficulties->keys(),
                'data' => $difficulties->values(),
                'colors' => ['#e3342f', '#f6993f', '#ffed4a', '#4dc0b5']
            ]
        ];
    }
}