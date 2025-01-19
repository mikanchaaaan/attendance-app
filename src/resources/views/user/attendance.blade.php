@extends('layouts.app')

@section('page-move')
    <div class="header__button">
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