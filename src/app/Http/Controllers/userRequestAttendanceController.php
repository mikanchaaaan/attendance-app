<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class userRequestAttendanceController extends Controller
{
    // 勤怠詳細の表示
    public function userDetailView()
    {
        return view('user.attendanceDetail');
    }
}
