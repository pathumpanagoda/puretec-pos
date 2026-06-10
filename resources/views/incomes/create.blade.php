@extends('layouts.app')

@section('title', 'Add Income')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Income</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Income</h1>
        <p class="page-subtitle">Record a new income entry</p>
    </div>
    <a href="{{ route('incomes.index') }}" class="btn btn-outline-secondary btn-cpos">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<form action="{{ route('incomes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g., Salary Payment, Investment Return">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Amount (Rs.)</label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" required min="0.01" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Income Date</label>
                            <input type="date" name="income_date" class="form-control" value="{{ old('income_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <div class="input-group">
                                <select name="income_category_id" class="form-select">
                                    <option value="">No Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('income_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add Category">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Received From</label>
                            <input type="text" name="received_from" class="form-control" value="{{ old('received_from') }}" placeholder="Person or company name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="Cheque no., Transaction ID, etc.">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
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
                        <input type="checkbox" name="is_recurring" class="form-check-input" id="isRecurring" {{ old('is_recurring') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isRecurring">This is recurring income</label>
                    </div>
                    <div id="recurringOptions" style="display: none;">
                        <label class="form-label">Frequency</label>
                        <select name="recurring_interval" class="form-select">
                            <option value="monthly" {{ old('recurring_interval') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="weekly" {{ old('recurring_interval') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="yearly" {{ old('recurring_interval') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg">
                <i class="bi bi-check-lg me-2"></i> Save Income
            </button>
        </div>
    </div>
</form>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Income Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="newCategoryName" class="form-control" placeholder="e.g., Salary, Investments">
                </div>
                <div class="mb-3">
                    <label class="form-label">Color</label>
                    <input type="color" id="newCategoryColor" class="form-control form-control-color" value="#4CAF50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addCategory()">Add Category</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('isRecurring')?.addEventListener('change', function() {
    document.getElementById('recurringOptions').style.display = this.checked ? 'block' : 'none';
});

async function addCategory() {
    const name = document.getElementById('newCategoryName').value;
    const color = document.getElementById('newCategoryColor').value;

    if (!name) {
        alert('Please enter a category name');
        return;
    }

    try {
        const res = await fetch('{{ route("incomes.category.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, color })
        });

        const data = await res.json();
        if (data.success) {
            const select = document.querySelector('select[name="income_category_id"]');
            const option = new Option(data.category.name, data.category.id, true, true);
            select.appendChild(option);
            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            document.getElementById('newCategoryName').value = '';
        }
    } catch (e) {
        alert('Failed to add category');
    }
}
</script>
@endpush
@endsection
