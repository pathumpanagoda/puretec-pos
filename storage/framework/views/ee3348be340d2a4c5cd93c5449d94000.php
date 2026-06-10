<?php $__env->startSection('title', 'Sales Report'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Sales Analysis</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Sales Analysis</h1>
        <p class="page-subtitle"><?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'sales', 'params' => ['from' => $startDate, 'to' => $endDate]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?php echo e($startDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?php echo e($endDate); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-4">
                <div class="btn-group w-100">
                    <a href="<?php echo e(route('reports.sales', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Week</a>
                    <a href="<?php echo e(route('reports.sales', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Month</a>
                    <a href="<?php echo e(route('reports.sales', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['total_sales'] ?? 0, 0)); ?></div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($summary['total_orders'] ?? 0); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['gross_profit'] ?? 0, 0)); ?></div>
            <div class="stat-label">Gross Profit</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calculator"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['avg_order_value'] ?? 0, 0)); ?></div>
            <div class="stat-label">Avg. Order Value</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="top-product-item">
                    <div class="top-rank"><?php echo e($index + 1); ?></div>
                    <div>
                        <div class="top-name"><?php echo e($product->product_name); ?></div>
                        <div class="top-meta"><?php echo e($product->total_qty); ?> units sold</div>
                    </div>
                    <div class="top-revenue">Rs. <?php echo e(number_format($product->total_revenue, 0)); ?></div>
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

    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Sales by Category</h5>
            </div>
            <div class="card-body p-4">
                <?php $totalCategorySales = $salesByCategory->sum('total'); ?>
                <?php $__empty_1 = true; $__currentLoopData = $salesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="category-row mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-600"><?php echo e($cat->category ?? 'Uncategorized'); ?></span>
                        <span>Rs. <?php echo e(number_format($cat->total, 0)); ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo e($totalCategorySales > 0 ? ($cat->total / $totalCategorySales) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-4">No category data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-person-badge"></i> Sales by Cashier</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Cashier</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $salesByUser; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-600"><?php echo e($user['user']); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark"><?php echo e($user['orders']); ?></span></td>
                            <td class="text-end fw-600 text-success">Rs. <?php echo e(number_format($user['total'], 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">No data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-graph-up"></i> Daily Sales Trend</h5>
            </div>
            <div class="card-body p-4">
                <div class="mini-chart">
                    <?php $maxDaily = collect($dailyChart)->max('sales') ?: 1; ?>
                    <?php $__currentLoopData = array_slice($dailyChart, -14); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="chart-bar-wrap" title="<?php echo e($day['date']); ?>: Rs. <?php echo e(number_format($day['sales'] ?? 0, 0)); ?>">
                        <div class="chart-bar" style="height: <?php echo e((($day['sales'] ?? 0) / $maxDaily) * 100); ?>%"></div>
                        <span class="chart-label"><?php echo e(\Carbon\Carbon::parse($day['date'])->format('d')); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.mini-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 150px;
    gap: 4px;
    padding-top: 20px;
}
.chart-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.chart-bar {
    width: 100%;
    max-width: 30px;
    background: linear-gradient(180deg, var(--cp-primary), var(--cp-blue-400));
    border-radius: 4px 4px 0 0;
    margin-top: auto;
    min-height: 4px;
    transition: height 0.3s ease;
}
.chart-bar-wrap:hover .chart-bar {
    background: var(--cp-primary-dark);
}
.chart-label {
    font-size: 10px;
    color: var(--cp-text-muted);
    margin-top: 6px;
}
.category-row .progress {
    background: var(--cp-bg-alt);
    border-radius: 10px;
}

@media print {
    .page-actions, .cpos-card:first-child { display: none !important; }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/sales.blade.php ENDPATH**/ ?>