<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\User;
use App\Models\EducationalBackground;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Chat;
use App\Models\Attachment;
use App\Models\ConversationParticipant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Fetch paginated conversations for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get query parameters
        $type = $request->input('type'); // 'global', 'batch', 'campus', 'personal', 'group'
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        // Current authenticated user
        $user = Auth::user();
    
        // Query to fetch conversations
        $query = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    
        // Filter by conversation type
        if ($type) {
            $query->where('type', $type);
        }
    
        // Subquery to get the latest message ID for each conversation
        $latestMessageQuery = Message::select('id')
            ->whereColumn('conversation_id', 'conversations.id')
            ->orderByDesc('created_at')
            ->limit(1);
    
        // Fetch conversations with participants and the latest message
        $conversations = $query->with(['participants.user'])
            ->addSelect([
                'latest_message_id' => $latestMessageQuery,
            ])
            ->with(['latestMessage' => function ($q) {
                $q->with('sender'); // Include sender details
            }])
            ->orderByDesc('updated_at') // Order conversations by most recent activity
            ->paginate($perPage, ['*'], 'page', $page);
    
        // Format the response
        return response()->json([
            'data' => $conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'name' => $conversation->name ?? null,
                    'type' => $conversation->type,
                    'encrypted_id' => encrypt($conversation->id),
                    'profile_picture' => $conversation->profile_picture
                    ? '/storage/' . $conversation->profile_picture
                    : null,
                    'participants' => $conversation->participants->map(function ($participant) {
                        return [
                            'user_id' => $participant->user_id,
                            'name' => $participant->user->name,
                        ];
                    }),
                    'latest_message' => $conversation->latestMessage
                        ? [
                            'content' => $conversation->latestMessage->content,
                            'sender_name' => $conversation->latestMessage->sender->name,
                            'created_at' => $conversation->latestMessage->created_at,
                        ]
                        : null,
                ];
            }),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
                'per_page' => $conversations->perPage(),
                'has_more' => $conversations->hasMorePages(),
            ],
        ]);
    }
    public function start(User $user)
    {
        // You can pass the target user to the chat view
        return Inertia::render('Chat/ChatBox', [
            'user' => $user,
        ]);
    }
    public function getMessages(User $user)
{
    $authUserId = auth()->id();

    $messages = Chat::where(function($query) use ($authUserId, $user) {
        $query->where('sender_id', $authUserId)->where('receiver_id', $user->id);
    })->orWhere(function($query) use ($authUserId, $user) {
        $query->where('sender_id', $user->id)->where('receiver_id', $authUserId);
    })->orderBy('created_at', 'asc')->get();

    return back()->with('messages', $messages);
}



public function send(Request $request)
{
    $chat = Chat::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
    ]);

    return back();
}

    public function fetchConversations(Request $request)
    {
        // Fetch conversations with participants and the latest message
        $conversations = Conversation::with(['participants.user'])
            ->whereHas('participants', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1); // Fetch only the latest message
            }])
            ->get();
    
        // Add encrypted_id and format the response
        return $conversations->map(function ($conversation) {
            $conversation->encrypted_id = encrypt($conversation->id); // Encrypt the ID
    
            // Extract the latest message details
            $latestMessage = $conversation->messages->first();
            $conversation->latest_message = $latestMessage
                ? [
                    'content' => $latestMessage->content,
                    'sender_name' => $latestMessage->sender->name,
                    'created_at' => $latestMessage->created_at,
                ]
                : null;
                $conversation->profile_picture = $conversation->profile_picture
                ? '/storage/' . $conversation->profile_picture
                : null;
        
            return $conversation;
        });
    }
