@extends('puretec.layouts.app')

@section('title', 'Failed Synchronizations')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Failed Synchronization Monitoring</h1>
        <p class="text-muted">Inspect and track all synchronization errors and conflicts across all client deployments</p>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('puretec.sync-monitor.index') }}">
            <i class="bi bi-grid me-1"></i>Client Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active bg-danger" href="{{ route('puretec.sync-monitor.failed') }}">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>Failed Sync Logs
            @if(($stats['failed_today'] ?? 0) > 0)
                <span class="badge bg-white text-danger ms-1">{{ $stats['failed_today'] }}</span>
            @endif
        </a>
    </li>
</ul>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="bi bi-x-octagon"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['total_failed'] ?? 0) }}</h3>
                <p class="stat-label">Total Failed Syncs</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-circle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['failed_today'] ?? 0) }}</h3>
                <p class="stat-label">Failed Today</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-shuffle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['conflict_total'] ?? 0) }}</h3>
                <p class="stat-label">Multi-Device Conflicts</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ number_format($stats['affected_tenants'] ?? 0) }}</h3>
                <p class="stat-label">Affected Clients</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('puretec.sync-monitor.failed') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Client</label>
                <select class="form-select" name="tenant_id">
                    <option value="">All Clients</option>
                    @foreach($tenants as $id => $name)
                        <option value="{{ $id }}" {{ request('tenant_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Entity Type</label>
                <select class="form-select" name="entity_type">
                    <option value="">All Entities</option>
                    @foreach($entityTypes as $type)
                        <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('puretec.sync-monitor.failed') }}" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Failed Sync Logs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed Sync Logs</h5>
        <span class="badge bg-secondary">{{ $failedLogs->total() }} entries</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Direction</th>
                        <th>Entity</th>
                        <th>Device / User</th>
                        <th>Error Message</th>
                        <th>Timestamp</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedLogs as $log)
                        <tr>
                            <td>
                                @if($log->tenant)
                                    <strong>{{ $log->tenant->business_name }}</strong>
                                    <br><small class="text-muted">{{ $log->tenant->code }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(strtolower($log->direction ?? '') === 'push')
                                    <span class="badge bg-primary"><i class="bi bi-arrow-up me-1"></i>Push</span>
                                @else
                                    <span class="badge bg-info"><i class="bi bi-arrow-down me-1"></i>Pull</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $log->entity_type }}</span>
                                @if($log->entity_uuid)
                                    <br><small class="text-muted"><code>{{ substr($log->entity_uuid, 0, 8) }}...</code></small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $log->user->name ?? 'Background' }}</div>
                                @if($log->device_id)
                                    <small class="text-muted">Dev: <code>{{ substr($log->device_id, 0, 10) }}</code></small>
                                @endif
                            </td>
                            <td>
                                <div class="text-danger small fw-semibold" style="max-width: 380px; word-break: break-word;">
                                    {{ $log->error_message ?? 'Unknown sync processing error' }}
                                </div>
                                @if(!empty($log->payload_summary))
                                    <small class="text-muted d-block mt-1">Payload: {{ json_encode($log->payload_summary) }}</small>
                                @endif
                            </td>
                            <td>
                                <small title="{{ $log->created_at }}">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                    <br><span class="text-muted">{{ $log->created_at->diffForHumans() }}</span>
                                </small>
                            </td>
                            <td class="text-end">
                                @if($log->tenant_id)
                                    <a href="{{ route('puretec.sync-monitor.show', $log->tenant_id) }}" class="btn btn-sm btn-outline-primary" title="View Client Sync Details">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                                <h6 class="mt-3 text-muted">No Failed Sync Logs Found</h6>
                                <p class="text-muted small mb-0">All synchronizations are processing normally without error.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($failedLogs->hasPages())
        <div class="card-footer">
            {{ $failedLogs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

