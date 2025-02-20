@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_verify.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="verify-message">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
    </div>
    <div class="verify-button">
        <a href="http://localhost:8025" class="btn btn-primary" target="_blank">認証はこちらから</a>
    </div>
    <!-- 認証メール再送信フォーム -->
    <div class="verify-resend">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-secondary">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection