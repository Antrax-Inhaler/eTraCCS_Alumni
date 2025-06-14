<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AdminLoginAttempt; // Add this import
use App\Observers\AdminLoginAttemptObserver; // Add this import

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AdminLoginAttempt::observe(AdminLoginAttemptObserver::class);
    }
}