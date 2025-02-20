<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffManagementController extends Controller
{
    // スタッフ一覧の表示（管理者用）

    public function viewStaffList() {

        $users = User::all();
        return view('admin.staffList', compact('users'));
    }

    // スタッフごとの勤怠一覧の表示（管理者用）
    public function viewStaffAttendance(Request $request) {

        $user = User::findOrFail($request->user_id);

        // 月のフィルタを取得（デフォルトは現在の月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // Carbonで月を管理
        $currentMonthCarbon = Carbon::parse($currentMonth);

        // 前月と後月の計算
        $prevMonth = $currentMonthCarbon->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonthCarbon->copy()->addMonth()->format('Y-m');

        // 当月の開始日と終了日
        $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();

        // 当月の勤怠データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests') // Rest データを取得（中間テーブル経由）
            ->get()
            ->keyBy('date'); // 日付をキーにする

        // **当月の日付リストを作成**
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

        // **勤怠データをマージ**
        foreach ($attendances as $date => $attendance) {
            $clockInTime = Carbon::parse($attendance->clock_in_time);
            $clockOutTime = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time) : null;

            // 退勤が出勤より前なら翌日とみなす
            if ($clockOutTime && $clockOutTime < $clockInTime) {
                $clockOutTime->addDay();
            }

            // 勤務時間計算
            $workTime = $clockOutTime ? $clockInTime->diffInMinutes($clockOutTime) : 0;

            // **休憩時間を計算**
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

            // マージ
            $dates[$date] = [
                'date' => $date,
                'clock_in_time' => $attendance->clock_in_time,
                'clock_out_time' => $attendance->clock_out_time,
                'work_time' => sprintf('%02d:%02d', $workHours, $workMinutes),
                'rest_time' => sprintf('%02d:%02d', $restHours, $restMinutes),
                'attendance_id' => $attendance->id ?? null, // 勤怠データがない日は null
            ];
        }
        return view('admin.staffAttendance', compact('user', 'currentMonth', 'prevMonth', 'nextMonth', 'dates'));
    }

    public function csvExport($userId, Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m')); // クエリパラメータで月を取得

        // 指定した月の勤怠データを取得
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [
                Carbon::parse($month . '-01')->startOfMonth(),
                Carbon::parse($month . '-01')->endOfMonth()
            ])
            ->with('rests') // rests リレーションをロード
            ->get();

        $filename = "attendance_{$userId}_{$month}.csv";

        // ストリームを開いてCSVを返す
        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv(
                $handle,
                ['日付', '出勤時間', '退勤時間', '休憩時間', '勤務時間']
            ); // ヘッダー行

            foreach ($attendances as $attendance) {
                // 休憩時間を計算
                $restTime = 0;
                foreach ($attendance->rests as $rest) {
                    if ($rest->rest_out_time && $rest->rest_in_time) {
                        $restIn = Carbon::parse($rest->rest_in_time);
                        $restOut = Carbon::parse($rest->rest_out_time);
                        $restTime += $restIn->diffInMinutes($restOut);
                    }
                }

                // 休憩時間が計算されていない場合は '0:00'
                $formattedRestTime = gmdate('H:i', $restTime * 60);

                // 勤務時間を計算（出勤時間と退勤時間の差）
                if ($attendance->clock_in_time && $attendance->clock_out_time) {
                    $clockIn = Carbon::parse($attendance->clock_in_time);
                    $clockOut = Carbon::parse($attendance->clock_out_time);
                    $workTimeInMinutes = $clockIn->diffInMinutes($clockOut) - $restTime; // 勤務時間から休憩時間を引く
                    $formattedWorkTime = gmdate('H:i', $workTimeInMinutes * 60); // 分を時間に変換
                } else {
                    $formattedWorkTime = '-'; // 出勤時間か退勤時間がない場合
                }

                fputcsv($handle, [
                    $attendance->date,
                    $attendance->clock_in_time ?? '-',
                    $attendance->clock_out_time ?? '-',
                    $formattedRestTime,
                    $formattedWorkTime
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }
}
