<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Nexfloit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/nexfloit.css')); ?>" rel="stylesheet">
</head>
<body class="nexfloit-login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="brand">
                    <i class="bi bi-lightning-charge-fill brand-icon"></i>
                    <span class="brand-text">Nexfloit</span>
                </div>
                <p class="text-muted mt-2">Create New Password</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?>

            <div class="alert alert-success mb-4">
                <i class="bi bi-person-check me-2"></i>
                Account verified: <strong><?php echo e($user->name); ?></strong>
            </div>

            <form method="POST" action="<?php echo e(route('nexfloit.reset-password')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="identifier" value="<?php echo e($identifier); ?>">
                <input type="hidden" name="token" value="<?php echo e($token); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password"
                               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="password"
                               name="password"
                               placeholder="Enter new password"
                               minlength="8"
                               required
                               autofocus>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small class="text-muted">Minimum 8 characters</small>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="Confirm new password"
                               minlength="8"
                               required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-check-lg me-2"></i>Reset Password
                </button>

                <div class="text-center mt-4">
                    <a href="<?php echo e(route('nexfloit.login')); ?>" class="text-muted">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            </form>

            <div class="login-footer">
                <small class="text-muted">Ceyloan POS Platform Management</small>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/nexfloit/auth/reset-password.blade.php ENDPATH**/ ?>