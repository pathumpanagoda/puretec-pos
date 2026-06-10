<?php $__env->startSection('title', 'Inventory Report'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Inventory</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Inventory Report</h1>
        <p class="page-subtitle">Stock levels, valuation, and alerts</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'inventory', 'params' => []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <a href="<?php echo e(route('inventory.index')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-box-seam"></i> Manage Inventory
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
            <div class="stat-value"><?php echo e($products->count()); ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalValue, 0)); ?></div>
            <div class="stat-label">Stock Value (Retail)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value"><?php echo e($lowStock->count()); ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-value"><?php echo e($outOfStock->count()); ?></div>
            <div class="stat-label">Out of Stock</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Stock Valuation</h5>
            </div>
            <div class="card-body p-4">
                <div class="value-row">
                    <span>Cost Value</span>
                    <span class="fw-600">Rs. <?php echo e(number_format($totalCost, 2)); ?></span>
                </div>
                <div class="value-row">
                    <span>Retail Value</span>
                    <span class="fw-600">Rs. <?php echo e(number_format($totalValue, 2)); ?></span>
                </div>
                <hr>
                <div class="value-row text-success">
                    <span class="fw-600">Potential Profit</span>
                    <span class="fw-700">Rs. <?php echo e(number_format($potentialProfit, 2)); ?></span>
                </div>
            </div>
        </div>

        
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Stock by Category</h5>
            </div>
            <div class="card-body p-4">
                <?php $__empty_1 = true; $__currentLoopData = $stockByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="category-stock-row">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-600"><?php echo e($category ?? 'Uncategorized'); ?></span>
                        <span><?php echo e($data['count']); ?> products</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><?php echo e(number_format($data['quantity'])); ?> units</span>
                        <span>Rs. <?php echo e(number_format($data['value'], 0)); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-3">No categories</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header bg-warning-soft">
                <h5 class="card-title text-warning"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
                <span class="badge bg-warning text-dark"><?php echo e($lowStock->count()); ?></span>
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                <?php $__empty_1 = true; $__currentLoopData = $lowStock->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="alert-item">
                    <div class="product-thumb-sm me-3">
                        <?php if($product->image): ?>
                            <img src="<?php echo e(asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image)); ?>" alt="<?php echo e($product->name); ?>" class="thumb-img-sm">
                        <?php else: ?>
                            <i class="bi bi-box thumb-placeholder-sm"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-600"><?php echo e($product->name); ?></div>
                        <small class="text-muted text-mono"><?php echo e($product->sku); ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark"><?php echo e($product->stock_quantity); ?> left</span>
                        <small class="d-block text-muted">Min: <?php echo e($product->min_stock); ?></small>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mb-0 mt-2">All items in stock</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="cpos-card">
            <div class="cpos-card-header bg-danger-soft">
                <h5 class="card-title text-danger"><i class="bi bi-x-circle"></i> Out of Stock</h5>
                <span class="badge bg-danger"><?php echo e($outOfStock->count()); ?></span>
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                <?php $__empty_1 = true; $__currentLoopData = $outOfStock->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="alert-item">
                    <div class="product-thumb-sm me-3">
                        <?php if($product->image): ?>
                            <img src="<?php echo e(asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image)); ?>" alt="<?php echo e($product->name); ?>" class="thumb-img-sm">
                        <?php else: ?>
                            <i class="bi bi-box thumb-placeholder-sm"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-600"><?php echo e($product->name); ?></div>
                        <small class="text-muted text-mono"><?php echo e($product->sku); ?></small>
                    </div>
                    <span class="badge bg-danger">Out of Stock</span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mb-0 mt-2">No items out of stock</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Top Selling (30 days)</h5>
            </div>
            <div class="card-body p-0">
                <?php $__empty_1 = true; $__currentLoopData = $topSelling; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="top-product-item">
                    <div class="top-rank"><?php echo e($index + 1); ?></div>
                    <div>
                        <div class="top-name"><?php echo e($product->product_name); ?></div>
                        <div class="top-meta"><?php echo e(number_format($product->total_sold)); ?> units sold</div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state-sm">
                    <i class="bi bi-box"></i>
                    <p>No sales data</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Products Stock</h5>
        <span class="text-muted"><?php echo e($products->count()); ?> products</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th style="width: 60px">Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Min Stock</th>
                    <th class="text-center">Unit</th>
                    <th class="text-end">Cost Price</th>
                    <th class="text-end">Sell Price</th>
                    <th class="text-end">Stock Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $products->sortBy('stock_quantity')->take(50); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="product-thumb">
                            <?php if($product->image): ?>
                                <img src="<?php echo e(asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image)); ?>" alt="<?php echo e($product->name); ?>" class="thumb-img">
                            <?php else: ?>
                                <i class="bi bi-box thumb-placeholder"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-600"><?php echo e($product->name); ?></div>
                        <small class="text-muted text-mono"><?php echo e($product->sku); ?></small>
                        <?php if($product->barcode): ?>
                            <small class="d-block text-muted">Barcode: <?php echo e($product->barcode); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($product->category?->name ?? '-'); ?></td>
                    <td><?php echo e($product->supplier?->name ?? '-'); ?></td>
                    <td class="text-center">
                        <?php if(!$product->track_stock): ?>
                            <span class="text-muted">N/A</span>
                        <?php else: ?>
                            <span class="fw-600 <?php echo e($product->stock_quantity <= 0 ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : '')); ?>">
                                <?php echo e(number_format($product->stock_quantity, 2)); ?>

                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e(number_format($product->min_stock ?? 0, 2)); ?></td>
                    <td class="text-center"><span class="badge bg-light text-dark"><?php echo e(strtoupper($product->unit ?? 'PCS')); ?></span></td>
                    <td class="text-end">Rs. <?php echo e(number_format($product->cost_price, 2)); ?></td>
                    <td class="text-end">Rs. <?php echo e(number_format($product->selling_price, 2)); ?></td>
                    <td class="text-end fw-600">Rs. <?php echo e(number_format($product->selling_price * $product->stock_quantity, 0)); ?></td>
                    <td>
                        <?php if(!$product->track_stock): ?>
                            <span class="badge bg-secondary">Untracked</span>
                        <?php elseif($product->isOutOfStock()): ?>
                            <span class="status-badge status-cancelled">Out of Stock</span>
                        <?php elseif($product->isLowStock()): ?>
                            <span class="status-badge status-pending">Low Stock</span>
                        <?php else: ?>
                            <span class="status-badge status-completed">In Stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.value-row { display: flex; justify-content: space-between; padding: 10px 0; }
.category-stock-row { padding: 12px 0; border-bottom: 1px solid var(--cp-border-light); }
.category-stock-row:last-child { border-bottom: none; }
.alert-item {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--cp-border-light);
}
.alert-item:last-child { border-bottom: none; }
.bg-warning-soft { background: var(--cp-warning-light) !important; }
.bg-danger-soft { background: var(--cp-danger-light) !important; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
/* Product thumbnails */
.product-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--cp-bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--cp-border-light);
}
.product-thumb .thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-thumb .thumb-placeholder {
    font-size: 20px;
    color: var(--cp-text-muted);
}
.product-thumb-sm {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    overflow: hidden;
    background: var(--cp-bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--cp-border-light);
    flex-shrink: 0;
}
.product-thumb-sm .thumb-img-sm {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-thumb-sm .thumb-placeholder-sm {
    font-size: 16px;
    color: var(--cp-text-muted);
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/inventory.blade.php ENDPATH**/ ?>