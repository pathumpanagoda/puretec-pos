@extends('layouts.app')

@section('title', 'Income')

@section('breadcrumb')
    <li class="breadcrumb-item active">Income</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Income</h1>
        <p class="page-subtitle">Track all income sources</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('incomes.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Income
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. {{ number_format($totalIncome, 0) }}</div>
            <div class="stat-label">Total Income (Filtered)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
            <div class="stat-value">Rs. {{ number_format($thisMonth, 0) }}</div>
            <div class="stat-label">This Month</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $incomes->total() }}</div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-2">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Method</label>
                <select name="method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ request('method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="other" {{ request('method') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="{{ route('incomes.index') }}" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Income Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-wallet2"></i> Income Records</h5>
        <span class="text-muted">{{ $incomes->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Received From</th>
                    <th>Method</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomes as $income)
                <tr>
                    <td>
                        <div>{{ $income->income_date->format('M d, Y') }}</div>
                    </td>
                    <td>
                        <div class="fw-600">{{ $income->title }}</div>
                        @if($income->reference_number)
                            <small class="text-muted">Ref: {{ $income->reference_number }}</small>
                        @endif
                    </td>
                    <td>
                        @if($income->category)
                            <span class="badge" style="background: {{ $income->category->color }}20; color: {{ $income->category->color }};">
                                {{ $income->category->name }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $income->received_from ?? '-' }}</td>
                    <td>
                        @php
                            $methodIcon = match($income->payment_method) {
                                'cash' => 'bi-cash',
                                'bank_transfer' => 'bi-bank',
                                'cheque' => 'bi-file-earmark-text',
                                default => 'bi-wallet2'
                            };
                        @endphp
                        <i class="{{ $methodIcon }} me-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $income->payment_method)) }}
                    </td>
                    <td class="text-end">
                        <span class="fw-600 text-success">Rs. {{ number_format($income->amount, 2) }}</span>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('incomes.edit', $income) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('incomes.destroy', $income) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this income record?')">
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
                            <p>No income records found</p>
                            <a href="{{ route('incomes.create') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add Income
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($incomes->hasPages())
    <div class="card-body">
        {{ $incomes->links() }}
    </div>
    @endif
</div>
@endsection
