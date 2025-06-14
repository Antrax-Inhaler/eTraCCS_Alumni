<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Attachment;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use App\Events\NewMessage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all conversations for the user with last message and participants
        $conversations = Conversation::with(['participants.user', 'lastMessage'])
            ->whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('messages.conversation_id', 'conversations.id')
                    ->latest()
                    ->take(1)
            )
            ->get()
            ->map(function ($conversation) use ($user) {
                $unreadCount = $this->getUnreadCount($conversation->id, $user->id);
                
                // For personal conversations, get the other participant
                $otherParticipant = null;
                if ($conversation->type === 'personal') {
                    $otherParticipant = $conversation->participants
                        ->where('user_id', '!=', $user->id)
                        ->first()
                        ->user ?? null;
                }
                
                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->name ?? ($otherParticipant ? $otherParticipant->full_name : 'Group Chat'),
                    'avatar' => $conversation->avatar ?? ($otherParticipant ? $otherParticipant->profile_photo_url : null),
                    'last_message' => $conversation->lastMessage ? [
                        'content' => $conversation->lastMessage->content,
                        'sender_name' => $conversation->lastMessage->sender->full_name,
                        'created_at' => $conversation->lastMessage->created_at,
                        'is_current_user' => $conversation->lastMessage->sender_id === $user->id,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'participants' => $conversation->participants->map(function ($participant) {
                        return [
                            'id' => $participant->user_id,
                            'name' => $participant->user->full_name,
                            'avatar' => $participant->user->profile_photo_url,
                            'is_online' => $participant->user->is_online,
                        ];
                    }),
                    'created_at' => $conversation->created_at,
                ];
            });

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'current_user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'avatar' => $user->profile_photo_url,
            ],
        ]);
    }

    public function start(User $user)
    {
        $authUser = auth()->user();
        
        // Find or create conversation
        $conversation = Conversation::where('type', 'personal')
            ->whereHas('participants', fn($q) => $q->where('user_id', $authUser->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'personal',
                'created_by' => $authUser->id,
            ]);

            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $authUser->id, 'joined_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'joined_at' => now()],
            ]);
        }

        return redirect()->route('chat.conversation', $conversation);
    }

    public function showConversation(Conversation $conversation)
    {
        $user = auth()->user();
        
        // Verify user is part of this conversation
        if (!$conversation->participants->contains('user_id', $user->id)) {
            abort(403);
        }

        // Mark messages as seen
        $this->markConversationAsSeen($conversation);

        $conversation->load(['participants.user']);

        // For personal conversations, get the other participant
        $otherParticipant = null;
        if ($conversation->type === 'personal') {
            $otherParticipant = $conversation->participants
                ->where('user_id', '!=', $user->id)
                ->first()
                ->user ?? null;
        }

        return Inertia::render('Chat/Conversation', [
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'name' => $conversation->name ?? ($otherParticipant ? $otherParticipant->full_name : 'Group Chat'),
                'avatar' => $conversation->avatar ?? ($otherParticipant ? $otherParticipant->profile_photo_url : null),
                'participants' => $conversation->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user_id,
                        'name' => $participant->user->full_name,
                        'avatar' => $participant->user->profile_photo_url,
                        'is_online' => $participant->user->is_online,
                    ];
                }),
                'is_admin' => $conversation->created_by === $user->id,
                'created_at' => $conversation->created_at,
            ],
            'current_user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'avatar' => $user->profile_photo_url,
            ],
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

        return $this->fetchConversationMessages($conversation, $lastMessageId);
    }

    public function fetchConversationMessages(Conversation $conversation, $lastMessageId = null)
    {
        $authId = auth()->id();

        // Verify user is part of this conversation
        if (!$conversation->participants->contains('user_id', $authId)) {
            abort(403);
        }

        // Build query for fetching messages
        $query = Message::where('conversation_id', $conversation->id)
            ->with(['attachments', 'sender'])
            ->orderBy('id');

        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        }

        // Fetch messages with relationships
        $messages = $query->get();

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
                'status' => $message->status,
                'attachments' => $message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_path' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                }),
                'sender_name' => $message->sender->full_name,
                'sender_profile_photo_url' => $message->sender->profile_photo_url,
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

        return $this->sendToConversation($request, $conversation);
    }

    public function sendToConversation(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachments.*' => 'file|max:51200' // max 10MB per file
        ]);

        $authId = auth()->id();

        // Verify user is part of this conversation
        if (!$conversation->participants->contains('user_id', $authId)) {
            abort(403);
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
                $filePath = $file->store('attachments', 'public');

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

        // Broadcast event for real-time updates
        event(new NewMessage($message));

        return response()->json([
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'created_at' => $message->created_at,
            'attachments' => $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => asset('storage/' . $attachment->file_path),
                    'file_type' => $attachment->file_type,
                ];
            }),
            'sender_name' => $message->sender->full_name,
            'sender_profile_photo_url' => $message->sender->profile_photo_url,
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

        return $this->markConversationAsSeen($conversation);
    }

    public function markConversationAsSeen(Conversation $conversation)
    {
        $authId = auth()->id();

        // Verify user is part of this conversation
        if (!$conversation->participants->contains('user_id', $authId)) {
            abort(403);
        }

        // Mark all messages from other users as seen
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
            'statuses' => $statuses->pluck('status', 'id')
        ]);
    }

