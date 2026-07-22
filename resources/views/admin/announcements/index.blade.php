@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Create Announcement</h2></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.announcements.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input id="title" type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="7" required maxlength="10000">{{ old('content') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Announcement</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Announcement History</h2>
                <a href="{{ route('admin.announcements.send-form') }}" class="btn btn-outline-primary btn-sm">Send to Learners</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Created By</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($announcements as $announcement)
                        <tr>
                            <td>{{ $announcements->firstItem() + $loop->index }}</td>
                            <td>{{ $announcement->title }}</td>
                            <td>{{ $announcement->sent_by }}</td>
                            <td>{{ $announcement->created_at->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                @if ($announcement->targets_count > 0)
                                    <form method="POST" action="{{ route('admin.announcements.resend', $announcement) }}" onsubmit="return confirm('Resend this announcement to its saved audience?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Resend</button>
                                    </form>
                                @else
                                    <span class="text-muted small">Not sent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No announcements found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">Showing {{ $announcements->firstItem() ?? 0 }}–{{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }}</small>
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
