@extends('layouts.admin')

@section('title', 'Verify Registration Code')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Enter Registration Code</h2></div>
            <div class="card-body">
                @if (session('otpSent'))
                    <div class="alert alert-success">{{ session('otpSent') }}</div>
                @endif
                <p class="text-muted">The six-digit code expires after 10 minutes. Five invalid attempts cancel the registration.</p>
                <form method="POST" action="{{ route('admin.otp.verify.submit') }}" id="otpForm">
                    @csrf
                    <div class="mb-3">
                        <label for="otp" class="form-label">Registration Code</label>
                        <input id="otp" type="text" name="otp" class="form-control form-control-lg text-center" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.register.form') }}" class="btn btn-light">Start Over</a>
                        <button type="submit" class="btn btn-primary flex-grow-1" id="verifyButton">Verify and Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('otpForm')?.addEventListener('submit', function () {
        const button = document.getElementById('verifyButton');
        button.disabled = true;
        button.textContent = 'Verifying...';
    });
</script>
@endpush
