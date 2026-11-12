@extends('puretec.layouts.app')

@section('title', 'Sync Monitor - ' . $tenant->business_name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('puretec.sync-monitor.index') }}">Sync Monitor</a></li>
                <li class="breadcrumb-item active">{{ $tenant->business_name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $tenant->business_name }}</h1>
        <p class="text-muted">Sync details and device activity</p>
    </div>
    <a href="{{ route('puretec.sync-monitor.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['total_syncs'] ?? 0) }}</h3>
                <p class="stat-label">Total Syncs</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['success_today'] ?? 0) }}</h3>
                <p class="stat-label">Success Today</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['failed_today'] ?? 0) }}</h3>
                <p class="stat-label">Failed Today</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-files"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['duplicate_today'] ?? 0) }}</h3>
                <p class="stat-label">Duplicate Today</p>
            </div>
        </div>
    </div>
</div>

<!-- Registered Devices -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>Registered Devices</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Type</th>
                        <th>User</th>
                        <th>Last Seen</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td class="fw-medium">{{ $device->device_name }}</td>
                            <td>
                                @php
                                    $typeBadge = match(strtolower($device->device_type)) {
                                        'mobile', 'android', 'ios' => 'bg-info',
                                        'tablet' => 'bg-primary',
                                        'desktop', 'pc' => 'bg-secondary',
                                        default => 'bg-dark',
                                    };
                                @endphp
                                <span class="badge {{ $typeBadge }}">{{ $device->device_type }}</span>
                            </td>
                            <td>{{ $device->user->name ?? '—' }}</td>
                            <td>
                                @if($device->last_seen_at)
                                    <small title="{{ $device->last_seen_at }}">
                                        {{ \Carbon\Carbon::parse($device->last_seen_at)->diffForHumans() }}
                                    </small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($device->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-phone text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No devices registered</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Sync Logs -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Sync Logs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Direction</th>
                        <th>Entity Type</th>
                        <th>Status</th>
                        <th>Error Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td>
                                @if(strtolower($log->direction ?? '') === 'push')
                                    <span class="badge bg-primary"><i class="bi bi-arrow-up me-1"></i>Push</span>
                                @else
                                    <span class="badge bg-info"><i class="bi bi-arrow-down me-1"></i>Pull</span>
                                @endif
                            </td>
                            <td>{{ $log->entity_type }}</td>
                            <td>
                                @php
                                    $statusBadge = match(strtolower($log->status)) {
                                        'success' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        'duplicate' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ ucfirst($log->status) }}</span>
                            </td>
                            <td>
                                @if($log->error_message)
                                    <small class="text-danger">{{ Str::limit($log->error_message, 80) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small>{{ $log->created_at->format('M d, Y H:i:s') }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-journal text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No sync logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($recentLogs->hasPages())
        <div class="card-footer">
            {{ $recentLogs->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Failed Syncs -->
@if($failedLogs->isNotEmpty())
<div class="card mb-4">
    <div class="card-header bg-danger bg-opacity-10">
        <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed Syncs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Direction</th>
                        <th>Entity Type</th>
                        <th>Error Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedLogs as $log)
                        <tr>
                            <td>
                                @if(strtolower($log->direction ?? '') === 'push')
                                    <span class="badge bg-primary"><i class="bi bi-arrow-up me-1"></i>Push</span>
                                @else
                                    <span class="badge bg-info"><i class="bi bi-arrow-down me-1"></i>Pull</span>
                                @endif
                            </td>
                            <td>{{ $log->entity_type }}</td>
                            <td>
                                <div class="text-danger">
                                    <small>{{ $log->error_message }}</small>
                                </div>
                            </td>
                            <td><small>{{ $log->created_at->format('M d, Y H:i:s') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
