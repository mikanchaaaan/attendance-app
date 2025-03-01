<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Fortify\LoginResponse;
use App\Actions\Fortify\LogoutResponse;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('user.register');
        });

        Fortify::loginView(function () {
            return request()->is('admin/login') ? view('admin.login') : view('user.login');
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->email . $request->ip());
        });


        Fortify::authenticateUsing(function ($request) {
            if ($request->is('admin/login')) {
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    Config::set('session.table', env('SESSION_TABLE_ADMIN', 'sessions_admin'));
                    Config::set('session.cookie', env('SESSION_COOKIE_ADMIN', 'admin_session'));
                    session(['auth_guard' => 'admin']);
                    Session::save();

                    return $admin;
                }
            }

            if ($request->is('login')) {
                $user = User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    Auth::guard('web')->login($user);
                    Config::set('session.table', env('SESSION_TABLE', 'sessions'));
                    Config::set('session.cookie', env('SESSION_COOKIE_USER', 'user_session'));
                    session(['auth_guard' => 'web']);
                    Session::save();

                    return $user;
                }
            }
            return null;
        });
    }
}
