@extends('puretec.layouts.app')

@section('title', 'Activity / Audit Log')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Activity / Audit Log</h1>
        <p class="text-muted">Track all system actions and changes</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#filterCollapse" role="button" aria-expanded="true">
        <h5 class="mb-0">
            <i class="bi bi-funnel me-2"></i>Filters
            <i class="bi bi-chevron-down float-end"></i>
        </h5>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body">
            <form action="{{ route('puretec.audit-log.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Action</label>
                    <select class="form-select" name="action">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Severity</label>
                    <select class="form-select" name="severity">
                        <option value="">All Severities</option>
                        <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info</option>
                        <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model Type</label>
                    <input type="text" class="form-control" name="model_type"
                           value="{{ request('model_type') }}"
                           placeholder="e.g. Tenant, User...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" name="date_from"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" name="date_to"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Store ID</label>
                    <input type="text" class="form-control" name="store_id"
                           value="{{ request('store_id') }}"
                           placeholder="Store ID...">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('puretec.audit-log.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Audit Log Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Log Entries</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Severity</th>
                        <th>Model Type</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="fw-medium">{{ $log->user->name ?? 'System' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>
                                @php
                                    $severityBadge = match(strtolower($log->severity)) {
                                        'info' => 'bg-info',
                                        'warning' => 'bg-warning text-dark',
                                        'critical' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $severityBadge }}">{{ ucfirst($log->severity) }}</span>
                            </td>
                            <td><small>{{ $log->model_type ?? '—' }}</small></td>
                            <td>
                                <small>{{ Str::limit($log->description, 80) }}</small>
                            </td>
                            <td><code class="text-muted">{{ $log->ip_address ?? '—' }}</code></td>
                            <td><small>{{ $log->created_at->format('M d, Y H:i:s') }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-journal text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No log entries found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
