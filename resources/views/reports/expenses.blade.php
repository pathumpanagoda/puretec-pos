@extends('layouts.app')

@section('title', 'Expense Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Expense Report</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        @include('components.report-export', ['report' => 'expenses', 'params' => ['from' => $startDate, 'to' => $endDate]])
        <a href="{{ route('expenses.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Expense
        </a>
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
                    <a href="{{ route('reports.expenses', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Week</a>
                    <a href="{{ route('reports.expenses', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Month</a>
                    <a href="{{ route('reports.expenses', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalExpenses, 0) }}</div>
            <div class="stat-label">Total Expenses</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $expenses->count() }}</div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calculator"></i></div>
            <div class="stat-value">Rs. {{ number_format($expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0, 0) }}</div>
            <div class="stat-label">Avg. Expense</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Expenses by Category --}}
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> By Category</h5>
            </div>
            <div class="card-body p-4">
                @forelse($byCategory as $category => $data)
                <div class="category-expense-row">
                    <div class="ce-info">
                        <span class="fw-600">{{ $category }}</span>
                        <span class="text-muted">{{ $data['count'] }} records</span>
                    </div>
                    <div class="ce-bar">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: {{ $totalExpenses > 0 ? ($data['amount'] / $totalExpenses) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="ce-amount">Rs. {{ number_format($data['amount'], 0) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">No expense data</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Expenses by Payment Method --}}
    <div class="col-lg-6">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> By Payment Method</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $paymentIcons = [
                        'cash' => 'bi-cash',
                        'card' => 'bi-credit-card',
                        'bank_transfer' => 'bi-bank',
                    ];
                @endphp
                @forelse($byPaymentMethod as $method => $amount)
                <div class="payment-expense-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pe-icon">
                            <i class="bi {{ $paymentIcons[$method] ?? 'bi-wallet2' }}"></i>
                        </div>
                        <span class="fw-600">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                    </div>
                    <span class="fw-600 text-danger">Rs. {{ number_format($amount, 0) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-3">No data</div>
                @endforelse
            </div>
        </div>

        {{-- Monthly Comparison --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-bar-chart"></i> Monthly Trend</h5>
            </div>
            <div class="card-body p-4">
                @php $maxMonthly = max($monthlyComparison) ?: 1; @endphp
                <div class="monthly-chart">
                    @foreach($monthlyComparison as $month => $amount)
                    <div class="month-bar-wrap">
                        <div class="month-bar" style="height: {{ ($amount / $maxMonthly) * 100 }}%">
                            @if($amount > 0)
                            <span class="month-value">{{ number_format($amount / 1000, 0) }}K</span>
                            @endif
                        </div>
                        <span class="month-label">{{ $month }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Expense Records --}}
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Expenses</h5>
        <span class="text-muted">{{ $expenses->count() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Payment</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses->take(30) as $expense)
                <tr>
                    <td class="fw-600">{{ $expense->expense_date?->format('M d, Y') }}</td>
                    <td>
                        <div class="fw-600">{{ $expense->title }}</div>
                        @if($expense->notes)
                            <small class="text-muted">{{ Str::limit($expense->notes, 50) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($expense->category)
                            <span class="badge" style="background: {{ $expense->category->color ?? '#6c757d' }}20; color: {{ $expense->category->color ?? '#6c757d' }};">
                                {{ $expense->category->name }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'cash')) }}</span>
                    </td>
                    <td class="text-end fw-600 text-danger">Rs. {{ number_format($expense->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-receipt"></i>
                            <p>No expenses recorded</p>
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
.category-expense-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.category-expense-row:last-child { border-bottom: none; }
.ce-info { width: 140px; }
.ce-info span { display: block; }
.ce-bar { flex: 1; }
.ce-bar .progress { background: var(--cp-bg-alt); border-radius: 10px; }
.ce-amount { width: 100px; text-align: right; font-weight: 600; color: var(--cp-danger); }
.payment-expense-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.payment-expense-row:last-child { border-bottom: none; }
.pe-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--cp-bg-alt);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--cp-text-muted);
}
.monthly-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 140px;
    gap: 8px;
}
.month-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.month-bar {
    width: 100%;
    background: linear-gradient(180deg, var(--cp-danger), #f87171);
    border-radius: 4px 4px 0 0;
    margin-top: auto;
    min-height: 4px;
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.month-value {
    font-size: 10px;
    font-weight: 600;
    color: #fff;
    padding-top: 4px;
}
.month-label {
    font-size: 9px;
    color: var(--cp-text-muted);
    margin-top: 6px;
    white-space: nowrap;
}
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
@endpush
@endsection
