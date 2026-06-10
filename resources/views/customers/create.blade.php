@extends('layouts.app')

@section('title', 'Add Customer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Customer</h1>
        <p class="page-subtitle">Create a new customer profile</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('customers.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('customers.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Basic Information --}}
        <div class="col-lg-8">
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-person"></i> Basic Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Customer Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Full name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Customer Group</label>
                            <select name="customer_group_id" class="form-select">
                                <option value="">No Group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="e.g. 077 123 4567">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NIC Number</label>
                            <input type="text" name="nic" class="form-control"
                                   value="{{ old('nic') }}" placeholder="National ID">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   value="{{ old('date_of_birth') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address Information --}}
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
                              placeholder="Additional notes about this customer...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Credit Settings --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-credit-card"></i> Credit Settings</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Credit Limit (Rs.)</label>
                        <input type="number" name="credit_limit" class="form-control"
                               value="{{ old('credit_limit', 0) }}" min="0" step="0.01">
                        <small class="text-muted">Maximum credit allowed. 0 = no credit.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Opening Balance (Rs.)</label>
                        <input type="number" name="current_balance" class="form-control"
                               value="{{ old('current_balance', 0) }}" step="0.01">
                        <small class="text-muted">Outstanding balance if any.</small>
                    </div>
                </div>
            </div>

            {{-- Loyalty --}}
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-star"></i> Loyalty Program</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Starting Loyalty Points</label>
                        <input type="number" name="loyalty_points" class="form-control"
                               value="{{ old('loyalty_points', 0) }}" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loyalty Tier</label>
                        <select name="loyalty_tier" class="form-select">
                            <option value="">None</option>
                            <option value="bronze" {{ old('loyalty_tier') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                            <option value="silver" {{ old('loyalty_tier') == 'silver' ? 'selected' : '' }}>Silver</option>
                            <option value="gold" {{ old('loyalty_tier') == 'gold' ? 'selected' : '' }}>Gold</option>
                            <option value="platinum" {{ old('loyalty_tier') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                        </select>
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
                        <label class="form-check-label" for="statusSwitch">Active Customer</label>
                    </div>
                    <small class="text-muted">Inactive customers won't appear in POS search.</small>
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-cpos btn-primary w-100">
                <i class="bi bi-check-lg"></i> Create Customer
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
