@extends('layouts.app')

@section('title', 'Suppliers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Suppliers</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Suppliers</h1>
        <p class="page-subtitle">Manage your product suppliers</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('suppliers.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Supplier
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-truck"></i></div>
            <div class="stat-value">{{ $suppliers->total() }}</div>
            <div class="stat-label">Total Suppliers</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value">{{ $suppliers->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active Suppliers</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($suppliers->sum('current_balance'), 0) }}</div>
            <div class="stat-label">Total Balance Due</div>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by supplier name, company, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Suppliers Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-truck"></i> All Suppliers</h5>
        <span class="text-muted">{{ $suppliers->total() }} suppliers</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width: 40px; height: 40px; background: var(--cp-blue-100); color: var(--cp-blue-600);">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <div class="fw-600">{{ $supplier->name }}</div>
                                @if($supplier->company)
                                    <small class="text-muted">{{ $supplier->company }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($supplier->phone)
                            <div><i class="bi bi-telephone text-muted me-1"></i> {{ $supplier->phone }}</div>
                        @endif
                        @if($supplier->mobile)
                            <small class="text-muted"><i class="bi bi-phone me-1"></i> {{ $supplier->mobile }}</small>
                        @endif
                        @if($supplier->email)
                            <div><small class="text-muted"><i class="bi bi-envelope me-1"></i> {{ $supplier->email }}</small></div>
                        @endif
                    </td>
                    <td>
                        @if($supplier->city || $supplier->country)
                            <span><i class="bi bi-geo-alt text-muted me-1"></i> {{ $supplier->city }}{{ $supplier->country ? ', ' . $supplier->country : '' }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-600 {{ $supplier->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                            Rs. {{ number_format($supplier->current_balance, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($supplier->is_active)
                            <span class="status-badge status-completed">Active</span>
                        @else
                            <span class="status-badge status-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this supplier?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-truck"></i>
                            <p>No suppliers found</p>
                            <a href="{{ route('suppliers.create') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Supplier
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="card-body">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>
@endsection
