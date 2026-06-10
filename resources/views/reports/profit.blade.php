@extends('layouts.app')

@section('title', 'Profit & Loss Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Profit & Loss</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Profit & Loss Report</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'profit', 'params' => ['from' => $startDate, 'to' => $endDate]])
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

{{-- Summary Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalRevenue, 0) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value">Rs. {{ number_format($cogs, 0) }}</div>
            <div class="stat-label">Cost of Goods</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalExpenses, 0) }}</div>
            <div class="stat-label">Expenses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card {{ $netProfit >= 0 ? 'stat-green' : 'stat-danger' }}">
            <div class="stat-icon"><i class="bi bi-{{ $netProfit >= 0 ? 'graph-up-arrow' : 'graph-down-arrow' }}"></i></div>
            <div class="stat-value">Rs. {{ number_format(abs($netProfit), 0) }}</div>
            <div class="stat-label">{{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Profit Statement --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-file-text"></i> Profit Statement</h5>
            </div>
            <div class="card-body p-4">
                <div class="profit-statement">
                    <div class="ps-section">
                        <h6 class="ps-title">Revenue</h6>
                        <div class="ps-row">
                            <span>Sales Revenue</span>
                            <span class="text-success fw-600">Rs. {{ number_format($totalRevenue + $totalDiscount, 2) }}</span>
                        </div>
                        <div class="ps-row text-danger">
                            <span>Less: Discounts</span>
                            <span>- Rs. {{ number_format($totalDiscount, 2) }}</span>
                        </div>
                        <div class="ps-row fw-bold border-top pt-2">
                            <span>Net Revenue</span>
                            <span>Rs. {{ number_format($totalRevenue, 2) }}</span>
                        </div>
                    </div>

                    <div class="ps-section">
                        <h6 class="ps-title">Cost of Goods Sold</h6>
                        <div class="ps-row">
                            <span>Product Costs</span>
                            <span class="text-danger">- Rs. {{ number_format($cogs, 2) }}</span>
                        </div>
                        <div class="ps-row fw-bold border-top pt-2 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            <span>Gross Profit</span>
                            <span>Rs. {{ number_format($grossProfit, 2) }}</span>
                        </div>
                        <div class="ps-row small text-muted">
                            <span>Gross Margin</span>
                            <span>{{ number_format($grossMargin, 1) }}%</span>
                        </div>
                    </div>

                    <div class="ps-section">
                        <h6 class="ps-title">Operating Expenses</h6>
                        <div class="ps-row">
                            <span>Total Expenses</span>
                            <span class="text-danger">- Rs. {{ number_format($totalExpenses, 2) }}</span>
                        </div>
                        <div class="ps-row fw-bold border-top pt-2 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            <span>Net Profit</span>
                            <span>Rs. {{ number_format($netProfit, 2) }}</span>
                        </div>
                        <div class="ps-row small text-muted">
                            <span>Net Margin</span>
                            <span>{{ number_format($netMargin, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses by Category --}}
    <div class="col-lg-6">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Expenses by Category</h5>
            </div>
            <div class="card-body p-4">
                @forelse($expensesByCategory as $category => $amount)
                <div class="expense-row">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-600">{{ $category ?? 'Uncategorized' }}</span>
                        <span class="text-danger">Rs. {{ number_format($amount, 0) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: {{ $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No expense data</div>
                @endforelse
            </div>
        </div>

        {{-- Profit Margin Indicators --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-speedometer2"></i> Profit Margins</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="margin-indicator">
                            <div class="margin-circle {{ $grossMargin >= 30 ? 'good' : ($grossMargin >= 15 ? 'medium' : 'low') }}">
                                <span>{{ number_format($grossMargin, 1) }}%</span>
                            </div>
                            <span class="margin-label">Gross Margin</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="margin-indicator">
                            <div class="margin-circle {{ $netMargin >= 15 ? 'good' : ($netMargin >= 5 ? 'medium' : 'low') }}">
                                <span>{{ number_format($netMargin, 1) }}%</span>
                            </div>
                            <span class="margin-label">Net Margin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.profit-statement { display: flex; flex-direction: column; gap: 20px; }
.ps-section { padding-bottom: 16px; border-bottom: 1px solid var(--cp-border-light); }
.ps-section:last-child { border-bottom: none; padding-bottom: 0; }
.ps-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--cp-text-muted); margin-bottom: 12px; }
.ps-row { display: flex; justify-content: space-between; padding: 6px 0; }
.expense-row { margin-bottom: 16px; }
.expense-row:last-child { margin-bottom: 0; }
.expense-row .progress { background: var(--cp-bg-alt); border-radius: 10px; }
.margin-indicator { text-align: center; }
.margin-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
    font-weight: 800;
}
.margin-circle.good { background: var(--cp-success-light); color: var(--cp-success); border: 3px solid var(--cp-success); }
.margin-circle.medium { background: var(--cp-warning-light); color: var(--cp-warning); border: 3px solid var(--cp-warning); }
.margin-circle.low { background: var(--cp-danger-light); color: var(--cp-danger); border: 3px solid var(--cp-danger); }
.margin-label { font-size: 13px; font-weight: 600; color: var(--cp-text-muted); }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
