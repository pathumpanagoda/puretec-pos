<?php $__env->startSection('title', 'Edit Supplier'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('suppliers.index')); ?>">Suppliers</a></li>
    <li class="breadcrumb-item active">Edit</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Supplier</h1>
        <p class="page-subtitle"><?php echo e($supplier->name); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('suppliers.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="<?php echo e(route('suppliers.update', $supplier)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

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
                                   value="<?php echo e(old('name', $supplier->name)); ?>" required>
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
                                   value="<?php echo e(old('company', $supplier->company)); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?php echo e(old('phone', $supplier->phone)); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="<?php echo e(old('mobile', $supplier->mobile)); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?php echo e(old('email', $supplier->email)); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control"
                                   value="<?php echo e(old('tax_number', $supplier->tax_number)); ?>">
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
                            <textarea name="address" class="form-control" rows="2"><?php echo e(old('address', $supplier->address)); ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?php echo e(old('city', $supplier->city)); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                   value="<?php echo e(old('country', $supplier->country)); ?>">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3"><?php echo e(old('notes', $supplier->notes)); ?></textarea>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Supplier Info</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Products:</span>
                        <span class="fw-600"><?php echo e($supplier->products()->count()); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Purchase Orders:</span>
                        <span class="fw-600"><?php echo e($supplier->purchaseOrders()->count()); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Added On:</span>
                        <span><?php echo e($supplier->created_at->format('M d, Y')); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Financial</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (Rs.)</label>
                        <input type="number" name="opening_balance" class="form-control"
                               value="<?php echo e(old('opening_balance', $supplier->opening_balance)); ?>" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Balance (Rs.)</label>
                        <input type="number" name="current_balance" class="form-control"
                               value="<?php echo e(old('current_balance', $supplier->current_balance)); ?>" step="0.01">
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
                               <?php echo e(old('is_active', $supplier->is_active) ? 'checked' : ''); ?> id="statusSwitch">
                        <label class="form-check-label" for="statusSwitch">Active Supplier</label>
                    </div>
                </div>
            </div>

            
            <button type="submit" class="btn-cpos btn-primary w-100">
                <i class="bi bi-check-lg"></i> Update Supplier
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/suppliers/edit.blade.php ENDPATH**/ ?>