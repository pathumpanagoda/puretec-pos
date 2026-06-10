<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Question - Nexfloit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/nexfloit.css') }}" rel="stylesheet">
</head>
<body class="nexfloit-login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="brand">
                    <i class="bi bi-lightning-charge-fill brand-icon"></i>
                    <span class="brand-text">Nexfloit</span>
                </div>
                <p class="text-muted mt-2">Security Verification</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <div class="alert alert-info mb-4">
                <i class="bi bi-shield-check me-2"></i>
                Please answer your security question to continue.
            </div>

            <form method="POST" action="{{ route('nexfloit.security-question') }}">
                @csrf
                <input type="hidden" name="identifier" value="{{ $identifier }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label fw-bold">Security Question</label>
                    <p class="text-muted border rounded p-3 bg-light">
                        {{ $user->security_question }}
                    </p>
                </div>

                <div class="mb-4">
                    <label for="answer" class="form-label">Your Answer</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-chat-square-text"></i>
                        </span>
                        <input type="text"
                               class="form-control @error('answer') is-invalid @enderror"
                               id="answer"
                               name="answer"
                               placeholder="Enter your answer"
                               required
                               autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-check-circle me-2"></i>Verify Answer
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('nexfloit.forgot-password') }}" class="text-muted">
                        <i class="bi bi-arrow-left me-1"></i>Start Over
                    </a>
                </div>
            </form>

            <div class="login-footer">
                <small class="text-muted">Ceyloan POS Platform Management</small>
            </div>
        </div>
    </div>
</body>
</html>
