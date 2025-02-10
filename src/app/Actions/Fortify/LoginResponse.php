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

            return redirect('/admin/attendance/list');
        }

        Log::info('Redirecting to user dashboard');
        Auth::guard('admin')->logout();
        return redirect('/attendance');
    }
}