@extends('layouts.app')

@section('title', 'Forgot Password')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo">
                <img src="{{ asset('icons/logo png.png') }}" alt="Logo" class="auth-logo-img">
            </div>
            <h1 class="auth-title"><span class="text-gold">PURE</span>POS</h1>
            <p class="auth-subtitle">Password Reset Request</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-warning mb-3">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            </div>
        @endif

        {{-- Approval Notice --}}
        <div class="forgot-approval-notice">
            <i class="bi bi-shield-lock"></i>
            <div>
                <strong>Admin Approval Required</strong>
                <p class="mb-0">Your request will be reviewed by an administrator before password reset is allowed.</p>
            </div>
        </div>

        <form action="{{ route('password.find-user') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label">Email or Username</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="identifier" class="form-control form-control-auth" placeholder="Enter your email or username" value="{{ old('identifier') }}" required autofocus>
                </div>
                @error('identifier')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Reason <span class="text-muted" style="font-weight:400;font-size:12px;">(optional)</span></label>
                <div class="input-icon-wrap">
                    <i class="bi bi-chat-text input-icon" style="top:20px;transform:none;"></i>
                    <textarea name="reason" class="form-control form-control-auth" style="padding-left:48px;min-height:70px;resize:vertical;" placeholder="Why do you need to reset?" maxlength="500">{{ old('reason') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-auth w-100">
                <i class="bi bi-send me-2"></i>Submit Reset Request
            </button>
        </form>

        <div class="forgot-back-link">
            <a href="{{ route('login') }}">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
        </div>

        {{-- Trademark Footer --}}
        <div class="auth-trademark">
            <div class="trademark-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="trademark-text">
                <span class="trademark-brand"><span class="text-gold">PURE</span>POS</span> &copy; {{ date('Y') }} <strong>PURETEC</strong>
                <br>
                <small>All Rights Reserved. Licensed Software.</small>
            </div>
        </div>
    </div>
    <div class="auth-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Forgot Password - matching login page design */
.forgot-approval-notice {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    background: rgba(201, 162, 39, 0.08);
    border: 1px solid rgba(201, 162, 39, 0.2);
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 12px;
    color: rgba(255,255,255,.6);
    line-height: 1.5;
}
.forgot-approval-notice > i {
    font-size: 20px;
    color: var(--cp-primary);
    margin-top: 2px;
    flex-shrink: 0;
}
.forgot-approval-notice strong {
    color: var(--cp-primary);
    font-size: 13px;
    display: block;
    margin-bottom: 2px;
}
.forgot-approval-notice p {
    font-size: 11.5px;
    color: rgba(255,255,255,.45);
}

.forgot-back-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.forgot-back-link a {
    color: rgba(255,255,255,.45);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: color 0.2s ease;
}
.forgot-back-link a:hover {
    color: var(--cp-primary);
}

/* Auth page tweaks */
.auth-card {
    padding: 32px 36px;
}
.auth-brand {
    margin-bottom: 24px;
}
.auth-logo {
    width: 60px;
    height: 60px;
    font-size: 26px;
    margin-bottom: 14px;
}
.auth-title {
    font-size: 24px;
    margin-bottom: 4px;
}
.auth-subtitle {
    font-size: 13px;
}
.form-group .form-label {
    font-size: 12px;
    margin-bottom: 6px;
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
}

/* Trademark Footer */
.auth-trademark {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.1);
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}
.trademark-logo {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(96,165,250,.2), rgba(99,102,241,.1));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cp-blue-400);
    font-size: 18px;
    flex-shrink: 0;
}
.trademark-text {
    font-size: 11px;
    color: rgba(255,255,255,.4);
    line-height: 1.5;
}
.trademark-brand {
    font-weight: 700;
    color: rgba(255,255,255,.7);
}
.trademark-text strong {
    color: var(--cp-blue-400);
}

@media (max-width: 576px) {
    .auth-card {
        padding: 24px 20px;
    }
}
</style>
@endpush