public function showConversation($encryptedId)
{
    try {
        // Decrypt the ID
        $conversationId = decrypt($encryptedId);

        // Fetch the conversation
        $conversation = Conversation::with(['messages.sender', 'messages.attachments'])
            ->findOrFail($conversationId);

        // Ensure the user is a participant of the conversation
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403, 'You are not a participant of this conversation.');
        }

        return Inertia::render('Chat/Show', [
            'conversation' => $conversation,
        ]);
    } catch (\Exception $e) {
        abort(404, 'Conversation not found.');
    }
}
    // public function sendMessage(Request $request)
    // {
    //     $request->validate([
    //         'conversation_id' => 'required|exists:conversations,id',
    //         'content' => 'nullable|string', // Optional if only sending attachments
    //         'reply_to_message_id' => 'nullable|exists:messages,id',
    //         'attachments' => 'nullable|array',
    //         'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,pdf|max:10240', // Max 10MB
    //     ]);

    //     // Create the message
    //     $message = Message::create([
    //         'conversation_id' => $request->input('conversation_id'),
    //         'sender_id' => Auth::id(),
    //         'content' => $request->input('content'),
    //         'reply_to_message_id' => $request->input('reply_to_message_id'),
    //     ]);

    //     // Handle attachments
    //     if ($request->hasFile('attachments')) {
    //         foreach ($request->file('attachments') as $file) {
    //             $filePath = $file->store('attachments', 'public'); // Store file in storage/app/public/attachments
    //             $fileType = match ($file->getMimeType()) {
    //                
    //                 default => 'document',
    //             };

    //             Attachment::create([
    //                 'message_id' => $message->id,
    //                 'file_path' => $filePath,
    //                 'file_type' => $fileType,
    //             ]);
    //         }
    //     }

    //     return response()->json($message->load('attachments'));
    // }
    /**
     * Create or fetch a global chat room.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function getGlobalChat()
    {
        $conversation = Conversation::firstOrCreate(
            ['type' => 'global'],
            ['name' => 'Global Alumni Chat']
        );

        // Ensure the current user is a participant
        $conversation->participants()->firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        return response()->json($conversation);
    }

    /**
     * Create or fetch a batch-specific chat room.
     *
     * @param int $year
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBatchChat($year)
    {
        $conversation = Conversation::firstOrCreate(
            ['type' => 'batch', 'year_graduated' => $year],
            ['name' => "Batch $year Chat"]
        );

        // Add all users from the same batch as participants
        $usersInBatch = EducationalBackground::where('year_graduated', $year)
            ->pluck('user_id');

        foreach ($usersInBatch as $userId) {
            $conversation->participants()->firstOrCreate(['user_id' => $userId]);
        }

        return response()->json($conversation);
    }

    /**
     * Create or fetch a campus-specific chat room.
     *
     * @param string $campus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampusChat($campus)
    {
        $conversation = Conversation::firstOrCreate(
            ['type' => 'campus', 'campus' => $campus],
            ['name' => "$campus Campus Chat"]
        );

        // Add all users from the same campus as participants
        $usersInCampus = EducationalBackground::where('campus', $campus)
            ->pluck('user_id');

        foreach ($usersInCampus as $userId) {
            $conversation->participants()->firstOrCreate(['user_id' => $userId]);
        }

        return response()->json($conversation);
    }

    /**
     * Create or fetch a personal chat between two users.
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPersonalChat($userId)
    {
        $currentUser = Auth::id();
        $conversation = Conversation::where('type', 'personal')
            ->whereHas('participants', function ($q) use ($currentUser, $userId) {
                $q->whereIn('user_id', [$currentUser, $userId])
                  ->groupBy('conversation_id')
                  ->havingRaw('COUNT(DISTINCT user_id) = 2');
            })
            ->first();

        if (!$conversation) {
            // Create a new personal conversation
            $conversation = Conversation::create(['type' => 'personal']);
            $conversation->participants()->createMany([
                ['user_id' => $currentUser],
                ['user_id' => $userId],
            ]);
        }

        return response()->json($conversation);
    }

    /**
     * Create a new user-defined group chat.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGroupChat(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'participants' => 'required|array|min:1',
                'participants.*' => 'exists:users,id',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image upload
            ]);
    
            // Handle profile picture upload
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('group_profile_pictures', 'public');
            }
    
            // Create the conversation
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $validated['name'],
                'created_by' => Auth::id(),
                'profile_picture' => $profilePicturePath, // Save the file path
            ]);
    
            // Add participants to the group
            $participants = array_unique(array_merge([$request->user()->id], $validated['participants']));
            $conversation->participants()->createMany(array_map(function ($userId) {
                return ['user_id' => $userId];
            }, $participants));
    
            // Return the created conversation
            return response()->json($conversation, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors as JSON
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the error and return a 500 response
            \Log::error('Error creating group chat: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create group chat'], 500);
        }
    }
    // public function fetchMessages(Request $request, $conversationId)
    // {
    //     // Get query parameters
    //     $page = $request->input('page', 1); // Current page
    //     $perPage = $request->input('per_page', 20); // Number of messages per page
    
    //     // Fetch paginated messages for the conversation in descending order
    //     $messages = Message::where('conversation_id', $conversationId)
    //         ->with(['sender' => function ($query) {
    //             $query->select('id', 'name', 'profile_photo_path'); // Include only necessary fields
    //         }, 'attachments'])
    //         ->orderBy('created_at', 'desc') // Reverse chronological order
    //         ->paginate($perPage, ['*'], 'page', $page);
    
    //     // Format the response
    //     return response()->json([
    //         'data' => $messages->map(function ($message) {
    //             return [
    //                 'id' => $message->id,
    //                 'content' => $message->content,
    //                 'sender_id' => $message->sender_id,
    //                 'sender_name' => $message->sender->name,
    //                 'sender_profile_picture' => $message->sender->profile_photo_path
    //                     ? '/storage/' . $message->sender->profile_photo_path
    //                     : null,
    //                 'attachments' => $message->attachments->map(function ($attachment) {
    //                     return [
    //                         'file_type' => $attachment->file_type,
    //                         'file_path' => $attachment->file_path,
    //                     ];
    //                 }),
    //                 'status' => $message->status,
    //                 'reply_to_message_id' => $message->reply_to_message_id,
    //                 'created_at' => $message->created_at,
    //             ];
    //         }),
    //         'pagination' => [
    //             'current_page' => $messages->currentPage(),
    //             'last_page' => $messages->lastPage(),
    //             'total' => $messages->total(),
    //             'per_page' => $messages->perPage(),
    //             'has_more' => $messages->hasMorePages(),
    //         ],
    //     ]);
    // }
    // Add this new method to your controller
public function fetchNewMessages(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'last_message_id' => 'nullable|integer', // ID of the last known message
    ]);

    $conversation = Conversation::findOrFail($request->conversation_id);
    
    // Verify user is a participant
    if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
        abort(403, 'Unauthorized');
    }

    $query = $conversation->messages()->with(['sender', 'attachments']);

    if ($request->last_message_id) {
        $query->where('id', '>', $request->last_message_id);
    }

    $messages = $query->orderBy('created_at', 'desc')
                      ->limit(50) // Limit to prevent too much data
                      ->get();

    return response()->json([
        'messages' => $messages,
        'last_message_id' => $messages->isNotEmpty() ? $messages->last()->id : null,
    ]);
}
public function fetchUsers()
{
    return User::select('id', 'name')->get();
}
public function addParticipant(Request $request, $conversationId)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $conversation = Conversation::findOrFail($conversationId);

    // Ensure the authenticated user is a participant of the group
    if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
        return response()->json(['error' => 'You are not a participant of this group'], 403);
    }

    // Add the participant to the group
    $conversation->participants()->create([
        'user_id' => $request->input('user_id'),
    ]);

    return response()->json(['message' => 'Participant added successfully']);
}
public function removeParticipant(Request $request, $conversationId)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $conversation = Conversation::findOrFail($conversationId);

    // Ensure the authenticated user is either the creator or the participant being removed
    if ($request->input('user_id') !== Auth::id() && $conversation->created_by !== Auth::id()) {
        return response()->json(['error' => 'You are not authorized to remove this participant'], 403);
    }

    // Remove the participant from the group
    $conversation->participants()->where('user_id', $request->input('user_id'))->delete();

    return response()->json(['message' => 'Participant removed successfully']);
}
public function showGroupDetails($conversationId)
{
    $conversation = Conversation::with(['participants.user', 'creator'])->findOrFail($conversationId);

    return response()->json($conversation);
}
public function markMessagesAsDelivered(Request $request, $conversationId)
{
    $request->validate([
        'message_ids' => 'required|array',
        'message_ids.*' => 'exists:messages,id',
    ]);

    Message::whereIn('id', $request->input('message_ids'))
        ->where('conversation_id', $conversationId)
        ->update(['status' => 'delivered']);

    return response()->json(['message' => 'Messages marked as delivered']);
}
// public function markMessagesAsSeen(Request $request, $conversationId)
// {
//     $request->validate([
//         'message_ids' => 'required|array',
//         'message_ids.*' => 'exists:messages,id',
//     ]);

//     Message::whereIn('id', $request->input('message_ids'))
//         ->where('conversation_id', $conversationId)
//         ->update(['status' => 'seen']);

//     return response()->json(['message' => 'Messages marked as seen']);
// }
public function longPollMessages(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'last_message_id' => 'nullable|integer',
        'timeout' => 'nullable|integer|max:30', // Max 30 seconds
    ]);

    $timeout = $request->timeout ?? 25; // Default 25 seconds
    $startTime = time();
    $conversation = Conversation::findOrFail($request->conversation_id);

    // Verify user is a participant
    if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
        abort(403, 'Unauthorized');
    }

    do {
        $query = $conversation->messages()->with(['sender', 'attachments']);
        
        if ($request->last_message_id) {
            $query->where('id', '>', $request->last_message_id);
        }

        $messages = $query->orderBy('created_at', 'desc')->get();

        if ($messages->isNotEmpty()) {
            return response()->json([
                'messages' => $messages,
                'last_message_id' => $messages->last()->id,
            ]);
        }

        // Sleep for 1 second before checking again
        sleep(1);
    } while (time() - $startTime < $timeout);

    return response()->json([
        'messages' => [],
        'last_message_id' => $request->last_message_id,
    ]);
}
public function fetchMessages(User $user)
{
    $authId = auth()->id();
    $lastMessageId = request('last_id');

    // Find conversation between authenticated user and target user
    $conversation = Conversation::where('type', 'personal')
        ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
        ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
        ->first();

    if (!$conversation) {
        return response()->json(['messages' => []]);
    }

    // Build query for fetching messages
    $query = Message::where('conversation_id', $conversation->id)
        ->orderBy('id');

    if ($lastMessageId) {
        $query->where('id', '>', $lastMessageId);
    }

    // Fetch messages with relationships
    $messages = $query->with(['attachments', 'sender'])->get();

    // Mark outgoing messages (from auth user) as delivered
    if ($messages->isNotEmpty()) {
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $authId)
            ->where('status', 'sent')
            ->where('id', '<=', $messages->last()->id)
            ->update(['status' => 'delivered']);
    }

    // Format the messages
    $formattedMessages = $messages->map(function ($message) use ($authId) {
        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'is_current_user' => $message->sender_id === $authId,
            'content' => $message->content,
            'created_at' => $message->created_at,
            'status' => $message->status, // Include status in response
            'attachments' => $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => asset('storage/' . $attachment->file_path),
                    'file_type' => $attachment->file_type,
                ];
            }),
            'sender_name' => $message->sender?->name,
            'sender_profile_photo_url' => $message->sender?->profile_photo_url,
        ];
    });

    return response()->json([
        'messages' => $formattedMessages,
        'auth_user_id' => $authId,
    ]);
}

public function sendMessage(Request $request, User $user)
{
    $request->validate([
        'message' => 'nullable|string',
        'attachments.*' => 'file|max:51200' // max 10MB per file
    ]);

    $authId = auth()->id();

    // Check for an existing personal conversation between these two users
    $conversation = Conversation::where('type', 'personal')
        ->whereHas('participants', function ($q) use ($authId) {
            $q->where('user_id', $authId);
        })
        ->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->first();

    // Create conversation if not exists
    if (!$conversation) {
        $conversation = Conversation::create([
            'type' => 'personal',
            'created_by' => $authId,
        ]);

        ConversationParticipant::insert([
            ['conversation_id' => $conversation->id, 'user_id' => $authId, 'joined_at' => now()],
            ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'joined_at' => now()],
        ]);
    }

    // Save the message (even if it's just an attachment)
    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $authId,
        'content' => $request->message ?? '',
        'status' => 'sent',
    ]);

    // Handle attachments if any
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $filePath = $file->store('attachments', 'public'); // Store in storage/app/public/attachments

            $mime = $file->getMimeType();
            $fileType = match (true) {
                str_starts_with($mime, 'image/') => 'image',
                str_starts_with($mime, 'video/') => 'video',
                default => 'document',
            };

            Attachment::create([
                'message_id' => $message->id,
                'file_path' => $filePath,
                'file_type' => $fileType,
            ]);
        }
    }

    return response()->json([
        'id' => $message->id,
        'sender_id' => $message->sender_id,
        'content' => $message->content,
        'created_at' => $message->created_at,
        'attachments' => $message->attachments ?? [],
    ]);
}

public function markAsSeen(User $user)
{
    $authId = auth()->id();

    $conversation = Conversation::where('type', 'personal')
        ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
        ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
        ->first();

    if (!$conversation) {
        return response()->json(['error' => 'Conversation not found'], 404);
    }

    // Mark all messages from the other user as seen
    Message::where('conversation_id', $conversation->id)
        ->where('sender_id', '!=', $authId)
        ->where('status', '!=', 'seen')
        ->update(['status' => 'seen']);

    return response()->json(['success' => true]);
}
public function getMessageStatuses(Request $request)
{
    $ids = $request->query('ids');
    if (!$ids) return response()->json(['statuses' => []]);

    $messageIds = explode(',', $ids);
    $statuses = Message::whereIn('id', $messageIds)
        ->where('sender_id', auth()->id())
        ->get(['id', 'status']);

    return response()->json([
        'statuses' => $statuses
    ]);
}

}