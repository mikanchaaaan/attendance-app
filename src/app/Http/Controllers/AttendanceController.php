<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // 出勤登録画面の表示
    public function attendance()
    {
        return view('user.attendance');
    }
}
