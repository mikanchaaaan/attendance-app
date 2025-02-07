@extends('layouts.app')

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
        @auth
            <!-- ログインしている場合 -->
            <div class="header__button--logout">
                <form action="/admin/logout" class="logout-form" method="post">
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

    <div class="attendanceList__title">
        <h1>{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="attendanceList__selectMonth">
        <!-- 日付選択 & ナビゲーション -->
        <a href="{{ url('/admin/attendance/list') }}?date={{ $prevDate }}" class="btn btn-primary">←前日</a>
        <p>{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</p>
        <a href="{{ url('/admin/attendance/list') }}?date={{ $nextDate }}" class="btn btn-primary">翌日→</a>
    </div>

    <div class="adminAttendanceList__content">
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

@endsection