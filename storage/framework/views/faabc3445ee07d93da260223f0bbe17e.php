<?php $__env->startSection('title', 'Expense Report'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Expenses</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Expense Report</h1>
        <p class="page-subtitle"><?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'expenses', 'params' => ['from' => $startDate, 'to' => $endDate]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <a href="<?php echo e(route('expenses.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Expense
        </a>
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
                    <a href="<?php echo e(route('reports.expenses', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Week</a>
                    <a href="<?php echo e(route('reports.expenses', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Month</a>
                    <a href="<?php echo e(route('reports.expenses', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalExpenses, 0)); ?></div>
            <div class="stat-label">Total Expenses</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($expenses->count()); ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calculator"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0, 0)); ?></div>
            <div class="stat-label">Avg. Expense</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart"></i> By Category</h5>
            </div>
            <div class="card-body p-4">
                <?php $__empty_1 = true; $__currentLoopData = $byCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="category-expense-row">
                    <div class="ce-info">
                        <span class="fw-600"><?php echo e($category); ?></span>
                        <span class="text-muted"><?php echo e($data['count']); ?> records</span>
                    </div>
                    <div class="ce-bar">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: <?php echo e($totalExpenses > 0 ? ($data['amount'] / $totalExpenses) * 100 : 0); ?>%"></div>
                        </div>
                    </div>
                    <span class="ce-amount">Rs. <?php echo e(number_format($data['amount'], 0)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-4">No expense data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> By Payment Method</h5>
            </div>
            <div class="card-body p-4">
                <?php
                    $paymentIcons = [
                        'cash' => 'bi-cash',
                        'card' => 'bi-credit-card',
                        'bank_transfer' => 'bi-bank',
                    ];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $byPaymentMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="payment-expense-row">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pe-icon">
                            <i class="bi <?php echo e($paymentIcons[$method] ?? 'bi-wallet2'); ?>"></i>
                        </div>
                        <span class="fw-600"><?php echo e(ucfirst(str_replace('_', ' ', $method))); ?></span>
                    </div>
                    <span class="fw-600 text-danger">Rs. <?php echo e(number_format($amount, 0)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-3">No data</div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-bar-chart"></i> Monthly Trend</h5>
            </div>
            <div class="card-body p-4">
                <?php $maxMonthly = max($monthlyComparison) ?: 1; ?>
                <div class="monthly-chart">
                    <?php $__currentLoopData = $monthlyComparison; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="month-bar-wrap">
                        <div class="month-bar" style="height: <?php echo e(($amount / $maxMonthly) * 100); ?>%">
                            <?php if($amount > 0): ?>
                            <span class="month-value"><?php echo e(number_format($amount / 1000, 0)); ?>K</span>
                            <?php endif; ?>
                        </div>
                        <span class="month-label"><?php echo e($month); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-list-ul"></i> All Expenses</h5>
        <span class="text-muted"><?php echo e($expenses->count()); ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Payment</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $expenses->take(30); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-600"><?php echo e($expense->expense_date?->format('M d, Y')); ?></td>
                    <td>
                        <div class="fw-600"><?php echo e($expense->title); ?></div>
                        <?php if($expense->notes): ?>
                            <small class="text-muted"><?php echo e(Str::limit($expense->notes, 50)); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($expense->category): ?>
                            <span class="badge" style="background: <?php echo e($expense->category->color ?? '#6c757d'); ?>20; color: <?php echo e($expense->category->color ?? '#6c757d'); ?>;">
                                <?php echo e($expense->category->name); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark"><?php echo e(ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'cash'))); ?></span>
                    </td>
                    <td class="text-end fw-600 text-danger">Rs. <?php echo e(number_format($expense->amount, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-receipt"></i>
                            <p>No expenses recorded</p>
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
.category-expense-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.category-expense-row:last-child { border-bottom: none; }
.ce-info { width: 140px; }
.ce-info span { display: block; }
.ce-bar { flex: 1; }
.ce-bar .progress { background: var(--cp-bg-alt); border-radius: 10px; }
.ce-amount { width: 100px; text-align: right; font-weight: 600; color: var(--cp-danger); }
.payment-expense-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.payment-expense-row:last-child { border-bottom: none; }
.pe-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--cp-bg-alt);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--cp-text-muted);
}
.monthly-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 140px;
    gap: 8px;
}
.month-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.month-bar {
    width: 100%;
    background: linear-gradient(180deg, var(--cp-danger), #f87171);
    border-radius: 4px 4px 0 0;
    margin-top: auto;
    min-height: 4px;
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.month-value {
    font-size: 10px;
    font-weight: 600;
    color: #fff;
    padding-top: 4px;
}
.month-label {
    font-size: 9px;
    color: var(--cp-text-muted);
    margin-top: 6px;
    white-space: nowrap;
}
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/expenses.blade.php ENDPATH**/ ?>