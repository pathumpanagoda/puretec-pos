<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Forgot Password - Ceyloan POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/ceylon-pos.css')); ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6e8a 0%, #084c61 100%);
        }
        .forgot-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .forgot-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d6e8a, #084c61);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .forgot-icon i {
            font-size: 36px;
            color: #fff;
        }
        .forgot-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .forgot-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .btn-reset {
            background: linear-gradient(135deg, #0d6e8a, #084c61);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-reset:hover {
            background: linear-gradient(135deg, #0a5a72, #063d4e);
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
        .info-box {
            background: rgba(13, 110, 138, 0.1);
            border: 1px solid rgba(13, 110, 138, 0.2);
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .info-box i {
            color: #0d6e8a;
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <div class="forgot-header">
            <div class="forgot-icon">
                <i class="bi bi-key"></i>
            </div>
            <h1 class="forgot-title">Forgot Password?</h1>
            <p class="forgot-subtitle">Enter your email or username to reset your password</p>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="info-box">
            <i class="bi bi-info-circle me-2"></i>
            You can use either your email address or username to recover your account.
        </div>

        <form method="POST" action="<?php echo e(route('password.find-user')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-4">
                <label class="form-label">Email or Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text"
                           class="form-control <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           name="identifier"
                           value="<?php echo e(old('identifier')); ?>"
                           placeholder="Enter your email or username"
                           required
                           autofocus>
                </div>
                <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="text-danger mt-1 small"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn btn-primary btn-reset w-100">
                <i class="bi bi-arrow-right me-2"></i>Continue
            </button>
        </form>

        <div class="back-link">
            <a href="<?php echo e(route('login')); ?>">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>