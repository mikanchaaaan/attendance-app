<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class adminAttendanceListController extends Controller
{
    // 管理者用勤怠一覧ページの表示
    public function adminListView(Request $request){

        $user = auth('admin')->user();

        // クエリパラメータがなければ今日の日付をデフォルトにする
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        // 勤怠データを取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->orderBy('clock_in_time', 'asc')
            ->get();

        // 前日・翌日の日付を計算
        $prevDate = Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        $work_times = []; // 勤務時間の配列
        $rest_times = []; // 休憩時間の配列

        foreach($attendances as $attendance) {
            $clockInTime = Carbon::parse($attendance->clock_in_time);
            $clockOutTime = Carbon::parse($attendance->clock_out_time);

            // 退勤が出勤より前なら翌日とみなす
            if ($clockOutTime && $clockOutTime < $clockInTime) {
                $clockOutTime->addDay();
            }

            // 勤務時間計算
            $workTime = $clockOutTime ? $clockInTime->diffInMinutes($clockOutTime) : 0;

            // 休憩時間計算
            $restTime = 0;
            foreach ($attendance->rests as $rest) {
                if ($rest->rest_out_time) {
                    $restIn = Carbon::parse($rest->rest_in_time);
                    $restOut = Carbon::parse($rest->rest_out_time);
                    $restTime += $restIn->diffInMinutes($restOut);
                }
            }
                $restHours = floor($restTime / 60);
                $restMinutes = $restTime % 60;
                $workTime -= $restTime;
                $workHours = floor($workTime / 60);
                $workMinutes = $workTime % 60;

                // フォーマット変換
                $work_times[$attendance->id] = sprintf('%02d:%02d', $workHours, $workMinutes);
                $rest_times[$attendance->id] = sprintf('%02d:%02d', $restHours, $restMinutes);
        }
        return view('admin.attendanceList', compact('attendances', 'date', 'prevDate', 'nextDate', 'work_times', 'rest_times'));
    }
}