@extends('layouts.app')

@section('title', 'Inventory Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Inventory Report</h1>
        <p class="page-subtitle">Stock levels, valuation, and alerts</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'inventory', 'params' => []])
        <a href="{{ route('inventory.index') }}" class="btn-cpos btn-primary">
            <i class="bi bi-box-seam"></i> Manage Inventory
        </a>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
            <div class="stat-value">{{ $products->count() }}</div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalValue, 0) }}</div>
            <div class="stat-label">Stock Value (Retail)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $lowStock->count() }}</div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-value">{{ $outOfStock->count() }}</div>
            <div class="stat-label">Out of Stock</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Stock Value Summary --}}
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Stock Valuation</h5>
            </div>
            <div class="card-body p-4">
                <div class="value-row">
                    <span>Cost Value</span>
                    <span class="fw-600">Rs. {{ number_format($totalCost, 2) }}</span>
                </div>
                <div class="value-row">
                    <span>Retail Value</span>
                    <span class="fw-600">Rs. {{ number_format($totalValue, 2) }}</span>
                </div>
                <hr>
                <div class="value-row text-success">
                    <span class="fw-600">Potential Profit</span>
                    <span class="fw-700">Rs. {{ number_format($potentialProfit, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Stock by Category --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Stock by Category</h5>
            </div>
            <div class="card-body p-4">
                @forelse($stockByCategory as $category => $data)
                <div class="category-stock-row">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-600">{{ $category ?? 'Uncategorized' }}</span>
                        <span>{{ $data['count'] }} products</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ number_format($data['quantity']) }} units</span>
                        <span>Rs. {{ number_format($data['value'], 0) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3">No categories</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Low Stock & Out of Stock Alerts --}}
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header bg-warning-soft">
                <h5 class="card-title text-warning"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
                <span class="badge bg-warning text-dark">{{ $lowStock->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                @forelse($lowStock->take(10) as $product)
                <div class="alert-item">
                    <div class="product-thumb-sm me-3">
                        @if($product->image)
                            <img src="{{ asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) }}" alt="{{ $product->name }}" class="thumb-img-sm">
                        @else
                            <i class="bi bi-box thumb-placeholder-sm"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-600">{{ $product->name }}</div>
                        <small class="text-muted text-mono">{{ $product->sku }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark">{{ $product->stock_quantity }} left</span>
                        <small class="d-block text-muted">Min: {{ $product->min_stock }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mb-0 mt-2">All items in stock</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="cpos-card">
            <div class="cpos-card-header bg-danger-soft">
                <h5 class="card-title text-danger"><i class="bi bi-x-circle"></i> Out of Stock</h5>
                <span class="badge bg-danger">{{ $outOfStock->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                @forelse($outOfStock->take(10) as $product)
                <div class="alert-item">
                    <div class="product-thumb-sm me-3">
                        @if($product->image)
                            <img src="{{ asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) }}" alt="{{ $product->name }}" class="thumb-img-sm">
                        @else
                            <i class="bi bi-box thumb-placeholder-sm"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-600">{{ $product->name }}</div>
                        <small class="text-muted text-mono">{{ $product->sku }}</small>
                    </div>
                    <span class="badge bg-danger">Out of Stock</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mb-0 mt-2">No items out of stock</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top Selling Products --}}
    <div class="col-lg-4">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Top Selling (30 days)</h5>
            </div>
            <div class="card-body p-0">
                @forelse($topSelling as $index => $product)
                <div class="top-product-item">
                    <div class="top-rank">{{ $index + 1 }}</div>
                    <div>
                        <div class="top-name">{{ $product->product_name }}</div>
                        <div class="top-meta">{{ number_format($product->total_sold) }} units sold</div>
                    </div>
                </div>
                @empty
                <div class="empty-state-sm">
                    <i class="bi bi-box"></i>
                    <p>No sales data</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- All Products Table --}}
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Products Stock</h5>
        <span class="text-muted">{{ $products->count() }} products</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th style="width: 60px">Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Min Stock</th>
                    <th class="text-center">Unit</th>
                    <th class="text-end">Cost Price</th>
                    <th class="text-end">Sell Price</th>
                    <th class="text-end">Stock Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products->sortBy('stock_quantity')->take(50) as $product)
                <tr>
                    <td>
                        <div class="product-thumb">
                            @if($product->image)
                                <img src="{{ asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) }}" alt="{{ $product->name }}" class="thumb-img">
                            @else
                                <i class="bi bi-box thumb-placeholder"></i>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-600">{{ $product->name }}</div>
                        <small class="text-muted text-mono">{{ $product->sku }}</small>
                        @if($product->barcode)
                            <small class="d-block text-muted">Barcode: {{ $product->barcode }}</small>
                        @endif
                    </td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td>{{ $product->supplier?->name ?? '-' }}</td>
                    <td class="text-center">
                        @if(!$product->track_stock)
                            <span class="text-muted">N/A</span>
                        @else
                            <span class="fw-600 {{ $product->stock_quantity <= 0 ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : '') }}">
                                {{ number_format($product->stock_quantity, 2) }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($product->min_stock ?? 0, 2) }}</td>
                    <td class="text-center"><span class="badge bg-light text-dark">{{ strtoupper($product->unit ?? 'PCS') }}</span></td>
                    <td class="text-end">Rs. {{ number_format($product->cost_price, 2) }}</td>
                    <td class="text-end">Rs. {{ number_format($product->selling_price, 2) }}</td>
                    <td class="text-end fw-600">Rs. {{ number_format($product->selling_price * $product->stock_quantity, 0) }}</td>
                    <td>
                        @if(!$product->track_stock)
                            <span class="badge bg-secondary">Untracked</span>
                        @elseif($product->isOutOfStock())
                            <span class="status-badge status-cancelled">Out of Stock</span>
                        @elseif($product->isLowStock())
                            <span class="status-badge status-pending">Low Stock</span>
                        @else
                            <span class="status-badge status-completed">In Stock</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
.value-row { display: flex; justify-content: space-between; padding: 10px 0; }
.category-stock-row { padding: 12px 0; border-bottom: 1px solid var(--cp-border-light); }
.category-stock-row:last-child { border-bottom: none; }
.alert-item {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--cp-border-light);
}
.alert-item:last-child { border-bottom: none; }
.bg-warning-soft { background: var(--cp-warning-light) !important; }
.bg-danger-soft { background: var(--cp-danger-light) !important; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
/* Product thumbnails */
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
.product-thumb-sm {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    overflow: hidden;
    background: var(--cp-bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--cp-border-light);
    flex-shrink: 0;
}
.product-thumb-sm .thumb-img-sm {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-thumb-sm .thumb-placeholder-sm {
    font-size: 16px;
    color: var(--cp-text-muted);
}
</style>
@endpush
@endsection
