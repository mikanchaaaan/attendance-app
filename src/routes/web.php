<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserAttendanceListController;
use App\Http\Controllers\UserRequestAttendanceController;
use App\Http\Controllers\AdminAttendanceListController;
use App\Http\Controllers\AdminRequestAttendanceController;
use App\Http\Controllers\AdminAuthenticatedController;
use App\Http\Controllers\AdminStaffManagementController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware(['auth:web'])->group(function () {
    // 出勤登録画面の表示
    Route::get('/attendance', [UserAttendanceController::class, 'attendance']);

    // 出勤時
    Route::post('/attendance/clockIn', [UserAttendanceController::class, 'clockIn']);

    // 退勤時
    Route::post('/attendance/clockOut', [UserAttendanceController::class, 'clockOut']);

    // 休憩入時
    Route::post('/attendance/restIn', [UserAttendanceController::class, 'restIn']);

    // 休憩戻時
    Route::post('/attendance/restOut', [UserAttendanceController::class, 'restOut']);

    // 勤怠一覧の表示
    Route::get('/attendance/list', [UserAttendanceListController::class, 'userListView']);

    // 勤怠申請
    Route::post('attendance/request', [UserRequestAttendanceController::class, 'attendanceRequest']);
});

// 管理者ログインページの表示
Route::get('/admin/login', function () {
    return view('admin.login');
});

// 管理者ログイン
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

// 管理者ログアウト
Route::post('/admin/logout', [AdminAuthenticatedController::class, 'destroy']);

// 管理者用ページ (AdminMiddleware 適用)
Route::middleware(['auth:admin'])->group(function () {
    // 勤怠一覧表示
    Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'adminListView']);

    // 勤怠修正（申請なしの修正）
    Route::post('/admin/attendance/update', [AdminRequestAttendanceController::class, 'adminRequestUpdate']);

    // 修正申請承認画面の表示
    Route::get('/stamp_correction_request/approve/{attendanceRequest_id}', [AdminRequestAttendanceController::class, 'adminRequestView']);

    // 勤怠承認
    Route::post('/admin/attendance/approve', [AdminRequestAttendanceController::class, 'attendanceRequestApprove']);

    // スタッフ一覧表示
    Route::get('/admin/staff/list', [AdminStaffManagementController::class, 'viewStaffList']);

    // スタッフ別勤怠一覧表示
    Route::get('/admin/attendance/staff/{user_id}', [AdminStaffManagementController::class, 'viewStaffAttendance']);

    // スタッフ別勤怠CSVエクスポート
    Route::get('admin/attendance/export/{user_id}', [AdminStaffManagementController::class, 'csvExport']);
});

Route::middleware(['auth:web,admin'])->group(function () {
    // 勤怠詳細の表示
    Route::get('/attendance/{attendance_id}', [UserRequestAttendanceController::class, 'userDetailView']);

    // 勤怠申請一覧の表示
    Route::get('/stamp_correction_request/list', [UserRequestAttendanceController::class, 'requestView']);
});

// メール確認ページ
Route::get('email/verify', function () {
    return view('user.verify');
})->middleware(['auth'])->name('verification.notice');

// メール確認リンクの処理
Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth'])->name('verification.verify');

// メール確認の再送信
Route::middleware('auth')->post('email/verification-notification', function () {
    auth()->user()->sendEmailVerificationNotification();

    return back()->with('resent', true);
})->name('verification.send');