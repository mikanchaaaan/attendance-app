<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {

        $authGuard = session('auth_guard', 'web'); // デフォルトは "web"

        Log::info('LoginResponse3', [
            'auth_guard' => $authGuard,
            'is_admin' => Auth::guard('admin')->check(),
            'is_user' => Auth::guard('web')->check(),
        ]);

        // **セッションのガード情報でリダイレクトを決定**
        if ($authGuard === 'admin') {
            Log::info('Redirecting to admin dashboard');
            Auth::guard('web')->logout();

            Log::info('guardCheck_admin', [
                'auth_guard' => $authGuard,
                'is_admin' => Auth::guard('admin')->check(),
                'is_user' => Auth::guard('web')->check(),
            ]);

            // 管理者用のクッキーを設定
            $adminCookie = cookie('admin_session', 'some_value', 120);  // 任意の値と有効期限

            // ユーザー用のクッキーを削除
            $deleteUserCookie = cookie()->forget('user_session');

            // クッキーをレスポンスに添付してリダイレクト
            return redirect('/admin/attendance/list')
                ->withCookie($deleteUserCookie) // ユーザー用クッキーを削除
                ->withCookie($adminCookie); // 管理者用クッキー
        }

        Log::info('Redirecting to user dashboard');
        Auth::guard('admin')->logout();

        // ユーザー用のクッキーを設定
        $userCookie = cookie('user_session', 'some_value', 120);  // 任意の値と有効期限

        // クッキーをレスポンスに添付してリダイレクト
        return redirect('/attendance')->withCookie($userCookie);
    }
}
