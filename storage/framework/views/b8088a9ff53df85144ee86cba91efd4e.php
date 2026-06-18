<?php $__env->startSection('title', 'Purchase Orders'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Purchase Orders</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Purchase Orders</h1>
        <p class="page-subtitle">Manage your supplier purchase orders</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('purchases.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> New Purchase Order
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value"><?php echo e($purchases->total()); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value"><?php echo e($purchases->where('status', 'ordered')->count()); ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="bi bi-truck"></i></div>
            <div class="stat-value"><?php echo e($purchases->where('status', 'partial')->count()); ?></div>
            <div class="stat-label">Partial Received</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value"><?php echo e($purchases->where('status', 'received')->count()); ?></div>
            <div class="stat-label">Received</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end cpos-filter-form">
            <div class="col-md-8">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="ordered" <?php echo e(request('status') == 'ordered' ? 'selected' : ''); ?>>Ordered</option>
                    <option value="partial" <?php echo e(request('status') == 'partial' ? 'selected' : ''); ?>>Partial Received</option>
                    <option value="received" <?php echo e(request('status') == 'received' ? 'selected' : ''); ?>>Received</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="<?php echo e(route('purchases.index')); ?>" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-box-seam"></i> All Purchase Orders</h5>
        <span class="text-muted"><?php echo e($purchases->total()); ?> orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <span class="fw-600 text-mono" style="color: var(--cp-primary);">
                            <?php echo e($purchase->po_number); ?>

                        </span>
                    </td>
                    <td>
                        <?php if($purchase->supplier): ?>
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar" style="width: 36px; height: 36px; background: var(--cp-blue-100); color: var(--cp-blue-600);">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="fw-600"><?php echo e($purchase->supplier->name); ?></div>
                                    <?php if($purchase->supplier->company): ?>
                                        <small class="text-muted"><?php echo e($purchase->supplier->company); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">No Supplier</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?php echo e($purchase->order_date ? \Carbon\Carbon::parse($purchase->order_date)->format('M d, Y') : '-'); ?></div>
                    </td>
                    <td>
                        <?php if($purchase->expected_date): ?>
                            <?php echo e(\Carbon\Carbon::parse($purchase->expected_date)->format('M d, Y')); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-600">Rs. <?php echo e(number_format($purchase->total_amount, 2)); ?></span>
                    </td>
                    <td>
                        <?php
                            $statusClass = match($purchase->status) {
                                'draft' => 'status-pending',
                                'ordered' => 'status-pending',
                                'partial' => 'status-processing',
                                'received' => 'status-completed',
                                'cancelled' => 'status-cancelled',
                                default => 'status-pending'
                            };
                            $statusLabels = [
                                'draft' => 'Draft',
                                'ordered' => 'Ordered',
                                'partial' => 'Partial',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled'
                            ];
                        ?>
                        <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($statusLabels[$purchase->status] ?? ucfirst($purchase->status)); ?></span>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('purchases.show', $purchase)); ?>" class="action-btn" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if(in_array($purchase->status, ['ordered', 'partial'])): ?>
                            <a href="<?php echo e(route('purchases.show', $purchase)); ?>#receive" class="action-btn" title="Receive Stock" style="color: var(--cp-success);">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-box-seam"></i>
                            <p>No purchase orders found</p>
                            <a href="<?php echo e(route('purchases.create')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Create First Order
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($purchases->hasPages()): ?>
    <div class="card-body">
        <?php echo e($purchases->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/purchases/index.blade.php ENDPATH**/ ?>