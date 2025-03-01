<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthenticatedController extends AuthenticatedSessionController
{
    public function destroy(Request $request): LogoutResponse
    {
        $user = Auth::user();

        if(Auth::guard('admin')->check()) {
            Log::info('Logout_admin');
            Auth::guard('admin')->logout();
        } else {
            Auth::guard('web')->logout();
            Log::info('Logout_user');
        }

        return app(LogoutResponse::class);
    }
}
