<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $navbarCssVersion = filemtime(public_path('css/navbar.css'));
        $sidebarCssVersion = filemtime(public_path('css/sidebar.css'));
        $dashboardCssVersion = filemtime(public_path('css/dashboard-admin.css'));
        $sidebarJsVersion = filemtime(public_path('js/sidebar.js'));
        $materialIconMapJsVersion = filemtime(public_path('js/material-icon-map.js'));
        $statusMessage = match (session('status')) {
            'profile-updated' => __('ui.layout.status.profile_updated'),
            'password-updated' => __('ui.layout.status.password_updated'),
            default => session('status'),
        };
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('ui.layout.default_title'))</title>
    <link rel="stylesheet" href="{{ asset('css/material-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css').'?v='.$navbarCssVersion }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css').'?v='.$sidebarCssVersion }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css').'?v='.$dashboardCssVersion }}">
</head>
<body class="mg-admin-body">
    <div class="dashboard-layout">
        @include('components.sidebar')

        <div class="page">
            @include('components.navbar')

            <main class="panel admin-panel">
                @if ($statusMessage || session('error') || $errors->any())
                    <div class="alerts">
                        @if ($statusMessage)
                            <div class="alert alert--success">{{ $statusMessage }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert--danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert--danger">
                                <strong>{{ __('ui.layout.alerts.save_error_title') }}</strong>
                                <span>{{ __('ui.layout.alerts.save_error_body') }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js').'?v='.$sidebarJsVersion }}" defer></script>
    <script src="{{ asset('js/material-icon-map.js').'?v='.$materialIconMapJsVersion }}" defer></script>
    <script src="{{ asset('js/navbar.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
