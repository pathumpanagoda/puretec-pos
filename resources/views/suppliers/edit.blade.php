@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Supplier</h1>
        <p class="page-subtitle">{{ $supplier->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('suppliers.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('suppliers.update', $supplier) }}" method="POST">
    @csrf
    @method('PUT')

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
                                   value="{{ old('name', $supplier->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control"
                                   value="{{ old('company', $supplier->company) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $supplier->phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="{{ old('mobile', $supplier->mobile) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $supplier->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control"
                                   value="{{ old('tax_number', $supplier->tax_number) }}">
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
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city', $supplier->city) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                   value="{{ old('country', $supplier->country) }}">
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
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Supplier Stats --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Supplier Info</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Products:</span>
                        <span class="fw-600">{{ $supplier->products()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Purchase Orders:</span>
                        <span class="fw-600">{{ $supplier->purchaseOrders()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Added On:</span>
                        <span>{{ $supplier->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Financial Info --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Financial</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (Rs.)</label>
                        <input type="number" name="opening_balance" class="form-control"
                               value="{{ old('opening_balance', $supplier->opening_balance) }}" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Balance (Rs.)</label>
                        <input type="number" name="current_balance" class="form-control"
                               value="{{ old('current_balance', $supplier->current_balance) }}" step="0.01">
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
                               {{ old('is_active', $supplier->is_active) ? 'checked' : '' }} id="statusSwitch">
                        <label class="form-check-label" for="statusSwitch">Active Supplier</label>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-cpos btn-primary w-100">
                <i class="bi bi-check-lg"></i> Update Supplier
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
