@extends('nexfloit.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="text-muted">Welcome back! Here's your platform overview.</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $totalTenants }}</h3>
                <p class="stat-label">Total Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $activeTenants }}</h3>
                <p class="stat-label">Active Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $lockedTenants }}</h3>
                <p class="stat-label">Locked Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $trialTenants }}</h3>
                <p class="stat-label">On Trial</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y') }}
                </h6>
                <h4 class="card-title mb-0">Expected Revenue</h4>
                <p class="display-6 text-primary mb-0">Rs. {{ number_format($expectedRevenue, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-check2-circle me-1"></i>Collected
                </h6>
                <h4 class="card-title mb-0">Received</h4>
                <p class="display-6 text-success mb-0">Rs. {{ number_format($collectedRevenue, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-exclamation-circle me-1"></i>Awaiting
                </h6>
                <h4 class="card-title mb-0">Pending Payments</h4>
                <p class="display-6 text-warning mb-0">{{ $pendingPayments }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Overdue Subscriptions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Overdue Subscriptions
                </h5>
                <a href="{{ route('nexfloit.subscriptions.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                @if($overdueSubscriptions->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No overdue subscriptions!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <strong>{{ $subscription->tenant->business_name }}</strong>
                                            <br><small class="text-muted">{{ $subscription->tenant->code }}</small>
                                        </td>
                                        <td>{{ $subscription->billing_month_display }}</td>
                                        <td>Rs. {{ number_format($subscription->amount, 2) }}</td>
                                        <td>
                                            <span class="text-danger">{{ $subscription->due_date->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('nexfloit.subscriptions.record-payment', $subscription) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Payments
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentPayments->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No recent payments</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentPayments as $payment)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $payment->tenant->business_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $payment->payment_date->format('M d, Y') }} via {{ $payment->method_display }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Rs. {{ number_format($payment->amount, 2) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Tenants -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>Recent Tenants
                </h5>
                <a href="{{ route('nexfloit.tenants.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add Tenant
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Business</th>
                                <th>Plan</th>
                                <th>Monthly Fee</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTenants as $tenant)
                                <tr>
                                    <td><code>{{ $tenant->code }}</code></td>
                                    <td>
                                        <a href="{{ route('nexfloit.tenants.show', $tenant) }}">
                                            {{ $tenant->business_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tenant->plan_badge['class'] }}">{{ $tenant->plan_badge['label'] }}</span>
                                    </td>
                                    <td>Rs. {{ number_format($tenant->monthly_fee, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $tenant->status_badge['class'] }}">
                                            {{ $tenant->status_badge['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
