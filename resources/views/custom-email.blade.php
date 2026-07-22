@extends('layouts.admin')

@section('title', 'Custom Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-1">Send a Custom Email</h2>
                <p class="text-muted small mb-0">Messages are sent as escaped plain text to prevent unsafe HTML injection.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.custom-email.send') }}" id="customEmailForm">
                    @csrf
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input id="subject" type="text" name="subject" class="form-control" value="{{ old('subject') }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Message</label>
                        <textarea id="content" name="content" class="form-control" rows="8" required maxlength="10000">{{ old('content') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Recipients</label>
                            <label class="small"><input type="checkbox" id="selectAll"> Select all</label>
                        </div>
                        <div class="border rounded p-2 bg-light" style="max-height: 320px; overflow-y: auto;">
                            @foreach ($users as $user)
                                <label class="d-flex align-items-center gap-2 bg-white border rounded p-2 mb-2">
                                    <input type="checkbox" class="recipient-checkbox" name="recipients[]" value="{{ $user->id }}" @checked(in_array($user->id, old('recipients', [])))>
                                    <span><strong>{{ $user->name }}</strong><br><small class="text-muted">{{ $user->email }}</small></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="sendButton">
                        <i class="bi bi-send me-1"></i>Send Email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.recipient-checkbox').forEach((checkbox) => checkbox.checked = this.checked);
    });
    document.getElementById('customEmailForm')?.addEventListener('submit', function () {
        const button = document.getElementById('sendButton');
        button.disabled = true;
        button.textContent = 'Sending...';
    });
</script>
@endpush
