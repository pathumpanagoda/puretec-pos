@extends('layouts.app')

@section('title', 'Customer Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Customer Profile</h1>
        <p class="page-subtitle">{{ $customer->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('customers.edit', $customer) }}" class="btn-cpos btn-primary me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('customers.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Customer Info Sidebar --}}
    <div class="col-lg-4">
        {{-- Profile Card --}}
        <div class="cpos-card mb-4">
            <div class="card-body p-4 text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 28px;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h4 class="mb-1">{{ $customer->name }}</h4>
                <p class="text-muted mb-2">{{ $customer->group?->name ?? 'General Customer' }}</p>
                @if($customer->loyalty_tier)
                    <span class="status-badge status-pending mb-2">{{ ucfirst($customer->loyalty_tier) }} Member</span>
                @endif
                <br>
                @if($customer->is_active)
                    <span class="status-badge status-completed">Active</span>
                @else
                    <span class="status-badge status-cancelled">Inactive</span>
                @endif
            </div>
        </div>

        {{-- Contact Details --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Contact Details</h5>
            </div>
            <div class="card-body p-4">
                @if($customer->phone)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="action-btn" style="pointer-events: none;"><i class="bi bi-telephone"></i></div>
                    <div>
                        <small class="text-muted d-block">Phone</small>
                        <span class="fw-600">{{ $customer->phone }}</span>
                    </div>
                </div>
                @endif

                @if($customer->email)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="action-btn" style="pointer-events: none;"><i class="bi bi-envelope"></i></div>
                    <div>
                        <small class="text-muted d-block">Email</small>
                        <span>{{ $customer->email }}</span>
                    </div>
                </div>
                @endif

                @if($customer->address || $customer->city)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="action-btn" style="pointer-events: none;"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <small class="text-muted d-block">Address</small>
                        <span>{{ $customer->address }}{{ $customer->city ? ', ' . $customer->city : '' }}</span>
                    </div>
                </div>
                @endif

                @if($customer->nic)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="action-btn" style="pointer-events: none;"><i class="bi bi-card-text"></i></div>
                    <div>
                        <small class="text-muted d-block">NIC</small>
                        <span class="text-mono">{{ $customer->nic }}</span>
                    </div>
                </div>
                @endif

                @if($customer->date_of_birth)
                <div class="d-flex align-items-center gap-3">
                    <div class="action-btn" style="pointer-events: none;"><i class="bi bi-calendar"></i></div>
                    <div>
                        <small class="text-muted d-block">Birthday</small>
                        <span>{{ $customer->date_of_birth->format('M d, Y') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Credit Info --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> Credit Info</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Credit Limit:</span>
                    <span class="fw-600">Rs. {{ number_format($customer->credit_limit, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Current Balance:</span>
                    <span class="fw-600 {{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                        Rs. {{ number_format($customer->current_balance, 2) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Member Since:</span>
                    <span>{{ $customer->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card stat-blue">
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value">{{ $customer->total_orders }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value">Rs. {{ number_format($customer->total_purchases, 0) }}</div>
                    <div class="stat-label">Total Purchases</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="bi bi-star"></i></div>
                    <div class="stat-value">{{ number_format($customer->loyalty_points) }}</div>
                    <div class="stat-label">Loyalty Points</div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Recent Orders</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <span class="fw-600 text-mono" style="color: var(--cp-primary);">{{ $order->order_number }}</span>
                            </td>
                            <td>
                                <div>{{ $order->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td>{{ $order->items->count() }} items</td>
                            <td><span class="fw-600">Rs. {{ number_format($order->total_amount, 2) }}</span></td>
                            <td>
                                @php
                                    $statusClass = match($order->status) {
                                        'completed' => 'status-completed',
                                        'pending' => 'status-pending',
                                        'cancelled' => 'status-cancelled',
                                        'refunded' => 'status-refunded',
                                        default => 'status-pending'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('orders.show', $order) }}" class="action-btn" title="View Order">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state-sm">
                                    <i class="bi bi-receipt"></i>
                                    <p>No orders yet</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Notes --}}
        @if($customer->notes)
        <div class="cpos-card mt-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.btn-secondary {
    background: var(--cp-surface);
    border: 1px solid var(--cp-border);
    color: var(--cp-text);
}
.btn-secondary:hover {
    background: var(--cp-bg-alt);
}
</style>
@endpush
@endsection
