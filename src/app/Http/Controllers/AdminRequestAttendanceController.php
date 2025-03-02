<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceRequestForm;
use Illuminate\Support\Facades\DB;

class AdminRequestAttendanceController extends Controller
{
    // 勤怠修正（管理者用）
    public function adminRequestUpdate(AttendanceRequestForm $request)
    {
        $attendance = Attendance::findOrFail($request->input('id'));

        $updatedData = [];

        if ($request->has('clock_in_time') && $request->clock_in_time !== $attendance->clock_in_time) {
            $updatedData['clock_in_time'] = $request->clock_in_time;
        }

        if ($request->has('clock_out_time') && $request->clock_out_time !== $attendance->clock_out_time) {
            $updatedData['clock_out_time'] = $request->clock_out_time;
        }

        if (!empty($updatedData)) {
            $attendance->update($updatedData);
        }

        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restId => $restData) {
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
        $attendanceRequest = AttendanceRequest::where('attendance_id', $request->input('id'))
        ->where('status', 'pending')
        ->firstOrFail();

        $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);

        $attendance->update([
            'clock_in_time' => $attendanceRequest->requested_clock_in_time,
            'clock_out_time' => $attendanceRequest->requested_clock_out_time,
        ]);

        $attendanceRequestRests = DB::table('attendance_request_rest')
            ->where('attendance_request_id', $attendanceRequest->id)
            ->get();

        foreach ($attendanceRequestRests as $attendanceRequestRest) {
            $originalRest = Rest::find($attendanceRequestRest->original_rest_id);

            if ($originalRest) {
                $rest = Rest::find($attendanceRequestRest->rest_id);
                $originalRest->update([
                    'rest_in_time' => $rest->rest_in_time,
                    'rest_out_time' => $rest->rest_out_time,
                    ]);
            }
        }
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

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)
        ->orderByDesc('updated_at')
        ->first();

        if ($attendanceRequest) {
            $date = $attendanceRequest->requested_clock_date;
            $year = $attendanceRequest->requested_clock_date->format('Y') . '年';
            $monthDay = $attendanceRequest->requested_clock_date->format('n') . '月' . $attendanceRequest->requested_clock_date->format('j') . '日';
            $isPending = true;
            $status = $attendanceRequest->status;
            $comment = $attendanceRequest->comment;
        } else {
            $date = $attendance->date;
            $dateObj = new \DateTime($date);
            $year = $dateObj->format('Y') . '年';
            $monthDay = $dateObj->format('n') . '月' . $dateObj->format('j') . '日';
            $isPending = false;
            $status = 'approved';
        }

        return view('admin.attendanceApprove', compact('name', 'year', 'monthDay', 'attendance', 'rests', 'isPending', 'status', 'attendanceRequest', 'comment'));
    }
}
