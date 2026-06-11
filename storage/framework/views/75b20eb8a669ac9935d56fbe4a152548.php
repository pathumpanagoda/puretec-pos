<?php $__env->startSection('title', 'Stock History - ' . $product->name); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('inventory.index')); ?>">Inventory</a></li>
    <li class="breadcrumb-item active">Stock History</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Stock History</h1>
        <p class="page-subtitle">Movement history for: <?php echo e($product->name); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('inventory.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="cpos-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="product-thumb" style="width: 80px; height: 80px; border-radius: 12px;">
                        <?php if($product->image): ?>
                            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>" class="thumb-img">
                        <?php else: ?>
                            <i class="bi bi-box thumb-placeholder" style="font-size: 32px;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 class="mb-1"><?php echo e($product->name); ?></h4>
                        <p class="text-muted mb-2">SKU: <?php echo e($product->sku ?? 'N/A'); ?> | Barcode: <?php echo e($product->barcode ?? 'N/A'); ?></p>
                        <span class="status-badge status-<?php echo e($product->category ? 'completed' : 'pending'); ?>">
                            <?php echo e($product->category?->name ?? 'Uncategorized'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue h-100">
            <div class="stat-icon"><i class="bi bi-archive"></i></div>
            <div class="stat-value"><?php echo e(number_format($product->stock_quantity, 0)); ?></div>
            <div class="stat-label">Current Stock</div>
            <div class="stat-sub"><?php echo e($product->unit ?? 'pcs'); ?></div>
        </div>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-clock-history"></i> Movement History</h5>
        <span class="text-muted"><?php echo e($movements->total()); ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>User</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="fw-600"><?php echo e($movement->created_at->format('M d, Y')); ?></div>
                        <small class="text-muted"><?php echo e($movement->created_at->format('h:i A')); ?></small>
                    </td>
                    <td>
                        <?php
                            $typeColors = [
                                'sale' => 'status-cancelled',
                                'purchase' => 'status-completed',
                                'adjustment' => 'status-refunded',
                                'damage' => 'status-cancelled',
                                'expired' => 'status-cancelled',
                                'transfer' => 'status-pending',
                                'return' => 'status-completed',
                            ];
                            $typeIcons = [
                                'sale' => 'bi-cart-dash',
                                'purchase' => 'bi-cart-plus',
                                'adjustment' => 'bi-plus-slash-minus',
                                'damage' => 'bi-x-circle',
                                'expired' => 'bi-calendar-x',
                                'transfer' => 'bi-arrow-left-right',
                                'return' => 'bi-arrow-return-left',
                            ];
                        ?>
                        <span class="status-badge <?php echo e($typeColors[$movement->type] ?? 'status-pending'); ?>">
                            <i class="bi <?php echo e($typeIcons[$movement->type] ?? 'bi-circle'); ?> me-1"></i>
                            <?php echo e(ucfirst($movement->type)); ?>

                        </span>
                    </td>
                    <td>
                        <span class="fw-600 <?php echo e($movement->quantity >= 0 ? 'text-success' : 'text-danger'); ?>" style="font-size: 16px;">
                            <?php echo e($movement->quantity >= 0 ? '+' : ''); ?><?php echo e(number_format($movement->quantity, 0)); ?>

                        </span>
                    </td>
                    <td><?php echo e(number_format($movement->quantity_before, 0)); ?></td>
                    <td>
                        <span class="fw-600"><?php echo e(number_format($movement->quantity_after, 0)); ?></span>
                    </td>
                    <td>
                        <?php if($movement->user): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                    <?php echo e(strtoupper(substr($movement->user->name, 0, 1))); ?>

                                </div>
                                <span><?php echo e($movement->user->name); ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">System</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($movement->notes): ?>
                            <span title="<?php echo e($movement->notes); ?>"><?php echo e(Str::limit($movement->notes, 30)); ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-clock-history"></i>
                            <p>No stock movements recorded yet</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($movements->hasPages()): ?>
    <div class="card-body">
        <?php echo e($movements->links()); ?>

    </div>
    <?php endif; ?>
</div>

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/inventory/movements.blade.php ENDPATH**/ ?>