@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendancedetail.css') }}">
@endsection

@section('page-move')
    <div class="header__button">
        <div class="header__button--attendance">
            @if(Auth::guard('admin')->check())
                <a href="/admin/attendance/list" class="goto">勤怠一覧</a>
            @else
                <a href="/attendance" class="goto">勤怠</a>
            @endif
        </div>
        <div class="header__button--attendance-list">
            @if(Auth::guard('admin')->check())
                <a href="/admin/staff/list" class="goto">スタッフ一覧</a>
            @else
                <a href="/attendance/list" class="goto">勤怠一覧</a>
            @endif
        </div>
        <div class="header__button--attendance-request">
            @if(Auth::guard('admin')->check())
                <a href="/stamp_correction_request/list" class="goto">申請一覧</a>
            @else
                <a href="/stamp_correction_request/list" class="goto">申請</a>
            @endif
        </div>
        @auth
            <!-- ログインしている場合 -->
            <div class="header__button--logout">
                @if(Auth::guard('admin')->check())
                    <form action="/admin/logout" class="logout-form" method="post">
                        @csrf
                        <button class="logout-button">ログアウト</button>
                    </form>
                @else
                    <form action="/logout" class="logout-form" method="post">
                        @csrf
                        <button class="logout-button">ログアウト</button>
                    </form>
                @endif
            </div>
        @endauth
    </div>
@endsection

@section('content')

<div class="container">
    <div class="attendance__detail--title">
        <h1>勤怠詳細</h1>
    </div>

    <form method="POST" action="{{ Auth::guard('admin')->check() ? url('/admin/attendance/update') : url('/attendance/request') }}">
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
                            @if((Auth::guard('admin')->check()))
                                <input type="text" class="notRequesting" name="clock_year" value="{{ $year }}">
                            @elseif($isPending)
                                <input type="text" class="requesting" name="clock_year" value="{{ $year }}" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_year" value="{{ $year }}">
                            @endif
                        </td>
                        <td>
                            @if((Auth::guard('admin')->check()))
                                <input type="text" class="notRequesting" name="clock_monthDay" value="{{ $monthDay }}">
                            @elseif($isPending)
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
                            @if((Auth::guard('admin')->check()))
                                <input type="text" class="notRequesting" name="clock_in_time" value="{{ old('clock_in_time', $attendance->formatted_clock_in_time) }}">
                            @elseif($isPending)
                                <input type="text" class="requesting" name="clock_in_time" value="@formatTime($attendanceRequest->requested_clock_in_time)" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_in_time" value="{{ old('clock_in_time', $attendance->formatted_clock_in_time) }}">
                            @endif
                        </td>
                        <td>～</td>
                        <td>
                            @if((Auth::guard('admin')->check()))
                                <input type="text" class="notRequesting" name="clock_out_time" value="{{ old('clock_out_time', $attendance->formatted_clock_out_time) }}">
                            @elseif($isPending)
                                <input type="text" class="requesting" name="clock_out_time" value="@formatTime($attendanceRequest->requested_clock_out_time)" readonly>
                            @else
                                <input type="text" class="notRequesting" name="clock_out_time" value="{{ old('clock_out_time', $attendance->formatted_clock_out_time) }}">
                            @endif
                        </td>
                    </tr>
                    @foreach($rests as $index => $rest)
                        <tr>
                            <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                            <td>
                                @if((Auth::guard('admin')->check()))
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_in_time]" value="{{ old('rests.' . $rest->id . '.rest_in_time', $rest->formatted_rest_in_time) }}">
                                @elseif($isPending)
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_in_time]" value="@formatTime($attendanceRequest->rests[$index]->rest_in_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_in_time]" value="{{ old('rests.' . $rest->id . '.rest_in_time', $rest->formatted_rest_in_time) }}">
                                @endif
                            </td>
                            <td>～</td>
                            <td>
                                @if((Auth::guard('admin')->check()))
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_out_time]" value="{{ old('rests.' . $rest->id . '.rest_out_time', $rest->formatted_rest_out_time) }}">
                                @elseif($isPending)
                                    <input type="text" class="requesting" name="rests[{{ $rest->id }}][rest_out_time]" value="@formatTime($attendanceRequest->rests[$index]->rest_out_time)" readonly>
                                @else
                                    <input type="text" class="notRequesting" name="rests[{{ $rest->id }}][rest_out_time]" value="{{ old('rests.' . $rest->id . '.rest_out_time', $rest->formatted_rest_out_time) }}">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            @if((Auth::guard('admin')->check()))
                                <textarea class="notRequesting" name="comment"></textarea>
                            @elseif($isPending)
                                <textarea class="requesting" name="comment" readonly>{{ ($attendanceRequest->comment) }}</textarea>
                            @else
                                <textarea class="notRequesting" name="comment"></textarea>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($errors->any())
            <div class="form__error">
                @foreach (array_unique($errors->all()) as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ボタンの表示 --}}
        <div class="attendance__request--button">
            @if((Auth::guard('admin')->check()))
                <button type="submit" class="btn btn-repair">修正</button>
            @elseif($isPending)
                <div class="pending-message">
                    <p>* 承認待ちのため修正はできません。</p>
                </div>
            @else
                <button type="submit" class="btn btn-repair">修正</button>
            @endif
        </div>
    </div>
</form>
@endsection

