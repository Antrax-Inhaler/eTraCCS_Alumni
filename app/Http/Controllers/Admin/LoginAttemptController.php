<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginAttempt;
use Inertia\Inertia;

class LoginAttemptController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Auth/LoginAttempts', [
            'attempts' => AdminLoginAttempt::latest()
                ->paginate(20)
                ->through(fn ($attempt) => [
                    'email' => $attempt->email,
                    'ip_address' => $attempt->ip_address,
                    'attempted_at' => $attempt->attempted_at->format('Y-m-d H:i:s'),
                    'successful' => $attempt->successful,
                    'user_agent' => $attempt->user_agent,
                ])
        ]);
    }
}
