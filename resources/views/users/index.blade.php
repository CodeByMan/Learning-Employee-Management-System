@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Registered Users</h1>
        <p class="text-muted mb-0">Manage account details and assigned roles.</p>
    </div>
    <a href="{{ route('admin.register.form') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Register User
    </a>
</div>

<form id="welcomeEmailForm" method="POST" action="{{ route('admin.users.send-welcome-email') }}">
    @csrf
</form>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold">User Directory</span>
        <button type="submit" form="welcomeEmailForm" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-envelope me-1"></i>Send Welcome Email
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th><input type="checkbox" id="selectAll" aria-label="Select all users"></th>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $managedUser)
                <tr>
                    <td>
                        <input type="checkbox" form="welcomeEmailForm" name="recipients[]" value="{{ $managedUser->id }}" class="recipient-checkbox" aria-label="Select {{ $managedUser->name }}">
                    </td>
                    <td>{{ $users->firstItem() + $loop->index }}</td>
                    <td>{{ $managedUser->name }}</td>
                    <td>{{ $managedUser->email }}</td>
                    <td><span class="badge text-bg-secondary text-uppercase">{{ $managedUser->getRoleNames()->first() ?? 'Unassigned' }}</span></td>
                    <td>{{ $managedUser->created_at->format('M d, Y') }}</td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editUser{{ $managedUser->id }}" aria-label="Edit {{ $managedUser->name }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @unless (auth()->user()->is($managedUser))
                            <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Delete {{ $managedUser->name }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</small>
        {{ $users->links() }}
    </div>
</div>

@foreach ($users as $managedUser)
    <div class="modal fade" id="editUser{{ $managedUser->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="modal-title h5">Edit User</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="name{{ $managedUser->id }}">Name</label>
                            <input class="form-control" id="name{{ $managedUser->id }}" name="name" value="{{ $managedUser->name }}" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email{{ $managedUser->id }}">Email</label>
                            <input class="form-control" id="email{{ $managedUser->id }}" type="email" name="email" value="{{ $managedUser->email }}" required maxlength="255">
                        </div>
                        <div>
                            <label class="form-label" for="role{{ $managedUser->id }}">Role</label>
                            <select class="form-select" id="role{{ $managedUser->id }}" name="role" required @disabled(auth()->user()->is($managedUser))>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected($managedUser->hasRole($role))>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @if (auth()->user()->is($managedUser))
                                <input type="hidden" name="role" value="{{ $managedUser->getRoleNames()->first() }}">
                                <div class="form-text">Your own role can only be changed from another administrator account.</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.recipient-checkbox').forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endpush