public function searchUsers(Request $request)
{
    $query = $request->query('q');
    $authId = auth()->id();

    if (!$query) {
        return response()->json([]);
    }

    return User::where('id', '!=', $authId)
        ->where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->limit(10)
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
                'is_online' => $user->is_online,
            ];
        });
}
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $authId = auth()->id();
        $members = array_unique(array_merge($request->members, [$authId]));

        // Create conversation
        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $request->name,
            'created_by' => $authId,
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('group-avatars', 'public');
            $conversation->update(['avatar' => $path]);
        }

        // Add participants
        $participants = [];
        foreach ($members as $memberId) {
            $participants[] = [
                'conversation_id' => $conversation->id,
                'user_id' => $memberId,
                'joined_at' => now(),
            ];
        }
        ConversationParticipant::insert($participants);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'avatar' => $conversation->avatar ? asset('storage/' . $conversation->avatar) : null,
                'type' => 'group',
            ]
        ]);
    }

    public function addGroupMembers(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::find($request->conversation_id);
        $authId = auth()->id();

        // Verify user is admin of this group
        if ($conversation->created_by !== $authId) {
            abort(403, 'Only group admin can add members');
        }

        // Get existing members to avoid duplicates
        $existingMembers = $conversation->participants->pluck('user_id')->toArray();

        // Add new participants
        $participants = [];
        foreach ($request->members as $memberId) {
            if (!in_array($memberId, $existingMembers)) {
                $participants[] = [
                    'conversation_id' => $conversation->id,
                    'user_id' => $memberId,
                    'joined_at' => now(),
                ];
            }
        }

        if (!empty($participants)) {
            ConversationParticipant::insert($participants);
        }

        return response()->json(['success' => true]);
    }

    public function removeGroupMember(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation = Conversation::find($request->conversation_id);
        $authId = auth()->id();

        // Verify user is admin or the user themselves
        if ($conversation->created_by !== $authId && $request->user_id !== $authId) {
            abort(403, 'Only group admin or the user themselves can remove members');
        }

        // Can't remove admin unless they're leaving themselves
        if ($request->user_id === $conversation->created_by && $authId !== $conversation->created_by) {
            abort(403, 'Cannot remove group admin');
        }

        // Remove participant
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function updateGroup(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $conversation = Conversation::find($request->conversation_id);
        $authId = auth()->id();

        // Verify user is admin of this group
        if ($conversation->created_by !== $authId) {
            abort(403, 'Only group admin can update group');
        }

        $updates = ['name' => $request->name];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($conversation->avatar) {
                Storage::disk('public')->delete($conversation->avatar);
            }
            
            $path = $request->file('avatar')->store('group-avatars', 'public');
            $updates['avatar'] = $path;
        }

        $conversation->update($updates);

        return response()->json(['success' => true]);
    }

    public function leaveGroup(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $conversation = Conversation::find($request->conversation_id);
        $authId = auth()->id();

        // Verify user is part of this group
        if (!$conversation->participants->contains('user_id', $authId)) {
            abort(403, 'You are not a member of this group');
        }

        // If user is admin, assign new admin or delete group if last member
        if ($conversation->created_by === $authId) {
            $otherParticipants = $conversation->participants->where('user_id', '!=', $authId);
            
            if ($otherParticipants->count() > 0) {
                // Assign admin to another participant
                $newAdmin = $otherParticipants->first()->user_id;
                $conversation->update(['created_by' => $newAdmin]);
            } else {
                // Last member - delete the group
                $conversation->delete();
                return response()->json(['success' => true, 'deleted' => true]);
            }
        }

        // Remove participant
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $authId)
            ->delete();

        return response()->json(['success' => true, 'deleted' => false]);
    }

    private function getUnreadCount($conversationId, $userId)
    {
        return Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->where('status', '!=', 'seen')
            ->count();
    }
