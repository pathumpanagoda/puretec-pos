@extends('layouts.app')

@section('title', 'Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage business expenses</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('expenses.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Expense
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $expenses->total() }}</div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. {{ number_format($total, 0) }}</div>
            <div class="stat-label">Total Expenses (Filtered)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calendar-date"></i></div>
            <div class="stat-value">{{ $expenses->count() > 0 ? $expenses->first()->expense_date?->format('M d') : '-' }}</div>
            <div class="stat-label">Latest Expense</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-3">
                <label class="form-label small text-muted">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="{{ route('expenses.index') }}" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Expenses Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-wallet2"></i> All Expenses</h5>
        <span class="text-muted">{{ $expenses->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Added By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>
                        <div class="fw-600">{{ $expense->expense_date?->format('M d, Y') ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="fw-600">{{ $expense->title }}</div>
                        @if($expense->notes)
                            <small class="text-muted">{{ Str::limit($expense->notes, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($expense->category)
                            <span class="badge" style="background: {{ $expense->category->color ?? '#6c757d' }}20; color: {{ $expense->category->color ?? '#6c757d' }};">
                                {{ $expense->category->name }}
                            </span>
                        @else
                            <span class="text-muted">Uncategorized</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $paymentIcon = match($expense->payment_method) {
                                'cash' => 'bi-cash',
                                'card' => 'bi-credit-card',
                                'bank_transfer' => 'bi-bank',
                                default => 'bi-wallet2'
                            };
                        @endphp
                        <span><i class="{{ $paymentIcon }} me-1 text-muted"></i>{{ ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'cash')) }}</span>
                    </td>
                    <td>
                        <span class="fw-600 text-danger">Rs. {{ number_format($expense->amount, 2) }}</span>
                    </td>
                    <td>{{ $expense->user?->name ?? '-' }}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('expenses.edit', $expense) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this expense?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-wallet2"></i>
                            <p>No expenses recorded</p>
                            <a href="{{ route('expenses.create') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Expense
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-body">
        {{ $expenses->links() }}
    </div>
    @endif
</div>
@endsection
