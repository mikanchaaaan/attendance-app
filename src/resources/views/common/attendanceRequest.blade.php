@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendancerequest.css') }}">
@endsection

@section('page-move')
    <div class="header__button">
        <div class="header__button--attendance">
            @if(Auth::guard('admin')->check())
                <a href="/admin/attendance/list" class="goto">勤怠一覧</a>
            @else
                <a href="/attendance" class="goto">勤怠</a>
            @endif
        </div>
        <div class="header__button--attendance-list">
            @if(Auth::guard('admin')->check())
                <a href="/admin/staff/list" class="goto">スタッフ一覧</a>
            @else
                <a href="/attendance/list" class="goto">勤怠一覧</a>
            @endif
        </div>
        <div class="header__button--attendance-request">
            @if(Auth::guard('admin')->check())
                <a href="/stamp_correction_request/list" class="goto">申請一覧</a>
            @else
                <a href="/stamp_correction_request/list" class="goto">申請</a>
            @endif
        </div>
        @auth
            <div class="header__button--logout">
                @if(Auth::guard('admin')->check())
                    <form action="/admin/logout" class="logout-form" method="post">
                        @csrf
                        <button class="logout-button">ログアウト</button>
                    </form>
                @else
                    <form action="/logout" class="logout-form" method="post">
                        @csrf
                        <button class="logout-button">ログアウト</button>
                    </form>
                @endif
            </div>
        @endauth
    </div>
@endsection

@section('content')

<div class="container">
    <div class="attendanceList__title">
        <h1>申請一覧</h1>
    </div>

    <div class="attendanceRequest__tab">
        <a href="/stamp_correction_request/list?tab=pending" class="attendance__tab--pending {{ $tab == 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="/stamp_correction_request/list?tab=approved" class="attendance__tab--approved {{ $tab == 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <div class="attendanceRequest__content">
        <table border="1">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceRequests as $attendanceRequest)
                    <tr>
                        <td>
                            @if ($attendanceRequest->status == 'pending')
                                承認待ち
                            @elseif ($attendanceRequest->status == 'approved')
                                承認済み
                            @endif
                        </td>
                        <td>{{ $attendanceRequest->user->name }}</td>
                        <td>{{ $attendanceRequest->requested_clock_date->format('Y/m/d') }}</td>
                        <td>{{ $attendanceRequest->comment }}</td>
                        <td>{{ $attendanceRequest->created_at->format('Y/m/d') }}</td>
                        <td>
                            @if(Auth::guard('admin')->check())
                                <a href="/stamp_correction_request/approve/{{$attendanceRequest['attendance_id']}}">詳細</a>
                            @else
                                <a href="/attendance/{{ $attendanceRequest['attendance_id'] }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection