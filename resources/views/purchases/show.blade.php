@extends('layouts.app')

@section('title', 'Purchase Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchase Orders</a></li>
    <li class="breadcrumb-item active">{{ $purchase->po_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Purchase Order Details</h1>
        <p class="page-subtitle">{{ $purchase->po_number }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('purchases.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Order Items --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-cart"></i> Order Items</h5>
                <span class="text-muted">{{ $purchase->items->count() }} items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Ordered</th>
                            <th class="text-center">Received</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
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
                                <span class="badge bg-light text-dark">{{ $item->ordered_quantity }} {{ $item->unit }}</span>
                            </td>
                            <td class="text-center">
                                @if($item->received_quantity >= $item->ordered_quantity)
                                    <span class="badge bg-success">{{ $item->received_quantity }} {{ $item->unit }}</span>
                                @elseif($item->received_quantity > 0)
                                    <span class="badge bg-warning">{{ $item->received_quantity }} {{ $item->unit }}</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td class="text-end">Rs. {{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-600">Rs. {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-600">Total:</td>
                            <td class="text-end fw-600 fs-5" style="color: var(--cp-primary);">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Receive Stock Form --}}
        @if(in_array($purchase->status, ['ordered', 'partial']))
        <div class="cpos-card" id="receive">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-box-arrow-in-down"></i> Receive Stock</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('purchases.receive', $purchase) }}" method="POST">
                    @csrf
                    <div class="table-responsive mb-4">
                        <table class="table table-cpos mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Remaining</th>
                                    <th style="width: 150px;">Receive Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                @php
                                    $remaining = $item->ordered_quantity - $item->received_quantity;
                                @endphp
                                @if($remaining > 0)
                                <tr>
                                    <td>
                                        <div class="fw-600">{{ $item->product_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $remaining }} {{ $item->unit }}</span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                        <input type="number" name="items[{{ $loop->index }}][received_quantity]"
                                               class="form-control" min="0" max="{{ $remaining }}" value="0">
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="update_cost" value="1" class="form-check-input" id="updateCost">
                        <label class="form-check-label" for="updateCost">Update product cost prices from this order</label>
                    </div>

                    <button type="submit" class="btn-cpos btn-primary">
                        <i class="bi bi-check-lg"></i> Receive Stock
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($purchase->notes)
        <div class="cpos-card mt-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0">{{ $purchase->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
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
                        $statusClass = match($purchase->status) {
                            'draft' => 'status-pending',
                            'ordered' => 'status-pending',
                            'partial' => 'status-processing',
                            'received' => 'status-completed',
                            'cancelled' => 'status-cancelled',
                            default => 'status-pending'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($purchase->status) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Date:</span>
                    <span>{{ $purchase->order_date ? \Carbon\Carbon::parse($purchase->order_date)->format('M d, Y') : '-' }}</span>
                </div>
                @if($purchase->expected_date)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Expected:</span>
                    <span>{{ \Carbon\Carbon::parse($purchase->expected_date)->format('M d, Y') }}</span>
                </div>
                @endif
                @if($purchase->received_date)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Received:</span>
                    <span>{{ \Carbon\Carbon::parse($purchase->received_date)->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Created By:</span>
                    <span>{{ $purchase->user?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Supplier Info --}}
        @if($purchase->supplier)
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-building"></i> Supplier</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="user-avatar" style="width: 50px; height: 50px;">
                        {{ strtoupper(substr($purchase->supplier->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600">{{ $purchase->supplier->name }}</div>
                        @if($purchase->supplier->company)
                            <small class="text-muted">{{ $purchase->supplier->company }}</small>
                        @endif
                    </div>
                </div>
                @if($purchase->supplier->phone)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-telephone text-muted"></i>
                    <span>{{ $purchase->supplier->phone }}</span>
                </div>
                @endif
                @if($purchase->supplier->email)
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope text-muted"></i>
                    <span>{{ $purchase->supplier->email }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Order Summary --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items:</span>
                    <span class="fw-600">{{ $purchase->items->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Ordered:</span>
                    <span class="fw-600">{{ $purchase->items->sum('ordered_quantity') }} units</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Received:</span>
                    <span class="fw-600 {{ $purchase->items->sum('received_quantity') < $purchase->items->sum('ordered_quantity') ? 'text-warning' : 'text-success' }}">
                        {{ $purchase->items->sum('received_quantity') }} units
                    </span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span>Rs. {{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-600">Total Amount:</span>
                    <span class="fw-600 fs-5" style="color: var(--cp-primary);">Rs. {{ number_format($purchase->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
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
.status-processing {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}
</style>
@endpush
@endsection
