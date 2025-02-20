@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_stafflist.css') }}">
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
        <h1>スタッフ一覧</h1>
    </div>

    <div class="staffList__content">
        <table border="1">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection