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
<div class="container">
    <div class="attendance__detail--title">
        <h1>勤怠詳細</h1>
    </div>

    <div class="attendance__detail--table">
        <table>
            <tbody>
                <tr>
                    <th>名前</th>
                    <td></td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td></td>
                    <td>～</td>
                    <td></td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td></td>
                    <td>～</td>
                    <td></td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

