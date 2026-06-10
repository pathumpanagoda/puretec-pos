@extends('nexfloit.layouts.app')

@section('title', $tenant->business_name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('nexfloit.tenants.index') }}">Tenants</a></li>
                <li class="breadcrumb-item active">{{ $tenant->code }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $tenant->business_name }}</h1>
        <p class="text-muted">
            <code>{{ $tenant->code }}</code>
            <span class="badge {{ $tenant->plan_badge['class'] }} ms-2">
                @if($tenant->is_locked)
                    <i class="bi bi-lock-fill me-1"></i>
                @elseif(!$tenant->is_active)
                    <i class="bi bi-pause-circle me-1"></i>
                @elseif($tenant->isOnTrial())
                    <i class="bi bi-hourglass-split me-1"></i>
                @endif
                {{ $tenant->plan_badge['label'] }}
            </span>
        </p>
    </div>
    <div class="btn-group">
        <a href="{{ route('nexfloit.tenants.edit', $tenant) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @if($tenant->is_locked)
            <form action="{{ route('nexfloit.tenants.unlock', $tenant) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-unlock me-1"></i>Unlock
                </button>
            </form>
        @else
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#lockModal">
                <i class="bi bi-lock me-1"></i>Lock
            </button>
        @endif
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-shop"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $stats['stores_count'] }}</h3>
                <p class="stat-label">Stores</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">{{ $stats['users_count'] }}</h3>
                <p class="stat-label">Users</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-cash"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['total_paid'], 0) }}</h3>
                <p class="stat-label">Total Paid</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. {{ number_format($stats['pending_amount'], 0) }}</h3>
                <p class="stat-label">Pending</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Business Info -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="40%">Owner</th>
                        <td>{{ $tenant->owner_name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $tenant->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $tenant->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Plan</th>
                        <td><span class="badge {{ $tenant->plan_badge['class'] }}">{{ $tenant->plan_badge['label'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Monthly Fee</th>
                        <td><strong>Rs. {{ number_format($tenant->monthly_fee, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Joined</th>
                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                    </tr>
                    @if($tenant->isOnTrial())
                        <tr>
                            <th>Trial Ends</th>
                            <td><span class="text-info">{{ $tenant->trial_ends_at->format('M d, Y') }}</span></td>
                        </tr>
                    @endif
                    @if($tenant->is_locked)
                        <tr>
                            <th>Locked At</th>
                            <td><span class="text-danger">{{ $tenant->locked_at->format('M d, Y H:i') }}</span></td>
                        </tr>
                        <tr>
                            <th>Lock Reason</th>
                            <td><span class="text-danger">{{ $tenant->lock_reason }}</span></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Stores -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shop me-2"></i>Stores</h5>
            </div>
            <div class="card-body p-0">
                @if($tenant->stores->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">No stores yet</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($tenant->stores as $store)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $store->name }}</strong>
                                    <br><small class="text-muted">{{ $store->code }}</small>
                                </div>
                                <span class="badge {{ $store->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $store->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Subscriptions & Payments -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Recent Subscriptions</h5>
                @php
                    $currentMonth = now()->format('Y-m');
                    $hasCurrentSub = $tenant->subscriptions->contains('billing_month', $currentMonth);
                @endphp
                @unless($hasCurrentSub)
                    <form action="{{ route('nexfloit.tenants.create-subscription', $tenant) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="month" value="{{ $currentMonth }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Create subscription for {{ now()->format('F Y') }}">
                            <i class="bi bi-plus-lg me-1"></i>{{ now()->format('M Y') }}
                        </button>
                    </form>
                @endunless
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenant->subscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->billing_month_display }}</td>
                                    <td>Rs. {{ number_format($subscription->amount, 2) }}</td>
                                    <td>{{ $subscription->due_date->format('M d') }}</td>
                                    <td>
                                        <span class="badge {{ $subscription->status_badge['class'] }}">
                                            {{ $subscription->status_badge['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($subscription->status !== 'paid')
                                            <a href="{{ route('nexfloit.subscriptions.record-payment', $subscription) }}"
                                               class="btn btn-sm btn-success" title="Record Payment">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No subscriptions yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Recent Payments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenant->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->method_display }}</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No payments yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lock Modal -->
<div class="modal fade" id="lockModal" tabindex="-1">
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
                        <input type="text" class="form-control" name="reason" value="Payment overdue" required>
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
@endsection
