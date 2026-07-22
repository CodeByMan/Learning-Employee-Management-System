@extends('layouts.admin')

@section('title', 'Send Announcement')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-1">Select an Audience</h2>
                <p class="text-muted small mb-0">Leave grade and section blank to send to all learners.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.announcements.process-send') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="announcement_id" class="form-label">Announcement</label>
                        <select id="announcement_id" name="announcement_id" class="form-select" required>
                            <option value="">Select an announcement</option>
                            @foreach ($announcements as $announcement)
                                <option value="{{ $announcement->id }}" @selected((string) old('announcement_id') === (string) $announcement->id)>{{ $announcement->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="grade_level" class="form-label">Grade Level</label>
                            <select id="grade_level" name="grade_level" class="form-select">
                                <option value="">All grades</option>
                                @foreach ($gradeLevels as $level)
                                    <option value="{{ $level }}" @selected(old('grade_level') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="section" class="form-label">Section</label>
                            <select id="section" name="section" class="form-select">
                                <option value="">All sections</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section }}" @selected(old('section') === $section)>{{ $section }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Announcement</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
