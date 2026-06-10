<?php $__env->startSection('title', 'Income'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Income</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Income</h1>
        <p class="page-subtitle">Track all income sources</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('incomes.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Income
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($totalIncome, 0)); ?></div>
            <div class="stat-label">Total Income (Filtered)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($thisMonth, 0)); ?></div>
            <div class="stat-label">This Month</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($incomes->total()); ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category') == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash" <?php echo e(request('method') == 'cash' ? 'selected' : ''); ?>>Cash</option>
                    <option value="bank_transfer" <?php echo e(request('method') == 'bank_transfer' ? 'selected' : ''); ?>>Bank Transfer</option>
                    <option value="cheque" <?php echo e(request('method') == 'cheque' ? 'selected' : ''); ?>>Cheque</option>
                    <option value="other" <?php echo e(request('method') == 'other' ? 'selected' : ''); ?>>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control" value="<?php echo e(request('from')); ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control" value="<?php echo e(request('to')); ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn-cpos btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
            <div class="col-md-1">
                <a href="<?php echo e(route('incomes.index')); ?>" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-wallet2"></i> Income Records</h5>
        <span class="text-muted"><?php echo e($incomes->total()); ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Received From</th>
                    <th>Method</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $incomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $income): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div><?php echo e($income->income_date->format('M d, Y')); ?></div>
                    </td>
                    <td>
                        <div class="fw-600"><?php echo e($income->title); ?></div>
                        <?php if($income->reference_number): ?>
                            <small class="text-muted">Ref: <?php echo e($income->reference_number); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($income->category): ?>
                            <span class="badge" style="background: <?php echo e($income->category->color); ?>20; color: <?php echo e($income->category->color); ?>;">
                                <?php echo e($income->category->name); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($income->received_from ?? '-'); ?></td>
                    <td>
                        <?php
                            $methodIcon = match($income->payment_method) {
                                'cash' => 'bi-cash',
                                'bank_transfer' => 'bi-bank',
                                'cheque' => 'bi-file-earmark-text',
                                default => 'bi-wallet2'
                            };
                        ?>
                        <i class="<?php echo e($methodIcon); ?> me-1"></i>
                        <?php echo e(ucfirst(str_replace('_', ' ', $income->payment_method))); ?>

                    </td>
                    <td class="text-end">
                        <span class="fw-600 text-success">Rs. <?php echo e(number_format($income->amount, 2)); ?></span>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('incomes.edit', $income)); ?>" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('incomes.destroy', $income)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this income record?')">
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
                            <p>No income records found</p>
                            <a href="<?php echo e(route('incomes.create')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add Income
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($incomes->hasPages()): ?>
    <div class="card-body">
        <?php echo e($incomes->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/incomes/index.blade.php ENDPATH**/ ?>