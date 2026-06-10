@extends('nexfloit.layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Subscriptions</h1>
        <p class="text-muted">Manage monthly subscription billing</p>
    </div>
    <form action="{{ route('nexfloit.subscriptions.generate') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="month" value="{{ now()->addMonth()->format('Y-m') }}">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Generate Next Month
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['total'], 0) }}</h3>
                <p class="stat-label">Total Expected</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['paid'], 0) }}</h3>
                <p class="stat-label">Paid</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['pending'], 0) }}</h3>
                <p class="stat-label">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="bi bi-lock"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['locked'], 0) }}</h3>
                <p class="stat-label">Locked</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('nexfloit.subscriptions.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Billing Month</label>
                <select class="form-select" name="month">
                    @foreach($availableMonths as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search Tenant</label>
                <input type="text" class="form-control" name="search"
                       value="{{ request('search') }}"
                       placeholder="Business name or code...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Billing Month</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Paid At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>
                                <a href="{{ route('nexfloit.tenants.show', $subscription->tenant) }}" class="fw-medium">
                                    {{ $subscription->tenant->business_name }}
                                </a>
                                <br><small class="text-muted">{{ $subscription->tenant->code }}</small>
                            </td>
                            <td>{{ $subscription->billing_month_display }}</td>
                            <td><strong>Rs. {{ number_format($subscription->amount, 2) }}</strong></td>
                            <td>
                                @if($subscription->isOverdue() && $subscription->status !== 'paid')
                                    <span class="text-danger">{{ $subscription->due_date->format('M d, Y') }}</span>
                                @else
                                    {{ $subscription->due_date->format('M d, Y') }}
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $subscription->status_badge['class'] }}">
                                    {{ $subscription->status_badge['label'] }}
                                </span>
                            </td>
                            <td>
                                @if($subscription->paid_at)
                                    {{ $subscription->paid_at->format('M d, Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($subscription->status !== 'paid')
                                    <a href="{{ route('nexfloit.subscriptions.record-payment', $subscription) }}"
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-cash me-1"></i>Record Payment
                                    </a>
                                @else
                                    <span class="text-success"><i class="bi bi-check-circle"></i></span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No subscriptions found for this period</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscriptions->hasPages())
        <div class="card-footer">
            {{ $subscriptions->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
