<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\AdminLoginAttempt;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Admin/Auth/Login2');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Record login attempt before validation
        $this->recordLoginAttempt($request->email, false);

        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            // Update last attempt to successful
            $this->recordLoginAttempt($request->email, true);
            
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    protected function recordLoginAttempt(string $email, bool $successful)
    {
        AdminLoginAttempt::create([
            'email' => $email,
            'successful' => $successful,
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}