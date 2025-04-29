<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmploymentHistory;
use App\Models\EducationalBackground;
use App\Models\AlumniLocation;
use App\Models\ContentItem;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Total counts
        $totalAlumni = User::count();
        $activeUsers = User::where('last_seen_at', '>', now()->subMonth())->count();
        $employedCount = EmploymentHistory::whereNull('end_date')->count();
        
        // Growth calculations
        $alumniGrowth = $this->calculateGrowth(
            User::where('created_at', '<', now()->subYear())->count(),
            $totalAlumni
        );
        
        $activeGrowth = $this->calculateGrowth(
            User::where('last_seen_at', '>', now()->subMonths(2))
                ->where('last_seen_at', '<', now()->subMonth())
                ->count(),
            $activeUsers
        );
        
        $employmentChange = $this->calculateGrowth(
            EmploymentHistory::whereNull('end_date')
                ->where('start_date', '<', now()->subYear())
                ->count(),
            $employedCount
        );
        
        $newRegistrations = User::where('created_at', '>', now()->subMonth())->count();
        $registrationGrowth = $this->calculateGrowth(
            User::where('created_at', '>', now()->subMonths(2))
                ->where('created_at', '<', now()->subMonth())
                ->count(),
            $newRegistrations
        );

        // Employment status breakdown
        $employmentStatus = [
            'Employed' => $employedCount,
            'Unemployed' => $totalAlumni - $employedCount,
            'Self-employed' => EmploymentHistory::whereNull('end_date')
                ->where('employment_type', 'freelance')
                ->count(),
            'Further studies' => EducationalBackground::where('year_graduated', '>', now()->subYear())
                ->count()
        ];

        // Additional degrees data
        $additionalDegrees = EducationalBackground::where('institution', '!=', 'MinSU')
            ->groupBy('degree_earned')
            ->selectRaw('degree_earned, count(*) as count')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Recent content items (events, posts, job postings)
        $recentContent = ContentItem::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'title' => $item->title,
                    'date' => $item->date,
                    'user' => $item->user->full_name,
                    'created_at' => $item->created_at->format('M d, Y')
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalAlumni' => $totalAlumni,
                'activeUsers' => $activeUsers,
                'employmentRate' => $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100, 1) : 0,
                'newRegistrations' => $newRegistrations,
                'alumniGrowth' => $alumniGrowth,
                'activeGrowth' => $activeGrowth,
                'employmentChange' => $employmentChange,
                'registrationGrowth' => $registrationGrowth,
                'totalEvents' => ContentItem::where('type', 'event')->count(),
                'totalJobs' => ContentItem::where('type', 'job_posting')->count()
            ],
            'recentAlumni' => User::with([
                    'educationalBackgrounds', 
                    'employmentHistories' => function($q) {
                        $q->whereNull('end_date')->latest();
                    },
                    'employmentHistories.company'
                ])
                ->latest()
                ->take(5)
                ->get()
                ->map(function($user) {
                    $education = $user->educationalBackgrounds->first();
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'photo' => $user->profile_photo_url,
                        'graduation_year' => $education ? $education->year_graduated : null,
                        'degree' => $education ? $education->degree_earned : null,
                        'current_job' => $user->employmentHistories->first() ? [
                            'title' => $user->employmentHistories->first()->job_title,
                            'company' => optional($user->employmentHistories->first()->company)->name
                        ] : null,
                        'is_active' => $user->last_seen_at && $user->last_seen_at > now()->subMonth()
                    ];
                }),
            'employmentData' => [
                'rates' => array_values($employmentStatus),
                'labels' => array_keys($employmentStatus)
            ],
            'educationData' => [
                'degrees' => $additionalDegrees->pluck('degree_earned'),
                'counts' => $additionalDegrees->pluck('count')
            ],
            'featuredAlumni' => User::with([
                    'educationalBackgrounds', 
                    'employmentHistories' => function($q) {
                        $q->whereNull('end_date')->latest();
                    },
                    'alumniLocation',
                    'employmentHistories.company'
                ])
                ->whereHas('employmentHistories')
                ->inRandomOrder()
                ->take(3)
                ->get()
                ->map(function($user) {
                    $education = $user->educationalBackgrounds->first();
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'photo' => $user->profile_photo_url,
                        'degree' => $education ? $education->degree_earned : null,
                        'graduation_year' => $education ? $education->year_graduated : null,
                        'location' => $user->alumniLocation ? [
                            'city' => $user->alumniLocation->city,
                            'country' => $user->alumniLocation->country
                        ] : null,
                        'content_items' => $user->contentItems()->count(),
                        'connections' => $user->followers()->count(),
                        'job_related' => optional($user->employmentHistories->first())->is_job_related_to_degree
                    ];
                }),
            'recentContent' => $recentContent
        ]);
    }

    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}