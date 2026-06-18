<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Nexfloit Admin</title>
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
                <p class="text-muted mt-2">Reset Your Password</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('nexfloit.forgot-password') }}">
                @csrf

                <div class="mb-4">
                    <label for="identifier" class="form-label">Email or Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text"
                               class="form-control @error('identifier') is-invalid @enderror"
                               id="identifier"
                               name="identifier"
                               value="{{ old('identifier') }}"
                               placeholder="Enter your email or username"
                               required
                               autofocus>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="bi bi-info-circle me-1"></i>
                        Enter the email or username associated with your account
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-arrow-right-circle me-2"></i>Continue
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('nexfloit.login') }}" class="text-muted">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            </form>

            <div class="login-footer">
                <small class="text-muted">Pure POS Platform Management</small>
            </div>
        </div>
    </div>
</body>
</html>
