@extends('layouts.app')

@section('title', 'Stock Movements Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Stock Movements</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Stock Movements Report</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'stock-movements', 'params' => ['from' => $startDate, 'to' => $endDate]])
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div class="stat-value">{{ number_format($summary['total_movements']) }}</div>
            <div class="stat-label">Total Movements</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-box-arrow-in-down"></i></div>
            <div class="stat-value">{{ number_format($summary['stock_in'], 2) }}</div>
            <div class="stat-label">Stock In</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-box-arrow-up"></i></div>
            <div class="stat-value">{{ number_format($summary['stock_out'], 2) }}</div>
            <div class="stat-label">Stock Out</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_value'], 0) }}</div>
            <div class="stat-label">Total Value</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Movement Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($movementTypes as $t)
                        <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('reports.stock-movements') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Movements Table --}}
<div class="cpos-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px">Image</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th class="text-end">Qty Before</th>
                        <th class="text-end">Change</th>
                        <th class="text-end">Qty After</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Total</th>
                        <th>By</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr>
                        <td>
                            <div class="product-thumb">
                                @if($movement->product && $movement->product->image)
                                    <img src="{{ asset(Str::startsWith($movement->product->image, 'storage/') ? $movement->product->image : 'storage/'.$movement->product->image) }}" alt="{{ $movement->product->name }}" class="thumb-img">
                                @else
                                    <i class="bi bi-box thumb-placeholder"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-600">{{ $movement->product?->name ?? 'Deleted Product' }}</div>
                            <small class="text-muted">{{ $movement->product?->sku ?? '-' }}</small>
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'sale' => 'danger',
                                    'purchase' => 'success',
                                    'opening' => 'primary',
                                    'adjustment' => 'warning',
                                    'damage' => 'danger',
                                    'expired' => 'danger',
                                    'return' => 'info',
                                ];
                                $color = $typeColors[$movement->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($movement->type) }}</span>
                        </td>
                        <td class="text-end">{{ number_format($movement->quantity_before, 2) }}</td>
                        <td class="text-end">
                            <span class="{{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-600">
                                {{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }}
                            </span>
                        </td>
                        <td class="text-end fw-600">{{ number_format($movement->quantity_after, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($movement->unit_cost ?? 0, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($movement->total_cost ?? 0, 2) }}</td>
                        <td>{{ $movement->user?->name ?? '-' }}</td>
                        <td>
                            <div>{{ $movement->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($movement->notes)
                                <span class="text-muted" title="{{ $movement->notes }}">{{ Str::limit($movement->notes, 20) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No stock movements found for this period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movements->hasPages())
    <div class="card-footer">
        {{ $movements->links() }}
    </div>
    @endif
</div>

@push('styles')
<style>
.product-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--cp-bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--cp-border-light);
}
.product-thumb .thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-thumb .thumb-placeholder {
    font-size: 20px;
    color: var(--cp-text-muted);
}
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
