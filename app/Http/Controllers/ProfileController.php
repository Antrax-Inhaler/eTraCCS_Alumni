<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EducationalBackground;
use App\Models\ProfessionalExam;
use App\Models\TrainingAttended;
use App\Models\EmploymentHistory;
use App\Models\AlumniLocation;
use App\Models\CompanyLocation;
use App\Models\EmploymentStatus;
use App\Models\Company;
use App\Models\Follow;
use App\Models\Reaction;
use App\Models\ContentItem;
use App\Models\JobHuntingDifficulty;
use App\Models\JobSource;
use App\Models\JobMaintenanceMethod;
use App\Models\Competency;
use Illuminate\Validation\Rule;

use App\Models\UserViewHistory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
class ProfileController extends Controller
{
    // Fetch all profile data for the authenticated user
    public function index($encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);
            $user = User::with([
                'educationalBackgrounds',
                'professionalExams',
                'trainingsAttended',
                'employmentHistory.company',
                'alumniLocation',
                'contentItems.mediaFiles',
                'followers',    // Add followers relationship
                'following'     // Add following relationship
            ])->findOrFail($id);
    
            // Separate current and past employment
            $currentEmployment = $user->employmentHistory->first(function ($job) {
                return $job->end_date === null;
            });
    
            $pastEmployment = $user->employmentHistory->filter(function ($job) {
                return $job->end_date !== null;
            })->values();
    
            $companyLocations = $this->getUsersByCompany();
            $photos = $user->contentItems->flatMap(function ($contentItem) {
                return $contentItem->mediaFiles->map(function ($mediaFile) use ($contentItem) {
                    return [
                        'id' => $mediaFile->id,
                        'url' => '/storage/' . $mediaFile->file_path, // Add /storage/ prefix here
                        'caption' => $contentItem->title ?? $contentItem->body,
                        'content_item_id' => $contentItem->id,
                        'created_at' => $mediaFile->created_at
                    ];
                });
            })->sortByDesc('created_at')->values();
            // Get counts
            $postCount = $user->contentItems->count();
            $followersCount = $user->followers->count();
            $followingCount = $user->following->count();
            $viewedEntities = UserViewHistory::where('user_id', auth()->id())
            ->get()
            ->groupBy('entity_type')
            ->map(function ($group) {
                return $group->pluck('entity_id')->toArray();
            });

        // Get user's posts (content items) with pagination
        $perPage = 10;
        $query = ContentItem::where('user_id', $user->id)
        ->with(['mediaFiles', 'reactions', 'comments.user', 'creator'])
        ->orderBy('created_at', 'desc');
    
    $posts = $query->paginate($perPage);

        // Transform the posts using your existing method
        $transformedPosts = $this->transformEntities($posts->getCollection(), auth()->user(), $viewedEntities);
        $posts->setCollection($transformedPosts);

