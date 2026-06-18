<?php $__env->startSection('title', 'Expenses'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Expenses</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage business expenses</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('expenses.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Expense
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($expenses->total()); ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($total, 0)); ?></div>
            <div class="stat-label">Total Expenses (Filtered)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-calendar-date"></i></div>
            <div class="stat-value"><?php echo e($expenses->count() > 0 ? $expenses->first()->expense_date?->format('M d') : '-'); ?></div>
            <div class="stat-label">Latest Expense</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-3">
                <label class="form-label small text-muted">From Date</label>
                <input type="date" name="from" class="form-control" value="<?php echo e(request('from')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">To Date</label>
                <input type="date" name="to" class="form-control" value="<?php echo e(request('to')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="<?php echo e(route('expenses.index')); ?>" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-wallet2"></i> All Expenses</h5>
        <span class="text-muted"><?php echo e($expenses->total()); ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Added By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="fw-600"><?php echo e($expense->expense_date?->format('M d, Y') ?? '-'); ?></div>
                    </td>
                    <td>
                        <div class="fw-600"><?php echo e($expense->title); ?></div>
                        <?php if($expense->notes): ?>
                            <small class="text-muted"><?php echo e(Str::limit($expense->notes, 40)); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($expense->category): ?>
                            <span class="badge" style="background: <?php echo e($expense->category->color ?? '#6c757d'); ?>20; color: <?php echo e($expense->category->color ?? '#6c757d'); ?>;">
                                <?php echo e($expense->category->name); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-muted">Uncategorized</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            $paymentIcon = match($expense->payment_method) {
                                'cash' => 'bi-cash',
                                'card' => 'bi-credit-card',
                                'bank_transfer' => 'bi-bank',
                                default => 'bi-wallet2'
                            };
                        ?>
                        <span><i class="<?php echo e($paymentIcon); ?> me-1 text-muted"></i><?php echo e(ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'cash'))); ?></span>
                    </td>
                    <td>
                        <span class="fw-600 text-danger">Rs. <?php echo e(number_format($expense->amount, 2)); ?></span>
                    </td>
                    <td><?php echo e($expense->user?->name ?? '-'); ?></td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('expenses.edit', $expense)); ?>" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('expenses.destroy', $expense)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this expense?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-wallet2"></i>
                            <p>No expenses recorded</p>
                            <a href="<?php echo e(route('expenses.create')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Expense
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($expenses->hasPages()): ?>
    <div class="card-body">
        <?php echo e($expenses->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/expenses/index.blade.php ENDPATH**/ ?>