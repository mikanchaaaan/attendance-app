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

@endsection