        $mutualFriends = User::whereHas('followers', function ($query) use ($id) {
            $query->where('follower_id', $id);
        })
        ->whereHas('following', function ($query) use ($id) {
            $query->where('followed_id', $id);
        })
        ->with(['followers', 'following'])
        ->get()
        ->map(function ($friend) use ($user) {
            // Count mutual friends between this friend and the profile user
            $mutualCount = User::whereHas('followers', function ($query) use ($friend, $user) {
                    $query->where('follower_id', $friend->id);
                })
                ->whereHas('followers', function ($query) use ($user) {
                    $query->where('follower_id', $user->id);
                })
                ->count();
            
            return [
                'id' => $friend->id,
                'encrypted_id' => Crypt::encryptString($friend->id),
                'first_name' => $friend->first_name,
                'middle_name' => $friend->middle_initial,
                'last_name' => $friend->last_name,
                'avatar' => $friend->profile_photo_url,
                'online' => $friend->isOnline(),
                'mutual_count' => $mutualCount
            ];
        });
            return inertia('Profile/Index', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                       'cover_photo' => $user->cover_photo ? asset('storage/' . $user->cover_photo) : null,
                    'last_name' => $user->last_name,
                    'middle_initial' => $user->middle_initial,
                    'profile_photo_url' => $user->profile_photo_url,
                    'full_name' => trim("{$user->first_name} {$user->middle_initial} {$user->last_name}"),
                    'encrypted_id' => Crypt::encryptString($user->id),
                    'is_online' => $user->isOnline(),
                ],
                'is_following' => \App\Models\Follow::where('follower_id', auth()->id())
                    ->where('followed_id', $user->id)
                    ->exists(),
                'auth_user_id' => auth()->id(),
                'educationalBackgrounds' => $user->educationalBackgrounds,
                'professionalExams' => $user->professionalExams,
'trainingsAttended' => $user->trainingsAttended,
                'employmentHistory' => $user->employmentHistory->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'company' => $history->company ? [
                            'id' => $history->company->id,
                            'name' => $history->company->name,
                            'latitude' => $history->company->latitude,
                            'longitude' => $history->company->longitude,
                            'industry' => $history->company->industry,
                        ] : null,
                        'job_title' => $history->job_title,
                        'start_date' => $history->start_date,
                        'end_date' => $history->end_date,
                    ];
                }),
                'alumniLocation' => $user->alumniLocation,
                'companyLocations' => $companyLocations,
                'currentEmployment' => $currentEmployment ? [
                    'id' => $currentEmployment->id,
                    'company_name' => $currentEmployment->company ? $currentEmployment->company->name : null,
                    'job_title' => $currentEmployment->job_title,
                    'start_date' => $currentEmployment->start_date,
                    'end_date' => $currentEmployment->end_date,
                    'employment_type' => $currentEmployment->employment_type,
                    'industry' => $currentEmployment->company ? $currentEmployment->company->industry : null,
                ] : null,
                'pastEmployment' => $pastEmployment->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'company_name' => $job->company ? $job->company->name : null,
                        'job_title' => $job->job_title,
                        'start_date' => $job->start_date,
                        'end_date' => $job->end_date,
                        'employment_type' => $job->employment_type,
                        'industry' => $job->company ? $job->company->industry : null,
                    ];
                }),
                'posts' => [
                    'data' => $posts->items(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'next_page_url' => $posts->nextPageUrl(),
                ],
                'friends' => $mutualFriends,
                'photos' => $photos,
                'stats' => [ // Add stats array
                    'post_count' => $postCount,
                    'followers_count' => $followersCount,
                    'following_count' => $followingCount,
                    'friend_count' => $mutualFriends->count()

                ]
                
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Invalid or tampered data');
        }
    }
    private function transformEntities($collection, User $user, $viewedEntities)
    {
        return $collection->map(function ($contentItem) use ($user, $viewedEntities) {
            // Calculate reaction counts
            $contentItem->reaction_counts = $this->getReactionCounts($contentItem->id);
    
            // Get the user's current reaction (if any)
            $userReaction = Reaction::where('user_id', $user->id)
                ->where('content_item_id', $contentItem->id)
                ->first();
            $contentItem->user_reaction = $userReaction ? $userReaction->reaction_type : null;
    
            // Add score (ensure it defaults to 0 if not calculated)
            $contentItem->score = $this->calculateScore($contentItem, $user) ?? 0;
    
            // Check if the content item has been viewed by the user
            $contentItem->is_viewed = in_array(
                $contentItem->id,
                array_merge(
                    $viewedEntities['job_posting'] ?? [],
                    $viewedEntities['post'] ?? [],
                    $viewedEntities['event'] ?? []
                )
            );
    
            // Format creator details
            $this->formatUserData($contentItem->creator);
    
            // Format media files
            $contentItem->mediaFiles = $contentItem->mediaFiles->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_type' => $file->file_type,
                    'file_path' => '/storage/' . $file->file_path,
                ];
            });
    
            // Format comments
            $contentItem->comments = $this->formatComments($contentItem->comments);
            $contentItem->comment_count = $contentItem->comments->count();
            // Limit comments
            $allComments = $contentItem->comments;
            $contentItem->visible_comments = $allComments->take(1);
            $contentItem->has_more_comments = $allComments->count() > 1;
            $contentItem->all_comments = $allComments;
    
            return $contentItem;
        });
    }
    public function toggleFollow($id)
    {
        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);
    
        // Check if the current user is already following the target user
        $follow = Follow::where('follower_id', $currentUser->id)
            ->where('followed_id', $targetUser->id)
            ->first();
    
        if ($follow) {
            // Unfollow the user
            $follow->delete();
            return redirect()->back()->with([
                'is_following' => false,
                'message' => 'Unfollowed successfully'
            ]);
        } else {
            // Follow the user
            Follow::create([
                'follower_id' => $currentUser->id,
                'followed_id' => $targetUser->id,
            ]);
            return redirect()->back()->with([
                'is_following' => true,
                'message' => 'Followed successfully'
            ]);
        }
    }
    public function isFollowing($id)
{
    $currentUser = auth()->user();
    $isFollowing = Follow::where('follower_id', $currentUser->id)
        ->where('followed_id', $id)
        ->exists();

    return response()->json(['is_following' => $isFollowing], 200);
}
    public function getUsersByCompany()
{
    // Fetch all companies with their associated users via employment histories
    $companies = Company::with('employmentHistories.user')->get()->map(function ($company) {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'latitude' => $company->latitude,
            'longitude' => $company->longitude,
            'industry' => $company->industry,
            'users' => $company->employmentHistories->map(function ($history) {
                return [
                    'id' => $history->user->id,
                    'full_name' => trim("{$history->user->first_name} {$history->user->middle_initial} {$history->user->last_name}"),
                    'profile_photo_url' => $history->user->profile_photo_url,
                ];
            })->unique('id'), // Ensure unique users (in case a user has multiple entries for the same company)
        ];
    });

    return $companies;
}
private function getReactionCounts($contentItemId)
{
    return Reaction::where('content_item_id', $contentItemId)
        ->selectRaw('reaction_type, COUNT(*) as count')
        ->groupBy('reaction_type')
        ->pluck('count', 'reaction_type')
        ->toArray();
}
private function calculateScore($entity, $user)
{
    $daysSinceCreation = now()->diffInDays($entity->created_at);
    $recencyScore = exp(-$daysSinceCreation / 7);

    $engagementScore = ($entity->reaction_counts['total'] ?? 0) + count($entity->comments);

    // Handle null alumniLocation
    $locationMatchScore = 0;
    if ($user->alumniLocation) {
        $locationMatchScore =
            ($entity->location === $user->alumniLocation->city ? 20 : 0) +
            ($entity->city === $user->alumniLocation->city ? 10 : 0);
    }

    $educationMatchScore = 0;
    if ($user->educationalBackground) {
        $educationMatchScore =
            ($entity->creator->institution === $user->educationalBackground->institution ? 15 : 0) +
            ($entity->creator->degree_earned === $user->educationalBackground->degree_earned ? 10 : 0);
    }

    $employmentMatchScore = 0;
    if ($user->employmentHistory && $user->employmentHistory->isNotEmpty()) {
        $latestEmployment = $user->employmentHistory->first();
        if ($latestEmployment) {
            $employmentMatchScore =
                ($entity->industry === ($latestEmployment->industry ?? 'Unknown') ? 15 : 0) +
                ($entity->company_name === ($latestEmployment->company_name ?? 'Unknown') ? 10 : 0);
        }
    }

    $popularityScore = $entity->creator->followers_count ?? 0;

    // NEW: Following boost (if viewer follows the creator)
    $followingBoostScore = \App\Models\Follow::where('follower_id', $user->id)
        ->where('followed_id', $entity->creator->id)
        ->exists() ? 5 : 0;

    // Final score calculation with weights
    $score = (
        $recencyScore * 0.3 +
        $engagementScore * 0.3 +
        $locationMatchScore * 0.2 +
        $educationMatchScore * 0.1 +
        $employmentMatchScore * 0.1 +
        $popularityScore * 0.1 +
        $followingBoostScore * .1 // Just additive; adjust if needed
    );

    return max($score, 0);
}
private function formatUserData($user): void
{
    $user->full_name = $this->getFullName($user);
    $user->profile_picture_url = $user->profile_photo_url;
    $user->is_active = $this->isActive($user);

    // Add an encrypted version of the user's ID
    $user->encrypted_id = Crypt::encrypt($user->id);
}
/**
 * Format comments with user data
 */
