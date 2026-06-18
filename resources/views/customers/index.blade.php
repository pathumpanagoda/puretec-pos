@extends('layouts.app')

@section('title', 'Customers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Customers</h1>
        <p class="page-subtitle">Manage your customer database</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('customers.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Customer
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $customers->total() }}</div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="stat-value">{{ $customers->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-teal">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($customers->sum('total_purchases'), 0) }}</div>
            <div class="stat-label">Total Purchases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-star"></i></div>
            <div class="stat-value">{{ number_format($customers->sum('loyalty_points'), 0) }}</div>
            <div class="stat-label">Total Loyalty Points</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-5">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Group</label>
                <select name="group" class="form-select">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="{{ route('customers.index') }}" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Customers Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-people"></i> All Customers</h5>
        <span class="text-muted">{{ $customers->total() }} customers</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Group</th>
                    <th>Total Purchases</th>
                    <th>Loyalty Points</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600">{{ $customer->name }}</div>
                                @if($customer->city)
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $customer->city }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($customer->phone)
                            <div><i class="bi bi-telephone text-muted me-1"></i> {{ $customer->phone }}</div>
                        @endif
                        @if($customer->email)
                            <small class="text-muted"><i class="bi bi-envelope me-1"></i> {{ $customer->email }}</small>
                        @endif
                    </td>
                    <td>
                        @if($customer->group)
                            <span class="status-badge status-refunded">{{ $customer->group->name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-600">Rs. {{ number_format($customer->total_purchases, 2) }}</span>
                        <br><small class="text-muted">{{ $customer->total_orders }} orders</small>
                    </td>
                    <td>
                        <span class="fw-600 text-primary">{{ number_format($customer->loyalty_points) }}</span>
                        @if($customer->loyalty_tier)
                            <br><small class="text-muted">{{ ucfirst($customer->loyalty_tier) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($customer->is_active)
                            <span class="status-badge status-completed">Active</span>
                        @else
                            <span class="status-badge status-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('customers.show', $customer) }}" class="action-btn" title="View Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this customer?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-people"></i>
                            <p>No customers found</p>
                            <a href="{{ route('customers.create') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Customer
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-body">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
