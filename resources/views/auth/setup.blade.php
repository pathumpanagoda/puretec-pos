@extends('layouts.app')

@section('title', 'Initial Setup')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-wrapper" style="min-height: 100vh; padding: 40px 0; display: flex; align-items: center; justify-content: center;">
    <div class="auth-card" style="max-width: 500px; width: 100%; margin: auto; background: rgba(20, 20, 20, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
        <div class="auth-brand text-center">
            <div class="auth-logo mx-auto">
                <img src="{{ asset('icons/logo png.png') }}" alt="Logo" class="auth-logo-img">
            </div>
            <h1 class="auth-title"><span class="text-gold">PURE</span>POS</h1>
            <p class="auth-subtitle">Initial Onboarding Setup</p>
            <p class="text-muted small">Configure your business and admin credentials to get started.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('setup.store') }}" method="POST" autocomplete="off">
            @csrf
            
            <h5 class="text-gold mb-3 border-bottom pb-2" style="font-size: 13px; letter-spacing: 0.8px; font-weight: 700;">1. BUSINESS INFORMATION</h5>
            
            <div class="form-group mb-3">
                <label class="form-label">Store / Business Name</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-shop input-icon"></i>
                    <input type="text" name="store_name" class="form-control form-control-auth" placeholder="e.g. Pure POS - Main Store" value="{{ old('store_name', $store->name ?? '') }}" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Business Phone (Optional)</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-telephone input-icon"></i>
                    <input type="text" name="store_phone" class="form-control form-control-auth" placeholder="e.g. +94 11 000 0000" value="{{ old('store_phone') }}">
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Business Address (Optional)</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-geo-alt input-icon"></i>
                    <input type="text" name="store_address" class="form-control form-control-auth" placeholder="e.g. No. 1, Main Street, Colombo" value="{{ old('store_address') }}">
                </div>
            </div>

            <h5 class="text-gold mb-3 border-bottom pb-2" style="font-size: 13px; letter-spacing: 0.8px; font-weight: 700;">2. ADMINISTRATOR CREDENTIALS</h5>

            <div class="form-group mb-3">
                <label class="form-label">Username</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="username" class="form-control form-control-auth" placeholder="e.g. admin" value="{{ old('username') }}" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control form-control-auth" placeholder="e.g. admin@yourbusiness.com" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control form-control-auth" placeholder="••••••••" required>
                    <button type="button" class="input-icon-right btn-password-toggle" onclick="togglePassword('passwordInput', 'passwordEye')">
                        <i class="bi bi-eye" id="passwordEye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" name="password_confirmation" id="passwordConfirmInput" class="form-control form-control-auth" placeholder="••••••••" required>
                    <button type="button" class="input-icon-right btn-password-toggle" onclick="togglePassword('passwordConfirmInput', 'passwordConfirmEye')">
                        <i class="bi bi-eye" id="passwordConfirmEye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-auth w-100">
                <i class="bi bi-rocket-takeoff me-2"></i>Complete Setup & Launch
            </button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-bottom {
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
}
.form-group .form-label {
    font-size: 12px;
    margin-bottom: 6px;
    color: rgba(255, 255, 255, 0.7);
}
.form-control-auth {
    padding: 12px 16px 12px 44px;
    font-size: 14px;
}
.input-icon {
    left: 14px;
    font-size: 16px;
}
.btn-auth {
    padding: 12px;
    font-size: 15px;
    background: linear-gradient(135deg, #d4af37, #aa8c2c);
    border: none;
    color: #000;
    font-weight: 700;
    transition: all 0.3s ease;
}
.btn-auth:hover {
    background: linear-gradient(135deg, #e5c158, #bba23b);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword(inputId, eyeId) {
    const inp = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        eye.className = 'bi bi-eye';
    }
}
</script>
@endpush
