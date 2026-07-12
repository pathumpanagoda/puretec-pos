<?php $__env->startSection('title', $user ? 'Edit User' : 'Add User'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('users.index')); ?>">Users</a></li>
    <li class="breadcrumb-item active"><?php echo e($user ? 'Edit' : 'Add'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?php echo e($user ? 'Edit User' : 'Add New User'); ?></h1>
        <p class="page-subtitle"><?php echo e($user ? 'Update user details and permissions' : 'Create a new user account'); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('users.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="<?php echo e($user ? route('users.update', $user) : route('users.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php if($user): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-lg-7">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-person"></i> User Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('name', $user?->name)); ?>" required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Username</label>
                            <input type="text" name="username" class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('username', $user?->username)); ?>" required>
                            <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('email', $user?->email)); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('phone', $user?->phone)); ?>">
                            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select name="role" class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(old('role', $user?->role) === $value ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       <?php echo e(old('is_active', $user?->is_active ?? true) ? 'checked' : ''); ?>>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select <?php $__errorArgs = ['payment_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="full" <?php echo e(old('payment_mode', $user?->payment_mode ?? 'full') === 'full' ? 'selected' : ''); ?>>
                                    Full Access (Bill + Payment)
                                </option>
                                <option value="bill_only" <?php echo e(old('payment_mode', $user?->payment_mode) === 'bill_only' ? 'selected' : ''); ?>>
                                    Bill Only (No Payment)
                                </option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Bill-only users create bills without collecting payment. Main cashier approves payments later.
                            </small>
                            <?php $__errorArgs = ['payment_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <div class="col-md-6">
                            <label class="form-label <?php echo e($user ? '' : 'required'); ?>">
                                Password <?php echo e($user ? '(leave blank to keep current)' : ''); ?>

                            </label>
                            <input type="password" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   <?php echo e($user ? '' : 'required'); ?>>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label <?php echo e($user ? '' : 'required'); ?>">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   <?php echo e($user ? '' : 'required'); ?>>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <div class="col-12">
                            <label class="form-label d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock text-primary"></i> Security Question (Password Recovery)
                            </label>
                            <small class="text-muted d-block mb-2">
                                Set a security question to allow password reset without email
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Security Question</label>
                            <select name="security_question" class="form-select <?php $__errorArgs = ['security_question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- No security question --</option>
                                <option value="What is your mother's maiden name?" <?php echo e(old('security_question', $user?->security_question) === "What is your mother's maiden name?" ? 'selected' : ''); ?>>What is your mother's maiden name?</option>
                                <option value="What was the name of your first pet?" <?php echo e(old('security_question', $user?->security_question) === "What was the name of your first pet?" ? 'selected' : ''); ?>>What was the name of your first pet?</option>
                                <option value="What city were you born in?" <?php echo e(old('security_question', $user?->security_question) === "What city were you born in?" ? 'selected' : ''); ?>>What city were you born in?</option>
                                <option value="What was your childhood nickname?" <?php echo e(old('security_question', $user?->security_question) === "What was your childhood nickname?" ? 'selected' : ''); ?>>What was your childhood nickname?</option>
                                <option value="What is the name of your favorite teacher?" <?php echo e(old('security_question', $user?->security_question) === "What is the name of your favorite teacher?" ? 'selected' : ''); ?>>What is the name of your favorite teacher?</option>
                                <option value="What was the make of your first car?" <?php echo e(old('security_question', $user?->security_question) === "What was the make of your first car?" ? 'selected' : ''); ?>>What was the make of your first car?</option>
                            </select>
                            <?php $__errorArgs = ['security_question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Security Answer</label>
                            <input type="text" name="security_answer" class="form-control <?php $__errorArgs = ['security_answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('security_answer')); ?>" placeholder="<?php echo e($user && $user->security_answer ? '(Answer is set - enter new to change)' : 'Enter your answer'); ?>">
                            <?php $__errorArgs = ['security_answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-5">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-shield-check"></i> Custom Permissions</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Leave all unchecked to use default role permissions.
                        Check specific items to override.
                    </p>

                    <?php
                        $userPerms = old('permissions', $user?->permissions ?? []);
                    ?>

                    <div class="row g-2">
                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="<?php echo e($key); ?>"
                                           class="form-check-input" id="perm_<?php echo e($key); ?>"
                                           <?php echo e(is_array($userPerms) && in_array($key, $userPerms) ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="perm_<?php echo e($key); ?>"><?php echo e($label); ?></label>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPerms">
                            Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllPerms">
                            Clear All
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="mt-4">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-check-lg"></i>
                    <?php echo e($user ? 'Update User' : 'Create User'); ?>

                </button>
            </div>
        </div>
    </div>
</form>

<?php $__env->startPush('styles'); ?>
<style>
.btn-secondary {
    background: var(--cp-surface);
    border: 1px solid var(--cp-border);
    color: var(--cp-text);
}
.btn-secondary:hover {
    background: var(--cp-bg-alt);
    color: var(--cp-text);
}
/* Fix label colors for user form */
.cpos-card .form-label {
    color: var(--cp-text) !important;
}
.cpos-card .form-check-label {
    color: var(--cp-text) !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('selectAllPerms').addEventListener('click', function() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = true);
});
document.getElementById('clearAllPerms').addEventListener('click', function() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/users/form.blade.php ENDPATH**/ ?>