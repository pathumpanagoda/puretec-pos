@extends('layouts.app')

@section('title', 'Stock History - ' . $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">Stock History</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Stock History</h1>
        <p class="page-subtitle">Movement history for: {{ $product->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('inventory.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>
</div>

{{-- Product Info Card --}}
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="cpos-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="product-thumb" style="width: 80px; height: 80px; border-radius: 12px;">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="thumb-img">
                        @else
                            <i class="bi bi-box thumb-placeholder" style="font-size: 32px;"></i>
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $product->name }}</h4>
                        <p class="text-muted mb-2">SKU: {{ $product->sku ?? 'N/A' }} | Barcode: {{ $product->barcode ?? 'N/A' }}</p>
                        <span class="status-badge status-{{ $product->category ? 'completed' : 'pending' }}">
                            {{ $product->category?->name ?? 'Uncategorized' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue h-100">
            <div class="stat-icon"><i class="bi bi-archive"></i></div>
            <div class="stat-value">{{ number_format($product->stock_quantity, 0) }}</div>
            <div class="stat-label">Current Stock</div>
            <div class="stat-sub">{{ $product->unit ?? 'pcs' }}</div>
        </div>
    </div>
</div>

{{-- Movements Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-clock-history"></i> Movement History</h5>
        <span class="text-muted">{{ $movements->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>User</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>
                        <div class="fw-600">{{ $movement->created_at->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        @php
                            $typeColors = [
                                'sale' => 'status-cancelled',
                                'purchase' => 'status-completed',
                                'adjustment' => 'status-refunded',
                                'damage' => 'status-cancelled',
                                'expired' => 'status-cancelled',
                                'transfer' => 'status-pending',
                                'return' => 'status-completed',
                            ];
                            $typeIcons = [
                                'sale' => 'bi-cart-dash',
                                'purchase' => 'bi-cart-plus',
                                'adjustment' => 'bi-plus-slash-minus',
                                'damage' => 'bi-x-circle',
                                'expired' => 'bi-calendar-x',
                                'transfer' => 'bi-arrow-left-right',
                                'return' => 'bi-arrow-return-left',
                            ];
                        @endphp
                        <span class="status-badge {{ $typeColors[$movement->type] ?? 'status-pending' }}">
                            <i class="bi {{ $typeIcons[$movement->type] ?? 'bi-circle' }} me-1"></i>
                            {{ ucfirst($movement->type) }}
                        </span>
                    </td>
                    <td>
                        <span class="fw-600 {{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 16px;">
                            {{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 0) }}
                        </span>
                    </td>
                    <td>{{ number_format($movement->quantity_before, 0) }}</td>
                    <td>
                        <span class="fw-600">{{ number_format($movement->quantity_after, 0) }}</span>
                    </td>
                    <td>
                        @if($movement->user)
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                    {{ strtoupper(substr($movement->user->name, 0, 1)) }}
                                </div>
                                <span>{{ $movement->user->name }}</span>
                            </div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td>
                        @if($movement->notes)
                            <span title="{{ $movement->notes }}">{{ Str::limit($movement->notes, 30) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-clock-history"></i>
                            <p>No stock movements recorded yet</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="card-body">
        {{ $movements->links() }}
    </div>
    @endif
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
