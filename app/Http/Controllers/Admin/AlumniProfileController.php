<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AlumniRecord;
use App\Models\Campus;
use App\Models\JobHuntingDifficulty;
use App\Models\JobMaintenanceMethod;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class AlumniProfileController extends Controller
{
public function show($encryptedId)
{
    try {
        $id = Crypt::decryptString($encryptedId);
        
        $user = User::with([
            'educationalBackgrounds.campus',
            'professionalExams',
            'trainingsAttended',
            'employmentHistory.company',
            'alumniLocation',
            'contentItems.mediaFiles',
            'followers',
            'following',
            'jobHuntingExperiences',
            'unemployedDetails'
        ])->findOrFail($id);

        // Get alumni record if exists
        $alumniRecord = AlumniRecord::where('student_number', $user->student_number)
            ->orWhere('email', $user->email)
            ->first();

        // Separate current and past employment
        $currentEmployment = $user->employmentHistory->filter(function ($job) {
            return $job->end_date === null || $job->end_date > now();
        })->first();

        $pastEmployment = $user->employmentHistory->filter(function ($job) {
            return $job->end_date !== null && $job->end_date <= now();
        })->values();

        // Get primary education
        $primaryEducation = $user->educationalBackgrounds->where('is_primary', 1)->first();

        // Get counts
        $postCount = $user->contentItems->count();
        $followersCount = $user->followers->count();
        $followingCount = $user->following->count();

        // Get all campuses for dropdowns
        $campuses = Campus::all()->map(function ($campus) {
            return [
                'id' => $campus->id,
                'name' => $campus->name,
                'location' => $campus->location
            ];
        });

        // Get job hunting difficulties and maintenance methods
        $jobDifficulties = JobHuntingDifficulty::all();
        $jobMaintenanceMethods = JobMaintenanceMethod::all();

        // Format employment history with more details
        $formattedEmploymentHistory = $user->employmentHistory->map(function ($history) {
            return [
                'id' => $history->id,
                'company' => $history->company ? [
                    'id' => $history->company->id,
                    'name' => $history->company->name,
                    'latitude' => $history->company->latitude,
                    'longitude' => $history->company->longitude,
                    'industry' => $history->company->industry,
                    'city' => $history->company->city,
                    'country' => $history->company->country,
                    'region' => $history->company->region,
                    'province' => $history->company->province,
                    'barangay' => $history->company->barangay,
                ] : null,
                'job_title' => $history->job_title,
                'start_date' => $history->start_date?->format('Y-m-d'),
                'end_date' => $history->end_date?->format('Y-m-d'),
                'employment_type' => $history->employment_type,
                'is_job_related_to_degree' => $history->is_job_related_to_degree,
                'is_first_job' => $history->is_first_job,
                'months_to_first_job' => $history->months_to_first_job,
                'current_salary' => $history->current_salary,
                'job_source' => $history->job_source,
                'other_source' => $history->other_source,
                'difficulties' => $history->difficulties ? explode(',', $history->difficulties) : [],
                'other_difficulty' => $history->other_difficulty,
                'competencies' => $history->competencies ? explode(',', $history->competencies) : [],
                'job_maintenance' => $history->job_maintenance ? explode(',', $history->job_maintenance) : [],
                'has_promotion' => $history->has_promotion,
                'has_awards' => $history->has_awards,
                'awards_details' => $history->awards_details,
                'curriculum_relevance' => $history->curriculum_relevance,
                'nature_of_industry' => $history->nature_of_industry
            ];
        });

        // Format educational backgrounds
        $formattedEducation = $user->educationalBackgrounds->map(function ($edu) {
            return [
                'id' => $edu->id,
                'degree_earned' => $edu->degree_earned,
                'degree_type' => $edu->degree_type,
                'institution' => $edu->institution,
                'campus' => $edu->campus ? [
                    'id' => $edu->campus->id,
                    'name' => $edu->campus->name,
                    'location' => $edu->campus->location
                ] : null,
                'college' => $edu->college,
                'year_graduated' => $edu->year_graduated,
                'currently_studying' => $edu->currently_studying,
                'is_primary' => $edu->is_primary,
                'is_graduate_study' => $edu->is_graduate_study,
                'from_mindoro_state' => $edu->from_mindoro_state,
                'reason_for_study' => $edu->reason_for_study,
                'other_reason' => $edu->other_reason
            ];
        });

        // Format job hunting experiences
        $formattedJobHunting = $user->jobHuntingExperiences->map(function ($exp) {
            return [
                'id' => $exp->id,
                'time_to_first_job' => $exp->time_to_first_job,
                'difficulties' => $exp->difficulties ? explode(',', str_replace(['"', '[', ']'], '', $exp->difficulties)) : [],
                'other_difficulty' => $exp->other_difficulty,
                'job_source' => $exp->job_source,
                'other_source' => $exp->other_source,
                'starting_salary' => $exp->starting_salary
            ];
        });

        // Connections (followers + following)
        $friends = $user->followers->merge($user->following)->unique('id');

        return Inertia::render('Admin/AlumniProfile', [
            'user' => array_merge(
                $user->toArray(),
                [
                    'profile_photo_url' => $user->profile_photo_url,
                    'cover_photo' => $user->cover_photo ? asset('storage/' . $user->cover_photo) : null,
                    'full_name' => trim("{$user->first_name} {$user->middle_initial} {$user->last_name}"),
                    'encrypted_id' => $encryptedId,
                    'is_online' => $user->isOnline(),
                    'alumni_record' => $alumniRecord,
                    'primary_education' => $primaryEducation,
                ]
            ),
            'educationalBackgrounds' => $formattedEducation,
            'professionalExams' => $user->professionalExams,
            'trainingsAttended' => $user->trainingsAttended,
            'employmentHistory' => $formattedEmploymentHistory,
            'currentEmployment' => $currentEmployment,
            'pastEmployment' => $pastEmployment,
            'friends' => $friends->map(function ($friend) {
                return [
                    'id' => $friend->id,
                    'first_name' => $friend->first_name,
                    'last_name' => $friend->last_name,
                    'middle_initial' => $friend->middle_initial,
                    'profile_photo_url' => $friend->profile_photo_url,
                    'current_employment' => $friend->employmentHistory
                        ->whereNull('end_date')
                        ->first(),
                ];
            }),
            'alumniLocation' => $user->alumniLocation,
            'jobHuntingExperiences' => $formattedJobHunting,
            'unemployedDetails' => $user->unemployedDetails,
            'posts' => $user->contentItems()
                ->with(['mediaFiles', 'reactions', 'comments.user', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(10),
            'stats' => [
                'post_count' => $postCount,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ],
            'dropdownData' => [
                'campuses' => $campuses,
                'jobDifficulties' => $jobDifficulties,
                'jobMaintenanceMethods' => $jobMaintenanceMethods,
                'employmentTypes' => [
                    'full-time', 'part-time', 'contract', 'internship', 'freelance'
                ],
                'salaryRanges' => [
                    'below_10k', '10k-20k', '20k-30k', '30k-50k', 'above_50k'
                ],
                'jobSources' => [
                    'online_portals', 'walk_in', 'referral', 
                    'university_fair', 'social_media', 'government', 'other'
                ],
                'studyReasons' => [
                    'career_advancement', 'promotion_requirement', 
                    'academic_interest', 'job_requirement', 'other'
                ]
            ],
            'mapApiKey' => config('services.google.maps_api_key'),
        ]);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        abort(404, 'Invalid or tampered data');
    }
}

}