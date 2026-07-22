@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-page-head">
        <div>
            <div class="soft-badge mb-3"><i class="bi bi-stars"></i>Hello, Admin</div>
            <div class="page-title">Optimize the whole system.</div>
            <p class="page-copy">Track people, attendance, communication, and learner activity from a cleaner administrative dashboard.</p>
        </div>
        <div class="admin-toolbar">
            <a href="{{ route('admin.learners.index') }}" class="btn btn-light btn-pill"><i class="bi bi-mortarboard me-1"></i>View Learners</a>
            <a href="{{ route('admin.register.form') }}" class="btn btn-primary btn-pill"><i class="bi bi-person-plus me-1"></i>New Admission</a>
        </div>
    </div>

    @php
        $cards = [
            ['label' => 'Total Users', 'value' => $userCount, 'icon' => 'bi-people', 'foot' => 'All registered accounts', 'badge' => '+Current'],
            ['label' => 'Total Learners', 'value' => $learnerCount, 'icon' => 'bi-mortarboard', 'foot' => 'Active learner records', 'badge' => '+Directory'],
            ['label' => 'Total Employees', 'value' => $employeeCount, 'icon' => 'bi-person-badge', 'foot' => 'Role-protected employee access', 'badge' => '+Managed'],
            ['label' => 'Email Logs', 'value' => $mailLogCount, 'icon' => 'bi-envelope-paper', 'foot' => 'Tracked email activity', 'badge' => '+History'],
            ['label' => 'Announcements', 'value' => $announcementCount, 'icon' => 'bi-megaphone', 'foot' => 'Saved communication notices', 'badge' => '+Content'],
            ['label' => 'Attendance Records', 'value' => $attendanceCount, 'icon' => 'bi-clipboard2-check', 'foot' => 'Daily learner attendance entries', 'badge' => '+Records'],
        ];
    @endphp

    <div class="row g-4 mb-4">
        @foreach ($cards as $card)
            <div class="col-sm-6 col-xxl-4">
                <div class="metric-card h-100">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <span class="icon-wrap"><i class="bi {{ $card['icon'] }}"></i></span>
                        <a href="#" class="topbar-icon-btn" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></a>
                    </div>
                    <div class="metric-label">{{ $card['label'] }}</div>
                    <div class="metric-value">{{ number_format($card['value']) }}</div>
                    <div class="metric-foot">
                        <span class="metric-badge">{{ $card['badge'] }}</span>
                        <span>{{ $card['foot'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="section-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h2 class="section-title fs-4 mb-1">People overview</h2>
                        <p class="section-subtitle">Current learner and employee distribution.</p>
                    </div>
                    <span class="status-chip"><i class="bi bi-people"></i>{{ number_format($learnerCount + $employeeCount) }} total</span>
                </div>
                <canvas id="peopleChart" aria-label="Learner and employee distribution" role="img" style="max-height: 320px;"></canvas>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="section-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <div>
                        <h2 class="section-title fs-4 mb-1">Communication records</h2>
                        <p class="section-subtitle">Compare email log activity against saved announcement records.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="status-chip"><i class="bi bi-envelope"></i>{{ number_format($mailLogCount) }} email logs</span>
                        <span class="status-chip"><i class="bi bi-megaphone"></i>{{ number_format($announcementCount) }} announcements</span>
                    </div>
                </div>
                <canvas id="communicationChart" aria-label="Email and announcement record counts" role="img" style="max-height: 320px;"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        new Chart(document.getElementById('peopleChart'), {
            type: 'doughnut',
            data: {
                labels: ['Learners', 'Employees'],
                datasets: [{
                    data: @json([$learnerCount, $employeeCount]),
                    backgroundColor: ['#2f8f4f', '#d5e8d1'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 6,
                    hoverOffset: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 10, color: '#4d5c51', font: { size: 13 } }
                    }
                }
            }
        });

        new Chart(document.getElementById('communicationChart'), {
            type: 'bar',
            data: {
                labels: ['Email Logs', 'Announcements'],
                datasets: [{
                    label: 'Records',
                    data: @json([$mailLogCount, $announcementCount]),
                    backgroundColor: ['#2f8f4f', '#9ccf95'],
                    borderRadius: 14,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#7f8d83' },
                        grid: { color: 'rgba(17, 117, 61, 0.08)' }
                    },
                    x: {
                        ticks: { color: '#4d5c51' },
                        grid: { display: false }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
@endpush
