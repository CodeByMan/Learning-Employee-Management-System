@php
    $role = Auth::user()->getRoleNames()->first();
    $dashboardRoute = route('dashboard');
@endphp

<nav x-data="{ open: false }" class="role-nav">
    <div class="px-4 py-3 px-lg-4">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <a href="{{ $dashboardRoute }}" class="brand-logo">
                <span class="brand-mark">
                    <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }} logo" class="h-100 w-100">
                </span>
                <span class="brand-text">
                    <strong>{{ config('app.name') }}</strong>
                    <span>{{ ucfirst($role ?? 'Member') }} dashboard</span>
                </span>
            </a>

            <div class="d-none d-md-flex align-items-center gap-2 flex-wrap">
                <a href="{{ $dashboardRoute }}" class="marketing-link {{ request()->routeIs('dashboard') ? 'bg-white shadow-sm text-success' : '' }}">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="marketing-link {{ request()->routeIs('profile.edit') ? 'bg-white shadow-sm text-success' : '' }}">Profile</a>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2">
                <span class="status-chip">
                    <i class="bi bi-person-circle"></i>
                    {{ Auth::user()->name }} · {{ ucfirst($role ?? 'Member') }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light btn-pill">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>

            <button @click="open = ! open" class="sidebar-toggle d-inline-flex d-md-none" type="button" aria-label="Toggle navigation">
                <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
        </div>

        <div x-show="open" x-transition class="d-md-none mt-3" style="display: none;">
            <div class="d-grid gap-2">
                <a href="{{ $dashboardRoute }}" class="btn btn-light text-start">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-light text-start">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 text-start">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
