<?php $__env->startSection('title', 'Edit ' . $tenant->business_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo e(route('nexfloit.tenants.index')); ?>">Tenants</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('nexfloit.tenants.show', $tenant)); ?>"><?php echo e($tenant->code); ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit Tenant</h1>
    <p class="text-muted"><?php echo e($tenant->business_name); ?> (<?php echo e($tenant->code); ?>)</p>
</div>

<form action="<?php echo e(route('nexfloit.tenants.update', $tenant)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="row">
        <!-- Business Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="business_name" value="<?php echo e(old('business_name', $tenant->business_name)); ?>" required>
                            <?php $__errorArgs = ['business_name'];
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
                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['owner_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="owner_name" value="<?php echo e(old('owner_name', $tenant->owner_name)); ?>" required>
                            <?php $__errorArgs = ['owner_name'];
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
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="email" value="<?php echo e(old('email', $tenant->email)); ?>" required>
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
                            <input type="text" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="phone" value="<?php echo e(old('phone', $tenant->phone)); ?>">
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
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                      name="address" rows="2"><?php echo e(old('address', $tenant->address)); ?></textarea>
                            <?php $__errorArgs = ['address'];
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

            <!-- Login Credentials -->
            <?php
                $adminUser = $tenant->users()->whereIn('role', ['admin', 'super_admin'])->first();
            ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Login Credentials</h5>
                </div>
                <div class="card-body">
                    <?php if($adminUser): ?>
                        <input type="hidden" name="admin_user_id" value="<?php echo e($adminUser->id); ?>">

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            These are the login credentials for <strong><?php echo e($tenant->business_name); ?></strong>'s POS system.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Login Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       name="admin_email" value="<?php echo e(old('admin_email', $adminUser->email)); ?>" required>
                                <?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted">Business admin uses this email to login</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['admin_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       name="admin_username" value="<?php echo e(old('admin_username', $adminUser->username)); ?>">
                                <?php $__errorArgs = ['admin_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted">Optional - can also login with username</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admin Name</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['admin_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       name="admin_name" value="<?php echo e(old('admin_name', $adminUser->name)); ?>" required>
                                <?php $__errorArgs = ['admin_name'];
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
                                <label class="form-label">Last Login</label>
                                <input type="text" class="form-control bg-dark" readonly
                                       value="<?php echo e($adminUser->last_login_at ? $adminUser->last_login_at->format('M d, Y H:i') . ' (' . $adminUser->last_login_ip . ')' : 'Never logged in'); ?>">
                            </div>
                        </div>

                        <hr class="my-3">

                        <h6 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Change Password</h6>
                        <p class="text-muted small mb-3">
                            <?php if($adminUser->password_changed_at): ?>
                                <i class="bi bi-clock me-1"></i>Last changed: <?php echo e($adminUser->password_changed_at->diffForHumans()); ?>

                                <?php if($adminUser->password_changed_by == 'nexfloit_admin'): ?>
                                    <span class="badge bg-warning ms-1">by Nexfloit</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-1">by User</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="bi bi-info-circle me-1"></i>Password has never been changed
                            <?php endif; ?>
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="admin_password"
                                       id="adminPassword" placeholder="Enter new password" minlength="6">
                                <small class="text-muted">Leave empty to keep current password</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="admin_password_confirmation"
                                       id="adminPasswordConfirm" placeholder="Confirm new password">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Leave empty to keep current password. Minimum 6 characters.
                                </small>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="generateRandomPassword()">
                                <i class="bi bi-shuffle me-1"></i>Generate Random Password
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye me-1" id="eyeIcon"></i>Show/Hide
                            </button>
                        </div>
                        <div id="generatedPasswordDisplay" class="alert alert-success mt-3 d-none">
                            <strong>Generated Password:</strong> <code id="generatedPasswordText"></code>
                            <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyPassword()">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No admin user found for this tenant. Create a store first.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Subscription & Payment Settings -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Plan <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['subscription_plan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="subscription_plan" required>
                            <option value="basic" <?php echo e(old('subscription_plan', $tenant->subscription_plan) == 'basic' ? 'selected' : ''); ?>>Basic</option>
                            <option value="standard" <?php echo e(old('subscription_plan', $tenant->subscription_plan) == 'standard' ? 'selected' : ''); ?>>Standard</option>
                            <option value="premium" <?php echo e(old('subscription_plan', $tenant->subscription_plan) == 'premium' ? 'selected' : ''); ?>>Premium</option>
                        </select>
                        <?php $__errorArgs = ['subscription_plan'];
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

                    <div class="mb-3">
                        <label class="form-label">Monthly Fee (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?php $__errorArgs = ['monthly_fee'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               name="monthly_fee" value="<?php echo e(old('monthly_fee', $tenant->monthly_fee)); ?>"
                               step="0.01" min="0" required>
                        <?php $__errorArgs = ['monthly_fee'];
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

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="is_active" <?php echo e(old('is_active', $tenant->is_active) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Reminder Settings -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-bell me-2"></i>Payment Reminder Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="payment_reminder_enabled" value="1"
                                   id="payment_reminder_enabled" <?php echo e(old('payment_reminder_enabled', $tenant->payment_reminder_enabled ?? true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="payment_reminder_enabled">
                                <strong>Enable Payment Reminder Popup</strong>
                            </label>
                        </div>
                        <small class="text-muted">Shows reminder popup to tenant when payment is due</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Payment Due Day</label>
                        <select class="form-select" name="payment_due_day">
                            <?php for($i = 1; $i <= 28; $i++): ?>
                                <option value="<?php echo e($i); ?>" <?php echo e(old('payment_due_day', $tenant->payment_due_day ?? 1) == $i ? 'selected' : ''); ?>>
                                    <?php echo e($i); ?><?php echo e($i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th'))); ?> of each month
                                </option>
                            <?php endfor; ?>
                        </select>
                        <small class="text-muted">Day when payment reminder appears</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Next Payment Due Date</label>
                        <input type="date" class="form-control <?php $__errorArgs = ['next_payment_due'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               name="next_payment_due" value="<?php echo e(old('next_payment_due', $tenant->next_payment_due?->format('Y-m-d'))); ?>">
                        <?php $__errorArgs = ['next_payment_due'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Override automatic calculation</small>
                    </div>

                    <hr>
                    <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>Contact info shown in payment reminder popup:</p>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-envelope me-1"></i>Billing Email</label>
                        <input type="email" class="form-control <?php $__errorArgs = ['billing_contact_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               name="billing_contact_email" value="<?php echo e(old('billing_contact_email', $tenant->billing_contact_email ?? 'billing@nexfloit.com')); ?>"
                               placeholder="billing@nexfloit.com">
                        <?php $__errorArgs = ['billing_contact_email'];
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

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-telephone me-1"></i>Billing Phone</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['billing_contact_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               name="billing_contact_phone" value="<?php echo e(old('billing_contact_phone', $tenant->billing_contact_phone ?? '+94 77 123 4567')); ?>"
                               placeholder="+94 77 123 4567">
                        <?php $__errorArgs = ['billing_contact_phone'];
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

                    <div class="mb-3">
                        <label class="form-label">Payment Notes</label>
                        <textarea class="form-control" name="payment_notes" rows="2"
                                  placeholder="Internal notes about payment..."><?php echo e(old('payment_notes', $tenant->payment_notes)); ?></textarea>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-grid gap-2 mt-3">
                        <?php if($tenant->is_locked): ?>
                            <button type="button" class="btn btn-success" onclick="unlockTenant()">
                                <i class="bi bi-unlock me-1"></i>Unlock System (Mark as Paid)
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-danger" onclick="lockTenant()">
                                <i class="bi bi-lock me-1"></i>Lock System (Unpaid)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Update Tenant
                </button>
                <a href="<?php echo e(route('nexfloit.tenants.show', $tenant)); ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

            <!-- Danger Zone -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Permanently delete this tenant and all associated data.</p>
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-1"></i>Delete Tenant
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('nexfloit.tenants.destroy', $tenant)); ?>" method="POST" id="deleteForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Tenant Completely</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete <strong><?php echo e($tenant->business_name); ?></strong>?</p>
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>This will permanently delete:</strong>
                        <ul class="mb-0 mt-2">
                            <li>All stores and settings</li>
                            <li>All products and categories</li>
                            <li>All orders and sales history</li>
                            <li>All customers and suppliers</li>
                            <li>All users and employees</li>
                            <li>All financial records</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <strong><?php echo e($tenant->code); ?></strong> to confirm:</label>
                        <input type="text" class="form-control" id="confirmCode" placeholder="Enter tenant code" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                        <i class="bi bi-trash me-1"></i>Delete Everything
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('confirmCode').addEventListener('input', function() {
    const btn = document.getElementById('deleteBtn');
    btn.disabled = this.value !== '<?php echo e($tenant->code); ?>';
});

// Generate random password
function generateRandomPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let password = '';
    for (let i = 0; i < 10; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    document.getElementById('adminPassword').value = password;
    document.getElementById('adminPasswordConfirm').value = password;
    document.getElementById('adminPassword').type = 'text';
    document.getElementById('adminPasswordConfirm').type = 'text';

    document.getElementById('generatedPasswordText').textContent = password;
    document.getElementById('generatedPasswordDisplay').classList.remove('d-none');
}

// Toggle password visibility
function togglePasswordVisibility() {
    const pass1 = document.getElementById('adminPassword');
    const pass2 = document.getElementById('adminPasswordConfirm');
    const icon = document.getElementById('eyeIcon');

    if (pass1.type === 'password') {
        pass1.type = 'text';
        pass2.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pass1.type = 'password';
        pass2.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Copy password to clipboard
function copyPassword() {
    const password = document.getElementById('generatedPasswordText').textContent;
    navigator.clipboard.writeText(password).then(() => {
        alert('Password copied to clipboard!');
    });
}

// Lock tenant system
function lockTenant() {
    if (confirm('Are you sure you want to LOCK this tenant system? They will not be able to use the POS until unlocked.')) {
        fetch('<?php echo e(route("nexfloit.tenants.lock", $tenant)); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('System locked successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to lock system');
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
        });
    }
}

// Unlock tenant system
function unlockTenant() {
    if (confirm('Are you sure you want to UNLOCK this tenant system? They will be able to use the POS again.')) {
        fetch('<?php echo e(route("nexfloit.tenants.unlock", $tenant)); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('System unlocked successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to unlock system');
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('nexfloit.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/nexfloit/tenants/edit.blade.php ENDPATH**/ ?>