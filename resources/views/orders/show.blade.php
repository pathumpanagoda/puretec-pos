@extends('layouts.app')

@section('title', 'Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Order Details</h1>
        <p class="page-subtitle">{{ $order->order_number }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('orders.edit', $order) }}" class="btn-cpos btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit Order
        </a>
        <div class="btn-group me-2">
            <a href="{{ route('orders.receipt', $order) }}" class="btn-cpos btn-primary" target="_blank">
                <i class="bi bi-printer"></i> Receipt
            </a>
            <button type="button" class="btn-cpos btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('orders.receipt', $order) }}" target="_blank"><i class="bi bi-receipt me-2"></i>Thermal Receipt</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a4']) }}" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>A4 Invoice</a></li>
                <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a5']) }}" target="_blank"><i class="bi bi-file-earmark me-2"></i>A5 Invoice</a></li>
                <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a3']) }}" target="_blank"><i class="bi bi-file-earmark-richtext me-2"></i>A3 Invoice</a></li>
            </ul>
        </div>
        <a href="{{ route('orders.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Order Items --}}
    <div class="col-lg-8">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-cart"></i> Order Items</h5>
                <span class="text-muted">{{ $order->items->count() }} items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar" style="width: 40px; height: 40px; background: var(--cp-blue-100); color: var(--cp-blue-600);">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600">{{ $item->product_name }}</div>
                                        @if($item->product)
                                            <small class="text-muted text-mono">{{ $item->product->sku }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $item->quantity }} {{ $item->unit }}</span>
                            </td>
                            <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end fw-600">Rs. {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Order Notes --}}
        @if($order->notes)
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Order Summary Sidebar --}}
    <div class="col-lg-4">
        {{-- Order Info --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Order Info</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Status:</span>
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
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Date:</span>
                    <span>{{ $order->created_at->format('M d, Y') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Time:</span>
                    <span>{{ $order->created_at->format('h:i A') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Cashier:</span>
                    <span>{{ $order->user?->name ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Customer:</span>
                    <span>{{ $order->customer?->name ?? 'Walk-in' }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount:</span>
                    <span>- Rs. {{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->tax_amount > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax:</span>
                    <span>Rs. {{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-600">TOTAL:</span>
                    <span class="fw-600 fs-5" style="color: var(--cp-primary);">Rs. {{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid:</span>
                    <span class="text-success">Rs. {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                @if($order->change_amount > 0)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Change:</span>
                    <span>Rs. {{ number_format($order->change_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Payment Methods --}}
        @if($order->payments && $order->payments->count() > 0)
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> Payments</h5>
            </div>
            <div class="card-body p-4">
                @foreach($order->payments as $payment)
                <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-3' : '' }}">
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $paymentIcon = match($payment->method) {
                                'cash' => 'bi-cash',
                                'card' => 'bi-credit-card',
                                'bank_transfer' => 'bi-bank',
                                default => 'bi-wallet2'
                            };
                        @endphp
                        <i class="{{ $paymentIcon }} text-muted"></i>
                        <span>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                    </div>
                    <span class="fw-600">Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        @if($order->status === 'completed')
        <div class="cpos-card">
            <div class="card-body p-4">
                <button type="button" class="btn btn-outline-danger w-100" onclick="showRefundModal()">
                    <i class="bi bi-arrow-return-left me-2"></i> Process Refund
                </button>
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
