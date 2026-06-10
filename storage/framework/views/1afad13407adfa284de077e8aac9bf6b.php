<?php $__env->startSection('title', 'Cash Flow Report'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Cash Flow</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Cash Flow Report</h1>
        <p class="page-subtitle"><?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'cashflow', 'params' => ['from' => $startDate, 'to' => $endDate]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
        </form>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalInflows, 0)); ?></div>
            <div class="stat-label">Total Inflows</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalOutflows, 0)); ?></div>
            <div class="stat-label">Total Outflows</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card <?php echo e($netCashFlow >= 0 ? 'stat-blue' : 'stat-warning'); ?>">
            <div class="stat-icon"><i class="bi bi-<?php echo e($netCashFlow >= 0 ? 'graph-up' : 'graph-down'); ?>"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format(abs($netCashFlow), 0)); ?></div>
            <div class="stat-label"><?php echo e($netCashFlow >= 0 ? 'Net Cash Flow' : 'Cash Deficit'); ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-down-circle text-success"></i> Cash Inflows</h5>
                <span class="fw-600 text-success">Rs. <?php echo e(number_format($totalInflows, 2)); ?></span>
            </div>
            <div class="card-body p-4">
                <div class="cashflow-item">
                    <div class="cf-icon bg-success-light"><i class="bi bi-cash"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Cash Sales</span>
                        <span class="cf-desc">Direct cash payments</span>
                    </div>
                    <span class="cf-amount text-success">Rs. <?php echo e(number_format($cashSales, 2)); ?></span>
                </div>

                <div class="cashflow-item">
                    <div class="cf-icon bg-primary-light"><i class="bi bi-credit-card"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Card Payments</span>
                        <span class="cf-desc">Credit/Debit card</span>
                    </div>
                    <span class="cf-amount text-success">Rs. <?php echo e(number_format($cardSales, 2)); ?></span>
                </div>

                <div class="cashflow-item">
                    <div class="cf-icon bg-info-light"><i class="bi bi-wallet2"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Other Payments</span>
                        <span class="cf-desc">Bank transfer, mobile, etc.</span>
                    </div>
                    <span class="cf-amount text-success">Rs. <?php echo e(number_format($otherInflows, 2)); ?></span>
                </div>

                <div class="cashflow-item">
                    <div class="cf-icon bg-teal-light"><i class="bi bi-cash-stack"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Other Income</span>
                        <span class="cf-desc">Salary, investments, etc.</span>
                    </div>
                    <span class="cf-amount text-success">Rs. <?php echo e(number_format($otherIncome ?? 0, 2)); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-up-circle text-danger"></i> Cash Outflows</h5>
                <span class="fw-600 text-danger">Rs. <?php echo e(number_format($totalOutflows, 2)); ?></span>
            </div>
            <div class="card-body p-4">
                <div class="cashflow-item">
                    <div class="cf-icon bg-danger-light"><i class="bi bi-cash-stack"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Cash Expenses</span>
                        <span class="cf-desc">Paid in cash</span>
                    </div>
                    <span class="cf-amount text-danger">Rs. <?php echo e(number_format($expensesByCash, 2)); ?></span>
                </div>

                <div class="cashflow-item">
                    <div class="cf-icon bg-warning-light"><i class="bi bi-bank"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Bank/Card Expenses</span>
                        <span class="cf-desc">Transfer and card payments</span>
                    </div>
                    <span class="cf-amount text-danger">Rs. <?php echo e(number_format($expensesByBank, 2)); ?></span>
                </div>

                <div class="cashflow-item">
                    <div class="cf-icon bg-purple-light"><i class="bi bi-box-seam"></i></div>
                    <div class="cf-info">
                        <span class="cf-name">Purchase Payments</span>
                        <span class="cf-desc">Supplier payments</span>
                    </div>
                    <span class="cf-amount text-danger">Rs. <?php echo e(number_format($purchasePayments, 2)); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-calendar3"></i> Daily Cash Flow</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-end text-success">Inflows</th>
                    <th class="text-end text-danger">Outflows</th>
                    <th class="text-end">Net</th>
                </tr>
            </thead>
            <tbody>
                <?php $runningTotal = 0; ?>
                <?php $__currentLoopData = $dailyCashFlow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $runningTotal += $day['net']; ?>
                <tr>
                    <td class="fw-600"><?php echo e(\Carbon\Carbon::parse($day['date'])->format('M d, Y (D)')); ?></td>
                    <td class="text-end text-success">
                        <?php if($day['inflow'] > 0): ?>
                            Rs. <?php echo e(number_format($day['inflow'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-danger">
                        <?php if($day['outflow'] > 0): ?>
                            Rs. <?php echo e(number_format($day['outflow'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-600 <?php echo e($day['net'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($day['net'] >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($day['net'], 2)); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background: var(--cp-bg-alt);">
                    <td>Total</td>
                    <td class="text-end text-success">Rs. <?php echo e(number_format($totalInflows, 2)); ?></td>
                    <td class="text-end text-danger">Rs. <?php echo e(number_format($totalOutflows, 2)); ?></td>
                    <td class="text-end <?php echo e($netCashFlow >= 0 ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($netCashFlow >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($netCashFlow, 2)); ?>

                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.cashflow-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.cashflow-item:last-child { border-bottom: none; }
.cf-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.bg-success-light { background: var(--cp-success-light); color: var(--cp-success); }
.bg-primary-light { background: var(--cp-blue-100); color: var(--cp-primary); }
.bg-info-light { background: #e0f2fe; color: #0284c7; }
.bg-danger-light { background: var(--cp-danger-light); color: var(--cp-danger); }
.bg-warning-light { background: var(--cp-warning-light); color: var(--cp-warning); }
.bg-purple-light { background: #ede9fe; color: #7c3aed; }
.cf-info { flex: 1; }
.cf-name { display: block; font-weight: 600; font-size: 14px; }
.cf-desc { display: block; font-size: 12px; color: var(--cp-text-muted); }
.cf-amount { font-weight: 700; font-size: 15px; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/cashflow.blade.php ENDPATH**/ ?>