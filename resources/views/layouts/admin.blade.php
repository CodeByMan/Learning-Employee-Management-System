<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('styles')
</head>
<body class="admin-body">
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<button type="button" class="sidebar-toggle admin-mobile-toggle" id="sidebarToggle" aria-label="Open navigation">
    <i class="bi bi-list fs-5"></i>
</button>

@php
    $user = auth()->user();
    $navGroups = [
        'Home Menu' => [
            ['route' => route('admin.dashboard'), 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2', 'active' => request()->routeIs('admin.dashboard')],
            ['route' => route('admin.users.index'), 'label' => 'Users', 'icon' => 'bi-people', 'active' => request()->routeIs('admin.users.*') || request()->routeIs('admin.register.*') || request()->routeIs('admin.otp.*')],
            ['route' => route('admin.learners.index'), 'label' => 'Learners', 'icon' => 'bi-mortarboard', 'active' => request()->routeIs('admin.learners.*')],
        ],
        'All Menu' => [
            ['route' => route('admin.attendance.index'), 'label' => 'Attendance', 'icon' => 'bi-qr-code-scan', 'active' => request()->routeIs('admin.attendance.*')],
            ['route' => route('admin.announcements.index'), 'label' => 'Announcements', 'icon' => 'bi-megaphone', 'active' => request()->routeIs('admin.announcements.index')],
            ['route' => route('admin.announcements.send-form'), 'label' => 'Send Announcement', 'icon' => 'bi-send', 'active' => request()->routeIs('admin.announcements.send-form')],
            ['route' => route('admin.announcements.logs'), 'label' => 'Announcement Logs', 'icon' => 'bi-journal-text', 'active' => request()->routeIs('admin.announcements.logs')],
            ['route' => route('admin.custom-email.form'), 'label' => 'Custom Email', 'icon' => 'bi-envelope-paper', 'active' => request()->routeIs('admin.custom-email.*')],
            ['route' => route('admin.email-logs.index'), 'label' => 'Email Logs', 'icon' => 'bi-envelope-check', 'active' => request()->routeIs('admin.email-logs.*')],
        ],
    ];
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
    <a href="{{ route('admin.dashboard') }}" class="brand-logo">
        <span class="brand-mark">
            <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }} logo">
        </span>
        <span class="brand-text">
            <strong>{{ config('app.name') }}</strong>
            <span>Admin workspace</span>
        </span>
    </a>

    @foreach ($navGroups as $section => $items)
        <div class="sidebar-section-label">{{ $section }}</div>
        <nav class="nav flex-column">
            @foreach ($items as $item)
                <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['route'] }}">
                    <span class="nav-copy">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </span>
                    <span class="nav-trailing">+</span>
                </a>
            @endforeach
        </nav>
    @endforeach
</aside>

<section class="admin-content">
    <header class="admin-topbar">
        <div class="topbar-search">
            <i class="bi bi-search"></i>
            <input id="adminNavSearch" type="search" class="form-control" placeholder="Search menu..." aria-label="Search dashboard menu">
        </div>

        <div class="topbar-actions">
            <a href="{{ route('admin.announcements.logs') }}" class="topbar-icon-btn" aria-label="Notifications">
                <i class="bi bi-bell"></i>
            </a>

            <a href="{{ route('admin.email-logs.index') }}" class="topbar-icon-btn" aria-label="Messages">
                <i class="bi bi-envelope"></i>
            </a>

            <div class="dropdown">
                <button class="profile-pill" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="profile-copy text-start">
                        <span class="profile-name">{{ $user->name }}</span>
                        <span class="profile-role d-none d-xl-block">{{ $user->getRoleNames()->first() }}</span>
                    </span>
                    <i class="bi bi-chevron-down profile-chevron"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><span class="dropdown-item-text small text-muted">{{ $user->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="admin-page-content">
        @if ($errors->any())
            <div class="alert alert-danger mb-4" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="admin-footer text-center small text-muted">
        © {{ date('Y') }} {{ config('app.name') }} · Muhammad Ali Nawaz · Karachi, Pakistan
    </footer>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const adminNavSearch = document.getElementById('adminNavSearch');

    adminNavSearch?.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        document.querySelectorAll('.admin-sidebar .nav-link').forEach((link) => {
            const matches = link.textContent.toLowerCase().includes(query);
            link.classList.toggle('d-none', query !== '' && !matches);
        });

        document.querySelectorAll('.admin-sidebar .sidebar-section-label').forEach((label) => {
            const nav = label.nextElementSibling;
            const hasVisibleLink = nav && Array.from(nav.querySelectorAll('.nav-link')).some((link) => !link.classList.contains('d-none'));
            label.classList.toggle('d-none', query !== '' && !hasVisibleLink);
        });
    });

    sidebarToggle?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    sidebarBackdrop?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

    @if (session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: @json(session('success')), timer: 3000, showConfirmButton: false });
    @elseif (session('warning'))
        Swal.fire({ icon: 'warning', title: 'Attention', text: @json(session('warning')) });
    @elseif (session('error'))
        Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
    @endif
</script>
@stack('scripts')
</body>
</html>
