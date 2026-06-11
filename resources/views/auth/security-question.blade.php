<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Question - Pure POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pure-pos.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6e8a 0%, #084c61 100%);
        }
        .security-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .security-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .security-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .security-icon i {
            font-size: 36px;
            color: #fff;
        }
        .security-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .security-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .question-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .question-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }
        .question-text {
            font-weight: 600;
            color: var(--text-primary);
        }
        .btn-verify {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-verify:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: var(--text-muted);
            text-decoration: none;
        }
        .back-link a:hover {
            color: #0d6e8a;
        }
        .user-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(13, 110, 138, 0.1);
            border-radius: 10px;
        }
        .user-info-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .user-info-email {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="security-card">
        <div class="security-header">
            <div class="security-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="security-title">Security Verification</h1>
            <p class="security-subtitle">Answer your security question to continue</p>
        </div>

        <div class="user-info">
            <div class="user-info-name">{{ $user_name }}</div>
            <div class="user-info-email">{{ $email }}</div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.verify-security') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="question-box">
                <div class="question-label">Security Question</div>
                <div class="question-text">{{ $question }}</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Your Answer</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                    <input type="text"
                           class="form-control @error('answer') is-invalid @enderror"
                           name="answer"
                           placeholder="Enter your answer"
                           required
                           autofocus>
                </div>
                @error('answer')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-warning btn-verify w-100 text-white">
                <i class="bi bi-check-lg me-2"></i>Verify Answer
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('password.request') }}">
                <i class="bi bi-arrow-left me-1"></i>Start Over
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
