<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $sidebarCssVersion = filemtime(public_path('css/sidebar.css'));
        $sidebarJsVersion = filemtime(public_path('js/sidebar.js'));
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/material-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css').'?v='.$sidebarCssVersion }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}">
</head>
<body class="mg-admin-body">
    <div class="dashboard-layout">
        @include('components.sidebar')

        <div class="page">
            @include('components.navbar')

            <main class="panel admin-panel">
                @if (session('status') || session('error') || $errors->any())
                    <div class="alerts">
                        @if (session('status'))
                            <div class="alert alert--success">{{ session('status') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert--danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert--danger">
                                <strong>Ma'lumotlarni saqlashda xatolik bor.</strong>
                                <span>Maydonlarni tekshirib, qayta urinib ko'ring.</span>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js').'?v='.$sidebarJsVersion }}" defer></script>
    <script src="{{ asset('js/navbar.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
