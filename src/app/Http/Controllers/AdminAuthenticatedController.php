<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Actions\Fortify\LoginResponse;

class AdminAuthenticatedController extends AuthenticatedSessionController
{
    public function destroy(Request $request): LogoutResponse
    {
        $user = Auth::user();  // ログアウト前のユーザー情報を取得

        // ユーザーの役割に基づいて適切なガードを選択してログアウト
        if(Auth::guard('admin')->check()) {
            Log::info('Logout_admin');
            Auth::guard('admin')->logout();  // 管理者用ガードでログアウト
        } else {
            Auth::guard('web')->logout();  // 一般ユーザー用ガードでログアウト
            Log::info('Logout_user');
        }

        //if ($request->hasSession()) {
            // $request->session()->invalidate();
            //$request->session()->regenerateToken();
        //}

        return app(LogoutResponse::class);
    }
}
