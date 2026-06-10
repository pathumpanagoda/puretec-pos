<?php $__env->startSection('title', 'Cash Balance Sheet'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Cash Balance Sheet</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Cash Balance Sheet</h1>
        <p class="page-subtitle"><?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php echo $__env->make('components.report-export', ['report' => 'balance-sheet', 'params' => ['from' => $startDate, 'to' => $endDate]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo e(route('reports.balance-sheet', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Week</a>
                    <a href="<?php echo e(route('reports.balance-sheet', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Month</a>
                    <a href="<?php echo e(route('reports.balance-sheet', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($openingBalance, 0)); ?></div>
            <div class="stat-label">Opening Balance</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalInflows, 0)); ?></div>
            <div class="stat-label">Total Inflows</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalOutflows, 0)); ?></div>
            <div class="stat-label">Total Outflows</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card <?php echo e($closingBalance >= $openingBalance ? 'stat-green' : 'stat-warning'); ?>">
            <div class="stat-icon"><i class="bi bi-safe"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($closingBalance, 0)); ?></div>
            <div class="stat-label">Closing Balance</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="mb-0">Net Change for Period</h6>
                <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($startDate)->format('M d')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></small>
            </div>
            <div class="col-md-6 text-end">
                <span class="fs-3 fw-bold <?php echo e($netChange >= 0 ? 'text-success' : 'text-danger'); ?>">
                    <?php echo e($netChange >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($netChange, 2)); ?>

                </span>
                <?php if($netChange >= 0): ?>
                    <i class="bi bi-arrow-up-circle-fill text-success ms-2"></i>
                <?php else: ?>
                    <i class="bi bi-arrow-down-circle-fill text-danger ms-2"></i>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="cpos-card h-100">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-down-circle text-success"></i> Inflows Breakdown</h5>
                <span class="fw-600 text-success">Rs. <?php echo e(number_format($totalInflows, 2)); ?></span>
            </div>
            <div class="card-body p-4">
                <?php $__currentLoopData = $inflowBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="balance-item">
                    <div class="bi-icon bg-<?php echo e($item['color']); ?>-light"><i class="bi <?php echo e($item['icon']); ?>"></i></div>
                    <div class="bi-info">
                        <span class="bi-name"><?php echo e($item['name']); ?></span>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-<?php echo e($item['color']); ?>" style="width: <?php echo e($totalInflows > 0 ? ($item['amount'] / $totalInflows * 100) : 0); ?>%"></div>
                        </div>
                    </div>
                    <span class="bi-amount text-success">Rs. <?php echo e(number_format($item['amount'], 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="cpos-card h-100">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-arrow-up-circle text-danger"></i> Outflows Breakdown</h5>
                <span class="fw-600 text-danger">Rs. <?php echo e(number_format($totalOutflows, 2)); ?></span>
            </div>
            <div class="card-body p-4">
                <?php $__currentLoopData = $outflowBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="balance-item">
                    <div class="bi-icon bg-<?php echo e($item['color']); ?>-light"><i class="bi <?php echo e($item['icon']); ?>"></i></div>
                    <div class="bi-info">
                        <span class="bi-name"><?php echo e($item['name']); ?></span>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-<?php echo e($item['color']); ?>" style="width: <?php echo e($totalOutflows > 0 ? ($item['amount'] / $totalOutflows * 100) : 0); ?>%"></div>
                        </div>
                    </div>
                    <span class="bi-amount text-danger">Rs. <?php echo e(number_format($item['amount'], 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>


<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-table"></i> Daily Balance Sheet</h5>
        <span class="badge bg-primary"><?php echo e(count($dailyTransactions)); ?> days with transactions</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-end text-success">Cash In</th>
                    <th class="text-end text-primary">Card In</th>
                    <th class="text-end text-info">Other In</th>
                    <th class="text-end fw-bold" style="background: var(--cp-success-light);">Total In</th>
                    <th class="text-end text-danger">Cash Out</th>
                    <th class="text-end text-warning">Bank Out</th>
                    <th class="text-end" style="color: #7c3aed;">Purchases</th>
                    <th class="text-end fw-bold" style="background: var(--cp-danger-light);">Total Out</th>
                    <th class="text-end fw-bold">Net</th>
                    <th class="text-end fw-bold" style="background: var(--cp-blue-100);">Balance</th>
                </tr>
            </thead>
            <tbody>
                
                <tr style="background: var(--cp-bg-alt);">
                    <td class="fw-bold">Opening Balance</td>
                    <td colspan="9"></td>
                    <td class="text-end fw-bold">Rs. <?php echo e(number_format($openingBalance, 2)); ?></td>
                </tr>

                <?php $__empty_1 = true; $__currentLoopData = $dailyTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-600"><?php echo e(\Carbon\Carbon::parse($day['date'])->format('M d (D)')); ?></td>
                    <td class="text-end text-success">
                        <?php if($day['cash_sales'] > 0): ?>
                            Rs. <?php echo e(number_format($day['cash_sales'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-primary">
                        <?php if($day['card_sales'] > 0): ?>
                            Rs. <?php echo e(number_format($day['card_sales'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-info">
                        <?php if($day['other_sales'] > 0): ?>
                            Rs. <?php echo e(number_format($day['other_sales'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-600 text-success" style="background: var(--cp-success-light);">
                        Rs. <?php echo e(number_format($day['total_in'], 2)); ?>

                    </td>
                    <td class="text-end text-danger">
                        <?php if($day['cash_expenses'] > 0): ?>
                            Rs. <?php echo e(number_format($day['cash_expenses'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-warning">
                        <?php if($day['bank_expenses'] > 0): ?>
                            Rs. <?php echo e(number_format($day['bank_expenses'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end" style="color: #7c3aed;">
                        <?php if($day['purchases'] > 0): ?>
                            Rs. <?php echo e(number_format($day['purchases'], 2)); ?>

                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-600 text-danger" style="background: var(--cp-danger-light);">
                        Rs. <?php echo e(number_format($day['total_out'], 2)); ?>

                    </td>
                    <td class="text-end fw-bold <?php echo e($day['net'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($day['net'] >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($day['net'], 2)); ?>

                    </td>
                    <td class="text-end fw-bold" style="background: var(--cp-blue-100);">
                        Rs. <?php echo e(number_format($day['balance'], 2)); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="11" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No transactions found for this period
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background: var(--cp-bg-alt);">
                    <td>TOTALS</td>
                    <td class="text-end text-success">Rs. <?php echo e(number_format($totalCashIn, 2)); ?></td>
                    <td class="text-end text-primary">Rs. <?php echo e(number_format($totalCardIn, 2)); ?></td>
                    <td class="text-end text-info">Rs. <?php echo e(number_format($totalOtherIn, 2)); ?></td>
                    <td class="text-end text-success" style="background: var(--cp-success-light);">Rs. <?php echo e(number_format($totalInflows, 2)); ?></td>
                    <td class="text-end text-danger">Rs. <?php echo e(number_format($totalCashOut, 2)); ?></td>
                    <td class="text-end text-warning">Rs. <?php echo e(number_format($totalBankOut, 2)); ?></td>
                    <td class="text-end" style="color: #7c3aed;">Rs. <?php echo e(number_format($totalPurchaseOut, 2)); ?></td>
                    <td class="text-end text-danger" style="background: var(--cp-danger-light);">Rs. <?php echo e(number_format($totalOutflows, 2)); ?></td>
                    <td class="text-end <?php echo e($netChange >= 0 ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($netChange >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($netChange, 2)); ?>

                    </td>
                    <td class="text-end" style="background: var(--cp-blue-100);">Rs. <?php echo e(number_format($closingBalance, 2)); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


<?php if($recentSessions->count() > 0): ?>
<div class="cpos-card mt-4">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-clipboard-check"></i> Cash Reconciliation</h5>
        <?php if($totalDiscrepancy != 0): ?>
            <span class="badge <?php echo e($totalDiscrepancy < 0 ? 'bg-danger' : 'bg-success'); ?>">
                <?php echo e($totalDiscrepancy < 0 ? 'Shortage' : 'Overage'); ?>: Rs. <?php echo e(number_format(abs($totalDiscrepancy), 2)); ?>

            </span>
        <?php else: ?>
            <span class="badge bg-success">Balanced</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Expected Cash</small>
                    <span class="fs-5 fw-bold">Rs. <?php echo e(number_format($totalExpected, 2)); ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Actual Cash Counted</small>
                    <span class="fs-5 fw-bold">Rs. <?php echo e(number_format($totalActual, 2)); ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded <?php echo e($totalDiscrepancy < 0 ? 'bg-danger-soft' : ($totalDiscrepancy > 0 ? 'bg-success-soft' : '')); ?>" style="background: var(--cp-bg-alt);">
                    <small class="text-muted d-block">Discrepancy</small>
                    <span class="fs-5 fw-bold <?php echo e($totalDiscrepancy < 0 ? 'text-danger' : ($totalDiscrepancy > 0 ? 'text-success' : '')); ?>">
                        <?php echo e($totalDiscrepancy >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($totalDiscrepancy, 2)); ?>

                    </span>
                </div>
            </div>
        </div>

        <h6 class="mb-3">Recent Register Sessions</h6>
        <div class="table-responsive">
            <table class="table table-sm table-cpos mb-0">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Actual</th>
                        <th class="text-end">Difference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $recentSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $diff = ($session->closing_balance ?? 0) - ($session->expected_balance ?? 0);
                    ?>
                    <tr>
                        <td><?php echo e($session->user?->name ?? 'Unknown'); ?></td>
                        <td><?php echo e($session->opened_at?->format('M d, H:i')); ?></td>
                        <td><?php echo e($session->closed_at?->format('M d, H:i')); ?></td>
                        <td class="text-end">Rs. <?php echo e(number_format($session->expected_balance ?? 0, 2)); ?></td>
                        <td class="text-end">Rs. <?php echo e(number_format($session->closing_balance ?? 0, 2)); ?></td>
                        <td class="text-end fw-bold <?php echo e($diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '')); ?>">
                            <?php if($diff != 0): ?>
                                <?php echo e($diff >= 0 ? '+' : ''); ?>Rs. <?php echo e(number_format($diff, 2)); ?>

                                <?php if($diff < 0): ?>
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('styles'); ?>
<style>
.balance-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid var(--cp-border-light);
}
.balance-item:last-child { border-bottom: none; }
.bi-icon {
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
.bi-info { flex: 1; }
.bi-name { display: block; font-weight: 600; font-size: 14px; }
.bi-amount { font-weight: 700; font-size: 15px; white-space: nowrap; }
.stat-danger::before { background: var(--cp-danger); }
.stat-danger .stat-icon { color: var(--cp-danger); background: var(--cp-danger-light); }
.bg-success-soft { background: var(--cp-success-light) !important; }
.bg-danger-soft { background: var(--cp-danger-light) !important; }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/balance-sheet.blade.php ENDPATH**/ ?>