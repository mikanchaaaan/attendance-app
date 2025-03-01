<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;

class UserAttendanceController extends Controller
{
    // 出勤登録画面の表示
    public function attendance()
    {
        Carbon::setLocale('ja');

        /** @var User $user */
        $userId = auth()->id();

        $today = Carbon::today();
        $currentTime = Carbon::now();

        $todayView = Carbon::now()->isoFormat('YYYY年M月D日（dd）');
        $currentTimeView = Carbon::now()->format('H:i');

        $attendance = Attendance::whereDate('date', $today)->where('user_id', $userId)->first();

        $isResting = $attendance ? $this->isResting($attendance) : false;
        $status = '';
        $showCheckInButton = false;
        $showCheckOutButton = false;
        $showRestInButton = false;
        $showRestOutButton = false;

        if (!$attendance) {
            $status = "勤務外";
            $showCheckInButton = true;
            $showCheckOutButton = false;
            $showRestInButton = false;
            $showRestOutButton = false;
        } elseif ($attendance->clock_in_time && !$attendance->clock_out_time) {
            $status = $isResting ? "休憩中" : "勤務中";
            $showCheckInButton = false;
            $showCheckOutButton = true;
            $showRestInButton = !$isResting;
            $showRestOutButton = $isResting;
        } elseif ($attendance->clock_in_time && $attendance->clock_out_time) {
            $status = "退勤済み";
            $showCheckInButton = false;
            $showCheckOutButton = false;
            $showRestInButton = false;
            $showRestOutButton = false;
        }

        $statusMessage = session()->get('status_message');

        return view('user.attendance', [
            'status' => $status,
            'today' => $todayView,
            'currentTime' => $currentTimeView,
            'showCheckInButton' => $showCheckInButton,
            'showCheckOutButton' => $showCheckOutButton,
            'showRestInButton' => $showRestInButton,
            'showRestOutButton' => $showRestOutButton,
            'statusMessage' => $statusMessage,
        ]);
    }

    private function isResting($attendance)
    {
        return $attendance->rests()->whereNull('rest_out_time')->exists();
    }

    // 出勤の登録
    public function clockIn()
    {
        $date = Carbon::today();
        $currentTime = Carbon::now();
        $userId = auth()->id();

        Attendance::create([
            'date' => $date,
            'user_id' => $userId,
            'clock_in_time' => $currentTime,
        ]);
        return redirect()->back()->with('status', '勤務中');
    }

    // 退勤の登録
    public function clockOut()
    {
        $currentTime = Carbon::now();
        $userId = auth()->id();
        $attendance = Attendance::whereDate('date', Carbon::today())->where('user_id', $userId)->first();
        $attendance->update([
            'clock_out_time' => $currentTime
        ]);

        session()->flash('status_message', 'お疲れ様でした。');

        return redirect('/attendance');
    }

    // 休憩開始の登録
    public function restIn()
    {
        $currentTime = Carbon::now();
        $userId = auth()->id();
        $attendance = Attendance::whereDate('date', Carbon::today())->where('user_id', $userId)->first();
        $rest = Rest::create([
            'rest_in_time' => $currentTime,
        ]);

        $attendance->rests()->attach($rest->id, ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->back()->with('status', '休憩中');
    }

    // 休憩終了の登録
    public function restOut()
    {
        $currentTime = Carbon::now();
        $userId = auth()->id();
        $attendance = Attendance::whereDate('date', Carbon::today())->where('user_id', $userId)->first();
        $rest = $attendance->rests()->whereNull('rest_out_time')->first();

        if ($rest) {
            $rest->update(['rest_out_time' => $currentTime]);
        }

        return redirect()->back()->with('status', '勤務中');
    }
}