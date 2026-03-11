<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.auth.login.page_title') }}</title>
    @vite(['resources/css/auth/login.css', 'resources/js/auth/login.js'])
</head>
<body class="login-page" data-login-background="{{ asset('design/login.png') }}">
    <div class="login-container">
        <div class="auth-wrapper">
            <div class="brand">
                <img src="{{ asset('logo/mgv.png') }}" alt="{{ __('ui.auth.login.brand_alt') }}">
                <h1>{{ __('ui.auth.login.organization_country') }}<br>{{ __('ui.auth.login.organization_agency') }}</h1>
                <p>{{ __('ui.auth.login.department') }}</p>
            </div>

            <section class="card" aria-label="{{ __('ui.auth.login.form_aria') }}">
                <h2>{{ __('ui.auth.login.heading') }}</h2>

                @if ($errors->any())
                    <div class="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="phone">{{ __('ui.auth.login.phone') }}</label>
                        <div class="input-wrap">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 4h4l2 5-2.5 1.5a14 14 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2.2 2A16 16 0 0 1 3 6.2 2 2 0 0 1 5 4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                placeholder="{{ __('ui.auth.login.phone_placeholder') }}"
                                autocomplete="tel"
                                required
                            >
                        </div>
                        @error('phone')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">{{ __('ui.auth.login.password') }}</label>
                        <div class="input-wrap">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="11" width="16" height="9" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8 11V8a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="{{ __('ui.auth.login.password_placeholder') }}"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" id="togglePassword" class="toggle-password" aria-label="{{ __('ui.auth.login.show_password') }}" aria-pressed="false">
                                <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12s3.8-6 10-6 10 6 10 6-3.8 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="1.7"/>
                                    <circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/>
                                </svg>
                                <svg id="eyeClosed" class="is-hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M9.5 5.6A11 11 0 0 1 12 5.3c6.2 0 10 6 10 6a16.4 16.4 0 0 1-3.4 4.2M6 8.2C3.8 10.1 2 12 2 12s3.8 6 10 6c1 0 2-.1 2.9-.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="remember" for="remember">
                        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>{{ __('ui.auth.login.remember') }}</span>
                    </label>

                    <button type="submit" class="submit-btn">{{ mb_strtoupper(__('ui.auth.login.submit')) }}</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
