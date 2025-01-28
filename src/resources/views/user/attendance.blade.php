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
                <button type="submit" class="btn btn-primary">出勤</button>
            </form>
        @endif

        @if ($showCheckOutButton)
            <form method="POST" action="/attendance/clockOut">
            @csrf
                <button type="submit" class="btn btn-danger">退勤</button>
            </form>
        @endif

        @if ($showRestInButton)
            <form method="POST" action="/attendance/restIn">
            @csrf
                <button type="submit" class="btn btn-warning">休憩入</button>
            </form>
        @endif

        @if ($showRestOutButton)
            <form method="POST" action="/attendance/restOut">
                @csrf
                <button type="submit" class="btn btn-success">休憩戻</button>
            </form>
        @endif

        @if(session('status_message'))
            <div class="alert alert-info">
            {{ session('status_message') }}
            </div>
        @endif
    </div>
</div>
@endsection