@extends('layouts.admin')

@section('title', 'Email Logs')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white"><h1 class="h5 mb-0">Email Audit Log</h1></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>No.</th>
                <th>User</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $logs->firstItem() + $loop->index }}</td>
                    <td>{{ $log->user?->name ?? 'Deleted user' }}</td>
                    <td>{{ $log->email }}</td>
                    <td>{{ $log->subject }}</td>
                    <td>
                        <span class="badge {{ $log->is_sent ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $log->is_sent ? 'Sent' : 'Failed' }}
                        </span>
                        @if ($log->error_message)
                            <span class="d-block small text-muted mt-1">Delivery error recorded</span>
                        @endif
                    </td>
                    <td>{{ $log->sent_at?->format('M d, Y h:i A') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No email logs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}</small>
        {{ $logs->links() }}
    </div>
</div>
@endsection
