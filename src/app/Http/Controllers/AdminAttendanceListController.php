<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class AdminAttendanceListController extends Controller
{
    // 管理者用勤怠一覧ページの表示
    public function adminListView(Request $request){

        $user = auth('admin')->user();

        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->orderBy('clock_in_time', 'asc')
            ->get();

        $prevDate = Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        $work_times = [];
        $rest_times = [];

        foreach($attendances as $attendance) {
            $clockInTime = Carbon::parse($attendance->clock_in_time);
            $clockOutTime = Carbon::parse($attendance->clock_out_time);

            if ($clockOutTime && $clockOutTime < $clockInTime) {
                $clockOutTime->addDay();
            }

            $workTime = $clockOutTime ? $clockInTime->diffInMinutes($clockOutTime) : 0;

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