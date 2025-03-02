<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceRequestForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRequestAttendanceController extends Controller
{
    // 勤怠詳細の表示
    public function userDetailView($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        $rests = $attendance->rests()->get();
        $user = $attendance->user;
        $name = $user->name;

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
            $monthDay = $dateObj->format('n') . '月' . $dateObj->format('j') . '日';
            $isPending = false;
        }

        return view('common.attendanceDetail', compact('name','year','monthDay','attendance','rests', 'isPending', 'attendanceRequest'));
    }

    // 勤怠申請
    public function attendanceRequest(AttendanceRequestForm $request)
    {
        $user = auth()->user();
        $date = $request->input('date');
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->firstOrFail();

        $clockYear = str_replace('年', '', $request->input('clock_year'));
        $clockMonthDay = str_replace(['月', '日'], ['-', ''], $request->input('clock_monthDay'));
        $clockDate = Carbon::createFromFormat('Y-n-j', $clockYear . '-' . $clockMonthDay)->format('Y-m-d');
        $clockInTime = $request->input('clock_in_time') ?? $attendance->clock_in_time;
        $clockOutTime = $request->input('clock_out_time') ?? $attendance->clock_out_time;

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_date' => $clockDate,
            'status' => 'pending',
            'requested_clock_in_time' => $clockInTime,
            'requested_clock_out_time' => $clockOutTime,
            'comment' => $request->input('comment'),
        ]);

        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restData) {
                $rest = Rest::create([
                    'rest_in_time' => $restData['rest_in_time'],
                    'rest_out_time' => $restData['rest_out_time'],
                ]);
                $rests[] = $rest; // 作成した rest を配列に追加
            }

            $originalRests = DB::table('attendance_rest')
                ->where('attendance_id', $attendance->id)
                ->pluck('rest_id');

            foreach ($rests as $rest) {
                // `$originalRests` から最初の `rest_id` を選択
                $originalRest = $originalRests->shift(); // `pluck` で取得したコレクションから1つずつ取得する
                $attendanceRequest->rests()->attach($rest->id, [
                    'original_rest_id' => $originalRest ? $originalRest : null, // 取得できなければ null
                ]);
            }
        }
        return redirect('/stamp_correction_request/list');
    }

    // 勤怠申請一覧の表示
    public function requestView(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'pending');

        if((Auth::guard('admin')->check())) {
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