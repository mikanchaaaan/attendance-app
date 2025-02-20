@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendancelist.css') }}">
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
    <div class="attendanceList__title">
        <h1>{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="attendanceList__selectMonth">
        <div class="attendanceList__selectMonth--last">
            <img src="{{ asset('img/arrow.png')}}" alt="arrow_left" class="arrow-left">
            <a href="{{ url('/admin/attendance/list') }}?date={{ $prevDate }}" class="btn btn-primary">前日</a>
        </div>
        <div class="attendanceList__selectMonth--date">
            <img src="{{asset('img/calendar.png') }}" alt="calendar" class="calendar-img">
            <span class="today">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</span>
        </div>
        <div class="attendanceList__selectMonth--next">
            <a href="{{ url('/admin/attendance/list') }}?date={{ $nextDate }}" class="btn btn-primary">翌日</a>
            <img src="{{ asset('img/arrow.png')}}" alt="arrow_right" class="arrow-right">
        </div>
    </div>

    <div class="attendanceList__content">
        <table border="1">
            <thead>
                <tr>
                    <th>名前</th>
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
                        <td>{{ $attendance->user?->name ?? '未登録' }}</td>
                        <td>@formatTime($attendance->clock_in_time)</td>
                        <td>@formatTime($attendance->clock_out_time)</td>
                        <td>
                            @if($attendance->clock_out_time)
                            {{ $rest_times[$attendance->id] }}
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($attendance->clock_out_time)
                            {{ $work_times[$attendance->id] }}
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($attendance->clock_out_time)
                                <a href="/attendance/{{ $attendance->id }}">詳細</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection