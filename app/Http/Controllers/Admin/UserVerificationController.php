<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserVerificationController extends Controller
{
    // Get all unverified users
    public function unverifiedUsers()
    {
        $users = User::where('is_verified', 0)->get();
        return response()->json($users);
    }

    // Verify a specific user by ID
    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_verified = 1;
        $user->save();

        return response()->json(['message' => 'User verified successfully']);
    }
}
