<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Notifications\AdminResetPassword; 

class Admin extends Authenticatable
    {
        use Notifiable, TwoFactorAuthenticatable;
    
        protected $guard = 'admin';
    
        protected $fillable = [
            'name', 'email', 'password',
        ];
    
        protected $hidden = [
            'password',
            'remember_token',
        ];
        protected $casts = [
            'last_seen_at' => 'datetime',
        ];
        public function sendPasswordResetNotification($token)
        {
            $this->notify(new AdminResetPassword($token));
        }
        public function isOnline()
        {
            return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(2));
        }
    }
    
