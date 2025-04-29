<?php
namespace App\Traits;

use App\Models\Feed;

trait Feedable
{
    // The feedable model will automatically associate itself with a feed entry.
    public function addToFeed($userId, $score = 0)
    {
        return Feed::create([
            'user_id' => $userId,
            'entity_type' => $this->getMorphClass(),
            'entity_id' => $this->id,
            'score' => $score,  // This could be dynamically calculated based on interactions
            'created_at' => now(),
        ]);
    }
}