// app/Http/Controllers/ChatController.php
public function getTypingStatus(Conversation $conversation)
{
    // Get users currently typing in this conversation
    $typingUsers = Cache::get("conversation:{$conversation->id}:typing", []);
    
    // Filter out expired typing indicators (older than 3 seconds)
    $validTypingUsers = array_filter($typingUsers, function($timestamp) {
        return now()->timestamp - $timestamp < 3;
    });
    
    // Get user details
    $users = User::whereIn('id', array_keys($validTypingUsers))
                ->get(['id', 'name', 'profile_photo_url']);
    
    return response()->json([
        'typing_users' => $users
    ]);
}
public function setTypingStatus(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'user_id' => 'required|exists:users,id',
        'typing' => 'required|boolean'
    ]);
    
    $key = "conversation:{$request->conversation_id}:typing";
    $typingUsers = Cache::get($key, []);
    
    if ($request->typing) {
        $typingUsers[$request->user_id] = now()->timestamp;
    } else {
        unset($typingUsers[$request->user_id]);
    }
    
    Cache::put($key, $typingUsers, now()->addMinutes(5));
    
    return response()->json(['success' => true]);
}
public function showChatInterface(Conversation $conversation = null)
{
    $user = auth()->user();
    
    // Get all conversations for the user with last message and participants
    $conversations = Conversation::with(['participants.user', 'lastMessage'])
        ->whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderByDesc(
            Message::select('created_at')
                ->whereColumn('messages.conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        )
        ->get()
        ->map(function ($conversation) use ($user) {
            $unreadCount = $this->getUnreadCount($conversation->id, $user->id);
            
            // For personal conversations, get the other participant
            $otherParticipant = null;
            if ($conversation->type === 'personal') {
                $otherParticipant = $conversation->participants
                    ->where('user_id', '!=', $user->id)
                    ->first()
                    ->user ?? null;
            }
            
            return [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'name' => $conversation->name ?? ($otherParticipant ? $otherParticipant->full_name : 'Group Chat'),
                'avatar' => $conversation->avatar ?? ($otherParticipant ? $otherParticipant->profile_photo_url : null),
                'last_message' => $conversation->lastMessage ? [
                    'content' => $conversation->lastMessage->content,
                    'sender_name' => $conversation->lastMessage->sender->full_name,
                    'created_at' => $conversation->lastMessage->created_at,
                    'is_current_user' => $conversation->lastMessage->sender_id === $user->id,
                ] : null,
                'unread_count' => $unreadCount,
                'participants' => $conversation->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user_id,
                        'name' => $participant->user->full_name,
                        'avatar' => $participant->user->profile_photo_url,
                        'is_online' => $participant->user->is_online,
                    ];
                }),
                'created_at' => $conversation->created_at,
            ];
        });

    return Inertia::render('Chat/Index', [
        'conversations' => $conversations,
        'current_user' => [
            'id' => $user->id,
            'name' => $user->full_name,
            'avatar' => $user->profile_photo_url,
        ],
        'initial_conversation' => $conversation ? [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->name ?? ($conversation->type === 'personal' ? 
                $conversation->participants->where('user_id', '!=', $user->id)->first()->user->full_name : 
                'Group Chat'),
            'avatar' => $conversation->avatar ?? ($conversation->type === 'personal' ? 
                $conversation->participants->where('user_id', '!=', $user->id)->first()->user->profile_photo_url : 
                null),
            'participants' => $conversation->participants->map(function ($participant) {
                return [
                    'id' => $participant->user_id,
                    'name' => $participant->user->full_name,
                    'avatar' => $participant->user->profile_photo_url,
                    'is_online' => $participant->user->is_online,
                ];
            }),
            'is_admin' => $conversation->created_by === $user->id,
            'created_at' => $conversation->created_at,
        ] : null,
    ]);
}
}