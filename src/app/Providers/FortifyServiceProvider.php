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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
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
        // ユーザー登録の設定
        Fortify::createUsersUsing(CreateNewUser::class);

        // 登録ページ
        Fortify::registerView(function () {
            return view('user.register');
        });

        // **ログインページの表示を分ける**
        Fortify::loginView(function () {
            Log::info('Current request URL:', [
                'path' => request()->path(),
                'full_url' => request()->fullUrl(),
            ]);
            return request()->is('admin/login') ? view('admin.login') : view('user.login');
        });

        // ログインレートリミット
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->email . $request->ip());
        });


        // カスタム認証ロジックを設定
        Fortify::authenticateUsing(function ($request) {
            Log::info('Current request URL:', [
                'path' => request()->path(),
                'full_url' => request()->fullUrl(),
            ]);

            // ログインURLが '/admin/login' の場合
            if ($request->is('admin/login')) {
                // 管理者の認証
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    // 管理者ガードでログイン
                    Auth::guard('admin')->login($admin);

                    Log::info('Admin session', ['cookie' => env('SESSION_COOKIE_ADMIN', 'admin_session')]);
                    Config::set('session.table', env('SESSION_TABLE_ADMIN', 'sessions_admin'));
                    Config::set('session.cookie', env('SESSION_COOKIE_ADMIN', 'admin_session'));

                    session(['auth_guard' => 'admin']);  // 管理者ガードのセッション情報

                    Log::info('LoginResponse1', [
                        'auth_guard' => session('auth_guard'),
                        'is_admin' => Auth::guard('admin')->check(),
                        'is_user' => Auth::guard('web')->check(),
                    ]);

                    Log::info('Session Settings_admin', [
                        'table' => Config::get('session.table'),
                        'cookie' => Config::get('session.cookie')
                    ]);

                    Session::save();

                    return $admin; // 認証成功
                }
            }

            if ($request->is('login')) {
                $user = User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    // 一般ユーザーガードでログイン
                    Auth::guard('web')->login($user);

                    Log::info('User session', ['cookie' => env('SESSION_COOKIE_USER', 'user_session')]);
                    Config::set('session.table', env('SESSION_TABLE', 'sessions'));
                    Config::set('session.cookie', env('SESSION_COOKIE_USER', 'user_session'));

                    session(['auth_guard' => 'web']);

                    Log::info('LoginResponse2', [
                        'auth_guard' => session('auth_guard'),
                        'is_admin' => Auth::guard('admin')->check(),
                        'is_user' => Auth::guard('web')->check(),
                    ]);

                    Log::info('Session Settings_user', [
                        'table' => Config::get('session.table'),
                        'cookie' => Config::get('session.cookie')
                    ]);

                    Session::save();

                    return $user; // 認証成功
                }
            }
            return null; // 認証失敗
        });
    }
}