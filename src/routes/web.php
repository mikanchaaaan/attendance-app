<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userAttendanceController;
use App\Http\Controllers\userAttendanceListController;
use App\Http\Controllers\userRequestAttendanceController;

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

});