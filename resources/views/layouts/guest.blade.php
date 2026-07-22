<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Employee LEMS') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="guest-body {{ request()->routeIs('login') ? 'guest-login-page' : '' }}">
    <main class="auth-shell">
        <section class="auth-side">
            <a href="{{ url('/') }}" class="brand-logo">
                <span class="brand-mark">
                    <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }} logo" class="h-100 w-100">
                </span>
                <span class="brand-text">
                    <strong>{{ config('app.name') }}</strong>
                    <span>Learner &amp; Employee Management</span>
                </span>
            </a>

            <div class="soft-badge auth-side-badge"><i class="bi bi-shield-check"></i>Modern and secure workspace</div>
            <h1>Manage people and learning with clarity.</h1>
            <p>Access role-based accounts, learner records, QR attendance, announcements, and secure communication from one focused platform.</p>

            <div class="auth-points auth-points-compact">
                <div class="auth-point">
                    <i class="bi bi-qr-code-scan"></i>
                    <div>
                        <div class="fw-semibold">QR attendance</div>
                        <div class="small text-muted">Fast, accurate learner session tracking.</div>
                    </div>
                </div>
                <div class="auth-point">
                    <i class="bi bi-person-check"></i>
                    <div>
                        <div class="fw-semibold">Protected access</div>
                        <div class="small text-muted">Secure role-aware authentication flows.</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-card">
            {{ $slot }}
        </section>
    </main>
</body>
</html>
