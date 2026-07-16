@extends('layouts.app')

@section('title', 'Activate License')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-wrapper" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
    <div class="auth-card" style="max-width: 480px; width: 100%; margin: auto; background: rgba(20, 20, 20, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
        
        <div class="auth-brand text-center">
            <div class="auth-logo mx-auto" style="background: linear-gradient(135deg, rgba(201, 162, 39, 0.2), rgba(201, 162, 39, 0.05)); border: 2px solid #c9a227; box-shadow: 0 8px 25px rgba(201, 162, 39, 0.25);">
                <i class="bi bi-shield-lock" style="color: #c9a227;"></i>
            </div>
            <h1 class="auth-title"><span class="text-gold">PURE</span>POS</h1>
            <p class="auth-subtitle">Software Activation Required</p>
            <p class="small" style="color: rgba(255, 255, 255, 0.65);">Please activate your software license key to unlock the application.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('license.process') }}" method="POST" autocomplete="off">
            @csrf



            <!-- License Key Input -->
            <div class="form-group mb-4">
                <label class="form-label">License Activation Key</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-key input-icon"></i>
                    <input type="text" name="license_key" class="form-control form-control-auth" placeholder="XXXX-XXXX-XXXX-XXXX" value="{{ old('license_key') }}" required autofocus style="letter-spacing: 1px; text-transform: uppercase;">
                </div>
            </div>

            <button type="submit" class="btn btn-auth w-100 mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>Activate License
            </button>
        </form>

        {{-- Customer Support --}}
        <div class="auth-support">
            <div class="support-title"><i class="bi bi-headset"></i> Customer Support</div>
            <div class="support-contacts">
                <div class="support-person" style="justify-content: center;">
                    <span class="support-numbers" style="font-size: 14px; color: rgba(255,255,255,.85); font-weight: bold;">071 915 3554</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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
.auth-support {
    margin-top: 20px;
    padding: 14px 16px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
}
.support-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--cp-blue-400, #d4a843);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}
.support-person {
    display: flex;
    font-size: 12px;
}
.support-numbers {
    color: rgba(255,255,255,.5);
    font-family: var(--font-mono);
}
</style>
@endpush


