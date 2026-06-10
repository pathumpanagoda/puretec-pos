@extends('layouts.app')

@section('title', 'Edit Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Income</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Income</h1>
        <p class="page-subtitle">{{ $income->title }}</p>
    </div>
    <a href="{{ route('incomes.index') }}" class="btn btn-outline-secondary btn-cpos">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<form action="{{ route('incomes.update', $income) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Income Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">Title / Description</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $income->title) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Amount (Rs.)</label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount', $income->amount) }}" required min="0.01" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Income Date</label>
                            <input type="date" name="income_date" class="form-control" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="income_category_id" class="form-select">
                                <option value="">No Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('income_category_id', $income->income_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" {{ old('payment_method', $income->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method', $income->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method', $income->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="other" {{ old('payment_method', $income->payment_method) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Received From</label>
                            <input type="text" name="received_from" class="form-control" value="{{ old('received_from', $income->received_from) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $income->reference_number) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $income->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-paperclip"></i> Attachment</h5>
                </div>
                <div class="card-body p-4">
                    @if($income->attachment)
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark text-primary fs-4"></i>
                                <span>{{ basename($income->attachment) }}</span>
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="remove_attachment" class="form-check-input" id="removeAttachment">
                                <label class="form-check-label text-danger" for="removeAttachment">Remove attachment</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">JPG, PNG, PDF - Max 5MB</small>
                </div>
            </div>

            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-arrow-repeat"></i> Recurring</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="is_recurring" class="form-check-input" id="isRecurring" {{ $income->is_recurring ? 'checked' : '' }}>
                        <label class="form-check-label" for="isRecurring">This is recurring income</label>
                    </div>
                    <div id="recurringOptions" style="display: {{ $income->is_recurring ? 'block' : 'none' }};">
                        <label class="form-label">Frequency</label>
                        <select name="recurring_interval" class="form-select">
                            <option value="monthly" {{ $income->recurring_interval == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="weekly" {{ $income->recurring_interval == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="yearly" {{ $income->recurring_interval == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg">
                <i class="bi bi-check-lg me-2"></i> Update Income
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('isRecurring')?.addEventListener('change', function() {
    document.getElementById('recurringOptions').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush
@endsection
