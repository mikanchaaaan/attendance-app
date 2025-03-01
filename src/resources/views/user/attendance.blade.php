@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance.css') }}">
@endsection

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
        <div class="header__button--logout">
            <form action="/logout" class="logout-form" method="post">
                @csrf
                <button class="logout-button">ログアウト</button>
            </form>
        </div>
    </div>
@endsection

@section('content')
<div class="attendance__content">
    <div class="attendance__content--status">
        <p>{{ $status }}</p>
    </div>
    <div class="attendance__content--date">
        <p>{{ $today }}</p>
    </div>
    <div class="attendance__content--currentTime">
        <p>{{ $currentTime }}</p>
    </div>

    <div class="attendance__content--button">
        @if ($showCheckInButton && !session('status_message'))
            <form method="POST" action="/attendance/clockIn">
            @csrf
                <button type="submit" class="btn btn-clock_in">出勤</button>
            </form>
        @endif

        @if ($showCheckOutButton)
            <form method="POST" action="/attendance/clockOut">
            @csrf
                <button type="submit" class="btn btn-clock_out">退勤</button>
            </form>
        @endif

        @if ($showRestInButton)
            <form method="POST" action="/attendance/restIn">
            @csrf
                <button type="submit" class="btn btn-rest_in">休憩入</button>
            </form>
        @endif

        @if ($showRestOutButton)
            <form method="POST" action="/attendance/restOut">
                @csrf
                <button type="submit" class="btn btn-rest_out">休憩戻</button>
            </form>
        @endif

        @if(session('status_message'))
            <div class="clockOut-message">
            {{ session('status_message') }}
            </div>
        @endif
    </div>
</div>
@endsection