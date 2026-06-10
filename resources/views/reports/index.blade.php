@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">Business analytics and insights</p>
    </div>
    <div class="page-actions">
        @include('components.report-export', ['report' => 'summary', 'params' => []])
    </div>
</div>

{{-- Quick Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($todaySales, 0) }}</div>
            <div class="stat-label">Today's Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value">Rs. {{ number_format($monthSales, 0) }}</div>
            <div class="stat-label">This Month Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. {{ number_format($monthExpenses, 0) }}</div>
            <div class="stat-label">This Month Expenses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $lowStockCount }}</div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
</div>

{{-- Report Categories --}}
<div class="row g-4">
    {{-- Sales Reports --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-graph-up"></i> Sales Reports</h5>
            </div>
            <div class="card-body p-4">
                <div class="report-list">
                    <a href="{{ route('reports.daily') }}" class="report-item">
                        <div class="report-icon bg-primary-soft"><i class="bi bi-calendar-day"></i></div>
                        <div class="report-info">
                            <h6>Daily Sales Report</h6>
                            <p>View sales transactions for a specific day with hourly breakdown</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.sales') }}" class="report-item">
                        <div class="report-icon bg-success-soft"><i class="bi bi-bar-chart"></i></div>
                        <div class="report-info">
                            <h6>Sales Analysis</h6>
                            <p>Comprehensive sales data with top products and category breakdown</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.pos') }}" class="report-item">
                        <div class="report-icon bg-info-soft"><i class="bi bi-cart-check"></i></div>
                        <div class="report-info">
                            <h6>POS Report</h6>
                            <p>Payment methods, order status, and transaction details</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Reports --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Financial Reports</h5>
            </div>
            <div class="card-body p-4">
                <div class="report-list">
                    <a href="{{ route('reports.profit') }}" class="report-item">
                        <div class="report-icon bg-success-soft"><i class="bi bi-piggy-bank"></i></div>
                        <div class="report-info">
                            <h6>Profit & Loss</h6>
                            <p>Revenue, costs, expenses, and profit margins analysis</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.cashflow') }}" class="report-item">
                        <div class="report-icon bg-primary-soft"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="report-info">
                            <h6>Cash Flow</h6>
                            <p>Money inflows and outflows with daily breakdown</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.expenses') }}" class="report-item">
                        <div class="report-icon bg-danger-soft"><i class="bi bi-receipt"></i></div>
                        <div class="report-info">
                            <h6>Expense Report</h6>
                            <p>Expense tracking by category and payment method</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.balance-sheet') }}" class="report-item">
                        <div class="report-icon bg-purple-soft"><i class="bi bi-journal-text"></i></div>
                        <div class="report-info">
                            <h6>Cash Balance Sheet</h6>
                            <p>Complete cash position with opening/closing balance and reconciliation</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.cash-book') }}" class="report-item">
                        <div class="report-icon bg-teal-soft"><i class="bi bi-book"></i></div>
                        <div class="report-info">
                            <h6>Cash Book (Printable)</h6>
                            <p>Line-by-line transaction ledger for printing</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Reports --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-box-seam"></i> Inventory Reports</h5>
            </div>
            <div class="card-body p-4">
                <div class="report-list">
                    <a href="{{ route('reports.inventory') }}" class="report-item">
                        <div class="report-icon bg-warning-soft"><i class="bi bi-boxes"></i></div>
                        <div class="report-info">
                            <h6>Stock Report</h6>
                            <p>Current stock levels, valuation, and low stock alerts</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('reports.stock-movements') }}" class="report-item">
                        <div class="report-icon bg-info-soft"><i class="bi bi-arrow-repeat"></i></div>
                        <div class="report-info">
                            <h6>Stock Movements</h6>
                            <p>View all inventory adjustments and stock history</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Reports --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-people"></i> Customer Reports</h5>
            </div>
            <div class="card-body p-4">
                <div class="report-list">
                    <a href="{{ route('reports.customers') }}" class="report-item">
                        <div class="report-icon bg-primary-soft"><i class="bi bi-person-lines-fill"></i></div>
                        <div class="report-info">
                            <h6>Customer Analysis</h6>
                            <p>Top customers, credit balances, and loyalty stats</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Operations Reports --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-gear"></i> Operations Reports</h5>
            </div>
            <div class="card-body p-4">
                <div class="report-list">
                    <a href="{{ route('reports.sessions') }}" class="report-item">
                        <div class="report-icon bg-info-soft"><i class="bi bi-person-badge"></i></div>
                        <div class="report-info">
                            <h6>Cashier Sessions</h6>
                            <p>Monitor register sessions, cash handling, and shift reports</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.report-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.report-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: var(--cp-radius-md);
    background: var(--cp-bg);
    border: 1px solid var(--cp-border-light);
    text-decoration: none;
    transition: all 0.2s ease;
}
.report-item:hover {
    background: var(--cp-blue-50);
    border-color: var(--cp-blue-200);
    transform: translateX(4px);
}
.report-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.bg-primary-soft { background: var(--cp-blue-100); color: var(--cp-blue-600); }
.bg-success-soft { background: var(--cp-success-light); color: var(--cp-success); }
.bg-warning-soft { background: var(--cp-warning-light); color: var(--cp-warning); }
.bg-danger-soft { background: var(--cp-danger-light); color: var(--cp-danger); }
.bg-info-soft { background: #e0f2fe; color: #0284c7; }
.bg-purple-soft { background: #ede9fe; color: #7c3aed; }
.report-info h6 {
    font-size: 15px;
    font-weight: 600;
    color: var(--cp-text);
    margin-bottom: 4px;
}
.report-info p {
    font-size: 13px;
    color: var(--cp-text-muted);
    margin: 0;
}
.report-item > .bi-chevron-right {
    margin-left: auto;
    color: var(--cp-text-light);
    font-size: 14px;
}
.stat-danger::before { background: var(--cp-danger); }
.stat-danger::after { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
