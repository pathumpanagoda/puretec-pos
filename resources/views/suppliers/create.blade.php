@extends('layouts.app')

@section('title', 'Add Supplier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Supplier</h1>
        <p class="page-subtitle">Create a new supplier profile</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('suppliers.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('suppliers.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Basic Information --}}
        <div class="col-lg-8">
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-building"></i> Supplier Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Supplier Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contact person name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control"
                                   value="{{ old('company') }}" placeholder="Company/Business name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone') }}" placeholder="Office phone">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="{{ old('mobile') }}" placeholder="Mobile number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email') }}" placeholder="email@company.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control"
                                   value="{{ old('tax_number') }}" placeholder="VAT/Tax registration number">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-geo-alt"></i> Address</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Street address">{{ old('address') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city') }}" placeholder="City">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                   value="{{ old('country', 'Sri Lanka') }}" placeholder="Country">
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
                              placeholder="Additional notes about this supplier...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Financial Info --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Financial</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (Rs.)</label>
                        <input type="number" name="opening_balance" class="form-control"
                               value="{{ old('opening_balance', 0) }}" step="0.01">
                        <small class="text-muted">Amount you owe this supplier initially.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Balance (Rs.)</label>
                        <input type="number" name="current_balance" class="form-control"
                               value="{{ old('current_balance', 0) }}" step="0.01">
                        <small class="text-muted">Outstanding amount to pay.</small>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-toggle-on"></i> Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                               {{ old('is_active', true) ? 'checked' : '' }} id="statusSwitch">
                        <label class="form-check-label" for="statusSwitch">Active Supplier</label>
                    </div>
                    <small class="text-muted">Inactive suppliers won't appear in dropdowns.</small>
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-cpos btn-primary w-100">
                <i class="bi bi-check-lg"></i> Create Supplier
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
</style>
@endpush
@endsection
