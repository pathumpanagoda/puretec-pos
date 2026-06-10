@extends('layouts.app')

@section('title', $user ? 'Edit User' : 'Add User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $user ? 'Edit User' : 'Add New User' }}</h1>
        <p class="page-subtitle">{{ $user ? 'Update user details and permissions' : 'Create a new user account' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('users.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ $user ? route('users.update', $user) : route('users.store') }}" method="POST">
    @csrf
    @if($user) @method('PUT') @endif

    <div class="row g-4">
        {{-- User Details Card --}}
        <div class="col-lg-7">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-person"></i> User Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user?->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $user?->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user?->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user?->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role', $user?->role) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror">
                                <option value="full" {{ old('payment_mode', $user?->payment_mode ?? 'full') === 'full' ? 'selected' : '' }}>
                                    Full Access (Bill + Payment)
                                </option>
                                <option value="bill_only" {{ old('payment_mode', $user?->payment_mode) === 'bill_only' ? 'selected' : '' }}>
                                    Bill Only (No Payment)
                                </option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Bill-only users create bills without collecting payment. Main cashier approves payments later.
                            </small>
                            @error('payment_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <div class="col-md-6">
                            <label class="form-label {{ $user ? '' : 'required' }}">
                                Password {{ $user ? '(leave blank to keep current)' : '' }}
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   {{ $user ? '' : 'required' }}>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label {{ $user ? '' : 'required' }}">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   {{ $user ? '' : 'required' }}>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <div class="col-12">
                            <label class="form-label d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock text-primary"></i> Security Question (Password Recovery)
                            </label>
                            <small class="text-muted d-block mb-2">
                                Set a security question to allow password reset without email
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Security Question</label>
                            <select name="security_question" class="form-select @error('security_question') is-invalid @enderror">
                                <option value="">-- No security question --</option>
                                <option value="What is your mother's maiden name?" {{ old('security_question', $user?->security_question) === "What is your mother's maiden name?" ? 'selected' : '' }}>What is your mother's maiden name?</option>
                                <option value="What was the name of your first pet?" {{ old('security_question', $user?->security_question) === "What was the name of your first pet?" ? 'selected' : '' }}>What was the name of your first pet?</option>
                                <option value="What city were you born in?" {{ old('security_question', $user?->security_question) === "What city were you born in?" ? 'selected' : '' }}>What city were you born in?</option>
                                <option value="What was your childhood nickname?" {{ old('security_question', $user?->security_question) === "What was your childhood nickname?" ? 'selected' : '' }}>What was your childhood nickname?</option>
                                <option value="What is the name of your favorite teacher?" {{ old('security_question', $user?->security_question) === "What is the name of your favorite teacher?" ? 'selected' : '' }}>What is the name of your favorite teacher?</option>
                                <option value="What was the make of your first car?" {{ old('security_question', $user?->security_question) === "What was the make of your first car?" ? 'selected' : '' }}>What was the make of your first car?</option>
                            </select>
                            @error('security_question')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Security Answer</label>
                            <input type="text" name="security_answer" class="form-control @error('security_answer') is-invalid @enderror"
                                   value="{{ old('security_answer') }}" placeholder="{{ $user && $user->security_answer ? '(Answer is set - enter new to change)' : 'Enter your answer' }}">
                            @error('security_answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Permissions Card --}}
        <div class="col-lg-5">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-shield-check"></i> Custom Permissions</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Leave all unchecked to use default role permissions.
                        Check specific items to override.
                    </p>

                    @php
                        $userPerms = old('permissions', $user?->permissions ?? []);
                    @endphp

                    <div class="row g-2">
                        @foreach($permissions as $key => $label)
                            <div class="col-6">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                           class="form-check-input" id="perm_{{ $key }}"
                                           {{ is_array($userPerms) && in_array($key, $userPerms) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-3">

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPerms">
                            Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllPerms">
                            Clear All
                        </button>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-4">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-check-lg"></i>
                    {{ $user ? 'Update User' : 'Create User' }}
                </button>
            </div>
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
    color: var(--cp-text);
}
/* Fix label colors for user form */
.cpos-card .form-label {
    color: var(--cp-text) !important;
}
.cpos-card .form-check-label {
    color: var(--cp-text) !important;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('selectAllPerms').addEventListener('click', function() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = true);
});
document.getElementById('clearAllPerms').addEventListener('click', function() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
});
</script>
@endpush
@endsection
