<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomAuthenticatedSessionController extends AuthenticatedSessionController
{
    public function destroy(Request $request): LogoutResponse
    {
        $user = Auth::user();  // ログアウト前のユーザー情報を取得

        // ログアウト後のリダイレクト先を決定するために、セッションに一時保存
        $request->session()->put('logout_user_role', $user ? $user->role : 'null');

        Log::info('User logging out:', [
            'email' => $user->email ?? 'no user',
            'role' => $user->role ?? 'no role',
            'saved_role' => session()->get('logout_user_role')
        ]);

        Auth::guard()->logout();

        //if ($request->hasSession()) {
            // $request->session()->invalidate();
            //$request->session()->regenerateToken();
        //}

        // セッションから取得（無効化前に flash に保存した値）
        $userRole = $request->session()->get('logout_user_role', 'null');

        Log::info('After session invalidation:', [
            'logout_user_role' => $userRole
        ]);

        return app(LogoutResponse::class);
    }
}
