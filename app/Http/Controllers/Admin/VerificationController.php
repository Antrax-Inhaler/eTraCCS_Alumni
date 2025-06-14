<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AlumniRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['educationalBackgrounds', 'alumniLocation'])
            ->where('verification_status', '!=', 'verified')
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('student_number', 'like', '%'.$request->search.'%');
            });
        }

        // Get users with their best match score
        $users = $query->paginate(10)->through(function ($user) {
            $bestMatch = $this->findBestMatch($user);
            return tap($user)->setAttribute('match_score', $bestMatch ? $bestMatch['score'] : null);
        });

        return Inertia::render('Admin/Verify', [
            'unverifiedUsers' => $users,
            'filters' => $request->only(['search']),
            'matchThreshold' => config('alumni.verification_threshold', 80),
        ]);
    }


    public function verify(User $user)
    {
        // Find potential matches in official records
        $matches = AlumniRecord::where('first_name', 'like', $user->first_name.'%')
            ->where('last_name', 'like', $user->last_name.'%')
            ->get();

        $bestMatch = null;
        $highestScore = 0;

        foreach ($matches as $record) {
            $score = $this->calculateMatchScore($user, $record);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $record;
            }
        }

        if ($bestMatch && $highestScore >= config('alumni.verification_threshold', 80)) {
            $user->update([
                'verification_status' => 'verified',
                'is_verified' => true,
                'student_number' => $bestMatch->student_number,
            ]);

            return back()->with('success', 'User verified successfully with '.$highestScore.'% match to official records.');
        }

        return back()->with('error', 'No strong match found in official records. Please verify manually.');
    }

    public function manualVerify(Request $request, User $user)
    {
        $request->validate([
            'student_number' => 'required|exists:alumni_records,student_number',
            'notes' => 'nullable|string',
        ]);

        $user->update([
            'verification_status' => 'verified',
            'is_verified' => true,
            'student_number' => $request->student_number,
            'verification_notes' => $request->notes,
        ]);

        return back()->with('success', 'User manually verified successfully.');
    }

    public function reject(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $user->update([
            'verification_status' => 'rejected',
            'verification_notes' => $request->reason,
        ]);

        return back()->with('success', 'User verification rejected.');
    }

    protected function calculateMatchScore(User $user, AlumniRecord $record)
    {
        $score = 0;
        
        // Name matching (40% weight)
        $nameScore = 0;
        if (strtolower($user->first_name) === strtolower($record->first_name)) {
            $nameScore += 20;
        }
        if (strtolower($user->last_name) === strtolower($record->last_name)) {
            $nameScore += 20;
        }
        $score += $nameScore;

        // Education matching (30% weight)
        foreach ($user->educationalBackgrounds as $edu) {
            if (strtolower($edu->degree_earned) === strtolower($record->degree_earned)) {
                $score += 15;
            }
            if (strtolower($edu->campus) === strtolower($record->campus)) {
                $score += 10;
            }
            if ($edu->year_graduated == $record->year_graduated) {
                $score += 5;
            }
        }

        // Student number matching (30% weight)
        if ($user->student_number && $user->student_number === $record->student_number) {
            $score += 30;
        }

        return min(100, $score); // Cap at 100%
    }
    public function matchDetails(User $user)
    {
        $matches = AlumniRecord::where('first_name', 'like', $user->first_name.'%')
            ->orWhere('last_name', 'like', $user->last_name.'%')
            ->get()
            ->map(function ($record) use ($user) {
                $score = $this->calculateMatchScore($user, $record);
                return [
                    'record' => $record,
                    'score' => $score,
                    'details' => $this->getMatchDetails($user, $record)
                ];
            })
            ->sortByDesc('score')
            ->values();
    
        return response()->json(['matches' => $matches]);
    }
    protected function findBestMatch(User $user)
    {
        $matches = AlumniRecord::where('first_name', 'like', $user->first_name.'%')
            ->orWhere('last_name', 'like', $user->last_name.'%')
            ->get();

        $bestMatch = null;
        $highestScore = 0;

        foreach ($matches as $record) {
            $score = $this->calculateMatchScore($user, $record);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = [
                    'score' => $score,
                    'record' => $record
                ];
            }
        }

        return $bestMatch;
    }

    protected function getMatchDetails(User $user, AlumniRecord $record)
    {
        return [
            'name_match' => strtolower($user->first_name) === strtolower($record->first_name) && 
                           strtolower($user->last_name) === strtolower($record->last_name),
            'student_number_match' => $user->student_number && $user->student_number === $record->student_number,
            'degree_match' => $user->educationalBackgrounds->contains('degree_earned', $record->degree_earned),
            'campus_match' => $user->educationalBackgrounds->contains('campus', $record->campus),
            'year_match' => $user->educationalBackgrounds->contains('year_graduated', $record->year_graduated),
        ];
    }

}