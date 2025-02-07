<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Fortify\LoginResponse;
use App\Actions\Fortify\LogoutResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
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
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Actions\Fortify\RegisterResponse::class
        );

        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function() {
            return view('user.register');
        });

        // Fortifyの標準機能にカスタムLoginResponseを提供
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // 一般ユーザ用のログインページを表示
        Fortify::loginView(function () {
            // /admin/login から来た場合、管理者用のログインページを返す
            if (request()->path() === 'admin/login') {
                return view('admin.login');  // 管理者用ログインページ
            }

            return view('user.login');  // 一般ユーザ用ログインページ
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // Fortifyのカスタム認証ロジック
        Fortify::authenticateUsing(function (Request $request) {
            Log::info('authenticateUsing START:', [
                'path' => $request->path(),
                'full_url' => $request->fullUrl(),
                'email' => $request->email ?? 'No Email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::info('User not found:', ['email' => $request->email]);
                return null;
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::info('Password mismatch:', ['email' => $request->email]);
                return null;
            }

            $userRole = $user->role ?? 'user';

            Log::info('User authenticated:', [
                'email' => $request->email,
                'role' => $userRole,
            ]);

            // `/admin/login` なら管理者のみログイン許可
            if (strpos($request->fullUrl(), '/admin/login') !== false) {
                Session::put('login_from_admin', true); // 管理者ログインページからのログイン
                if ($userRole !== 'admin') {
                    Log::info('Rejecting non-admin login attempt:', [
                        'email' => $request->email,
                        'role' => $userRole,
                    ]);
                    return null; // 一般ユーザーはログイン不可
                }
            } else {
                Session::put('login_from_admin', false); // 通常ログイン
            }

            return $user;
        });
    }
}