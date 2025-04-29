<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\SystemFeedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request)
    {
        $feedback = SystemFeedback::create([
            'user_id' => auth()->id(),
            'ease_of_use_rating' => $request->ease_of_use_rating,
            'usefulness_rating' => $request->usefulness_rating,
            'satisfaction_rating' => $request->satisfaction_rating,
            'intention_to_use_rating' => $request->intention_to_use_rating,
            'comments' => $request->comments,
        ]);
        auth()->user()->update([
            'last_feedback_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
    public function check()
{
    $lastFeedback = SystemFeedback::where('user_id', auth()->id())
        ->latest()
        ->first();

    return response()->json([
        'hasSubmitted' => $lastFeedback && $lastFeedback->created_at->diffInDays(now()) < 30,
        'lastSubmission' => $lastFeedback ? $lastFeedback->created_at->toISOString() : null
    ]);
}
}