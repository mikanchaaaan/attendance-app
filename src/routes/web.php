<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userAttendanceController;
use App\Http\Controllers\userAttendanceListController;
use App\Http\Controllers\userRequestAttendanceController;
use App\Http\Controllers\adminAttendanceListController;
use App\Http\Controllers\CustomAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware(['auth'])->group(function () {
    // 出勤登録画面の表示
    Route::get('/attendance', [userAttendanceController::class, 'attendance']);

    // 出勤時
    Route::post('/attendance/clockIn', [userAttendanceController::class, 'clockIn']);

    // 退勤時
    Route::post('/attendance/clockOut', [userAttendanceController::class, 'clockOut']);

    // 休憩入時
    Route::post('/attendance/restIn', [userAttendanceController::class, 'restIn']);

    // 休憩戻時
    Route::post('/attendance/restOut', [userAttendanceController::class, 'restOut']);

    // 勤怠一覧の表示
    Route::get('/attendance/list', [userAttendanceListController::class, 'userListView']);

    // 勤怠詳細の表示
    Route::get('/attendance/{attendance_id}', [userRequestAttendanceController::class, 'userDetailView']);

    // 勤怠申請
    Route::post('attendance/request', [userRequestAttendanceController::class, 'attendanceRequest']);

    // 勤怠申請一覧の表示
    Route::get('/stamp_correction_request/list', [userRequestAttendanceController::class, 'requestView']);

});
// 管理者のログインページ
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login'); // 名前付きルートを明示的に追加

// Fortifyの認証処理を使用して管理者ログイン
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

// Fortifyの認証処理を使用して管理者ログアウト
Route::post('/admin/logout', [CustomAuthenticatedSessionController::class, 'destroy']);

// 管理者ページ (AdminMiddleware 適用)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [adminAttendanceListController::class, 'adminListView'])
        ->name('admin.attendance.list');
});