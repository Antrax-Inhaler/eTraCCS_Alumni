<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLoginAttempt extends Model
{
    use HasFactory;
    protected $casts = [
        'attempted_at' => 'datetime',
    ];
    protected $fillable = [
        'email', 
        'ip_address',
        'user_agent',
        'successful'
    ];
}