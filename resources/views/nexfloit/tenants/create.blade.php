@extends('nexfloit.layouts.app')

@section('title', 'Create Tenant')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('nexfloit.tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item active">Create New</li>
        </ol>
    </nav>
    <h1 class="page-title">Create New Tenant</h1>
    <p class="text-muted">Add a new business to the platform</p>
</div>

<form action="{{ route('nexfloit.tenants.store') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Business Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror"
                                   name="business_name" value="{{ old('business_name') }}" required>
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                                   name="owner_name" value="{{ old('owner_name') }}" required>
                            @error('owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      name="address" rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin User -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Admin User Account</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">This will be the primary administrator for the tenant's POS system.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Admin Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('admin_name') is-invalid @enderror"
                                   name="admin_name" value="{{ old('admin_name') }}" required>
                            @error('admin_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('admin_email') is-invalid @enderror"
                                   name="admin_email" value="{{ old('admin_email') }}" required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('admin_password') is-invalid @enderror"
                                   name="admin_password" required minlength="6">
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Store Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                   name="store_name" value="{{ old('store_name', 'Main Store') }}" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">The initial store for this tenant</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Plan <span class="text-danger">*</span></label>
                        <select class="form-select @error('subscription_plan') is-invalid @enderror"
                                name="subscription_plan" required>
                            <option value="basic" {{ old('subscription_plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                            <option value="standard" {{ old('subscription_plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="premium" {{ old('subscription_plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                        @error('subscription_plan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monthly Fee (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('monthly_fee') is-invalid @enderror"
                               name="monthly_fee" value="{{ old('monthly_fee', '5000') }}"
                               step="0.01" min="0" required>
                        @error('monthly_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trial Days</label>
                        <input type="number" class="form-control @error('trial_days') is-invalid @enderror"
                               name="trial_days" value="{{ old('trial_days', '14') }}"
                               min="0" max="90">
                        @error('trial_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Set to 0 for no trial period</div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Create Tenant
                </button>
                <a href="{{ route('nexfloit.tenants.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
