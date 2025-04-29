<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\Post;
use App\Models\Event;
use App\Models\Feed;
use App\Models\MediaFile;
use App\Models\Reaction;
use App\Models\UserViewHistory;
use App\Models\Comment;
use App\Models\ContentItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class JobPostingController extends Controller
{
    public function index(Request $request)
    {
        try {
            // ✅ Step 1: Get the authenticated user
            $user = auth()->user();
    
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User must be authenticated to access this resource.',
                ], 401);
            }
    
            // ✅ Step 2: Fetch all users with minimal info & is_online status
            // In your controller
$users = User::select('id', 'first_name', 'last_name', 'profile_photo_path', 'last_seen_at')
    ->get()
    ->map(function ($u) {
        return [
            'id' => $u->id,
            'encrypted_id' => Crypt::encryptString($u->id),
            'full_name' => trim("{$u->first_name} {$u->last_name}"),
            'profile_photo_url' => $u->profile_photo_url,
            'is_online' => $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(2)),
        ];
    })
    ->sortByDesc('is_online'); // This will put online users first
    
            // ✅ Step 3: Get user's view history for recommendations
            $viewedEntities = UserViewHistory::where('user_id', $user->id)
                ->get()
                ->groupBy('entity_type')
                ->map(function ($group) {
                    return $group->pluck('entity_id')->toArray();
                });
    
            // ✅ Step 4: Fetch paginated content items with eager loading
            $perPage = 30;
            $query = ContentItem::with(['mediaFiles', 'reactions', 'comments.user', 'creator']);
            $paginatedItems = $query->paginate($perPage);
    
            // ✅ Step 5: Transform the content entities
            $entities = $this->transformEntities($paginatedItems->getCollection(), $user, $viewedEntities);
            $paginatedItems->setCollection($entities);
    
            // ✅ Step 6: Return Inertia response with data
            return inertia('JobPosting/Index', [
                'current_user' => $this->getCurrentUserData($user),  // You can also inline this if preferred
                'entities' => $paginatedItems->items(),
                'pagination' => [
                    'current_page' => $paginatedItems->currentPage(),
                    'last_page' => $paginatedItems->lastPage(),
                    'total' => $paginatedItems->total(),
                    'per_page' => $paginatedItems->perPage(),
                    'has_more' => $paginatedItems->hasMorePages(),
                ],
                'userList' => $users,
                'recommendedUsers' => $this->recommendUsersTo($user),
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Error in JobPostingController@index: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function index2(Request $request)
    {
        try {
            // ✅ Step 1: Get the authenticated user
            $user = auth()->user();
    
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User must be authenticated to access this resource.',
                ], 401);
            }
    
            // ✅ Step 2: Fetch all users with minimal info & is_online status
            $users = User::select('id', 'first_name', 'last_name', 'profile_photo_path', 'last_seen_at')
                ->orderBy('last_name')
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'full_name' => trim("{$u->first_name} {$u->last_name}"),
                        'profile_photo_url' => $u->profile_photo_url,
                        'is_online' => $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(2)),
                    ];
                });
    
            // ✅ Step 3: Get user's view history for recommendations
            $viewedEntities = UserViewHistory::where('user_id', $user->id)
                ->get()
                ->groupBy('entity_type')
                ->map(function ($group) {
                    return $group->pluck('entity_id')->toArray();
                });
    
            // ✅ Step 4: Fetch paginated content items with eager loading
            $perPage = 20;
            $query = ContentItem::with(['mediaFiles', 'reactions', 'comments.user', 'creator']);
            $paginatedItems = $query->paginate($perPage);
    
            // ✅ Step 5: Transform the content entities
            $entities = $this->transformEntities($paginatedItems->getCollection(), $user, $viewedEntities);
            $paginatedItems->setCollection($entities);
    
            // ✅ Step 6: Return Inertia response with data
            return inertia('Dashboard', [
                'current_user' => $this->getCurrentUserData($user),  // You can also inline this if preferred
                'entities' => $paginatedItems->items(),
                'pagination' => [
                    'current_page' => $paginatedItems->currentPage(),
                    'last_page' => $paginatedItems->lastPage(),
                    'total' => $paginatedItems->total(),
                    'per_page' => $paginatedItems->perPage(),
                    'has_more' => $paginatedItems->hasMorePages(),
                ],
                'userList' => $users,
                'recommendedUsers' => $this->recommendUsersTo($user),
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Error in JobPostingController@index: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function getUserList()
{
    return User::select('id', 'first_name', 'last_name', 'profile_photo_path', 'last_seen_at')
        ->orderBy('last_name')
        ->get()
        ->map(function ($u) {
            return [
                'id' => $u->id,
                'full_name' => trim("{$u->first_name} {$u->last_name}"),
                'profile_photo_url' => $u->profile_photo_url,
                'is_online' => $u->last_seen_at && $u->last_seen_at->gt(now()->subMinutes(2)),
            ];
        });
}

    /**
     * Get formatted data for the current authenticated user
     */
    private function getCurrentUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_initial' => $user->middle_initial,
            'full_name' => $this->getFullName($user),
'profile_photo_url' => $user->profile_photo_url,
            'is_active' => $this->isActive($user),
        ];
    }
    
    /**
     * Transform the collection of content items with additional data
     */
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
    // In your JobPostingController
