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

    <form action="/attendance/request/" method="post">
        @csrf
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
                                    <input type="text" class="requesting" name="clock_in_time" value="@formatTime($attendanceRequest->rests[$index]->rest_in_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rest_in_time[]" value="@formatTime($rest->rest_in_time)">
                                @endif
                            </td>
                            <td>～</td>
                            <td>
                                @if($isPending)
                                    <input type="text" class="requesting" name="clock_out_time" value="@formatTime($attendanceRequest->rests[$index]->rest_out_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rest_out_time[]" value="@formatTime($rest->rest_out_time)">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td>
                            @if($isPending)
                                <textarea class="requesting" name="comment" value="" readonly></textarea>
                            @else
                                <textarea class="notRequesting" name="comment" value="" ></textarea>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($isPending)
            <div class="pending-message">
                <p>* 承認待ちのため修正はできません。</p>
            </div>
        @else
            <div class="attendance__request--button">
                <button>修正</button>
            </div>
        @endif
    </div>
</form>
@endsection

