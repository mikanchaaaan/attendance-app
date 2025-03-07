<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <title>coachtech_attendance</title>
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                @if(Auth::guard('admin')->check())
                    <a href="/admin/attendance/list" class="header__logo--inner">
                        <img src="{{ asset('img/logo.svg') }}" alt="ロゴ">
                    </a>
                @else
                    <a href="/attendance" class="header__logo--inner">
                        <img src="{{ asset('img/logo.svg') }}" alt="ロゴ">
                    </a>
                @endif
            </div>
            @yield('page-move')
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>