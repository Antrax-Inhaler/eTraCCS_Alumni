<?php

namespace App\Observers;

use App\Models\AdminLoginAttempt;
use Illuminate\Http\Request;
class AdminLoginAttemptObserver
{
    /**
     * Handle the AdminLoginAttempt "created" event.
     */
    public function created(AdminLoginAttempt $adminLoginAttempt): void
    {
        //
    }

    /**
     * Handle the AdminLoginAttempt "updated" event.
     */
    public function updated(AdminLoginAttempt $adminLoginAttempt): void
    {
        //
    }

    /**
     * Handle the AdminLoginAttempt "deleted" event.
     */
    public function deleted(AdminLoginAttempt $adminLoginAttempt): void
    {
        //
    }

    /**
     * Handle the AdminLoginAttempt "restored" event.
     */
    public function restored(AdminLoginAttempt $adminLoginAttempt): void
    {
        //
    }

    /**
     * Handle the AdminLoginAttempt "force deleted" event.
     */
    public function forceDeleted(AdminLoginAttempt $adminLoginAttempt): void
    {
        //
    }
    public function __construct(protected Request $request) {}

    public function creating(AdminLoginAttempt $attempt)
    {
        $attempt->ip_address = $this->request->ip();
        $attempt->user_agent = $this->request->userAgent();
        $attempt->attempted_at = now();
    }
}
