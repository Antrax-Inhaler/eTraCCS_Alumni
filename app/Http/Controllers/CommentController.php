<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    // Add a new comment

public function store(StoreCommentRequest $request)
{
    // Ensure only one entity ID is provided
    $entityTypes = array_filter([
        'post_id' => $request->post_id,
        'event_id' => $request->event_id,
        'job_id' => $request->job_id,
    ]);

    if (count($entityTypes) !== 1) {
        return response()->json(['error' => 'Invalid request. You must provide exactly one entity ID.'], 400);
    }

    $userId = auth()->id();
    $comment = Comment::create([
        'user_id' => $userId,
        'content' => $request->content,
        'parent_id' => $request->parent_id,
        key($entityTypes) => current($entityTypes),
    ]);

    $comment->load('user');

    return response()->json([
        'message' => 'Comment added successfully.',
        'comment' => $comment,
    ]);
}


    // Fetch all comments for an entity
    public function index(Request $request)
    {
        $request->validate([
            'post_id' => 'nullable|exists:posts,id',
            'event_id' => 'nullable|exists:events,id',
            'job_id' => 'nullable|exists:job_postings,id',
        ]);
    
        // Ensure only one entity is provided
        $entityTypes = array_filter([
            'post_id' => $request->post_id,
            'event_id' => $request->event_id,
            'job_id' => $request->job_id,
        ]);
    
        if (count($entityTypes) !== 1) {
            return response()->json(['error' => 'Invalid request. You must provide exactly one entity ID.'], 400);
        }
    
        $entityType = key($entityTypes);
        $entityId = current($entityTypes);
    
        // Fetch all top-level comments and their replies
        $comments = Comment::with('user') // Include user details
            ->whereNull('parent_id')     // Only top-level comments
            ->where($entityType, $entityId)
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json([
            'comments' => $comments,
        ]);
    }
    public function delete(Request $request)
{
    $request->validate([
        'post_id' => 'nullable|exists:posts,id',
        'event_id' => 'nullable|exists:events,id',
        'job_id' => 'nullable|exists:job_postings,id',
    ]);

    $entityTypes = array_filter([
        'post_id' => $request->post_id,
        'event_id' => $request->event_id,
        'job_id' => $request->job_id,
    ]);

    if (count($entityTypes) !== 1) {
        return response()->json(['error' => 'Invalid request. You must provide exactly one entity ID.'], 400);
    }

    $entityType = key($entityTypes);
    $entityId = current($entityTypes);

    if ($entityType === 'post') {
        Post::find($entityId)->delete();
    } elseif ($entityType === 'event') {
        Event::find($entityId)->delete();
    } elseif ($entityType === 'job') {
        JobPosting::find($entityId)->delete();
    }

    return response()->json(['message' => 'Entity deleted successfully.']);
}
}