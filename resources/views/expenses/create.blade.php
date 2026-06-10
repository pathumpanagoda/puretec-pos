@extends('layouts.app')

@section('title', 'Add Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Expense</h1>
        <p class="page-subtitle">Record a new business expense</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('expenses.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('expenses.store') }}" method="POST">
    @csrf

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
                                   value="{{ old('title') }}" placeholder="What was this expense for?" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Amount (Rs.)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Date</label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                                   value="{{ old('expense_date', today()->toDateString()) }}" required>
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="expense_category_id" class="form-select">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
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
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Additional details or receipt information...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Categories --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-lightning"></i> Quick Categories</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($categories->take(6) as $category)
                        <button type="button" class="btn btn-outline-secondary btn-sm quick-category"
                                data-id="{{ $category->id }}" style="border-color: {{ $category->color ?? '#6c757d' }}; color: {{ $category->color ?? '#6c757d' }};">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                    @if($categories->count() == 0)
                        <p class="text-muted text-center mb-0">No categories available</p>
                    @endif
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-cpos btn-primary w-100 btn-lg">
                <i class="bi bi-check-lg"></i> Save Expense
            </button>
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
.quick-category:hover {
    color: white !important;
    background-color: currentColor;
}
.quick-category.active {
    color: white !important;
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.quick-category').forEach(btn => {
    btn.addEventListener('click', function() {
        const categoryId = this.dataset.id;
        const select = document.querySelector('[name="expense_category_id"]');
        select.value = categoryId;

        // Update active state
        document.querySelectorAll('.quick-category').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        this.style.backgroundColor = this.style.borderColor;
    });
});
</script>
@endpush
@endsection
