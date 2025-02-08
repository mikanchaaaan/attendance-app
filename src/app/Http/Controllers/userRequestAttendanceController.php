<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class userRequestAttendanceController extends Controller
{
    // 勤怠詳細の表示
    public function userDetailView($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        $rests = $attendance->rests()->get();
        $user = $attendance->user;

        $name = $user->name;

        // 承認待ちの申請があるか確認
        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->first();

        if($attendanceRequest){
            $date = $attendanceRequest->requested_clock_date;
            $year = $attendanceRequest->requested_clock_date->format('Y') . '年';
            $monthDay = $attendanceRequest->requested_clock_date->format('n') . '月' . $attendanceRequest->requested_clock_date->format('j') . '日';
            $isPending = true;
        } else {
            $date = $attendance->date;
            $dateObj = new \DateTime($date);
            $year = $dateObj->format('Y') . '年';
            $monthDay = $dateObj->format('n') . '月' . $dateObj->format('j') . '日';  // X月X日
            $isPending = false;
        }

        return view('common.attendanceDetail', compact('name','year','monthDay','attendance','rests', 'isPending', 'attendanceRequest'));
    }

    // 勤怠申請
    public function attendanceRequest(Request $request)
    {
        $user = auth()->user();

        // フォームから送られてきた日付を取得
        $date = $request->input('date');

        // 該当の日付の勤怠情報を取得（ユーザーの出勤情報を `date` で検索）
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->firstOrFail();

        // 勤務時間の変更
        $clockYear = str_replace('年', '', $request->input('clock_year'));
        $clockMonthDay = str_replace(['月', '日'], ['-', ''], $request->input('clock_monthDay'));
        $clockDate = Carbon::createFromFormat('Y-n-j', $clockYear . '-' . $clockMonthDay)->format('Y-m-d');

        // 変更がない場合は、元の `attendance` の値を使う
        $clockInTime = $request->input('clock_in_time') ?? $attendance->clock_in_time;
        $clockOutTime = $request->input('clock_out_time') ?? $attendance->clock_out_time;

        // 申請データを保存
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_date' => $clockDate,
            'status' => 'pending',
            'requested_clock_in_time' => $clockInTime,
            'requested_clock_out_time' => $clockOutTime,
            'comment' => $request->input('comment'),
        ]);

        // 休憩時間の新規作成
        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restData) {
                // 新しい休憩時間を作成
                $rest = Rest::create([
                    'rest_in_time' => $restData['rest_in_time'],
                    'rest_out_time' => $restData['rest_out_time'],
                ]);

                // attendance_request_rest テーブルで紐づけ
                $attendanceRequest->rests()->attach($rest->id);
            }
        }

        return redirect('/stamp_correction_request/list');
    }

    // 勤怠申請一覧の表示
    public function requestView(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'pending');

        // 管理者なら全員分、一般ユーザなら自分の申請のみ取得
        if ($user->role === 'admin') {
            if ($tab == 'pending') {
                $attendanceRequests = AttendanceRequest::where('status', 'pending')->get();
            } elseif ($tab == 'approved') {
                $attendanceRequests = AttendanceRequest::where('status', 'approved')->get();
            }
        } else {
            if ($tab == 'pending') {
                $attendanceRequests = AttendanceRequest::where('status', 'pending')
                    ->where('user_id', $user->id)
                    ->get();
            } elseif ($tab == 'approved') {
                $attendanceRequests = AttendanceRequest::where('status', 'approved')
                    ->where('user_id', $user->id)
                    ->get();
            }
        }
        return view('common.attendanceRequest', compact('tab', 'attendanceRequests'));
    }
}
