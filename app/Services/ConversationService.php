<?php

// app/Services/ConversationService.php
namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class ConversationService
{
    public function getOrCreatePersonalConversation($user1Id, $user2Id)
    {
        // Ensure consistent order to prevent duplicate conversations
        $participants = [$user1Id, $user2Id];
        sort($participants);
        
        $conversation = Conversation::where('type', 'personal')
            ->whereHas('participants', function($q) use ($participants) {
                $q->whereIn('user_id', $participants)
                  ->groupBy('conversation_id')
                  ->havingRaw('COUNT(DISTINCT user_id) = 2');
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'personal']);
            $conversation->participants()->createMany([
                ['user_id' => $participants[0]],
                ['user_id' => $participants[1]],
            ]);
        }

        return $conversation;
    }

    public function encryptId($id)
    {
        return Crypt::encryptString($id);
    }

    public function decryptId($encryptedId)
    {
        return Crypt::decryptString($encryptedId);
    }

    public function getConversationHash($user1Id, $user2Id)
    {
        $sorted = [$user1Id, $user2Id];
        sort($sorted);
        return md5(implode('|', $sorted));
    }
}