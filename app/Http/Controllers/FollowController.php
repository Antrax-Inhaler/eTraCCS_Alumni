<?php
namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\Request;
use App\Models\User;
class FollowController extends Controller
{
    public function toggle(Request $request)
    {
        $followerId = auth()->id();
        $followedId = $request->input('followed_id');

        if ($followerId == $followedId) {
            return response()->json(['message' => 'You cannot follow yourself.'], 400);
        }

        $existing = Follow::where('follower_id', $followerId)
                          ->where('followed_id', $followedId)
                          ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['followed' => false]);
        } else {
            Follow::create([
                'follower_id' => $followerId,
                'followed_id' => $followedId,
            ]);
            return response()->json(['followed' => true]);
        }
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
}
