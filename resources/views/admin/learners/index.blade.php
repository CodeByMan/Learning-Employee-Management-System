@extends('layouts.admin')

@section('title', 'Learner Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Learners</h1>
        <p class="text-muted mb-0">Manage learner profiles and QR attendance identifiers.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLearnerModal">
        <i class="bi bi-person-plus me-1"></i>Add Learner
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Grade</th>
                <th>Section</th>
                <th>QR Code</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($learners as $learner)
                <tr>
                    <td>{{ $learners->firstItem() + $loop->index }}</td>
                    <td>{{ trim("{$learner->fname} {$learner->mname} {$learner->lname}") }}</td>
                    <td>{{ $learner->email }}</td>
                    <td>{{ $learner->grade_level }}</td>
                    <td>{{ $learner->section }}</td>
                    <td><code>{{ $learner->qr_code }}</code></td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editLearner{{ $learner->id }}" aria-label="Edit {{ $learner->fname }} {{ $learner->lname }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.learners.destroy', $learner) }}" class="d-inline" onsubmit="return confirm('Delete this learner and attendance history?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Delete {{ $learner->fname }} {{ $learner->lname }}"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No learners found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">Showing {{ $learners->firstItem() ?? 0 }}–{{ $learners->lastItem() ?? 0 }} of {{ $learners->total() }}</small>
        {{ $learners->links() }}
    </div>
</div>

<div class="modal fade" id="addLearnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.learners.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5">Add Learner</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.learners.partials.fields', ['learner' => null])
                    <div class="form-text mt-2">A unique QR attendance code is generated automatically.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Learner</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($learners as $learner)
    <div class="modal fade" id="editLearner{{ $learner->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.learners.update', $learner) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="modal-title h5">Edit Learner</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.learners.partials.fields', ['learner' => $learner])
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
