@extends('layouts.app')

@section('title', 'POS Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">POS Report</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">POS Report</h1>
        <p class="page-subtitle">Payment methods and transaction details</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'pos', 'params' => ['from' => $startDate, 'to' => $endDate]])
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
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalSales, 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $orders->count() }}</div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalRefunds, 0) }}</div>
            <div class="stat-label">Total Refunds</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalSales - $totalRefunds, 0) }}</div>
            <div class="stat-label">Net Sales</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Payment Methods Breakdown --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> Payment Methods</h5>
            </div>
            <div class="card-body p-4">
                @php $totalPayments = array_sum($paymentSummary); @endphp

                <div class="payment-method-row">
                    <div class="pm-icon" style="background: #d1fae5; color: #059669;">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="pm-info">
                        <span class="pm-name">Cash</span>
                        <div class="progress" style="height: 6px; flex: 1;">
                            <div class="progress-bar bg-success" style="width: {{ $totalPayments > 0 ? ($paymentSummary['cash'] / $totalPayments) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="pm-amount">Rs. {{ number_format($paymentSummary['cash'], 2) }}</span>
                </div>

                <div class="payment-method-row">
                    <div class="pm-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="pm-info">
                        <span class="pm-name">Card</span>
                        <div class="progress" style="height: 6px; flex: 1;">
                            <div class="progress-bar bg-primary" style="width: {{ $totalPayments > 0 ? ($paymentSummary['card'] / $totalPayments) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="pm-amount">Rs. {{ number_format($paymentSummary['card'], 2) }}</span>
                </div>

                <div class="payment-method-row">
                    <div class="pm-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="bi bi-bank"></i>
                    </div>
                    <div class="pm-info">
                        <span class="pm-name">Bank Transfer</span>
                        <div class="progress" style="height: 6px; flex: 1;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalPayments > 0 ? ($paymentSummary['bank_transfer'] / $totalPayments) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="pm-amount">Rs. {{ number_format($paymentSummary['bank_transfer'], 2) }}</span>
                </div>

                <div class="payment-method-row">
                    <div class="pm-icon" style="background: #e0e7ff; color: #4f46e5;">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div class="pm-info">
                        <span class="pm-name">Mobile</span>
                        <div class="progress" style="height: 6px; flex: 1;">
                            <div class="progress-bar" style="background: #4f46e5; width: {{ $totalPayments > 0 ? ($paymentSummary['mobile'] / $totalPayments) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="pm-amount">Rs. {{ number_format($paymentSummary['mobile'], 2) }}</span>
                </div>

                @if($paymentSummary['other'] > 0)
                <div class="payment-method-row">
                    <div class="pm-icon" style="background: #f3f4f6; color: #6b7280;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="pm-info">
                        <span class="pm-name">Other</span>
                        <div class="progress" style="height: 6px; flex: 1;">
                            <div class="progress-bar bg-secondary" style="width: {{ $totalPayments > 0 ? ($paymentSummary['other'] / $totalPayments) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="pm-amount">Rs. {{ number_format($paymentSummary['other'], 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Order Status Summary --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-check2-circle"></i> Order Status</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="status-card bg-success-soft">
                            <div class="status-count">{{ $statusSummary['completed'] }}</div>
                            <div class="status-name">Completed</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="status-card bg-warning-soft">
                            <div class="status-count">{{ $statusSummary['pending'] }}</div>
                            <div class="status-name">Pending</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="status-card bg-danger-soft">
                            <div class="status-count">{{ $statusSummary['cancelled'] }}</div>
                            <div class="status-name">Cancelled</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="status-card bg-info-soft">
                            <div class="status-count">{{ $statusSummary['refunded'] }}</div>
                            <div class="status-name">Refunded</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> Recent Transactions</h5>
        <span class="text-muted">Last 50 transactions</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Order #</th>
                    <th>Cashier</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders->take(50) as $order)
                <tr>
                    <td>
                        <div>{{ $order->created_at->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <a href="{{ route('orders.show', $order) }}" class="fw-600 text-mono" style="color: var(--cp-primary);">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td>{{ $order->user?->name ?? '-' }}</td>
                    <td>
                        @foreach($order->payments as $payment)
                            <span class="badge bg-light text-dark me-1">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                        @endforeach
                    </td>
                    <td class="fw-600">Rs. {{ number_format($order->total_amount, 2) }}</td>
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
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
.payment-method-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.payment-method-row:last-child { border-bottom: none; }
.pm-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.pm-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.pm-name { font-weight: 600; font-size: 14px; }
.pm-amount { font-weight: 700; font-size: 15px; color: var(--cp-text); }
.status-card {
    padding: 20px;
    border-radius: var(--cp-radius-md);
    text-align: center;
}
.status-count { font-size: 28px; font-weight: 800; }
.status-name { font-size: 13px; font-weight: 600; color: var(--cp-text-muted); margin-top: 4px; }
.bg-success-soft { background: var(--cp-success-light); }
.bg-success-soft .status-count { color: #059669; }
.bg-warning-soft { background: var(--cp-warning-light); }
.bg-warning-soft .status-count { color: #d97706; }
.bg-danger-soft { background: var(--cp-danger-light); }
.bg-danger-soft .status-count { color: #dc2626; }
.bg-info-soft { background: #e0f2fe; }
.bg-info-soft .status-count { color: #0284c7; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
