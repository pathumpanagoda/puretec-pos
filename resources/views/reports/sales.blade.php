@extends('layouts.app')

@section('title', 'Sales Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Sales Analysis</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sales Analysis</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'sales', 'params' => ['from' => $startDate, 'to' => $endDate]])
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
            <div class="col-md-4">
                <div class="btn-group w-100">
                    <a href="{{ route('reports.sales', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Week</a>
                    <a href="{{ route('reports.sales', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Month</a>
                    <a href="{{ route('reports.sales', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['total_sales'] ?? 0, 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $summary['total_orders'] ?? 0 }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['gross_profit'] ?? 0, 0) }}</div>
            <div class="stat-label">Gross Profit</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calculator"></i></div>
            <div class="stat-value">Rs. {{ number_format($summary['avg_order_value'] ?? 0, 0) }}</div>
            <div class="stat-label">Avg. Order Value</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top Products --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                @forelse($topProducts as $index => $product)
                <div class="top-product-item">
                    <div class="top-rank">{{ $index + 1 }}</div>
                    <div>
                        <div class="top-name">{{ $product->product_name }}</div>
                        <div class="top-meta">{{ $product->total_qty }} units sold</div>
                    </div>
                    <div class="top-revenue">Rs. {{ number_format($product->total_revenue, 0) }}</div>
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

    {{-- Sales by Category --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Sales by Category</h5>
            </div>
            <div class="card-body p-4">
                @php $totalCategorySales = $salesByCategory->sum('total'); @endphp
                @forelse($salesByCategory as $cat)
                <div class="category-row mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-600">{{ $cat->category ?? 'Uncategorized' }}</span>
                        <span>Rs. {{ number_format($cat->total, 0) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $totalCategorySales > 0 ? ($cat->total / $totalCategorySales) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No category data</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sales by Cashier --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-person-badge"></i> Sales by Cashier</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Cashier</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesByUser as $user)
                        <tr>
                            <td class="fw-600">{{ $user['user'] }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{ $user['orders'] }}</span></td>
                            <td class="text-end fw-600 text-success">Rs. {{ number_format($user['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Daily Sales Chart --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-graph-up"></i> Daily Sales Trend</h5>
            </div>
            <div class="card-body p-4">
                <div class="mini-chart">
                    @php $maxDaily = collect($dailyChart)->max('sales') ?: 1; @endphp
                    @foreach(array_slice($dailyChart, -14) as $day)
                    <div class="chart-bar-wrap" title="{{ $day['date'] }}: Rs. {{ number_format($day['sales'] ?? 0, 0) }}">
                        <div class="chart-bar" style="height: {{ (($day['sales'] ?? 0) / $maxDaily) * 100 }}%"></div>
                        <span class="chart-label">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.mini-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 150px;
    gap: 4px;
    padding-top: 20px;
}
.chart-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.chart-bar {
    width: 100%;
    max-width: 30px;
    background: linear-gradient(180deg, var(--cp-primary), var(--cp-blue-400));
    border-radius: 4px 4px 0 0;
    margin-top: auto;
    min-height: 4px;
    transition: height 0.3s ease;
}
.chart-bar-wrap:hover .chart-bar {
    background: var(--cp-primary-dark);
}
.chart-label {
    font-size: 10px;
    color: var(--cp-text-muted);
    margin-top: 6px;
}
.category-row .progress {
    background: var(--cp-bg-alt);
    border-radius: 10px;
}

@media print {
    .page-actions, .cpos-card:first-child { display: none !important; }
}
</style>
@endpush
@endsection
