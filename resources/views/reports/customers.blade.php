@extends('layouts.app')

@section('title', 'Customer Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Customer Report</h1>
        <p class="page-subtitle">Customer analysis and insights</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'customers', 'params' => ['from' => $startDate, 'to' => $endDate]])
    </div>
</div>

{{-- Date Filter --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $totalCustomers }}</div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
            <div class="stat-value">{{ $newCustomers }}</div>
            <div class="stat-label">New This Period</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-value">{{ $customersWithCredit->count() }}</div>
            <div class="stat-label">With Credit Balance</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalCredit, 0) }}</div>
            <div class="stat-label">Total Credit Due</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top Customers --}}
    <div class="col-lg-8">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Top Customers</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Customer</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $index => $customer)
                        <tr>
                            <td>
                                <div class="top-rank">{{ $index + 1 }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-600">{{ $customer->name }}</div>
                                        <small class="text-muted">{{ $customer->phone ?? $customer->email ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $customer->orders_count }}</span>
                            </td>
                            <td class="text-end fw-600 text-success">
                                Rs. {{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="empty-state-sm">
                                    <i class="bi bi-people"></i>
                                    <p>No customer data</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Loyalty Tiers --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-star"></i> Loyalty Tiers</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $tierColors = [
                        'bronze' => '#cd7f32',
                        'silver' => '#c0c0c0',
                        'gold' => '#ffd700',
                        'platinum' => '#e5e4e2'
                    ];
                @endphp
                @forelse($loyaltyStats as $tier => $count)
                <div class="loyalty-tier-row">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill" style="color: {{ $tierColors[$tier] ?? '#6b7280' }};"></i>
                        <span class="fw-600">{{ ucfirst($tier) }}</span>
                    </div>
                    <span class="badge bg-light text-dark">{{ $count }} members</span>
                </div>
                @empty
                <div class="text-center text-muted py-3">No loyalty members yet</div>
                @endforelse
            </div>
        </div>

        {{-- Customers with Credit --}}
        <div class="cpos-card">
            <div class="cpos-card-header bg-warning-soft">
                <h5 class="card-title text-warning"><i class="bi bi-exclamation-circle"></i> Credit Balances</h5>
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                @forelse($customersWithCredit->take(10) as $customer)
                <div class="credit-item">
                    <div>
                        <div class="fw-600">{{ $customer->name }}</div>
                        <small class="text-muted">{{ $customer->phone ?? '-' }}</small>
                    </div>
                    <span class="fw-600 text-danger">Rs. {{ number_format($customer->current_balance, 2) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mb-0 mt-2">No outstanding credit</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.loyalty-tier-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.loyalty-tier-row:last-child { border-bottom: none; }
.credit-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--cp-border-light);
}
.credit-item:last-child { border-bottom: none; }
.bg-warning-soft { background: var(--cp-warning-light) !important; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
