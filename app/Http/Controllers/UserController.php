<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getRecommendedUsers()
{
    $currentUserId = auth()->id();
    
    // Get users not already followed by current user
    $recommendedUsers = User::where('id', '!=', $currentUserId)
        ->whereDoesntHave('followers', function($query) use ($currentUserId) {
            $query->where('follower_id', $currentUserId);
        })
        ->inRandomOrder()
        ->limit(5)
        ->get(['id', 'first_name', 'last_name', 'username', 'profile_photo_url']);
    
    return response()->json($recommendedUsers);
}
public function suggestions()
{
    $currentUser = auth()->user();
    
    // Get users not followed by current user
    $suggestedUsers = User::where('id', '!=', $currentUser->id)
        ->whereDoesntHave('followers', function($query) use ($currentUser) {
            $query->where('follower_id', $currentUser->id);
        })
        ->inRandomOrder()
        ->limit(5)
        ->get()
        ->map(function($user) use ($currentUser) {
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'name' => $user->name,
                'profile_photo_url' => $user->profile_photo_url,
                'is_following' => false
            ];
        });
    
    // If not enough suggestions, add some random users
    if ($suggestedUsers->count() < 3) {
        $additionalUsers = User::whereNotIn('id', $suggestedUsers->pluck('id'))
            ->where('id', '!=', $currentUser->id)
            ->inRandomOrder()
            ->limit(5 - $suggestedUsers->count())
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'name' => $user->name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'is_following' => false
                ];
            });
        
        $suggestedUsers = $suggestedUsers->merge($additionalUsers);
    }
    
    return response()->json($suggestedUsers);
}

public function toggleFollow(User $user)
{
    $currentUser = auth()->user();
    
    if ($currentUser->id === $user->id) {
        return response()->json(['error' => 'Cannot follow yourself'], 400);
    }
    
    $follow = Follow::where('follower_id', $currentUser->id)
        ->where('followed_id', $user->id)
        ->first();
    
    if ($follow) {
        $follow->delete();
        return response()->json(['is_following' => false]);
    } else {
        Follow::create([
            'follower_id' => $currentUser->id,
            'followed_id' => $user->id,
        ]);
        return response()->json(['is_following' => true]);
    }
}
// In your UserController.php
public function updateLastSeen()
{
    auth()->user()->update(['last_seen_at' => now()]);
    return back();
}
}
