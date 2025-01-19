<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

// 出勤登録画面の表示
Route::get('/attendance', [AttendanceController::class, 'attendance']);