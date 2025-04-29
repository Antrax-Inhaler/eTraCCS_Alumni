<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sample; // Assuming you have a model named Sample

class SampleController extends Controller
{
    public function index(Request $request)
    {
        // Get the current page from the query string (default to 1 if not provided)
        $page = $request->input('page', 1);
        
        // Paginate the data (e.g., 10 items per page)
        $data = Sample::paginate(10);
        
        // Get authenticated user data
        $user = $request->user();
        
        // Return both the paginated data and user info
        return response()->json([
            'data' => $data,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'profile_photo_url' => $user->profile_photo_url, // This uses Laravel's default profile photo URL
            ] : null
        ]);
    }
}