<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

use App\Http\Controllers\ProfileController;
Route::get('/sample-tb', [SampleController::class, 'index']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/reactions', [JobPostingController::class, 'addOrUpdateReaction'])->name('api.reactions.add');
    Route::post('/comments', [JobPostingController::class, 'addComment'])->name('api.comments.add');
    Route::put('/posts/{id}/privacy', [JobPostingController::class, 'updatePrivacy'])->name('api.posts.updatePrivacy');
    Route::get('/api/profile/{id}/is-following', [ProfileController::class, 'isFollowing'])->name('profile.isFollowing');
    Route::get('/chat', [ChatController::class, 'index'])->name('api.chat.index');
    Route::get('/api/current-user', [ProfileController::class, 'getCurrentUserData'])->name('current.user');
// Fetch messages for a specific conversation
Route::get('/conversations/{id}/messages', [ChatController::class, 'fetchMessages'])->name('api.conversations.messages');

// Send a new message to a conversation
Route::post('/messages', [ChatController::class, 'sendMessage'])->name('api.messages.send');

// Get or create a global chat room
Route::get('/chat/global', [ChatController::class, 'getGlobalChat'])->name('api.chat.global');

// Get or create a batch-specific chat room
Route::get('/chat/batch/{year}', [ChatController::class, 'getBatchChat'])->name('api.chat.batch');

// Get or create a campus-specific chat room
Route::get('/chat/campus/{campus}', [ChatController::class, 'getCampusChat'])->name('api.chat.campus');

// Get or create a personal chat between two users
Route::get('/chat/personal/{userId}', [ChatController::class, 'getPersonalChat'])->name('api.chat.personal');

// Create a new user-defined group chat
Route::post('/chat/group', [ChatController::class, 'createGroupChat'])->name('api.chat.group');
// Fetch paginated messages for a specific conversation
Route::get('/conversations/{id}/messages', [ChatController::class, 'fetchMessages'])->name('api.conversations.messages');

Route::get('/users', [ChatController::class, 'fetchUsers'])->name('api.users.index');
Route::post('/chat/group', [ChatController::class, 'createGroupChat'])->name('api.chat.group');
Route::post('/conversations/create-group-chat', [ChatController::class, 'createGroupChat']);
Route::get('/conversations/{id}', [ChatController::class, 'showGroupDetails'])->name('api.conversations.show');

Route::post('/conversations/{conversationId}/add-participant', [ChatController::class, 'addParticipant'])
    ->name('api.conversations.add-participant');

// Remove Participant
Route::post('/conversations/{conversationId}/remove-participant', [ChatController::class, 'removeParticipant'])
    ->name('api.conversations.remove-participant');
    Route::get('/job-postings', [JobPostingController::class, 'index']);

    Route::get('/api/recommended-users', [UserController::class, 'getRecommendedUsers']);
    Route::post('/follow/toggle/{user}', [FollowController::class, 'toggleFollow']);
});
Route::get('/map', [MapController::class, 'getCompanyLocationsWithUsers']);
Route::post('/conversations/{conversationId}/mark-delivered', [ChatController::class, 'markMessagesAsDelivered'])
->name('api.conversations.mark-delivered');

Route::post('/conversations/{conversationId}/mark-seen', [ChatController::class, 'markMessagesAsSeen'])
->name('api.conversations.mark-seen');
Route::post('/toggle-reaction', [JobPostingController::class, 'toggleReaction'])
    ->middleware('auth:sanctum');
    Route::post('/reactions', [JobPostingController::class, 'addOrUpdateReaction'])
    ->name('api.reactions.add')
    ->middleware('auth:sanctum');
    Route::post('/reactions', [JobPostingController::class, 'addOrUpdateReaction'])
    ->middleware('auth:sanctum')
    ->name('api.reactions');

    Route::middleware('auth:sanctum')->group(function () {
        // Get user suggestions
        Route::get('/users/suggestions', [UserController::class, 'suggestions']);
        
        // Toggle follow
        Route::post('/users/{user}/toggle-follow', [UserController::class, 'toggleFollow']);
    });