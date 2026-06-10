@extends('nexfloit.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment History</h1>
    <p class="text-muted">View all payment transactions</p>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('nexfloit.payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="from" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="to" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="method">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $value => $label)
                        <option value="{{ $value }}" {{ request('method') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
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

<!-- Total Summary -->
<div class="alert alert-info mb-4">
    <i class="bi bi-calculator me-2"></i>
    <strong>Total for filtered results:</strong> Rs. {{ number_format($totalAmount, 2) }}
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tenant</th>
                        <th>Billing Month</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('nexfloit.tenants.show', $payment->tenant) }}" class="fw-medium">
                                    {{ $payment->tenant->business_name }}
                                </a>
                                <br><small class="text-muted">{{ $payment->tenant->code }}</small>
                            </td>
                            <td>
                                @if($payment->subscription)
                                    {{ $payment->subscription->billing_month_display }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong class="text-success">Rs. {{ number_format($payment->amount, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ $payment->method_display }}</span>
                            </td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td>
                                @if($payment->receivedByUser)
                                    {{ $payment->receivedByUser->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No payments found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
