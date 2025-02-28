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

        // ログイン中のユーザ情報を取得
        /** @var User $user */
        $userId = auth()->id();

        // 現在日付と時間を取得
        $today = Carbon::today();
        $currentTime = Carbon::now();

        // viewへの表示用に表記を変更
        $todayView = Carbon::now()->isoFormat('YYYY年M月D日（dd）');
        $currentTimeView = Carbon::now()->format('H:i'); // 'HH:MM'

        // ログイン中のユーザの勤怠情報を取得
        $attendance = Attendance::whereDate('date', $today)->where('user_id', $userId)->first();

        // 初期化
        $isResting = $attendance ? $this->isResting($attendance) : false;
        $status = '';
        $showCheckInButton = false;
        $showCheckOutButton = false;
        $showRestInButton = false;
        $showRestOutButton = false;

        // 勤怠の確認
        if (!$attendance) {
            // まだ出勤していない場合
            $status = "勤務外";
            $showCheckInButton = true;
            $showCheckOutButton = false;
            $showRestInButton = false;
            $showRestOutButton = false;
        } elseif ($attendance->clock_in_time && !$attendance->clock_out_time) {
            // 出勤しているが、退勤していない場合
            $status = $isResting ? "休憩中" : "勤務中";
            $showCheckInButton = false;
            $showCheckOutButton = true;
            $showRestInButton = !$isResting;
            $showRestOutButton = $isResting;
        } elseif ($attendance->clock_in_time && $attendance->clock_out_time) {
            // 退勤済みの場合
            $status = "退勤済み";
            $showCheckInButton = false;
            $showCheckOutButton = false;
            $showRestInButton = false;
            $showRestOutButton = false;
        }

        $statusMessage = session()->get('status_message');

        // 画面の表示
        return view('user.attendance', [
            'status' => $status,
            'today' => $todayView,
            'currentTime' => $currentTimeView,
            'showCheckInButton' => $showCheckInButton,
            'showCheckOutButton' => $showCheckOutButton,
            'showRestInButton' => $showRestInButton,
            'showRestOutButton' => $showRestOutButton,
            'statusMessage' => $statusMessage,  // セッションからメッセージを取得
        ]);
    }
    // 休憩中かどうかを判断
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

        // 中間テーブルに関係を登録
        $attendance->rests()->attach($rest->id, ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->back()->with('status', '休憩中');
    }

    // 休憩終了の登録
    public function restOut()
    {
        $currentTime = Carbon::now();
        $userId = auth()->id();
        $attendance = Attendance::whereDate('date', Carbon::today())->where('user_id', $userId)->first();

        // 中間テーブル経由で紐づいている、休憩終了時間がまだ入っていない `Rest` を取得
        $rest = $attendance->rests()->whereNull('rest_out_time')->first();

        if ($rest) {
            $rest->update(['rest_out_time' => $currentTime]);
        }

        return redirect()->back()->with('status', '勤務中');
    }
}