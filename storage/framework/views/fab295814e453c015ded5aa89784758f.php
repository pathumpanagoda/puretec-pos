<?php $__env->startSection('title', 'Add Supplier'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('suppliers.index')); ?>">Suppliers</a></li>
    <li class="breadcrumb-item active">Add New</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Add Supplier</h1>
        <p class="page-subtitle">Create a new supplier profile</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('suppliers.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="<?php echo e(route('suppliers.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-building"></i> Supplier Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Supplier Name</label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('name')); ?>" placeholder="Contact person name" required>
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
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control"
                                   value="<?php echo e(old('company')); ?>" placeholder="Company/Business name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?php echo e(old('phone')); ?>" placeholder="Office phone">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="<?php echo e(old('mobile')); ?>" placeholder="Mobile number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?php echo e(old('email')); ?>" placeholder="email@company.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control"
                                   value="<?php echo e(old('tax_number')); ?>" placeholder="VAT/Tax registration number">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-geo-alt"></i> Address</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Street address"><?php echo e(old('address')); ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?php echo e(old('city')); ?>" placeholder="City">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                   value="<?php echo e(old('country', 'Sri Lanka')); ?>" placeholder="Country">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Additional notes about this supplier..."><?php echo e(old('notes')); ?></textarea>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Financial</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (Rs.)</label>
                        <input type="number" name="opening_balance" class="form-control"
                               value="<?php echo e(old('opening_balance', 0)); ?>" step="0.01">
                        <small class="text-muted">Amount you owe this supplier initially.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Balance (Rs.)</label>
                        <input type="number" name="current_balance" class="form-control"
                               value="<?php echo e(old('current_balance', 0)); ?>" step="0.01">
                        <small class="text-muted">Outstanding amount to pay.</small>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-toggle-on"></i> Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                               <?php echo e(old('is_active', true) ? 'checked' : ''); ?> id="statusSwitch">
                        <label class="form-check-label" for="statusSwitch">Active Supplier</label>
                    </div>
                    <small class="text-muted">Inactive suppliers won't appear in dropdowns.</small>
                </div>
            </div>

            
            <button type="submit" class="btn-cpos btn-primary w-100">
                <i class="bi bi-check-lg"></i> Create Supplier
            </button>
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
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/suppliers/create.blade.php ENDPATH**/ ?>