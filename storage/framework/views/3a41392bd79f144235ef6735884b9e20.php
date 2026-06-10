<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Categories</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Organize your products into categories</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('categories.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    </div>
</div>

<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-tags"></i> All Categories</h5>
        <span class="text-muted"><?php echo e($categories->count()); ?> categories</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: <?php echo e($category->color ?? '#3b82f6'); ?>; display: flex; align-items: center; justify-content: center;">
                                <i class="bi <?php echo e($category->icon ?? 'bi-tag'); ?>" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div>
                                <div class="fw-600"><?php echo e($category->name); ?></div>
                                <?php if($category->children->count() > 0): ?>
                                    <small class="text-muted"><?php echo e($category->children->count()); ?> subcategories</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-muted"><?php echo e(Str::limit($category->description, 40) ?? '-'); ?></span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo e($category->products_count > 0 ? 'completed' : 'pending'); ?>">
                            <?php echo e($category->products_count); ?> products
                        </span>
                    </td>
                    <td>
                        <?php if($category->is_active): ?>
                            <span class="status-badge status-completed">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('categories.edit', $category)); ?>" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('categories.destroy', $category)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this category?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                
                <?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center gap-3 ps-4">
                            <i class="bi bi-arrow-return-right text-muted"></i>
                            <div style="width: 30px; height: 30px; border-radius: 6px; background: <?php echo e($child->color ?? '#60a5fa'); ?>; display: flex; align-items: center; justify-content: center;">
                                <i class="bi <?php echo e($child->icon ?? 'bi-tag'); ?>" style="color: white; font-size: 12px;"></i>
                            </div>
                            <div>
                                <div class="fw-600"><?php echo e($child->name); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted"><?php echo e(Str::limit($child->description, 40) ?? '-'); ?></span></td>
                    <td>
                        <span class="status-badge status-<?php echo e($child->products_count > 0 ? 'completed' : 'pending'); ?>">
                            <?php echo e($child->products_count); ?> products
                        </span>
                    </td>
                    <td>
                        <?php if($child->is_active): ?>
                            <span class="status-badge status-completed">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('categories.edit', $child)); ?>" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('categories.destroy', $child)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Delete this subcategory?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-tags"></i>
                            <p>No categories yet</p>
                            <a href="<?php echo e(route('categories.create')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Category
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/categories/index.blade.php ENDPATH**/ ?>