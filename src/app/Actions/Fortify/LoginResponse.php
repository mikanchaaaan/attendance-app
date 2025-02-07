<?php

namespace App\Actions\Fortify;

use App\Models\User;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();
        $loginFromAdmin = Session::get('login_from_admin', false); // `true` or `false` を取得

        Log::info('LoginResponse:', [
            'user_role' => $user->role,
            'login_from_admin' => $loginFromAdmin
        ]);

        if ($user->role === 'admin') {
            return $loginFromAdmin ? redirect('/admin/attendance/list') : redirect('/attendance');
        }

        return redirect('/attendance');
    }
}