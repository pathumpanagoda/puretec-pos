<?php $__env->startSection('title', 'Login'); ?>
<?php $__env->startSection('body-class', 'auth-body'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo"><i class="bi bi-shop-window"></i></div>
            <h1 class="auth-title">Ceyloan POS</h1>
            <p class="auth-subtitle">by <strong>Nexfloit</strong> — Professional Point of Sale</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>
        <?php if(session('success')): ?>
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group mb-3">
                <label class="form-label">Email or Username</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="login" class="form-control form-control-auth" placeholder="admin@ceylonpos.lk" value="<?php echo e(old('login')); ?>" required autofocus>
                </div>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control form-control-auth" placeholder="••••••••" required>
                    <button type="button" class="input-icon-right btn-password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordEye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="<?php echo e(route('password.request')); ?>" class="forgot-password-link">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-auth w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        
        <div class="auth-support">
            <div class="support-title"><i class="bi bi-headset"></i> Customer Support</div>
            <div class="support-contacts">
                <div class="support-person">
                    <span class="support-name">Devinda</span>
                    <span class="support-numbers">076 644 1335 / 071 515 5503</span>
                </div>
                <div class="support-person">
                    <span class="support-name">Kalpana</span>
                    <span class="support-numbers">071 915 3554</span>
                </div>
            </div>
        </div>

        
        <div class="auth-trademark">
            <div class="trademark-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="trademark-text">
                <span class="trademark-brand">Ceyloan POS</span> &copy; <?php echo e(date('Y')); ?> <strong>Nexfloit</strong>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Support Section */
.auth-support {
    margin-top: 20px;
    padding: 14px 16px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px;
}
.support-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--cp-blue-400);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.support-contacts {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.support-person {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}
.support-name {
    color: rgba(255,255,255,.7);
    font-weight: 600;
}
.support-numbers {
    color: rgba(255,255,255,.5);
    font-family: var(--font-mono);
    font-size: 11px;
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

/* Make login fit better */
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

/* Forgot Password Link */
.forgot-password-link {
    color: var(--cp-blue-400);
    font-size: 12px;
    text-decoration: none;
    transition: color 0.2s ease;
}
.forgot-password-link:hover {
    color: #fff;
    text-decoration: underline;
}

@media (max-width: 576px) {
    .auth-card {
        padding: 24px 20px;
    }
    .support-person {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function togglePassword() {
    const inp = document.getElementById('passwordInput');
    const eye = document.getElementById('passwordEye');
    inp.type  = inp.type === 'password' ? 'text' : 'password';
    eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/auth/login.blade.php ENDPATH**/ ?>