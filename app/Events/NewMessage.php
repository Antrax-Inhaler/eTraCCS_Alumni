<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $channels = [];
        
        // Broadcast to all participants in the conversation
        foreach ($this->message->conversation->participants as $participant) {
            $channels[] = new PrivateChannel('user.' . $participant->user_id);
        }
        
        return $channels;
    }
    
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at,
            'attachments' => $this->message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => asset('storage/' . $attachment->file_path),
                    'file_type' => $attachment->file_type,
                ];
            }),
            'sender_name' => $this->message->sender->full_name,
            'sender_profile_photo_url' => $this->message->sender->profile_photo_url,
        ];
    }
}