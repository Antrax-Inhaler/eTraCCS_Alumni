<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\ContentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class ReactionController extends Controller
{
    public function handleReaction(Request $request)
    {
        try {
            $user = Auth::user();
            $contentItem = ContentItem::findOrFail($request->content_item_id);

            // Remove existing reaction if exists
            $user->reactions()
                ->where('content_item_id', $contentItem->id)
                ->delete();

            // Add new reaction if specified
            if ($request->reaction_type) {
                Reaction::create([
                    'user_id' => $user->id,
                    'content_item_id' => $contentItem->id,
                    'reaction_type' => $request->reaction_type
                ]);
            }

            // Return updated counts
            return response()->json([
                'success' => true,
                'reaction_counts' => $this->getUpdatedReactionCounts($contentItem),
                'top_reactions' => $this->getTopReactions($contentItem),
            ]);

        } catch (\Exception $e) {
            Log::error('Reaction error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    private function getUpdatedReactionCounts(ContentItem $contentItem)
    {
        return $contentItem->reactions()
            ->selectRaw('reaction_type, count(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();
    }

    private function getTopReactions(ContentItem $contentItem)
    {
        $counts = $this->getUpdatedReactionCounts($contentItem);
        
        return collect($counts)
            ->sortDesc()
            ->take(3)
            ->mapWithKeys(function ($count, $type) {
                return [$type => $count];
            })
            ->toArray();
    }
}