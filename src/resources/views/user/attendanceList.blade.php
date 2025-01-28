@extends('layouts.app')

@section('page-move')
    <div class="header__button">
        <div class="header__button--attendance">
            <a href="/attendance" class="goto">勤怠</a>
        </div>
        <div class="header__button--attendance-list">
            <a href="/attendance/list" class="goto">勤怠一覧</a>
        </div>
        <div class="header__button--attendance-request">
            <a href="/stamp_correction_request/list" class="goto">申請</a>
        </div>
        @auth
            <!-- ログインしている場合 -->
            <div class="header__button--logout">
                <form action="/logout" class="logout-form" method="post">
                    @csrf
                    <button class="logout-button">ログアウト</button>
                </form>
            </div>
        @else
            <!-- ログインしていない場合 -->
            <div class="header__button--login">
                <a href="/login" class="login-button">ログイン</a>
            </div>
        @endauth
    </div>
@endsection

@section('content')
<div class="attendanceList">
    <h1>勤怠一覧</h1>

    <div class="attendanceList__selectMonth">
        <a href="{{ url('/attendance/list') }}?month={{ $prevMonth }}" class="btn btn-primary">前月</a>
        <span class="this-month">{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</span>
        <a href="{{ url('/attendance/list') }}?month={{ $nextMonth }}" class="btn btn-primary">後月</a>
    </div>

    <div class="attendanceList__content">
        <table border="1">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>@formatDate($attendance->date)</td>
                        <td>@formatTime($attendance->clock_in_time)</td>
                        <td>@formatTime($attendance->clock_out_time)</td>
                        <td>
                            @php
                                $totalRestTime = $restTimes[$attendance->id]->total_rest_time ?? 0; // 対応するデータがなければ 0
                                $hours = floor($totalRestTime / 60); // 時間部分
                                $minutes = $totalRestTime % 60; // 分部分
                            @endphp
                            {{ sprintf('%02d:%02d', $hours, $minutes) }}
                        </td>
                        <td>
                            @php
                                // 勤務時間の計算 (勤務開始 - 勤務終了)
                                $start = strtotime($attendance->clock_in_time);
                                $end = strtotime($attendance->clock_out_time);
                                $workTime = ($end - $start) / 60; // 分単位で勤務時間を計算

                                // 休憩時間を差し引いた勤務時間
                                $workTime -= $totalRestTime; // 休憩時間を引く

                                // 時間と分に変換
                                $workHours = floor($workTime / 60); // 勤務時間の時間部分
                                $workMinutes = $workTime % 60; // 勤務時間の分部分
                            @endphp
                            {{ sprintf('%02d:%02d', $workHours, $workMinutes) }}
                        </td>
                        <td>
                            <a href="">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endsection