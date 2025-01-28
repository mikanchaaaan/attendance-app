<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;

class userAttendanceListController extends Controller
{
    // 出勤一覧の表示
    public function userListView(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        // 月のフィルタを取得（デフォルトは現在の月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // Carbonで月を管理
        $currentMonthCarbon = Carbon::parse($currentMonth);

        // 前月と後月の計算
        $prevMonth = $currentMonthCarbon->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonthCarbon->copy()->addMonth()->format('Y-m');

        // 指定された月の勤怠データを取得
        $startOfMonth = $currentMonthCarbon->copy();
        $startOfMonth->startOfMonth();  // 明示的に startOfMonth を呼び出す

        $endOfMonth = $currentMonthCarbon->copy();
        $endOfMonth->endOfMonth();  // 明示的に endOfMonth を呼び出す

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        // 休憩時間の算出
        $restTimes = Rest::selectRaw('attendance_id, SUM(TIMESTAMPDIFF(MINUTE, rest_in_time, rest_out_time)) as total_rest_time')
            ->whereIn('attendance_id', function ($query) use ($user) {
                $query->select('id')
                    ->from('attendances')
                    ->where('user_id', $user->id);
            })
            ->groupBy('attendance_id')
            ->get()
            ->keyBy('attendance_id'); // attendance_id をキーにする

        return view('user.attendanceList', compact('currentMonth', 'prevMonth', 'nextMonth', 'attendances', 'restTimes'));
    }
}
