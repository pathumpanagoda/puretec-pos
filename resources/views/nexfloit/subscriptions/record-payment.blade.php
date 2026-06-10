@extends('nexfloit.layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('nexfloit.subscriptions.index') }}">Subscriptions</a></li>
            <li class="breadcrumb-item active">Record Payment</li>
        </ol>
    </nav>
    <h1 class="page-title">Record Payment</h1>
    <p class="text-muted">{{ $subscription->tenant->business_name }} - {{ $subscription->billing_month_display }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('nexfloit.subscriptions.record-payment.store', $subscription) }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                   name="amount" value="{{ old('amount', $subscription->amount) }}"
                                   step="0.01" min="0.01" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                   name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror"
                                    name="payment_method" required>
                                <option value="">Select Method</option>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                   name="reference_number" value="{{ old('reference_number') }}"
                                   placeholder="Transaction ID, Receipt #, etc.">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      name="notes" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-lg me-2"></i>Record Payment
                        </button>
                        <a href="{{ route('nexfloit.subscriptions.index') }}" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Subscription Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Subscription Info</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th>Tenant</th>
                        <td>
                            <a href="{{ route('nexfloit.tenants.show', $subscription->tenant) }}">
                                {{ $subscription->tenant->business_name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Code</th>
                        <td><code>{{ $subscription->tenant->code }}</code></td>
                    </tr>
                    <tr>
                        <th>Billing Month</th>
                        <td>{{ $subscription->billing_month_display }}</td>
                    </tr>
                    <tr>
                        <th>Amount Due</th>
                        <td><strong class="text-primary">Rs. {{ number_format($subscription->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Due Date</th>
                        <td>
                            @if($subscription->isOverdue())
                                <span class="text-danger">{{ $subscription->due_date->format('M d, Y') }}</span>
                                <br><small class="text-danger">Overdue!</small>
                            @else
                                {{ $subscription->due_date->format('M d, Y') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $subscription->status_badge['class'] }}">
                                {{ $subscription->status_badge['label'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($subscription->tenant->is_locked)
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Tenant Locked</strong>
                <p class="mb-0 mt-1">Recording this payment will automatically unlock the tenant's system.</p>
            </div>
        @endif
    </div>
</div>
@endsection
