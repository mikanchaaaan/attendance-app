<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;

class UserAttendanceListController extends Controller
{
    // 出勤一覧の表示
    public function userListView(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonthCarbon = Carbon::parse($currentMonth);

        $prevMonth = $currentMonthCarbon->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonthCarbon->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->keyBy('date');

        $dates = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'clock_in_time' => null,
                'clock_out_time' => null,
                'work_time' => '00:00',
                'rest_time' => '00:00',
                'attendance_id' => null,
            ];
        }

        foreach ($attendances as $date => $attendance) {
            $clockInTime = Carbon::parse($attendance->clock_in_time);
            $clockOutTime = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time) : null;

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
            $dates[$date] = [
                'date' => $date,
                'clock_in_time' => $attendance->clock_in_time,
                'clock_out_time' => $attendance->clock_out_time,
                'work_time' => sprintf('%02d:%02d', $workHours, $workMinutes),
                'rest_time' => sprintf('%02d:%02d', $restHours, $restMinutes),
                'attendance_id' => $attendance->id ?? null,
            ];
        }

        return view('user.attendanceList', compact('currentMonth', 'prevMonth', 'nextMonth', 'dates'));
    }
}