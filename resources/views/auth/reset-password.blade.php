<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Ceyloan POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ceylon-pos.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6e8a 0%, #084c61 100%);
        }
        .reset-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .reset-icon i {
            font-size: 36px;
            color: #fff;
        }
        .reset-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .reset-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .btn-reset {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-reset:hover {
            background: linear-gradient(135deg, #218838, #1aa179);
        }
        .password-toggle {
            cursor: pointer;
        }
        .user-info {
            text-align: center;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            border-radius: 10px;
        }
        .user-info-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        .user-info-email {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .password-requirements {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
        }
        .password-requirements li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <div class="reset-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="reset-title">Reset Password</h1>
            <p class="reset-subtitle">Create a new password for your account</p>
        </div>

        <div class="user-info">
            @if(!empty($user_name))
                <div class="user-info-name">{{ $user_name }}</div>
            @endif
            <div class="user-info-email">{{ $email }}</div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           id="password"
                           placeholder="Enter new password"
                           required
                           minlength="6"
                           autofocus>
                    <span class="input-group-text password-toggle" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                @error('password')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
                <ul class="password-requirements">
                    <li>Minimum 6 characters</li>
                </ul>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password"
                           class="form-control"
                           name="password_confirmation"
                           id="password_confirmation"
                           placeholder="Confirm new password"
                           required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-reset w-100">
                <i class="bi bi-check-lg me-2"></i>Reset Password
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>
