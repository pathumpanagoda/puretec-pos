<?php $__env->startSection('title', 'Add Category'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('categories.index')); ?>">Categories</a></li>
    <li class="breadcrumb-item active">Add New</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Add Category</h1>
        <p class="page-subtitle">Create a new product category</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('categories.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="<?php echo e(route('categories.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Category Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label required">Category Name</label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('name')); ?>" placeholder="e.g. Electronics" required>
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
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>" <?php echo e(old('parent_id') == $parent->id ? 'selected' : ''); ?>>
                                        <?php echo e($parent->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Optional: Select if this is a subcategory</small>
                        </div>

                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Brief description of this category"><?php echo e(old('description')); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-gear"></i> Settings</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" class="form-control form-control-color"
                                   value="<?php echo e(old('color', '#3b82f6')); ?>" style="width: 50px; height: 40px;">
                            <input type="text" class="form-control" value="#3b82f6" id="colorText"
                                   style="width: 100px;" readonly>
                        </div>
                        <small class="text-muted">Used for display in POS</small>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <option value="bi-tag" <?php echo e(old('icon') == 'bi-tag' ? 'selected' : ''); ?>>Tag (Default)</option>
                            <option value="bi-box" <?php echo e(old('icon') == 'bi-box' ? 'selected' : ''); ?>>Box</option>
                            <option value="bi-cart" <?php echo e(old('icon') == 'bi-cart' ? 'selected' : ''); ?>>Cart</option>
                            <option value="bi-cup-hot" <?php echo e(old('icon') == 'bi-cup-hot' ? 'selected' : ''); ?>>Cup (Beverages)</option>
                            <option value="bi-egg-fried" <?php echo e(old('icon') == 'bi-egg-fried' ? 'selected' : ''); ?>>Food</option>
                            <option value="bi-phone" <?php echo e(old('icon') == 'bi-phone' ? 'selected' : ''); ?>>Phone</option>
                            <option value="bi-laptop" <?php echo e(old('icon') == 'bi-laptop' ? 'selected' : ''); ?>>Laptop</option>
                            <option value="bi-shirt" <?php echo e(old('icon') == 'bi-shirt' ? 'selected' : ''); ?>>Clothing</option>
                            <option value="bi-house" <?php echo e(old('icon') == 'bi-house' ? 'selected' : ''); ?>>Home</option>
                            <option value="bi-gift" <?php echo e(old('icon') == 'bi-gift' ? 'selected' : ''); ?>>Gift</option>
                            <option value="bi-tools" <?php echo e(old('icon') == 'bi-tools' ? 'selected' : ''); ?>>Tools</option>
                            <option value="bi-book" <?php echo e(old('icon') == 'bi-book' ? 'selected' : ''); ?>>Books</option>
                        </select>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                   <?php echo e(old('is_active', true) ? 'checked' : ''); ?> id="statusSwitch">
                            <label class="form-check-label" for="statusSwitch">Active</label>
                        </div>
                        <small class="text-muted">Inactive categories won't show in POS</small>
                    </div>

                    <hr>

                    
                    <button type="submit" class="btn-cpos btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Create Category
                    </button>
                </div>
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
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Update color text when color picker changes
document.querySelector('[name="color"]').addEventListener('input', function() {
    document.getElementById('colorText').value = this.value;
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/categories/create.blade.php ENDPATH**/ ?>