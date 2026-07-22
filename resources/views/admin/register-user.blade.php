@extends('layouts.admin')

@section('title', 'Register User')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-1">Register a Managed User</h2>
                <p class="text-muted small mb-0">A one-time code will be sent to the supplied email before the account is created.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.register.user') }}" id="registerUserForm">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Temporary Password</label>
                            <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
                            <div class="form-text">At least 8 characters with letters and numbers.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Select a role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="registerButton">Send Registration Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('registerUserForm')?.addEventListener('submit', function () {
        const button = document.getElementById('registerButton');
        button.disabled = true;
        button.textContent = 'Sending code...';
    });
</script>
@endpush
