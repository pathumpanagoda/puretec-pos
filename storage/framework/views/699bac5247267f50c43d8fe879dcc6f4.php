<?php $__env->startSection('title', 'Daily Sales Report'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Daily Sales</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Daily Sales Report</h1>
        <p class="page-subtitle"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'daily', 'params' => ['date' => $date]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="<?php echo e($date); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-search"></i> View Report
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?php echo e(route('reports.daily', ['date' => now()->toDateString()])); ?>" class="btn btn-outline-secondary w-100">Today</a>
            </div>
        </form>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($summary['total_orders']); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['total_sales'], 0)); ?></div>
            <div class="stat-label">Total Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-cash"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['cash_sales'], 0)); ?></div>
            <div class="stat-label">Cash Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($summary['card_sales'], 0)); ?></div>
            <div class="stat-label">Card Sales</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-4">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> Order Status</h5>
            </div>
            <div class="card-body p-4">
                <div class="status-breakdown">
                    <div class="status-row">
                        <span class="status-badge status-completed">Completed</span>
                        <span class="fw-600"><?php echo e($summary['completed']); ?></span>
                    </div>
                    <div class="status-row">
                        <span class="status-badge status-cancelled">Cancelled</span>
                        <span class="fw-600"><?php echo e($summary['cancelled']); ?></span>
                    </div>
                    <div class="status-row">
                        <span class="status-badge status-refunded">Refunded</span>
                        <span class="fw-600"><?php echo e($summary['refunded']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Sales Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="summary-row">
                    <span>Gross Sales</span>
                    <span class="fw-600">Rs. <?php echo e(number_format($summary['total_sales'] + $summary['total_discount'], 2)); ?></span>
                </div>
                <div class="summary-row text-success">
                    <span>Discounts</span>
                    <span>- Rs. <?php echo e(number_format($summary['total_discount'], 2)); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax Collected</span>
                    <span>Rs. <?php echo e(number_format($summary['total_tax'], 2)); ?></span>
                </div>
                <hr>
                <div class="summary-row fw-bold">
                    <span>Net Sales</span>
                    <span class="text-primary">Rs. <?php echo e(number_format($summary['total_sales'], 2)); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-8">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Hourly Sales</h5>
            </div>
            <div class="card-body p-4">
                <div class="hourly-chart">
                    <?php for($h = 6; $h <= 22; $h++): ?>
                        <?php
                            $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
                            $data = $hourlyData[$hour] ?? ['count' => 0, 'total' => 0];
                            $maxTotal = $hourlyData->max('total') ?: 1;
                            $percentage = ($data['total'] / $maxTotal) * 100;
                        ?>
                        <div class="hour-bar">
                            <div class="hour-label"><?php echo e($h > 12 ? ($h - 12) . 'PM' : ($h == 12 ? '12PM' : $h . 'AM')); ?></div>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?php echo e($percentage); ?>%"></div>
                            </div>
                            <div class="hour-value">
                                <?php if($data['count'] > 0): ?>
                                    <span class="badge bg-light text-dark"><?php echo e($data['count']); ?></span>
                                    Rs. <?php echo e(number_format($data['total'], 0)); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Transactions</h5>
        <span class="text-muted"><?php echo e($orders->count()); ?> orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($order->created_at->format('h:i A')); ?></td>
                    <td>
                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="fw-600 text-mono" style="color: var(--cp-primary);">
                            <?php echo e($order->order_number); ?>

                        </a>
                    </td>
                    <td><?php echo e($order->customer?->name ?? 'Walk-in'); ?></td>
                    <td><?php echo e($order->user?->name ?? '-'); ?></td>
                    <td><span class="badge bg-light text-dark"><?php echo e($order->items->count()); ?></span></td>
                    <td>
                        <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-light text-dark"><?php echo e(ucfirst($payment->method)); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td class="fw-600">Rs. <?php echo e(number_format($order->total_amount, 2)); ?></td>
                    <td>
                        <?php
                            $statusClass = match($order->status) {
                                'completed' => 'status-completed',
                                'pending' => 'status-pending',
                                'cancelled' => 'status-cancelled',
                                'refunded' => 'status-refunded',
                                default => 'status-pending'
                            };
                        ?>
                        <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($order->status)); ?></span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-calendar-x"></i>
                            <p>No transactions on this date</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.status-breakdown { display: flex; flex-direction: column; gap: 12px; }
.status-row { display: flex; justify-content: space-between; align-items: center; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
.hourly-chart { display: flex; flex-direction: column; gap: 8px; }
.hour-bar { display: flex; align-items: center; gap: 12px; }
.hour-label { width: 50px; font-size: 12px; color: var(--cp-text-muted); text-align: right; }
.bar-container { flex: 1; height: 24px; background: var(--cp-bg-alt); border-radius: 6px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(90deg, var(--cp-blue-400), var(--cp-primary)); border-radius: 6px; transition: width 0.3s ease; }
.hour-value { width: 120px; font-size: 12px; text-align: right; }

@media print {
    .page-actions, .cpos-card:first-child { display: none !important; }
    .cpos-card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/daily.blade.php ENDPATH**/ ?>