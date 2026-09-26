@extends('layouts.app')

@section('title', 'Reset Password')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo">
                <img src="{{ asset('icons/logo png.png') }}" alt="Logo" class="auth-logo-img">
            </div>
            <h1 class="auth-title"><span class="text-gold">PURE</span>POS</h1>
            <p class="auth-subtitle">Reset Your Password</p>
        </div>

        {{-- User Info --}}
        <div class="reset-user-info">
            <div class="reset-user-avatar">
                {{ strtoupper(substr($user_name ?: '?', 0, 1)) }}
            </div>
            <div>
                @if(!empty($user_name))
                    <div class="reset-user-name">{{ $user_name }}</div>
                @endif
                <div class="reset-user-email">{{ $email }}</div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group mb-3">
                <label class="form-label">New Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           class="form-control form-control-auth @error('password') is-invalid @enderror"
                           name="password"
                           id="password"
                           placeholder="Enter new password"
                           required
                           minlength="6"
                           autofocus>
                    <button type="button" class="input-icon-right btn-password-toggle" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
                <small class="text-muted d-block mt-1" style="font-size:11px;"><i class="bi bi-info-circle me-1"></i>Minimum 6 characters</small>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password"
                           class="form-control form-control-auth"
                           name="password_confirmation"
                           id="password_confirmation"
                           placeholder="Confirm new password"
                           required>
                    <button type="button" class="input-icon-right btn-password-toggle" onclick="togglePassword('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-auth w-100">
                <i class="bi bi-check-lg me-2"></i>Reset Password
            </button>
        </form>

        <div class="reset-back-link">
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
/* Reset page matching login design */
.reset-user-info {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: rgba(40, 167, 69, 0.08);
    border: 1px solid rgba(40, 167, 69, 0.2);
    border-radius: 12px;
    margin-bottom: 24px;
}
.reset-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(40,167,69,.3), rgba(32,201,151,.2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    color: #28a745;
    flex-shrink: 0;
}
.reset-user-name {
    font-weight: 600;
    color: rgba(255,255,255,.85);
    font-size: 14px;
}
.reset-user-email {
    font-size: 12px;
    color: rgba(255,255,255,.45);
}
.reset-back-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.reset-back-link a {
    color: rgba(255,255,255,.45);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: color 0.2s ease;
}
.reset-back-link a:hover {
    color: var(--cp-primary);
}

/* Auth page tweaks */
.auth-card { padding: 32px 36px; }
.auth-brand { margin-bottom: 24px; }
.auth-logo { width: 60px; height: 60px; font-size: 26px; margin-bottom: 14px; }
.auth-title { font-size: 24px; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; }
.form-group .form-label { font-size: 12px; margin-bottom: 6px; }
.form-control-auth { padding: 12px 16px 12px 44px; font-size: 14px; }
.input-icon { left: 14px; font-size: 16px; }
.btn-auth { padding: 12px; font-size: 15px; }

/* Trademark Footer */
.auth-trademark {
    margin-top: 20px; padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.1);
    display: flex; align-items: center; gap: 12px; text-align: left;
}
.trademark-logo {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, rgba(96,165,250,.2), rgba(99,102,241,.1));
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    color: var(--cp-blue-400); font-size: 18px; flex-shrink: 0;
}
.trademark-text { font-size: 11px; color: rgba(255,255,255,.4); line-height: 1.5; }
.trademark-brand { font-weight: 700; color: rgba(255,255,255,.7); }
.trademark-text strong { color: var(--cp-blue-400); }

@media (max-width: 576px) {
    .auth-card { padding: 24px 20px; }
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword(inputId, toggleBtn) {
    const input = document.getElementById(inputId);
    const icon = toggleBtn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endpush
