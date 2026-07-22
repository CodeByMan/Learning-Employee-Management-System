@extends('layouts.admin')

@section('title', 'Attendance')

@push('head')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Manual Attendance</h2></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.attendance.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="learner_id" class="form-label">Learner</label>
                        <select id="learner_id" name="learner_id" class="form-select" required>
                            <option value="">Select a learner</option>
                            @foreach ($learners as $learner)
                                <option value="{{ $learner->id }}">{{ $learner->lname }}, {{ $learner->fname }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('admin.attendance.partials.sessions')
                    <button type="submit" class="btn btn-primary mt-3">Record Attendance</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><h2 class="h5 mb-0">QR Scanner</h2></div>
            <div class="card-body">
                <div id="qr-reader" class="mb-3"></div>
                <div id="scanStatus" class="alert alert-secondary mb-0">Choose a session, then scan a learner QR code.</div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Today's Records</h2>
                <span class="text-muted small">{{ \Carbon\Carbon::parse($today)->format('F j, Y') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Learner</th>
                        <th>AM In</th>
                        <th>AM Out</th>
                        <th>PM In</th>
                        <th>PM Out</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendances->firstItem() + $loop->index }}</td>
                            <td>{{ $attendance->learner?->lname }}, {{ $attendance->learner?->fname }}</td>
                            <td>{{ $attendance->am_in ? \Carbon\Carbon::parse($attendance->am_in)->format('h:i A') : '—' }}</td>
                            <td>{{ $attendance->am_out ? \Carbon\Carbon::parse($attendance->am_out)->format('h:i A') : '—' }}</td>
                            <td>{{ $attendance->pm_in ? \Carbon\Carbon::parse($attendance->pm_in)->format('h:i A') : '—' }}</td>
                            <td>{{ $attendance->pm_out ? \Carbon\Carbon::parse($attendance->pm_out)->format('h:i A') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No attendance records for today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">Showing {{ $attendances->firstItem() ?? 0 }}–{{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }}</small>
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let scanInProgress = false;
    const statusBox = document.getElementById('scanStatus');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function submitScannedAttendance(qrCode) {
        if (scanInProgress) return;
        scanInProgress = true;

        const session = document.querySelector('input[name="session"]:checked')?.value;
        statusBox.className = 'alert alert-info mb-0';
        statusBox.textContent = 'Processing QR code...';

        try {
            const response = await fetch(@json(route('admin.attendance.store')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ qr_code: qrCode, session }),
            });
            const data = await response.json();

            statusBox.className = data.status === 'success' ? 'alert alert-success mb-0' : 'alert alert-warning mb-0';
            statusBox.textContent = data.message ?? 'Unable to record attendance.';

            if (data.status === 'success') {
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch (error) {
            statusBox.className = 'alert alert-danger mb-0';
            statusBox.textContent = 'Attendance could not be submitted. Please try again.';
        } finally {
            setTimeout(() => { scanInProgress = false; }, 1500);
        }
    }

    if (window.Html5QrcodeScanner) {
        const scanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: { width: 220, height: 220 } }, false);
        scanner.render(submitScannedAttendance, () => {});
    } else {
        statusBox.className = 'alert alert-warning mb-0';
        statusBox.textContent = 'The QR scanner library could not be loaded. Manual attendance remains available.';
    }
</script>
@endpush
