<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceRequestForm;

class adminRequestAttendanceController extends Controller
{
    // 勤怠修正（管理者用）
    public function adminRequestUpdate(AttendanceRequestForm $request)
    {
        // 勤怠データの取得
        $attendance = Attendance::findOrFail($request->input('id'));

        // 更新データを格納する配列
        $updatedData = [];

        // 出勤・退勤時間のチェック
        if ($request->has('clock_in_time') && $request->clock_in_time !== $attendance->clock_in_time) {
            $updatedData['clock_in_time'] = $request->clock_in_time;
        }

        if ($request->has('clock_out_time') && $request->clock_out_time !== $attendance->clock_out_time) {
            $updatedData['clock_out_time'] = $request->clock_out_time;
        }

        // 勤怠データの更新
        if (!empty($updatedData)) {
            $attendance->update($updatedData);
        }

        // 休憩時間の更新
        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restId => $restData) {
                // 中間テーブルを考慮して、該当の休憩データを取得
                $rest = $attendance->rests()->where('rests.id', $restId)->first();

                if ($rest) {
                    $updatedRestData = [];

                    if (!empty($restData['rest_in_time']) && $restData['rest_in_time'] !== $rest->rest_in_time) {
                        $updatedRestData['rest_in_time'] = $restData['rest_in_time'];
                    }

                    if (!empty($restData['rest_out_time']) && $restData['rest_out_time'] !== $rest->rest_out_time) {
                        $updatedRestData['rest_out_time'] = $restData['rest_out_time'];
                    }

                    if (!empty($updatedRestData)) {
                        $rest->update($updatedRestData);
                    }
                }
            }
        }
        return redirect('/admin/attendance/list');
    }

    // 勤怠承認
    public function attendanceRequestApprove(Request $request)
    {
        // 勤怠申請データを取得（status = 'pending' のもの）
        $attendanceRequest = AttendanceRequest::where('attendance_id', $request->input('id'))
        ->where('status', 'pending')
        ->firstOrFail();

        // 対応する勤怠データを取得
        $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);

        // 勤怠データの更新
        $attendance->update([
            'clock_in_time' => $attendanceRequest->requested_clock_in_time,
            'clock_out_time' => $attendanceRequest->requested_clock_out_time,
        ]);

        // 休憩データの更新
        $restRequests = $attendanceRequest->rests()->get(); // 中間テーブルを考慮して取得

        foreach ($restRequests as $restRequest) {
            // `attendance` に紐づく `rest` を中間テーブル経由で取得
            $rest = $attendance->rests()->where('rests.id', $restRequest->id)->first();

            if ($rest) {
                $rest->update([
                    'rest_in_time' => $restRequest->rest_in_time,
                    'rest_out_time' => $restRequest->rest_out_time,
                ]);
            }
        }

        // ステータスを `approved` に更新
        $attendanceRequest->update(['status' => 'approved']);

        return redirect('/stamp_correction_request/list?tab=approved');
    }

    // 勤怠承認画面の表示
    public function adminRequestView($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        $rests = $attendance->rests()->get();
        $user = $attendance->user;

        $name = $user->name;

        // 承認待ちの申請があるか確認
        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->first();

        if ($attendanceRequest) {
            $date = $attendanceRequest->requested_clock_date;
            $year = $attendanceRequest->requested_clock_date->format('Y') . '年';
            $monthDay = $attendanceRequest->requested_clock_date->format('n') . '月' . $attendanceRequest->requested_clock_date->format('j') . '日';
            $isPending = true;
            $status = $attendanceRequest->status;
        } else {
            $date = $attendance->date;
            $dateObj = new \DateTime($date);
            $year = $dateObj->format('Y') . '年';
            $monthDay = $dateObj->format('n') . '月' . $dateObj->format('j') . '日';  // X月X日
            $isPending = false;
            $status = 'approved';
        }

        return view('admin.attendanceApprove', compact('name', 'year', 'monthDay', 'attendance', 'rests', 'isPending', 'status', 'attendanceRequest'));
    }
}
