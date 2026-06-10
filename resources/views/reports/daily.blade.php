@extends('layouts.app')

@section('title', 'Daily Sales Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Daily Sales</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Daily Sales Report</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'daily', 'params' => ['date' => $date]])
    </div>
</div>

{{-- Date Picker --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-search"></i> View Report
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('reports.daily', ['date' => now()->toDateString()]) }}" class="btn btn-outline-secondary w-100">Today</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $summary['total_orders'] }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_sales'], 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-cash"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['cash_sales'], 0) }}</div>
            <div class="stat-label">Cash Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['card_sales'], 0) }}</div>
            <div class="stat-label">Card Sales</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Order Status Breakdown --}}
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Order Status</h5>
            </div>
            <div class="card-body p-4">
                <div class="status-breakdown">
                    <div class="status-row">
                        <span class="status-badge status-completed">Completed</span>
                        <span class="fw-600">{{ $summary['completed'] }}</span>
                    </div>
                    <div class="status-row">
                        <span class="status-badge status-cancelled">Cancelled</span>
                        <span class="fw-600">{{ $summary['cancelled'] }}</span>
                    </div>
                    <div class="status-row">
                        <span class="status-badge status-refunded">Refunded</span>
                        <span class="fw-600">{{ $summary['refunded'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Summary --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Sales Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="summary-row">
                    <span>Gross Sales</span>
                    <span class="fw-600">Rs. {{ number_format($summary['total_sales'] + $summary['total_discount'], 2) }}</span>
                </div>
                <div class="summary-row text-success">
                    <span>Discounts</span>
                    <span>- Rs. {{ number_format($summary['total_discount'], 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Tax Collected</span>
                    <span>Rs. {{ number_format($summary['total_tax'], 2) }}</span>
                </div>
                <hr>
                <div class="summary-row fw-bold">
                    <span>Net Sales</span>
                    <span class="text-primary">Rs. {{ number_format($summary['total_sales'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hourly Sales Chart --}}
    <div class="col-lg-8">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Hourly Sales</h5>
            </div>
            <div class="card-body p-4">
                <div class="hourly-chart">
                    @for($h = 6; $h <= 22; $h++)
                        @php
                            $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
                            $data = $hourlyData[$hour] ?? ['count' => 0, 'total' => 0];
                            $maxTotal = $hourlyData->max('total') ?: 1;
                            $percentage = ($data['total'] / $maxTotal) * 100;
                        @endphp
                        <div class="hour-bar">
                            <div class="hour-label">{{ $h > 12 ? ($h - 12) . 'PM' : ($h == 12 ? '12PM' : $h . 'AM') }}</div>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="hour-value">
                                @if($data['count'] > 0)
                                    <span class="badge bg-light text-dark">{{ $data['count'] }}</span>
                                    Rs. {{ number_format($data['total'], 0) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transactions List --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Transactions</h5>
        <span class="text-muted">{{ $orders->count() }} orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->created_at->format('h:i A') }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order) }}" class="fw-600 text-mono" style="color: var(--cp-primary);">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td>{{ $order->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $order->user?->name ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $order->items->count() }}</span></td>
                    <td>
                        @foreach($order->payments as $payment)
                            <span class="badge bg-light text-dark">{{ ucfirst($payment->method) }}</span>
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
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-calendar-x"></i>
                            <p>No transactions on this date</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
.status-breakdown { display: flex; flex-direction: column; gap: 12px; }
.status-row { display: flex; justify-content: space-between; align-items: center; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
.hourly-chart { display: flex; flex-direction: column; gap: 8px; }
.hour-bar { display: flex; align-items: center; gap: 12px; }
.hour-label { width: 50px; font-size: 12px; color: var(--cp-text-muted); text-align: right; }
.bar-container { flex: 1; height: 24px; background: var(--cp-bg-alt); border-radius: 6px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(90deg, var(--cp-blue-400), var(--cp-primary)); border-radius: 6px; transition: width 0.3s ease; }
.hour-value { width: 120px; font-size: 12px; text-align: right; }

@media print {
    .page-actions, .cpos-card:first-child { display: none !important; }
    .cpos-card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>
@endpush
@endsection
