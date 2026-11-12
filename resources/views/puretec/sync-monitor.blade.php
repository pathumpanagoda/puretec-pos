@extends('puretec.layouts.app')

@section('title', 'Sync Monitor')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Sync Monitor</h1>
        <p class="text-muted">Real-time sync status across all tenants</p>
    </div>
</div>

@php
    $failedTodayTotal = $tenants->sum(fn($t) => $t->sync_stats['failed_today'] ?? 0);
@endphp

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('puretec.sync-monitor.index') }}">
            <i class="bi bi-grid me-1"></i>Client Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $failedTodayTotal > 0 ? 'text-danger' : '' }}" href="{{ route('puretec.sync-monitor.failed') }}">
            <i class="bi bi-exclamation-triangle me-1"></i>Failed Sync Logs
            @if($failedTodayTotal > 0)
                <span class="badge bg-danger ms-1">{{ $failedTodayTotal }}</span>
            @endif
        </a>
    </li>
</ul>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $tenants->count() }}</h3>
                <p class="stat-label">Active Clients</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $tenants->sum(fn($t) => $t->sync_stats['total_today'] ?? 0) }}</h3>
                <p class="stat-label">Total Syncs Today</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $tenants->sum(fn($t) => $t->sync_stats['failed_today'] ?? 0) }}</h3>
                <p class="stat-label">Failed Today</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-phone"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $tenants->sum(fn($t) => $t->sync_stats['active_devices'] ?? 0) }}</h3>
                <p class="stat-label">Active Devices</p>
            </div>
        </div>
    </div>
</div>

<!-- Tenants Sync Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Tenant Sync Overview</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Status</th>
                        <th>Stores</th>
                        <th>Active Devices</th>
                        <th>Last Push</th>
                        <th>Last Pull</th>
                        <th>Failed Today</th>
                        <th>Total Today</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        @php
                            $stats = $tenant->sync_stats ?? [];
                            $failedToday = $stats['failed_today'] ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $tenant->business_name }}</span>
                                <br><small class="text-muted">{{ $tenant->code }}</small>
                            </td>
                            <td>
                                @if($tenant->is_active)
                                    <span class="badge bg-success">Active</span>
                                @elseif($tenant->is_locked)
                                    <span class="badge bg-danger">Locked</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $tenant->stores_count ?? $tenant->stores->count() }}</td>
                            <td>{{ $stats['active_devices'] ?? 0 }}</td>
                            <td>
                                @if(!empty($stats['last_push']))
                                    <small>{{ \Carbon\Carbon::parse($stats['last_push'])->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($stats['last_pull']))
                                    <small>{{ \Carbon\Carbon::parse($stats['last_pull'])->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="{{ $failedToday > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $failedToday }}
                                </span>
                            </td>
                            <td>{{ $stats['total_today'] ?? 0 }}</td>
                            <td class="text-end">
                                <a href="{{ route('puretec.sync-monitor.show', $tenant) }}"
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye me-1"></i>Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No tenants found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
