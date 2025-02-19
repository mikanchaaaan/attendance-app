<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Auth::guard('admin')->check()) {
            Config::set('session.table', env('SESSION_TABLE_ADMIN', 'admin_sessions'));
        } elseif (Auth::guard('web')->check()) {
            Config::set('session.table', env('SESSION_TABLE', 'sessions'));
        }
    }
}
