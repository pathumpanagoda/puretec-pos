@extends('nexfloit.layouts.app')

@section('title', 'Tenants')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Tenants</h1>
        <p class="text-muted">Manage all tenant accounts</p>
    </div>
    <a href="{{ route('nexfloit.tenants.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Tenant
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('nexfloit.tenants.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search"
                       value="{{ request('search') }}"
                       placeholder="Business name, email, or code...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>On Trial</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Plan</label>
                <select class="form-select" name="plan">
                    <option value="">All Plans</option>
                    <option value="basic" {{ request('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="standard" {{ request('plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="premium" {{ request('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tenants Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Business</th>
                        <th>Owner</th>
                        <th>Plan</th>
                        <th>Monthly Fee</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td><code>{{ $tenant->code }}</code></td>
                            <td>
                                <a href="{{ route('nexfloit.tenants.show', $tenant) }}" class="fw-medium">
                                    {{ $tenant->business_name }}
                                </a>
                                <br><small class="text-muted">{{ $tenant->email }}</small>
                            </td>
                            <td>{{ $tenant->owner_name }}</td>
                            <td>
                                <span class="badge {{ $tenant->plan_badge['class'] }}">
                                    @if($tenant->is_locked)
                                        <i class="bi bi-lock-fill me-1"></i>
                                    @elseif(!$tenant->is_active)
                                        <i class="bi bi-pause-circle me-1"></i>
                                    @elseif($tenant->isOnTrial())
                                        <i class="bi bi-hourglass-split me-1"></i>
                                    @endif
                                    {{ $tenant->plan_badge['label'] }}
                                </span>
                            </td>
                            <td>Rs. {{ number_format($tenant->monthly_fee, 2) }}</td>
                            <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('nexfloit.tenants.show', $tenant) }}"
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('nexfloit.tenants.edit', $tenant) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($tenant->is_locked)
                                        <form action="{{ route('nexfloit.tenants.unlock', $tenant) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Unlock">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Lock"
                                                data-bs-toggle="modal" data-bs-target="#lockModal{{ $tenant->id }}">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Lock Modal -->
                                <div class="modal fade" id="lockModal{{ $tenant->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('nexfloit.tenants.lock', $tenant) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Lock Tenant</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to lock <strong>{{ $tenant->business_name }}</strong>?</p>
                                                    <p class="text-muted">This will prevent the tenant from accessing their POS system.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason</label>
                                                        <input type="text" class="form-control" name="reason"
                                                               value="Payment overdue" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Lock Tenant</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No tenants found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tenants->hasPages())
        <div class="card-footer">
            {{ $tenants->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
