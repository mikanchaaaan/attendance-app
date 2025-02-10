@extends('layouts.app')

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

    <form method="POST" action="
        @if($isPending && (Auth::guard('admin')->check()))
            /admin/attendance/approve?id={{ $attendance->id }}
        @elseif(!$isPending && (Auth::guard('admin')->check()))
            /admin/attendance/update
        @else
            /attendance/request
        @endif
    ">
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
            @if($isPending && (Auth::guard('admin')->check()))
                <button type="submit" class="btn btn-success">承認</button>
            @elseif($attendanceRequest && $attendanceRequest->status === 'approved')
            <!-- 承認済みの場合 -->
                <button class="btn btn-secondary" disabled>承認済み</button>
            @elseif($isPending)
                <div class="pending-message">
                    <p>* 承認待ちのため修正はできません。</p>
                </div>
            @elseif((Auth::guard('admin')->check()))
                <button type="submit" class="btn btn-primary">修正</button>
            @else
                <button type="submit" class="btn btn-warning">申請</button>
            @endif
        </div>
    </div>
</form>
@endsection