private function getReactionCounts($contentItemId)
{
    return Reaction::where('content_item_id', $contentItemId)
        ->selectRaw('reaction_type, COUNT(*) as count')
        ->groupBy('reaction_type')
        ->pluck('count', 'reaction_type')
        ->toArray();
}

// Add this new method to handle reaction toggling

public function toggleReaction(Request $request)
{
    try {
        $request->validate([
            'content_item_id' => 'required|exists:content_items,id',
            'reaction_type' => 'nullable|integer|min:1|max:6'
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $existingReaction = Reaction::where([
            'user_id' => $user->id,
            'content_item_id' => $request->content_item_id
        ])->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type == $request->reaction_type) {
                $existingReaction->delete();
                $action = 'removed';
            } else {
                $existingReaction->update(['reaction_type' => $request->reaction_type]);
                $action = 'updated';
            }
        } else {
            Reaction::create([
                'user_id' => $user->id,
                'content_item_id' => $request->content_item_id,
                'reaction_type' => $request->reaction_type
            ]);
            $action = 'added';
        }

        $counts = Reaction::where('content_item_id', $request->content_item_id)
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();

        $userReaction = Reaction::where([
            'user_id' => $user->id,
            'content_item_id' => $request->content_item_id
        ])->first();

        return response()->json([
            'success' => true,
            'action' => $action,
            'counts' => $counts,
            'user_reaction' => $userReaction ? $userReaction->reaction_type : null
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}
    /**
     * Format user data consistently
     */
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
    
    // Helper function to get full name
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



    /**
     * Get the full name of a user.
     *
     * @param \App\Models\User $user
     * @return string
     */
    private function getReshownItemsRandomized($user, $viewedEntities, $page, $perPage)
{
    // Combine all viewed entity IDs into a single array
    $viewedIds = array_merge(
        $viewedEntities['job'] ?? [],
        $viewedEntities['post'] ?? [],
        $viewedEntities['event'] ?? []
    );

    // Fetch reshown content items
    $reshownContentItems = ContentItem::with(['mediaFiles', 'reactions', 'comments', 'creator'])
        ->whereIn('id', $viewedIds) // Only fetch previously viewed items
        ->inRandomOrder() // Randomize the order
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function ($contentItem) use ($user) {
            $contentItem->reaction_counts = $this->getReactionCounts($contentItem->type, $contentItem->id);
            $contentItem->comments = $this->getComments($contentItem->type, $contentItem->id);
            $contentItem->score = $this->calculateScore($contentItem, $user);
            $contentItem->creator->full_name = $this->getFullName($contentItem->creator);
            return $contentItem;
        });

    // Sort reshown entities by score
    $sortedReshownEntities = $reshownContentItems->sortByDesc('score')->values();

    return $sortedReshownEntities;
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
private function recommendUsersTo($user)
{
    $users = \App\Models\User::where('id', '!=', $user->id)->get();

    $recommendations = $users->map(function ($u) use ($user) {
        $score = 0;

        // Alumni location match
        if ($user->alumniLocation && $u->alumniLocation) {
            if ($user->alumniLocation->city === $u->alumniLocation->city) {
                $score += 20;
            }
        }

        // Educational match
        if ($user->educationalBackground && $u->educationalBackground) {
            if ($user->educationalBackground->institution === $u->educationalBackground->institution) {
                $score += 15;
            }
            if ($user->educationalBackground->degree_earned === $u->educationalBackground->degree_earned) {
                $score += 10;
            }
        }

        // Employment match
        if ($user->employmentHistory->isNotEmpty() && $u->employmentHistory->isNotEmpty()) {
            $userEmployment = $user->employmentHistory->first();
            $otherEmployment = $u->employmentHistory->first();

            if ($userEmployment->industry === $otherEmployment->industry) {
                $score += 10;
            }
            if ($userEmployment->company_name === $otherEmployment->company_name) {
                $score += 10;
            }
        }

        // Online now?
        $isOnline = $u->last_seen_at && \Carbon\Carbon::parse($u->last_seen_at)->gt(now()->subMinutes(2));
        if ($isOnline) {
            $score += 5;
        }

        // Not followed yet?
        $isFollowing = \App\Models\Follow::where('follower_id', $user->id)->where('followed_id', $u->id)->exists();
        if (!$isFollowing) {
            $score += 5;
        }

        // Profile picture bonus
        if (!empty($u->profile_photo_url)) {
            $score += 3;
        }

        // Popularity
        $score += floor(($u->followers_count ?? 0) / 10);

        return [
            'id' => $u->id,
            'first_name' => $u->first_name,
            'last_name' => $u->last_name,
            'profile_photo_url' => $u->profile_photo_url,
            'is_online' => $isOnline,
            'is_following' => $isFollowing,
            'score' => $score,
        ];
    });

    // Sort by score descending
    return $recommendations->sortByDesc('score')->values();
}

    private function getReshownItems($user, $viewedEntities, $page, $perPage)
{
    // Get reshown job postings
    $reshownJobPostings = JobPosting::with('mediaFiles', 'reactions', 'comments', 'creator')
        ->whereIn('id', $viewedEntities['job'] ?? [])
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function ($job) use ($user) {
            $job->reaction_counts = $this->getReactionCounts('job', $job->id);
            $job->comments = $this->getComments('job', $job->id);
            $job->score = $this->calculateScore($job, $user);
            $job->type = 'job'; // Assign entity type
            $job->creator->full_name = $this->getFullName($job->creator); // Add full name
            return $job;
        });

    // Get reshown posts
    $reshownPosts = Post::with('mediaFiles', 'reactions', 'comments', 'creator')
        ->whereIn('id', $viewedEntities['post'] ?? [])
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function ($post) use ($user) {
            $post->reaction_counts = $this->getReactionCounts('post', $post->id);
            $post->comments = $this->getComments('post', $post->id);
            $post->score = $this->calculateScore($post, $user);
            $post->type = 'post'; // Assign entity type
            $post->creator->full_name = $this->getFullName($post->creator); // Add full name
            return $post;
        });

    // Get reshown events
    $reshownEvents = Event::with('mediaFiles', 'reactions', 'comments', 'creator')
        ->whereIn('id', $viewedEntities['event'] ?? [])
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function ($event) use ($user) {
            $event->reaction_counts = $this->getReactionCounts('event', $event->id);
            $event->comments = $this->getComments('event', $event->id);
            $event->score = $this->calculateScore($event, $user);
            $event->type = 'event'; // Assign entity type
            $event->creator->full_name = $this->getFullName($event->creator); // Add full name
            return $event;
        });

    // Combine reshown entities
    $combinedReshownEntities = collect([...$reshownJobPostings, ...$reshownPosts, ...$reshownEvents]);

    // Sort reshown entities by score
    $sortedReshownEntities = $combinedReshownEntities->sortByDesc('score')->values();

    return $sortedReshownEntities;
}

