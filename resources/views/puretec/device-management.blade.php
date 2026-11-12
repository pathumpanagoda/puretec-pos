@extends('puretec.layouts.app')

@section('title', 'Device Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Device Management</h1>
        <p class="text-muted">Manage registered POS devices across all tenants</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('puretec.devices.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Tenant</label>
                <select class="form-select" name="tenant_id">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Apply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Devices Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>Registered Devices</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Device ID</th>
                        <th>Type</th>
                        <th>User</th>
                        <th>Store</th>
                        <th>Last Seen</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td class="fw-medium">{{ $device->device_name }}</td>
                            <td><code class="text-muted">{{ $device->device_id }}</code></td>
                            <td>
                                @php
                                    $typeBadge = match(strtolower($device->device_type ?? '')) {
                                        'mobile', 'android', 'ios' => 'bg-info',
                                        'tablet' => 'bg-primary',
                                        'desktop', 'pc' => 'bg-secondary',
                                        default => 'bg-dark',
                                    };
                                @endphp
                                <span class="badge {{ $typeBadge }}">{{ $device->device_type }}</span>
                            </td>
                            <td>{{ $device->user->name ?? '—' }}</td>
                            <td>{{ $device->store->name ?? '—' }}</td>
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
                                    <span class="badge bg-danger">Revoked</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($device->is_active)
                                    <form action="{{ route('puretec.devices.revoke', $device) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to revoke this device? It will no longer be able to sync.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke Device">
                                            <i class="bi bi-shield-x me-1"></i>Revoke
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Revoked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-phone text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No devices found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($devices->hasPages())
        <div class="card-footer">
            {{ $devices->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
