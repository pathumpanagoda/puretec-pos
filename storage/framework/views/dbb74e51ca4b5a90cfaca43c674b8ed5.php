<?php $__env->startSection('title','Dashboard'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-subtitle"><?php echo e(now()->format('l, d F Y')); ?> — Welcome back, <?php echo e(auth()->user()->name); ?>!</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('pos.index')); ?>" class="btn btn-primary btn-cpos">
            <i class="bi bi-cart3 me-2"></i>Open POS
        </a>
    </div>
</div>

<!-- Today Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-currency-exchange"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($todayStats['total_sales'], 2)); ?></div>
            <div class="stat-label">Today's Sales</div>
            <div class="stat-sub"><i class="bi bi-receipt me-1"></i><?php echo e($todayStats['total_orders']); ?> orders</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($todayStats['gross_profit'], 2)); ?></div>
            <div class="stat-label">Today's Profit</div>
            <div class="stat-sub"><i class="bi bi-percent me-1"></i>Gross profit</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card stat-teal">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-value"><?php echo e(number_format($totalCustomers)); ?></div>
            <div class="stat-label">Total Customers</div>
            <div class="stat-sub"><i class="bi bi-person-plus me-1"></i>Active accounts</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card <?php echo e($lowStockCount > 0 ? 'stat-warning' : 'stat-purple'); ?>">
            <div class="stat-icon"><i class="bi bi-<?php echo e($lowStockCount > 0 ? 'exclamation-triangle' : 'box-seam'); ?>"></i></div>
            <div class="stat-value"><?php echo e($lowStockCount > 0 ? $lowStockCount : number_format($totalProducts)); ?></div>
            <div class="stat-label"><?php echo e($lowStockCount > 0 ? 'Low Stock Alerts' : 'Total Products'); ?></div>
            <div class="stat-sub"><a href="<?php echo e(route('reports.inventory')); ?>" class="text-inherit">View details →</a></div>
        </div>
    </div>
</div>

