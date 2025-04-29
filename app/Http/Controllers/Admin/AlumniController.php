<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        // Only users marked as verified
        $query = User::with(['educationalBackgrounds', 'alumniLocation'])
            ->where('is_verified', 1)
            ->latest();
    
        if ($request->has('batch_year')) {
            $query->whereHas('educationalBackgrounds', function($q) use ($request) {
                $q->where('year_graduated', $request->batch_year);
            });
        }
    
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                  ->orWhere('last_name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
    
        $alumni = $query->paginate(15);
    
        $batchYears = \App\Models\EducationalBackground::distinct('year_graduated')
            ->orderBy('year_graduated', 'desc')
            ->pluck('year_graduated');
    
        $demographics = [
            'gender' => User::selectRaw('count(*) as count, gender')
                ->where('is_verified', 1)
                ->groupBy('gender')
                ->get(),
            'location' => \App\Models\AlumniLocation::selectRaw('count(*) as count, country')
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'batch_distribution' => \App\Models\EducationalBackground::selectRaw('count(distinct user_id) as count, year_graduated')
                ->groupBy('year_graduated')
                ->orderBy('year_graduated', 'desc')
                ->get()
        ];
    
        return Inertia::render('Admin/Alumni', [
            'alumni' => $alumni,
            'filters' => $request->only(['search', 'batch_year']),
            'batchYears' => $batchYears,
            'demographics' => $demographics
        ]);
    }
    
    public function unverify(User $user)
    {
        $user->update(['is_verified' => 0]);
        return back()->with('success', 'Alumni profile unverified successfully');
    }
    
}