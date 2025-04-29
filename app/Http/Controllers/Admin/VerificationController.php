<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function index()
    {
        $unverifiedUsers = User::with(['educationalBackgrounds', 'alumniLocation'])
            ->where('is_verified', false)
            ->get();
    
        return Inertia::render('Admin/Verify', [
            'unverifiedUsers' => $unverifiedUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_path 
                        ? asset('storage/' . $user->profile_photo_path)
                        : asset('images/default-profile.png'),
                    'is_verified' => $user->is_verified,
                    'educational_backgrounds' => $user->educationalBackgrounds->map(function ($edu) {
                        return [
                            'id' => $edu->id,
                            'school_name' => $edu->institution,
                            'campus' => $edu->campus,
                            'degree' => $edu->degree_earned,
                            'year_graduated' => $edu->year_graduated,
                            'course' => $edu->course ?? 'Not specified'
                        ];
                    }),
                    'alumni_location' => $user->alumniLocation ? [
                        'city' => $user->alumniLocation->city,
                        'country' => $user->alumniLocation->country
                    ] : null
                ];
            })
        ]);
    }

    public function verify(User $user)
    {
        $user->is_verified = true;
        $user->save();

        return back()->with('success', 'User verified successfully.');
    }
}