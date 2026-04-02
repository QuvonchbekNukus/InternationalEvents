<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($typographyCssVersion = filemtime(public_path('css/typography.css')))
    @php($themeCssVersion = filemtime(public_path('css/theme.css')))
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.auth.login.page_title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/typography.css').'?v='.$typographyCssVersion }}">
    @vite(['resources/css/auth/login.css', 'resources/js/auth/login.js'])
    <link rel="stylesheet" href="{{ asset('css/theme.css').'?v='.$themeCssVersion }}">
</head>
<body class="login-page auth-login-page">
    <div class="auth-login-page__backdrop" aria-hidden="true">
        <img class="auth-login-page__backdrop-image" src="{{ asset('design/login.png') }}" alt="">
    </div>

    <main class="auth-login-page__shell">
        <section class="auth-login-card" aria-label="{{ __('ui.auth.login.form_aria') }}">
            <header class="auth-login-card__header">
                <div class="auth-login-card__crest-shell">
                    <div class="auth-login-card__crest">
                        <img src="{{ asset('logo/mgv.png') }}" alt="{{ __('ui.auth.login.brand_alt') }}">
                    </div>
                </div>

                <p class="auth-login-card__eyebrow">{{ __('ui.auth.login.organization_country') }}</p>
                <h1 class="auth-login-card__brand-title">{{ __('ui.auth.login.organization_agency') }}</h1>
                <p class="auth-login-card__brand-subtitle">{{ __('ui.auth.login.department') }}</p>
            </header>

            <div class="auth-login-card__body">
                <div class="auth-login-card__intro">
                    <h2 class="auth-login-card__title">{{ __('ui.auth.login.heading') }}</h2>
                </div>

                @if (session('status'))
                    <div class="auth-login-alert auth-login-alert--success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-login-alert auth-login-alert--error" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="auth-login-form" method="POST" action="{{ route('login') }}" data-login-form>
                    @csrf

                    <div class="auth-login-field {{ $errors->has('phone') ? 'has-error' : '' }}">
                        <label class="auth-login-field__label" for="phone">{{ __('ui.auth.login.phone') }}</label>
                        <div class="auth-login-input-shell">
                            <svg class="auth-login-input-shell__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 4h4l2 5-2.5 1.5a14 14 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2.2 2A16 16 0 0 1 3 6.2 2 2 0 0 1 5 4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input
                                class="auth-login-input-shell__input"
                                id="phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                placeholder="{{ __('ui.auth.login.phone_placeholder') }}"
                                autocomplete="tel"
                                inputmode="tel"
                                aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('phone') ? 'phone-error' : '' }}"
                                required
                            >
                        </div>
                        @error('phone')
                            <div class="auth-login-field__error" id="phone-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-login-field {{ $errors->has('password') ? 'has-error' : '' }}">
                        <label class="auth-login-field__label" for="password">{{ __('ui.auth.login.password') }}</label>
                        <div class="auth-login-input-shell">
                            <svg class="auth-login-input-shell__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="11" width="16" height="9" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8 11V8a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <input
                                class="auth-login-input-shell__input"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="{{ __('ui.auth.login.password_placeholder') }}"
                                autocomplete="current-password"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                                data-password-input
                                required
                            >
                            <button
                                type="button"
                                id="togglePassword"
                                class="auth-login-password-toggle"
                                aria-label="{{ __('ui.auth.login.show_password') }}"
                                aria-controls="password"
                                aria-pressed="false"
                                data-password-toggle
                            >
                                <svg class="auth-login-password-toggle__icon" data-eye-open viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12s3.8-6 10-6 10 6 10 6-3.8 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="1.7"/>
                                    <circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/>
                                </svg>
                                <svg class="auth-login-password-toggle__icon auth-login-password-toggle__icon--hidden" data-eye-closed viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M9.5 5.6A11 11 0 0 1 12 5.3c6.2 0 10 6 10 6a16.4 16.4 0 0 1-3.4 4.2M6 8.2C3.8 10.1 2 12 2 12s3.8 6 10 6c1 0 2-.1 2.9-.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="auth-login-field__error" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-login-form__meta">
                        <label class="auth-login-remember" for="remember">
                            <input
                                class="auth-login-remember__input"
                                id="remember"
                                type="checkbox"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="auth-login-remember__box" aria-hidden="true">
                                <svg viewBox="0 0 16 16" fill="none">
                                    <path d="M3.5 8.5 6.5 11.5 12.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="auth-login-remember__label">{{ __('ui.auth.login.remember') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="auth-login-submit" data-submit-button>
                        <span class="auth-login-submit__content">
                            <span class="auth-login-submit__label">{{ __('ui.auth.login.submit') }}</span>
                            <span class="auth-login-submit__spinner" aria-hidden="true"></span>
                        </span>
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
