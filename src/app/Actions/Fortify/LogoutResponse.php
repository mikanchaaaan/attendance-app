<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // セッションから 'logout_user_role' を取得
        $userRole = Session::get('logout_user_role', 'null');

        Log::info('LogoutResponse:', [
            'user_role' => $userRole
        ]);

        // 管理者なら /admin/login、それ以外は /login にリダイレクト
        if ($userRole === 'admin') {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}