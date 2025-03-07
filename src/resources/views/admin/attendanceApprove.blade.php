@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendanceapprove.css') }}">
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
    <!-- ログインしている場合 -->
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
                        <td colspan="3">
                            {{ $name }}
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td colspan="2">
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_year" value="{{ $year }}" readonly>
                            @else
                                <input type="text" class="requesting" name="clock_year" value="{{ $year }}" readonly>
                            @endif
                        </td>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_monthDay" value="{{ $monthDay }}" readonly>
                            @else
                                <input type="text" class="requesting" name="clock_monthDay" value="{{ $monthDay }}" readonly>
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
                                <input type="text" class="requesting" name="clock_in_time" value="@formatTime($attendance->clock_in_time)" readonly>
                            @endif
                        </td>
                        <td>～</td>
                        <td>
                            @if($isPending)
                                <input type="text" class="requesting" name="clock_out_time" value="@formatTime($attendanceRequest->requested_clock_out_time)" readonly>
                            @else
                                <input type="text" class="requesting" name="clock_out_time" value="@formatTime($attendance->clock_out_time)" readonly>
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
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_in_time]" value="@formatTime($rest->rest_in_time)" readonly>
                                @endif
                            </td>
                            <td>～</td>
                            <td>
                                @if($isPending)
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_out_time]" value="@formatTime($attendanceRequest->rests[$index]->rest_out_time)" readonly>
                                @else
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_out_time]" value="@formatTime($rest->rest_out_time)" readonly>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            @if($isPending)
                                <textarea class="requesting" name="comment" readonly>{{ ($attendanceRequest->comment) }}</textarea>
                            @else
                                <textarea class="requesting" name="comment" readonly>{{ ($attendanceRequest->comment) }}</textarea>
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