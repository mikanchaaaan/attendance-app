<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::middleware(['auth'])->group(function () {
    // 出勤登録画面の表示
    Route::get('/attendance', [AttendanceController::class, 'attendance']);

    // 出勤時
    Route::post('/attendance/clockIn', [AttendanceController::class, 'clockIn']);

    // 退勤時
    Route::post('/attendance/clockOut', [AttendanceController::class, 'clockOut']);

    // 休憩入時
    Route::post('/attendance/restIn', [AttendanceController::class, 'restIn']);

    // 休憩戻時
    Route::post('/attendance/restOut', [AttendanceController::class, 'restOut']);

    // 勤怠一覧の表示
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList']);

});