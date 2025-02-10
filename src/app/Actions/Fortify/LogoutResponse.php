<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // 現在のガードを判定
        $authGuard = session('auth_guard');
        Log::info('logout_guardCheck', [
            'auth_guard' => $authGuard,
            'is_admin' => Auth::guard('admin')->check(),
            'is_user' => Auth::guard('web')->check(),
        ]);

        // 管理者なら管理者用ログインページへリダイレクト
        if ($authGuard === 'admin') {
            return redirect('/admin/login');
        }

        // 一般ユーザなら通常のログインページへ
        return redirect('/login');
    }
}