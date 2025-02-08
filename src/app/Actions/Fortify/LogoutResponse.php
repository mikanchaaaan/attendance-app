<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Session;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // セッションから 'logout_user_role' を取得
        $userRole = Session::get('logout_user_role', 'null');

        // 管理者なら /admin/login、それ以外は /login にリダイレクト
        if ($userRole === 'admin') {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}