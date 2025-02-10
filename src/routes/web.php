<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userAttendanceController;
use App\Http\Controllers\userAttendanceListController;
use App\Http\Controllers\userRequestAttendanceController;
use App\Http\Controllers\adminAttendanceListController;
use App\Http\Controllers\adminRequestAttendanceController;
use App\Http\Controllers\adminAuthenticatedController;
use App\Http\Controllers\adminStuffManagementController;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware(['auth:web,admin'])->group(function () {
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

// 管理者ログインページの表示
Route::get('/admin/login', function () {
    return view('admin.login');
});

// 管理者ログイン
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

// 管理者ログアウト
Route::post('/admin/logout', [adminAuthenticatedController::class, 'destroy']);

// 管理者用ページ (AdminMiddleware 適用)
Route::middleware(['auth:admin'])->group(function () {
    // 勤怠一覧表示
    Route::get('/admin/attendance/list', [adminAttendanceListController::class, 'adminListView']);

    // 勤怠修正（申請なしの修正）
    Route::post('/admin/attendance/update', [adminRequestAttendanceController::class, 'adminRequestUpdate']);

    // 勤怠承認
    Route::post('/admin/attendance/approve', [adminRequestAttendanceController::class, 'attendanceRequestApprove']);

    // スタッフ一覧表示
    Route::get('/admin/stuff/list', [adminStuffManagementController::class, 'viewStaffList']);
});