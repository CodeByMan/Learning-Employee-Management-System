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
</head>
<body>
    <div class="app-shell">
        @include('layouts.navigation')

        <main class="app-content-wrap flex-grow-1">
            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @isset($header)
                <div class="member-hero mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="soft-badge mb-3"><i class="bi bi-stars"></i>Secure account workspace</div>
                            <div class="fw-bold fs-2 text-dark">{{ $header }}</div>
                            <p class="text-muted mb-0 mt-2">Update your personal information, password, and account settings in a clean profile workspace.</p>
                        </div>
                        <div class="status-chip">
                            <i class="bi bi-person-badge"></i>
                            {{ auth()->user()->getRoleNames()->first() ?? 'Member' }}
                        </div>
                    </div>
                </div>
            @endisset

            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        <footer class="text-center text-muted small py-4">
            © {{ date('Y') }} {{ config('app.name') }} · Muhammad Ali Nawaz · Karachi, Pakistan
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
