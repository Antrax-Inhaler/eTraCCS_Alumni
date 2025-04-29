<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AdminResetPasswordController extends Controller
{

    // Set the password reset broker to 'admins'
    protected function broker()
    {
        return Password::broker('admins');
    }

    // Use the correct guard for admins
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