<!-- Monthly Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-header"><span>Monthly Sales</span><i class="bi bi-calendar3 text-muted"></i></div>
            <div class="metric-value">Rs. <?php echo e(number_format($monthStats['total_sales'], 2)); ?></div>
            <div class="metric-detail"><?php echo e($monthStats['total_orders']); ?> orders • Avg: Rs. <?php echo e(number_format($monthStats['avg_order_value'], 2)); ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-header"><span>Monthly Profit</span><i class="bi bi-graph-up text-success"></i></div>
            <div class="metric-value text-success">Rs. <?php echo e(number_format($monthStats['total_profit'], 2)); ?></div>
            <div class="metric-detail">After expenses deducted</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-header"><span>Discounts Given</span><i class="bi bi-tags text-warning"></i></div>
            <div class="metric-value text-warning">Rs. <?php echo e(number_format($monthStats['total_discounts'], 2)); ?></div>
            <div class="metric-detail">This month</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-header"><span>Held Orders</span><i class="bi bi-pause-circle text-info"></i></div>
            <div class="metric-value text-info"><?php echo e($pendingOrders); ?></div>
            <div class="metric-detail"><a href="<?php echo e(route('pos.index')); ?>">Resume in POS →</a></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sales Chart -->
    <div class="col-lg-8">
        <div class="card cpos-card">
            <div class="card-header cpos-card-header">
                <h5 class="card-title mb-0"><i class="bi bi-bar-chart me-2"></i>Sales Overview (Last 7 Days)</h5>
                <a href="<?php echo e(route('reports.sales')); ?>" class="btn btn-sm btn-outline-primary">Full Report</a>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="card cpos-card">
            <div class="card-header cpos-card-header">
                <h5 class="card-title mb-0"><i class="bi bi-trophy me-2"></i>Top Products</h5>
                <span class="badge bg-primary-soft text-primary">This Month</span>
            </div>
            <div class="card-body p-0">
                <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="top-product-item">
                    <div class="top-rank"><?php echo e($i + 1); ?></div>
                    <div class="top-info">
                        <div class="top-name"><?php echo e($prod->product_name); ?></div>
                        <div class="top-meta"><?php echo e(number_format($prod->total_qty, 0)); ?> sold</div>
                    </div>
                    <div class="top-revenue">Rs. <?php echo e(number_format($prod->total_revenue, 0)); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state-sm"><i class="bi bi-inbox"></i><p>No sales data yet</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card cpos-card">
            <div class="card-header cpos-card-header">
                <h5 class="card-title mb-0"><i class="bi bi-receipt me-2"></i>Recent Orders</h5>
                <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-cpos mb-0">
                        <thead>
                            <tr><th>Order #</th><th>Customer</th><th>Cashier</th><th>Total</th><th>Status</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('orders.show', $order)); ?>" class="link-primary fw-600"><?php echo e($order->order_number); ?></a></td>
                                <td><?php echo e($order->customer?->name ?? 'Walk-in'); ?></td>
                                <td><?php echo e($order->user?->name ?? '—'); ?></td>
                                <td class="fw-600">Rs. <?php echo e(number_format($order->total_amount, 2)); ?></td>
                                <td><span class="badge status-badge status-<?php echo e($order->status); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
                                <td class="text-muted"><?php echo e($order->created_at->diffForHumans()); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No orders yet today</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Chart -->
    <div class="col-lg-4">
        <div class="card cpos-card">
            <div class="card-header cpos-card-header">
                <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Payment Methods</h5>
                <span class="badge bg-primary-soft text-primary">Today</span>
            </div>
            <div class="card-body">
                <canvas id="paymentChart" height="160"></canvas>
                <div class="payment-legend mt-3">
                    <?php $__currentLoopData = $todayStats['payments_by_method'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="payment-legend-item">
                        <span class="legend-dot"></span>
                        <span class="legend-label"><?php echo e(ucfirst(str_replace('_', ' ', $method))); ?></span>
                        <span class="legend-value">Rs. <?php echo e(number_format($amount, 0)); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if(empty($todayStats['payments_by_method'])): ?>
                    <div class="empty-state-sm"><i class="bi bi-credit-card"></i><p>No payments today</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const chartColors = { blue: '#1a9bc1', green: '#22c55e', teal: '#0d9488', grid: 'rgba(0,0,0,0.05)' };

// Sales chart
const salesData = <?php echo json_encode($salesChart, 15, 512) ?>;
const labels    = salesData.map(d => new Date(d.date).toLocaleDateString('en-GB', { month:'short', day:'numeric' }));
const salesVals = salesData.map(d => parseFloat(d.sales) || 0);
const profitVals= salesData.map(d => parseFloat(d.profit) || 0);

if (document.getElementById('salesChart')) {
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Sales (Rs.)', data: salesVals, backgroundColor: 'rgba(26, 155, 193, 0.15)', borderColor: chartColors.blue, borderWidth: 2, borderRadius: 6, yAxisID: 'y' },
                { label: 'Profit (Rs.)', data: profitVals, type: 'line', borderColor: chartColors.green, backgroundColor: 'transparent', borderWidth: 2.5, pointBackgroundColor: chartColors.green, pointRadius: 4, tension: 0.4, yAxisID: 'y' },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } } },
            scales: {
                y: { beginAtZero: true, grid: { color: chartColors.grid }, ticks: { callback: v => 'Rs. ' + (v/1000).toFixed(0) + 'k' } },
                x: { grid: { display: false } }
            }
        }
    });
}

// Payment methods donut
const payMethods = <?php echo json_encode($todayStats['payments_by_method'] ?? [], 15, 512) ?>;
const pmKeys = Object.keys(payMethods);
const pmVals = Object.values(payMethods).map(Number);
const pmColors = ['#1a9bc1','#22c55e','#0d9488','#f59e0b','#6366f1','#ec4899'];

if (document.getElementById('paymentChart') && pmKeys.length) {
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: pmKeys.map(k => k.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase())),
            datasets: [{ data: pmVals, backgroundColor: pmColors, borderWidth: 0, hoverOffset: 8 }]
        },
        options: { responsive: true, cutout: '70%', plugins: { legend: { display: false } } }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>