private function formatComments($comments)
{
    return $comments->map(function ($comment) {
        $this->formatUserData($comment->user);
        return $comment;
    });
}

public function getCompanySuggestions(Request $request)
{
    $query = $request->input('query');
    
    $companies = Company::where('name', 'LIKE', "%{$query}%")
        ->limit(10)
        ->get(['id', 'name', 'latitude', 'longitude', 'industry']);
    
    return response()->json($companies);
}
    // Add or Update Educational Background
    public function storeEducationalBackground(Request $request)
    {
        $validated = $request->validate([
            'degree_earned' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'campus' => 'nullable|string|max:255',
            'year_graduated' => 'nullable|integer|min:1900|max:' . (now()->year + 5),
            'from_mindoro_state' => 'boolean',
            'is_primary' => 'boolean',
            'currently_studying' => 'boolean',
            'is_graduate_study' => 'boolean',
            'reason_for_study' => 'nullable|in:' . implode(',', array_keys(EducationalBackground::REASONS)),
            'other_reason' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255'
        ]);
    
        // Handle currently studying logic
        if ($validated['currently_studying'] ?? false) {
            $validated['year_graduated'] = null;
        }
    
        // Handle graduate study logic
        if (!($validated['is_graduate_study'] ?? false)) {
            $validated['reason_for_study'] = null;
            $validated['other_reason'] = null;
        }
    
        // Ensure only Mindoro State degrees can be primary
        if (($validated['is_primary'] ?? false) && !($validated['from_mindoro_state'] ?? false)) {
            return redirect()->back()
                ->with('error', 'Only Mindoro State University degrees can be marked as primary')
                ->withInput();
        }
    
        // Directly attach to the currently authenticated user
        auth()->user()->educationalBackgrounds()->create($validated);
    
        return redirect()->back()->with('success', 'Educational background added!');
    }
    
    public function updateEducationalBackground(Request $request, EducationalBackground $background)
    {
        // Ensure the authenticated user owns the educational background
        if ($background->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }
    
        $validated = $request->validate([
            'degree_earned' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'campus' => 'nullable|string|max:255',
            'year_graduated' => 'nullable|integer|min:1900|max:' . (now()->year + 5),
            'from_mindoro_state' => 'boolean',
            'is_primary' => 'boolean',
            'currently_studying' => 'boolean',
            'is_graduate_study' => 'boolean',
            'reason_for_study' => 'nullable|in:' . implode(',', array_keys(EducationalBackground::REASONS)),
            'other_reason' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255'
        ]);
    
        // Handle currently studying logic
        if ($validated['currently_studying'] ?? false) {
            $validated['year_graduated'] = null;
        }
    
        // Handle graduate study logic
        if (!($validated['is_graduate_study'] ?? false)) {
            $validated['reason_for_study'] = null;
            $validated['other_reason'] = null;
        }
    
        // Ensure only Mindoro State degrees can be primary
        if (($validated['is_primary'] ?? false) && !($validated['from_mindoro_state'] ?? false)) {
            return redirect()->back()
                ->with('error', 'Only Mindoro State University degrees can be marked as primary')
                ->withInput();
        }
    
        // Update the background
        $background->update($validated);
    
        return redirect()->back()->with('success', 'Educational background updated!');
    }
    
    private function getFullName($user): string
    {
        return trim(implode(' ', [
            $user->first_name,
            $user->middle_initial,
            $user->last_name
        ]));
    }
    
    // Helper function to check if user is active
    private function isActive($user): bool
    {
        return $user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(5));
    }

    public function destroyEducationalBackground(EducationalBackground $background)
    {
        // Ensure the authenticated user owns the educational background
        if ($background->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }
    
        // Delete the educational background
        $background->delete();
    
        return redirect()->back()->with('success', 'Educational background deleted!');
    }
    
    public function educationalBackgroundIndex($encryptedId)
    {
        try {
            // Decrypt the user ID
            $id = Crypt::decryptString($encryptedId);
    
            // Fetch the user
            $user = User::findOrFail($id);
    
            return inertia('Profile/EducationalBackground', [
                'educationalBackgrounds' => $user->educationalBackgrounds,
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Invalid or tampered data');
        }
    }

public function createEducationalBackground()
{
    return inertia('Profile/EducationalBackgroundForm'); // Form page for adding a new entry
}

public function editEducationalBackground(EducationalBackground $background)
{
    return inertia('Profile/EducationalBackgroundForm', [
        'background' => $background,
    ]);
}

    
public function storeEmploymentHistory(Request $request)
{
    $validated = $request->validate([
        'company_name' => 'required|string|max:255',
        'job_title' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'employment_type' => 'required|in:full-time,part-time,contract,internship,freelance',
        'industry' => 'nullable|string|max:255',
        'is_first_job' => 'boolean',
        'months_to_first_job' => 'nullable|integer',
        'current_salary' => 'nullable|in:below_10k,10k-20k,20k-30k,30k-50k,above_50k',
        'job_source' => 'nullable|in:online_portals,walk_in,referral,university_fair,social_media,government,other',
        'other_source' => 'nullable|string|max:100',
        'difficulties' => 'nullable|array',
        'difficulties.*' => 'string|in:lack_of_opportunities,high_competition,qualification_mismatch,lack_of_experience,personal_issues,other',
        'other_difficulty' => 'nullable|string|max:255',
        'curriculum_relevance' => 'nullable|integer|min:1|max:4',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'country' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:255',
        'region' => 'nullable|string|max:255',
        'barangay' => 'nullable|string|max:255',
        // Convert arrays to comma-separated strings
        'job_maintenance' => 'nullable|string',
        'competencies' => 'nullable|string',
        'nature_of_industry' => 'required|string|max:255'
    ]);

    // Find or create company
    $company = Company::updateOrCreate(
        ['name' => $validated['company_name']],
        [
            'industry' => $validated['industry'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'province' => $validated['province'] ?? null,
            'region' => $validated['region'] ?? null,
            'barangay' => $validated['barangay'] ?? null
        ]
    );

    // Prepare employment data
    $employmentData = [
        'company_id' => $company->id,
        'job_title' => $validated['job_title'],
        'nature_of_industry' => $validated['nature_of_industry'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'] ?? null,
        'employment_type' => $validated['employment_type'],
        'industry' => $validated['industry'] ?? null,
        'is_first_job' => $validated['is_first_job'] ?? false,
        'months_to_first_job' => $validated['months_to_first_job'] ?? null,
        'current_salary' => $validated['current_salary'] ?? null,
        'job_source' => $validated['job_source'] ?? null,
        'other_source' => $validated['other_source'] ?? null,
        'difficulties' => isset($validated['difficulties']) ? implode(',', $validated['difficulties']) : null,
        'other_difficulty' => $validated['other_difficulty'] ?? null,
        'curriculum_relevance' => $validated['curriculum_relevance'] ?? false,
        // Convert arrays to comma-separated strings
        'job_maintenance' => is_array($request->job_maintenance) ? implode(',', $request->job_maintenance) : $request->job_maintenance,
        'competencies' => is_array($request->competencies) ? implode(',', $request->competencies) : $request->competencies
    ];

    // Create employment history
    auth()->user()->employmentHistories()->create($employmentData);

    return redirect()->back()->with('success', 'Employment history saved!');
}
public function updateEmploymentHistory(Request $request, EmploymentHistory $history)
{
    $validated = $request->validate([
        'company_name' => 'required|string|max:255',
        'job_title' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'industry' => 'nullable|string|max:255',
        'is_first_job' => 'boolean',
        'months_to_first_job' => 'nullable|in:less_than_1,1_to_3,4_to_6,7_to_12,more_than_1,never',
        'first_job_salary' => 'nullable|in:below_10k,10k-15k,15k-20k,20k-30k,above_30k',
        'job_source' => 'nullable|in:online_portals,walk_in,referral,university_fair,social_media,government,other',
        'other_source' => 'nullable|string|max:255',
        'difficulties' => 'nullable|array',
        'difficulties.*' => 'string|in:lack_of_opportunities,high_competition,qualification_mismatch,lack_of_experience,personal_issues,other',
        'other_difficulty' => 'nullable|string|max:255',
        'employment_status' => 'nullable|in:permanent,contractual,project-based,probationary,part-time,freelance,other',
        'employer_type' => 'nullable|in:government,private,self-employed,ngo,other',
        'current_salary' => [
            'nullable',
            Rule::in(['below_10k', '10k-15k', '15k-20k', '20k-30k', 'above_30k', '10k-20k', '20k-30k', '30k-50k', 'above_50k'])
        ],        'job_maintenance' => 'nullable|array',
        'job_maintenance.*' => 'string|in:skills_from_college,on_the_job_training,certifications,self_learning,graduate_studies,company_training',
        'has_promotion' => 'boolean',
        'has_awards' => 'boolean',
        'awards_details' => 'nullable|string|max:500',
        'unemployment_reason' => 'nullable|in:seeking,studying,family,health,not_interested,other',
        'other_unemployment_reason' => 'nullable|string|max:255',
        'competencies' => 'nullable|array',
        'competencies.*' => 'string|in:programming,networking,database,project_management,communication,problem_solving,research,multimedia,other',
        'curriculum_relevance' => 'nullable|in:very_relevant,relevant,somewhat_relevant,not_relevant',
        'program_suggestions' => 'nullable|array',
        'program_suggestions.*' => 'string|in:industry_aligned,hands_on,updated_tools,stronger_internship,cert_prep,career_services,other',
        'other_suggestion' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:255',
        'region' => 'nullable|string|max:255',
        'barangay' => 'nullable|string|max:255'
    ]);

    // Find or create company
    $company = Company::updateOrCreate(
        ['name' => $validated['company_name']],
        [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'industry' => $validated['industry'],
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'province' => $validated['province'] ?? null,
            'region' => $validated['region'] ?? null,
            'barangay' => $validated['barangay'] ?? null
        ]
    );

    // Prepare update data
    $updateData = [
        'company_id' => $company->id,
        'job_title' => $validated['job_title'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'is_first_job' => $validated['is_first_job'] ?? false,
        'months_to_first_job' => $validated['months_to_first_job'] ?? null,
        'first_job_salary' => $validated['first_job_salary'] ?? null,
        'job_source' => $validated['job_source'] ?? null,
        'other_source' => $validated['other_source'] ?? null,
        'difficulties' => $validated['difficulties'] ? implode(',', $validated['difficulties']) : null,
        'other_difficulty' => $validated['other_difficulty'] ?? null,
        'employment_status' => $validated['employment_status'] ?? null,
        'employer_type' => $validated['employer_type'] ?? null,
        'current_salary' => $validated['current_salary'] ?? null,
        'job_maintenance' => $validated['job_maintenance'] ? implode(',', $validated['job_maintenance']) : null,
        'has_promotion' => $validated['has_promotion'] ?? false,
        'has_awards' => $validated['has_awards'] ?? false,
        'awards_details' => $validated['awards_details'] ?? null,
        'unemployment_reason' => $validated['unemployment_reason'] ?? null,
        'other_unemployment_reason' => $validated['other_unemployment_reason'] ?? null,
        'competencies' => $validated['competencies'] ? implode(',', $validated['competencies']) : null,
        'curriculum_relevance' => $validated['curriculum_relevance'] ?? null,
        'program_suggestions' => $validated['program_suggestions'] ? implode(',', $validated['program_suggestions']) : null,
        'other_suggestion' => $validated['other_suggestion'] ?? null
    ];

    // Update employment history
    $history->update($updateData);

    return redirect()->back()->with('success', 'Employment history updated!');
}

    // Delete Employment History
    public function destroyEmploymentHistory(EmploymentHistory $history)
    {
        $history->delete();
        return redirect()->back()->with('success', 'Employment history deleted!');
    }

    public function employmentHistoryIndex()
    {
        try {
            // Get the authenticated user with related employment histories and companies
            $user = auth()->user()->load([
                'employmentHistories' => function($query) {
                    $query->with(['company' => function($q) {
                        $q->select('id', 'name', 'industry', 'latitude', 'longitude', 
                                  'country', 'city', 'province', 'region', 'barangay');
                    }]);
                }
            ]);
    
            // Separate current and past employment
            $currentEmployment = $user->employmentHistories->first(function ($job) {
                return $job->end_date === null; // Current job has no end date
            });
    
            $pastEmployment = $user->employmentHistories->filter(function ($job) {
                return $job->end_date !== null; // Past jobs have an end date
            })->values(); // Reset array keys
    
            // Get all companies with their users for the map
            $companies = Company::with(['employmentHistories.user' => function($query) {
                $query->select('id', 'first_name', 'middle_initial', 'last_name', 'profile_photo_path');
            }])->get();
    
            // Format company locations for the map
            $companyLocations = $companies->map(function($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'latitude' => $company->latitude,
                    'longitude' => $company->longitude,
                    'industry' => $company->industry,
                    'country' => $company->country,
                    'city' => $company->city,
                    'province' => $company->province,
                    'region' => $company->region,
                    'barangay' => $company->barangay,
                    'users' => $company->employmentHistories->map(function($history) {
                        return [
                            'id' => $history->user->id,
                            'full_name' => trim("{$history->user->first_name} {$history->user->middle_initial} {$history->user->last_name}"),
                            'profile_photo_url' => $history->user->profile_photo_url
                        ];
                    })->filter()
                ];
            });
    
            // Get all reference data for the form
            $jobHuntingDifficulties = JobHuntingDifficulty::all(['id', 'name']);
            $jobSources = JobSource::all(['id', 'name']);
            $jobMaintenanceMethods = JobMaintenanceMethod::all(['id', 'name']);
            $competencies = Competency::all(['id', 'name']);
    
            // Salary range options
            $salaryRanges = [
                'below_10k' => 'Below ₱10,000',
                '10k-15k' => '₱10,000–₱15,000',
                '15k-20k' => '₱15,001–₱20,000',
                '20k-30k' => '₱20,001–₱30,000',
                'above_30k' => 'Above ₱30,000',
                '10k-20k' => '₱10,000–₱20,000',
                '20k-30k' => '₱20,001–₱30,000',
                '30k-50k' => '₱30,001–₱50,000',
                'above_50k' => 'Above ₱50,000'
            ];
    
            // Employment status options
            $employmentStatuses = [
                'permanent' => 'Permanent',
                'contractual' => 'Contractual',
                'project-based' => 'Project-based',
                'probationary' => 'Probationary',
                'part-time' => 'Part-time',
                'freelance' => 'Freelance',
                'other' => 'Other'
            ];
    
            // Employer type options
            $employerTypes = [
                'government' => 'Government',
                'private' => 'Private',
                'self-employed' => 'Self-employed',
                'ngo' => 'Non-profit/NGO',
                'other' => 'Other'
            ];
    
            // Curriculum relevance options
            $curriculumRelevance = [
                'very_relevant' => 'Very relevant',
                'relevant' => 'Relevant',
                'somewhat_relevant' => 'Somewhat relevant',
                'not_relevant' => 'Not relevant at all'
            ];
    
            // Format employment histories for the frontend
            $formattedCurrentEmployment = $currentEmployment ? [
                'id' => $currentEmployment->id,
                'company_id' => $currentEmployment->company_id,
                'company_name' => $currentEmployment->company ? $currentEmployment->company->name : null,
                'job_title' => $currentEmployment->job_title,
                'start_date' => $currentEmployment->start_date,
                'end_date' => $currentEmployment->end_date,
                'employment_type' => $currentEmployment->employment_type,
                'industry' => $currentEmployment->company ? $currentEmployment->company->industry : null,
                'is_first_job' => $currentEmployment->is_first_job,
                'months_to_first_job' => $currentEmployment->months_to_first_job,
                'first_job_salary' => $currentEmployment->first_job_salary,
                'job_source' => $currentEmployment->job_source,
                'other_source' => $currentEmployment->other_source,
                'difficulties' => $currentEmployment->difficulties ? explode(',', $currentEmployment->difficulties) : [],
                'other_difficulty' => $currentEmployment->other_difficulty,
                'employment_status' => $currentEmployment->employment_status,
                'employer_type' => $currentEmployment->employer_type,
                'current_salary' => $currentEmployment->current_salary,
                'job_maintenance' => $currentEmployment->job_maintenance ? explode(',', $currentEmployment->job_maintenance) : [],
                'has_promotion' => $currentEmployment->has_promotion,
                'has_awards' => $currentEmployment->has_awards,
                'awards_details' => $currentEmployment->awards_details,
                'competencies' => $currentEmployment->competencies ? explode(',', $currentEmployment->competencies) : [],
                'curriculum_relevance' => $currentEmployment->curriculum_relevance,
                'program_suggestions' => $currentEmployment->program_suggestions ? explode(',', $currentEmployment->program_suggestions) : [],
                'other_suggestion' => $currentEmployment->other_suggestion,
                'company' => $currentEmployment->company
            ] : null;
    
            $formattedPastEmployment = $pastEmployment->map(function ($job) {
                return [
                    'id' => $job->id,
                    'company_id' => $job->company_id,
                    'company_name' => $job->company ? $job->company->name : null,
                    'job_title' => $job->job_title,
                    'start_date' => $job->start_date,
                    'end_date' => $job->end_date,
                    'employment_type' => $job->employment_type,
                    'industry' => $job->company ? $job->company->industry : null,
                    'is_first_job' => $job->is_first_job,
                    'months_to_first_job' => $job->months_to_first_job,
                    'first_job_salary' => $job->first_job_salary,
                    'job_source' => $job->job_source,
                    'other_source' => $job->other_source,
                    'difficulties' => $job->difficulties ? explode(',', $job->difficulties) : [],
                    'other_difficulty' => $job->other_difficulty,
                    'employment_status' => $job->employment_status,
                    'employer_type' => $job->employer_type,
                    'current_salary' => $job->current_salary,
                    'job_maintenance' => $job->job_maintenance ? explode(',', $job->job_maintenance) : [],
                    'has_promotion' => $job->has_promotion,
                    'has_awards' => $job->has_awards,
                    'awards_details' => $job->awards_details,
                    'unemployment_reason' => $job->unemployment_reason,
                    'other_unemployment_reason' => $job->other_unemployment_reason,
                    'competencies' => $job->competencies ? explode(',', $job->competencies) : [],
                    'curriculum_relevance' => $job->curriculum_relevance,
                    'program_suggestions' => $job->program_suggestions ? explode(',', $job->program_suggestions) : [],
                    'other_suggestion' => $job->other_suggestion,
                    'company' => $job->company
                ];
            });
    
            // Return all the data to the frontend
            return inertia('Profile/EmploymentHistories', [
                'currentEmployment' => $formattedCurrentEmployment,
                'pastEmployment' => $formattedPastEmployment,
                'companyLocations' => $companyLocations,
                'employmentHistories' => $user->employmentHistories,
                'companies' => $companies,
                'user' => $user->only(['id', 'first_name', 'middle_initial', 'last_name', 'profile_photo_url']),
                'jobHuntingDifficulties' => $jobHuntingDifficulties,
                'jobSources' => $jobSources,
                'salaryRanges' => $salaryRanges,
                'employmentStatuses' => $employmentStatuses,
                'employerTypes' => $employerTypes,
                'jobMaintenanceMethods' => $jobMaintenanceMethods,
                'competencies' => $competencies,
                'curriculumRelevance' => $curriculumRelevance,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Employment history error: ' . $e->getMessage());
            abort(500, 'An error occurred while fetching employment histories.');
        }
    }

    // Edit Form for Employment History
    public function editEmploymentHistory(EmploymentHistory $history)
    {
        return inertia('Profile/EmploymentHistoryForm', [
            'history' => $history,
        ]);
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'opt_out_feedback' => 'sometimes|boolean',
            // Include other profile fields as needed
        ]);

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    }
    public function storeEmploymentStatus(Request $request)
    {
        $request->validate([
            'current_status' => 'required|in:Employed,Unemployed,Self-Employed,Others',
            'first_job_duration' => 'nullable|string|max:50',
            'occupation_classification' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'years_in_company' => 'nullable|integer|min:0',
            'job_relevance_to_degree' => 'nullable|in:Yes,No',
        ]);

        auth()->user()->employmentStatus()->create($request->all());
        return redirect()->back()->with('success', 'Employment status added!');
    }

    // Update Employment Status
    public function updateEmploymentStatus(Request $request, EmploymentStatus $status)
    {
        $request->validate([
            'current_status' => 'required|in:Employed,Unemployed,Self-Employed,Others',
            'first_job_duration' => 'nullable|string|max:50',
            'occupation_classification' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'years_in_company' => 'nullable|integer|min:0',
            'job_relevance_to_degree' => 'nullable|in:Yes,No',
        ]);

        $status->update($request->all());
        return redirect()->back()->with('success', 'Employment status updated!');
    }

    // Delete Employment Status
    public function destroyEmploymentStatus(EmploymentStatus $status)
    {
        $status->delete();
        return redirect()->back()->with('success', 'Employment status deleted!');
    }

    // Navigate to Employment Status Management Page
    public function employmentStatusIndex()
    {
        return inertia('Profile/EmploymentStatus', [
            'employmentStatus' => auth()->user()->employmentStatus,
        ]);
    }

    // Edit Form for Employment Status
    public function editEmploymentStatus(EmploymentStatus $status)
    {
        return inertia('Profile/EmploymentStatusForm', [
            'status' => $status,
        ]);
    }
    public function storeProfessionalExam(Request $request)
    {
        $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'license_number' => 'nullable|string|max:255',
        ]);

        auth()->user()->professionalExams()->create($request->all());
        return redirect()->back()->with('success', 'Professional exam added!');
    }

    // Update Professional Exam
    public function updateProfessionalExam(Request $request, ProfessionalExam $exam)
    {
        $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'license_number' => 'nullable|string|max:255',
        ]);

        $exam->update($request->all());
        return redirect()->back()->with('success', 'Professional exam updated!');
    }

    // Delete Professional Exam
    public function destroyProfessionalExam(ProfessionalExam $exam)
    {
        $exam->delete();
        return redirect()->back()->with('success', 'Professional exam deleted!');
    }

    // Navigate to Professional Exams Management Page
    public function professionalExamIndex()
    {
        return inertia('Profile/ProfessionalExams', [
            'professionalExams' => auth()->user()->professionalExams,
        ]);
    }

    // Edit Form for Professional Exam
    public function editProfessionalExam(ProfessionalExam $exam)
    {
        return inertia('Profile/ProfessionalExamForm', [
            'exam' => $exam,
        ]);
    }
    public function storeTrainingAttended(Request $request)
    {
        $request->validate([
            'training_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'year_attended' => 'required|integer|min:1900|max:' . now()->year,
        ]);

        auth()->user()->trainingAttendeds()->create($request->all());
        return redirect()->back()->with('success', 'Training attended added!');
    }

    // Update Training Attended
    public function updateTrainingAttended(Request $request, TrainingAttended $training)
    {
        $request->validate([
            'training_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'year_attended' => 'required|integer|min:1900|max:' . now()->year,
        ]);

        $training->update($request->all());
        return redirect()->back()->with('success', 'Training attended updated!');
    }

    // Delete Training Attended
    public function destroyTrainingAttended(TrainingAttended $training)
    {
        $training->delete();
        return redirect()->back()->with('success', 'Training attended deleted!');
    }

    // Navigate to Training Attended Management Page
    public function trainingAttendedIndex()
    {
        return inertia('Profile/TrainingAttended', [
            'trainingAttendeds' => auth()->user()->trainingAttendeds,
        ]);
    }

    // Edit Form for Training Attended
    public function editTrainingAttended(TrainingAttended $training)
    {
        return inertia('Profile/TrainingAttendedForm', [
            'training' => $training,
        ]);
    }
    public function storeCompanyLocation(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'industry' => 'required|string|max:255',
        ]);

        CompanyLocation::create($request->all());
        return redirect()->back()->with('success', 'Company location added!');
    }

    // Update Company Location
    public function updateCompanyLocation(Request $request, CompanyLocation $location)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'industry' => 'required|string|max:255',
        ]);

        $location->update($request->all());
        return redirect()->back()->with('success', 'Company location updated!');
    }

    // Delete Company Location
    public function destroyCompanyLocation(CompanyLocation $location)
    {
        $location->delete();
        return redirect()->back()->with('success', 'Company location deleted!');
    }

    // Navigate to Company Locations Management Page
    public function companyLocationIndex()
    {
        return inertia('Profile/CompanyLocations', [
            'companyLocations' => CompanyLocation::all(),
        ]);
    }

    // Edit Form for Company Location
    public function editCompanyLocation(CompanyLocation $location)
    {
        return inertia('Profile/CompanyLocationForm', [
            'location' => $location,
        ]);
    }
    public function getCurrentUserData()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo_url' => $user->profile_photo_url
                ? asset('storage/' . $user->profile_photo_url)
                : null,
        ], 200);
    }
}