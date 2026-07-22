<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Employee LEMS') }} | Learner and Employee Management</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    <main class="landing-hero">
        <a href="{{ url('/') }}" class="landing-brand" aria-label="{{ config('app.name') }} home">
            <span class="landing-brand-logo">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }} logo">
            </span>
            <span class="landing-brand-copy">
                <strong>{{ config('app.name') }}</strong>
                <span>Learner &amp; Employee Management</span>
            </span>
        </a>

        <div class="landing-hero-content">
            <div class="hero-chip landing-eyebrow">
                <i class="bi bi-lightning-charge"></i>
                Your all-in-one learner and employee platform
            </div>

            <h1 class="hero-title">Power Up Your<br>Learning Universe</h1>

            <p class="hero-description">
                A modern, secure platform that unifies learner and employee management, registration, QR attendance,
                announcements, and everyday administration for faster, more efficient operations.
            </p>

            <div class="hero-actions justify-content-center">
                <a href="{{ route('login') }}" class="btn btn-primary btn-pill px-5 py-3">Login</a>
                <a href="{{ route('register') }}" class="btn btn-light btn-pill px-5 py-3">
                    Create Learner Account <i class="bi bi-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
