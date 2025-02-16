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
<p>現在のガード: {{ Auth::getDefaultDriver() }}</p>

@if(Auth::guard('admin')->check())
    <p>管理者としてログイン中</p>
@elseif(Auth::guard('web')->check())
    <p>一般ユーザーとしてログイン中</p>
@else
    <p>未ログイン</p>
@endif

<div class="container">
    <div class="attendance__detail--title">
        <h1>勤怠詳細</h1>
    </div>

    <form method="POST" action="/admin/attendance/approve?id={{ $attendance->id }}">
    @csrf
        <input type="hidden" name="id" value="{{ $attendance->id }}">

        <div class="attendance__detail--table">
            <table>
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ $name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_year" value="{{ $year }}" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_year" value="{{ $year }}">
                            @endif
                        </td>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_monthDay" value="{{ $monthDay }}" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_monthDay" value="{{ $monthDay }}">
                            @endif
                        </td>
                    </tr>
                    <input type="hidden" name="date" value="{{ $attendance->date }}">

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_in_time" value="@formatTime($attendanceRequest->requested_clock_in_time)" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_in_time" value="@formatTime($attendance->clock_in_time)">
                            @endif
                        </td>
                        <td>～</td>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_out_time" value="@formatTime($attendanceRequest->requested_clock_out_time)" readonly>
                            @else
                                <input type="text" name="clock_out_time" value="@formatTime($attendance->clock_out_time)">
                            @endif
                        </td>
                    </tr>
                    @foreach($rests as $index => $rest)
                        <tr>
                            <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                            <td>
                                @if($isPending)
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_in_time]" value="@formatTime($attendanceRequest->rests[$index]->rest_in_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_in_time]" value="@formatTime($rest->rest_in_time)">
                                @endif
                            </td>
                            <td>～</td>
                            <td>
                                @if($isPending)
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_out_time]" value="@formatTime($attendanceRequest->rests[$index]->rest_out_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_out_time]" value="@formatTime($rest->rest_out_time)">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td>
                            @if($isPending)
                                <textarea class="requesting" name="comment" readonly>{{ ($attendanceRequest->comment) }}</textarea>
                            @else
                                <textarea class="notRequesting" name="comment"></textarea>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {{-- ボタンの表示 --}}
        <div class="attendance__request--button">
            @if($status === 'approved')
                <button type="submit" class="btn btn-success" disabled>承認済み</button>
            @else
                <button class="btn btn-secondary">承認</button>
            @endif
        </div>
    </div>
</form>
@endsection