public function storeContentItem(Request $request)
{
    // Validate common fields
    $request->validate([
        'type' => 'required|in:event,post,job_posting', // Ensure valid content type
        'title' => 'nullable|string|max:255',
        'body' => 'nullable|string',
        'event_name' => 'nullable|string|max:255|required_if:type,event',
        'date' => 'nullable|date|required_if:type,event',
        'latitude' => 'nullable|numeric|between:-90,90|required_if:type,event|required_if:type,job_posting', // Latitude for events and job postings
        'longitude' => 'nullable|numeric|between:-180,180|required_if:type,event|required_if:type,job_posting', // Longitude for events and job postings
        'company_name' => 'nullable|string|max:255|required_if:type,job_posting',
        'job_title' => 'nullable|string|max:255|required_if:type,job_posting',
        'description' => 'nullable|string|required_if:type,job_posting',
        'media_files' => 'nullable|array',
        'media_files.*' => 'file|mimes:jpg,jpeg,png,pdf,mp4,mov,avi|max:10240', // Max 10MB per file

        // New fields
        'organizer_name' => 'nullable|string|max:255|required_if:type,event',
        'registration_link' => 'nullable|url|required_if:type,event',
        'salary_range' => 'nullable|string|max:100|required_if:type,job_posting',
        'application_deadline' => 'nullable|date|required_if:type,job_posting',
        'tags' => 'nullable|array', // Tags for posts (JSON)
        'is_remote' => 'nullable|boolean', // Is the job remote?
        'privacy_setting' => 'required|in:public,private,batchmates,campus_only',
    ]);

    // Extract validated data
    $data = $request->only([
        'type', 'title', 'body', 'event_name', 'date', 'latitude', 'longitude',
        'company_name', 'job_title', 'description',
        'organizer_name', 'registration_link', 'salary_range', 'application_deadline', 'tags', 'is_remote', 
    ]);

    // Add the authenticated user's ID
    $data['user_id'] = auth()->id();

    // Handle tags as JSON
    if (isset($data['tags'])) {
        $data['tags'] = json_encode($data['tags']); // Convert tags array to JSON
    }

    // Create the content item
    $contentItem = \App\Models\ContentItem::create($data);

    // Handle media file uploads
    if ($request->hasFile('media_files')) {
        foreach ($request->file('media_files') as $file) {
            $filePath = $file->store('job_postings', 'public'); // Store in storage/app/public/content_items
            $contentItem->mediaFiles()->create([
                'file_type' => $file->getClientMimeType(),
                'file_path' => $filePath,
            ]);
        }
    }

    // Redirect back with success message
    return redirect()->back()->with('success', ucfirst($contentItem->type) . ' created successfully!');
}

public function addOrUpdateReaction(Request $request)
{
    try {
        $validated = $request->validate([
            'content_item_id' => 'required|exists:content_items,id',
            'reaction_type' => 'nullable|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $reaction = Reaction::where('user_id', $user->id)
            ->where('content_item_id', $validated['content_item_id'])
            ->first();

        if ($reaction) {
            if ($reaction->reaction_type === $validated['reaction_type']) {
                // Remove reaction
                $reaction->delete();
                return response()->json([
                    'message' => 'Reaction removed successfully',
                    'action' => 'removed',
                    'user_reaction' => null
                ]);
            } else {
                // Update reaction
                $reaction->update(['reaction_type' => $validated['reaction_type']]);
                return response()->json([
                    'message' => 'Reaction updated successfully',
                    'action' => 'updated',
                    'user_reaction' => $validated['reaction_type']
                ]);
            }
        } else {
            // Create new reaction
            Reaction::create([
                'user_id' => $user->id,
                'content_item_id' => $validated['content_item_id'],
                'reaction_type' => $validated['reaction_type'],
            ]);
            return response()->json([
                'message' => 'Reaction added successfully',
                'action' => 'added',
                'user_reaction' => $validated['reaction_type']
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'An unexpected error occurred'], 500);
    }
}
    private function getComments($entityType, $entityId)
{
    $comments = Comment::with('user')
        ->where('content_item_id', $entityId)
        ->whereNull('parent_id') // Only fetch top-level comments
        ->get();

    // Fetch replies for each top-level comment
    foreach ($comments as $comment) {
        $comment->replies = Comment::with('user')
            ->where('parent_id', $comment->id)
            ->get();
    }

    return $comments;
}

public function addComment(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'content_item_id' => 'required|exists:content_items,id',
                'content' => 'required|string|max:500', // Limit comment length
                'parent_id' => 'nullable|exists:comments,id', // Optional parent ID for replies
            ]);

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Create new comment
            $comment = Comment::create([
                'user_id' => $user->id,
                'content_item_id' => $validated['content_item_id'],
                'content' => $validated['content'],
                'parent_id' => $validated['parent_id'], // Null for top-level comments
            ]);

            // Return success response with the new comment
            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => $comment->load('user'), // Include user details
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in CommentController@addComment: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
    private function organizeComments($comments)
{
    // Create a map of comments indexed by their ID
    $commentMap = [];
    foreach ($comments as $comment) {
        $comment->replies = []; // Initialize replies array
        $commentMap[$comment->id] = $comment;
    }

    // Build the nested structure
    $rootComments = [];
    foreach ($comments as $comment) {
        if ($comment->parent_id === null) {
            // This is a root-level comment
            $rootComments[] = $comment;
        } else {
            // This is a reply, attach it to its parent
            if (isset($commentMap[$comment->parent_id])) {
                $commentMap[$comment->parent_id]->replies[] = $comment;
            }
        }
    }

    return $rootComments;
}
public function updatePrivacy(Request $request, $id)
{
    try {
        // Validate request
        $validated = $request->validate([
            'privacy_setting' => 'required|in:public,private',
        ]);

        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the post and ensure it belongs to the user
        $post = ContentItem::where('id', $id)->where('user_id', $user->id)->first();
        if (!$post) {
            return response()->json(['error' => 'Post not found or unauthorized'], 404);
        }

        // Update privacy setting
        $post->privacy_setting = $validated['privacy_setting'];
        $post->save();

        return response()->json(['message' => 'Privacy updated successfully']);
    } catch (\Exception $e) {
        \Log::error('Error in PostController@updatePrivacy: ' . $e->getMessage());
        return response()->json(['error' => 'An unexpected error occurred'], 500);
    }
}
public function share(Request $request)
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => Auth::user() ? [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ] : null,
        ],
    ]);
}
public function getRecentStories()
{
    try {
        // Fetch stories created within the last 24 hours
        $recentStories = ContentItem::where('type', 'story')
            ->where('created_at', '>=', now()->subHours(24))
            ->with(['creator', 'mediaFiles']) // Eager load related data
            ->get();

        // Transform the data as needed
        $stories = $recentStories->map(function ($story) {
            return [
                'id' => $story->id,
                'body' => $story->body,
                'hashtags' => $story->hashtags, // Assuming hashtags are stored as JSON or array
                'creator' => [
                    'full_name' => $this->getFullName($story->creator),
                    'profile_picture_url' => $story->creator->profile_photo_path
                        ? '/storage/' . $story->creator->profile_photo_path
                        : '/path/to/default/profile-picture.png',
                ],
                'media_files' => $story->mediaFiles->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'file_type' => $file->file_type,
                        'file_path' => '/storage/' . $file->file_path,
                    ];
                }),
                'created_at' => $story->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stories,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error fetching recent stories: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred.',
        ], 500);
    }
}
}