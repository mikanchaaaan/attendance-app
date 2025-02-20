@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_staffattendance.css') }}">
@endsection

@section('page-move')
    <div class="header__button">
        <div class="header__button--attendance">
            <a href="/admin/attendance/list" class="goto">勤怠一覧</a>
        </div>
        <div class="header__button--attendance-list">
            <a href="/admin/staff/list" class="goto">スタッフ一覧</a>
        </div>
        <div class="header__button--attendance-request">
            <a href="/stamp_correction_request/list" class="goto">申請一覧</a>
        </div>
        <div class="header__button--logout">
            <form action="/admin/logout" class="logout-form" method="post">
            @csrf
                <button class="logout-button">ログアウト</button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="container">
        <div class="staffList__title">
            <h1>{{ $user->name }}さんの勤怠</h1>
        </div>

        <div class="staffList__selectMonth">
            <div class="staffList__selectMonth--last">
                <img src="{{ asset('img/arrow.png')}}" alt="arrow_left" class="arrow-left">
                <a href="{{ url('/admin/attendance/staff/' . $user->id) }}?month={{ $prevMonth }}" class="btn btn-primary">前月</a>
            </div>
            <div class="staffList__selectMonth--date">
                <img src="{{asset('img/calendar.png') }}" alt="calendar" class="calendar-img">
                <span class="this-month">{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</span>
            </div>
            <div class="staffList__selectMonth--next">
                <a href="{{ url('/admin/attendance/staff/' . $user->id) }}?month={{ $nextMonth }}" class="btn btn-primary">後月</a>
                <img src="{{ asset('img/arrow.png')}}" alt="arrow_right" class="arrow-right">
            </div>
        </div>

        <div class="staffAttendance__content">
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
                    @foreach ($dates as $date)
                        <tr>
                            <td>@formatDate($date['date'])</td>
                            <td>@formatTime($date['clock_in_time'])</td>
                            <td>@formatTime($date['clock_out_time'])</td>
                            <td>
                                @php
                                    // 初期値を設定（対応するデータがない場合や勤務未完了時に備える）
                                    $totalRestTime = $restTimes[$date['date']]['total_rest_time'] ?? 0;
                                @endphp

                                @if ($date['clock_out_time'])
                                    {{ $date['rest_time'] }}
                                @else
                                    - <!-- 勤務未完了時の表示 -->
                                @endif
                            </td>

                            <td>
                                @if ($date['clock_out_time'])
                                    {{ $date['work_time'] }}
                                @else
                                    - <!-- 退勤していない場合は表示しない -->
                                @endif
                            </td>
                            <td>
                                @if($date['attendance_id'])
                                    <a href="/attendance/{{ $date['attendance_id'] }}">詳細</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="csv-report__button">
            <a href="/admin/attendance/export/{{ $user->id }}?month={{ $currentMonth }}">
                <button>CSV出力</button>
            </a>
        </div>
    </div>
@endsection