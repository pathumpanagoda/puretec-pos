<?php $__env->startSection('title', 'Inventory'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Inventory</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Inventory Management</h1>
        <p class="page-subtitle">Track and manage your product stock levels</p>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value"><?php echo e($products->count()); ?></div>
            <div class="stat-label">Total Products</div>
            <div class="stat-sub"><?php echo e($products->where('track_stock', true)->count()); ?> tracking stock</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value"><?php echo e($lowStock->count()); ?></div>
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-sub">Need reordering</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($products->sum(fn($p) => $p->stock_quantity * $p->cost_price), 0)); ?></div>
            <div class="stat-label">Total Stock Value</div>
            <div class="stat-sub">At cost price</div>
        </div>
    </div>
</div>


<?php if($lowStock->count() > 0): ?>
<div class="cpos-card mb-4" style="border-left: 4px solid var(--cp-warning);">
    <div class="cpos-card-header">
        <h5 class="card-title text-warning"><i class="bi bi-exclamation-triangle"></i> Low Stock Alerts</h5>
        <span class="status-badge status-pending"><?php echo e($lowStock->count()); ?> items</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-cpos mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $lowStock->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div class="fw-600"><?php echo e($product->name); ?></div>
                            <small class="text-muted"><?php echo e($product->sku); ?></small>
                        </td>
                        <td>
                            <span class="fw-600 <?php echo e($product->stock_quantity <= 0 ? 'text-danger' : 'text-warning'); ?>">
                                <?php echo e(number_format($product->stock_quantity, 0)); ?> <?php echo e($product->unit ?? 'pcs'); ?>

                            </span>
                        </td>
                        <td><?php echo e(number_format($product->reorder_level, 0)); ?> <?php echo e($product->unit ?? 'pcs'); ?></td>
                        <td>
                            <?php if($product->stock_quantity <= 0): ?>
                                <span class="status-badge status-cancelled">Out of Stock</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Low Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn-cpos btn-primary btn-sm" onclick="openAdjustModal(<?php echo e($product->id); ?>, '<?php echo e($product->name); ?>', <?php echo e($product->stock_quantity); ?>)">
                                <i class="bi bi-plus-slash-minus"></i> Adjust
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-archive"></i> All Products Stock</h5>
        <div class="d-flex gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search products..." style="width: 200px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0" id="inventoryTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Item Code</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Stock Qty</th>
                    <th>Stock Value</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="product-row" data-name="<?php echo e(strtolower($product->name)); ?>" data-sku="<?php echo e(strtolower($product->sku ?? '')); ?>">
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="product-thumb">
                                <?php if($product->image): ?>
                                    <img src="<?php echo e(asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image)); ?>" alt="<?php echo e($product->name); ?>" class="thumb-img">
                                <?php else: ?>
                                    <i class="bi bi-box thumb-placeholder"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-600"><?php echo e($product->name); ?></div>
                                <?php if($product->barcode): ?>
                                    <small class="text-muted text-mono"><?php echo e($product->barcode); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-600 text-mono" style="color: var(--cp-primary);"><?php echo e($product->sku ?? '-'); ?></span>
                    </td>
                    <td><?php echo e($product->category?->name ?? '-'); ?></td>
                    <td>Rs. <?php echo e(number_format($product->cost_price, 2)); ?></td>
                    <td>
                        <?php if($product->track_stock): ?>
                            <span class="fw-600 <?php echo e($product->stock_quantity <= 0 ? 'text-danger' : ($product->stock_quantity <= $product->reorder_level ? 'text-warning' : 'text-success')); ?>">
                                <?php echo e(number_format($product->stock_quantity, 0)); ?>

                            </span>
                            <small class="text-muted"><?php echo e($product->unit ?? 'pcs'); ?></small>
                        <?php else: ?>
                            <span class="text-muted">Not tracked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($product->track_stock): ?>
                            Rs. <?php echo e(number_format($product->stock_quantity * $product->cost_price, 2)); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!$product->track_stock): ?>
                            <span class="status-badge status-refunded">No Track</span>
                        <?php elseif($product->stock_quantity <= 0): ?>
                            <span class="status-badge status-cancelled">Out of Stock</span>
                        <?php elseif($product->stock_quantity <= $product->reorder_level): ?>
                            <span class="status-badge status-pending">Low Stock</span>
                        <?php else: ?>
                            <span class="status-badge status-completed">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <?php if($product->track_stock): ?>
                            <button class="action-btn" title="Adjust Stock"
                                    onclick="openAdjustModal(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', <?php echo e($product->stock_quantity); ?>)">
                                <i class="bi bi-plus-slash-minus"></i>
                            </button>
                            <?php endif; ?>
                            <a href="<?php echo e(route('inventory.movements', $product)); ?>" class="action-btn" title="View History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-archive"></i>
                            <p>No products found</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('inventory.adjust')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" id="adjustProductId">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-slash-minus me-2"></i>Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="adjustProductName" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="adjustCurrentStock" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Adjustment Type</label>
                        <select name="type" class="form-select" required>
                            <option value="adjustment">Stock Adjustment (+/-)</option>
                            <option value="damage">Damaged/Lost</option>
                            <option value="expired">Expired</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required step="1" placeholder="Enter + to add, - to subtract">
                        <small class="text-muted">Use positive number to add stock, negative to reduce</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes/Reason</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Why are you adjusting the stock?"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-cpos btn-primary">
                        <i class="bi bi-check-lg"></i> Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');

    rows.forEach(row => {
        const name = row.dataset.name;
        const sku = row.dataset.sku;
        if (name.includes(searchTerm) || sku.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Open adjust modal
function openAdjustModal(productId, productName, currentStock) {
    document.getElementById('adjustProductId').value = productId;
    document.getElementById('adjustProductName').value = productName;
    document.getElementById('adjustCurrentStock').value = currentStock + ' units';
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/inventory/index.blade.php ENDPATH**/ ?>