@extends('layouts.app')

@section('content')
    <div class="member-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="soft-badge mb-3"><i class="bi bi-mortarboard"></i>Learner workspace</span>
                <h1 class="section-title mb-2">Welcome, {{ auth()->user()->name }}</h1>
                <p class="section-subtitle">Your learner account is active. Learner records and attendance scanning are managed by authorized administrators.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-pill"><i class="bi bi-pencil-square me-1"></i>Edit Profile</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="section-card h-100 text-center">
                <span class="feature-icon mx-auto"><i class="bi bi-person-circle"></i></span>
                <h2 class="h4 mt-3 mb-2">Account profile</h2>
                <p class="text-muted mb-4">Review your learner account details and keep your password secure from one place.</p>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-pill px-4">Open Profile</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-card h-100 text-center">
                <span class="feature-icon mx-auto"><i class="bi bi-qr-code-scan"></i></span>
                <h2 class="h4 mt-3 mb-2">QR attendance</h2>
                <p class="text-muted mb-0">Your unique QR identifier is stored in the learner directory and is scanned by an administrator during attendance sessions.</p>
            </div>
        </div>
    </div>
@endsection
