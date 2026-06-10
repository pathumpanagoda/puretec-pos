@extends('layouts.app')

@section('title', 'Edit Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Expense</h1>
        <p class="page-subtitle">{{ $expense->title }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('expenses.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('expenses.update', $expense) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Expense Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Title / Description</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $expense->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Amount (Rs.)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Date</label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                                   value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" required>
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="expense_category_id" class="form-select">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ old('payment_method', $expense->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Expense Info --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Info</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span>{{ $expense->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Added By:</span>
                        <span>{{ $expense->user?->name ?? '-' }}</span>
                    </div>
                    @if($expense->updated_at->ne($expense->created_at))
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <span>{{ $expense->updated_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-cpos btn-primary w-100 btn-lg mb-3">
                <i class="bi bi-check-lg"></i> Update Expense
            </button>

            {{-- Delete --}}
            <form action="{{ route('expenses.destroy', $expense) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100"
                        data-confirm="Are you sure you want to delete this expense?">
                    <i class="bi bi-trash"></i> Delete Expense
                </button>
            </form>
        </div>
    </div>
</form>

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
