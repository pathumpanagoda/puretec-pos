<?php $__env->startSection('title', $tenant->business_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo e(route('nexfloit.tenants.index')); ?>">Tenants</a></li>
                <li class="breadcrumb-item active"><?php echo e($tenant->code); ?></li>
            </ol>
        </nav>
        <h1 class="page-title"><?php echo e($tenant->business_name); ?></h1>
        <p class="text-muted">
            <code><?php echo e($tenant->code); ?></code>
            <span class="badge <?php echo e($tenant->plan_badge['class']); ?> ms-2">
                <?php if($tenant->is_locked): ?>
                    <i class="bi bi-lock-fill me-1"></i>
                <?php elseif(!$tenant->is_active): ?>
                    <i class="bi bi-pause-circle me-1"></i>
                <?php elseif($tenant->isOnTrial()): ?>
                    <i class="bi bi-hourglass-split me-1"></i>
                <?php endif; ?>
                <?php echo e($tenant->plan_badge['label']); ?>

            </span>
        </p>
    </div>
    <div class="btn-group">
        <a href="<?php echo e(route('nexfloit.tenants.edit', $tenant)); ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if($tenant->is_locked): ?>
            <form action="<?php echo e(route('nexfloit.tenants.unlock', $tenant)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-unlock me-1"></i>Unlock
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#lockModal">
                <i class="bi bi-lock me-1"></i>Lock
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-shop"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($stats['stores_count']); ?></h3>
                <p class="stat-label">Stores</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($stats['users_count']); ?></h3>
                <p class="stat-label">Users</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-cash"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. <?php echo e(number_format($stats['total_paid'], 0)); ?></h3>
                <p class="stat-label">Total Paid</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass"></i></div>
            <div class="stat-content">
                <h3 class="stat-value">Rs. <?php echo e(number_format($stats['pending_amount'], 0)); ?></h3>
                <p class="stat-label">Pending</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Business Info -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="40%">Owner</th>
                        <td><?php echo e($tenant->owner_name); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><a href="mailto:<?php echo e($tenant->email); ?>"><?php echo e($tenant->email); ?></a></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo e($tenant->phone ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?php echo e($tenant->address ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Plan</th>
                        <td><span class="badge <?php echo e($tenant->plan_badge['class']); ?>"><?php echo e($tenant->plan_badge['label']); ?></span></td>
                    </tr>
                    <tr>
                        <th>Monthly Fee</th>
                        <td><strong>Rs. <?php echo e(number_format($tenant->monthly_fee, 2)); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Joined</th>
                        <td><?php echo e($tenant->created_at->format('M d, Y')); ?></td>
                    </tr>
                    <?php if($tenant->isOnTrial()): ?>
                        <tr>
                            <th>Trial Ends</th>
                            <td><span class="text-info"><?php echo e($tenant->trial_ends_at->format('M d, Y')); ?></span></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($tenant->is_locked): ?>
                        <tr>
                            <th>Locked At</th>
                            <td><span class="text-danger"><?php echo e($tenant->locked_at->format('M d, Y H:i')); ?></span></td>
                        </tr>
                        <tr>
                            <th>Lock Reason</th>
                            <td><span class="text-danger"><?php echo e($tenant->lock_reason); ?></span></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Stores -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shop me-2"></i>Stores</h5>
            </div>
            <div class="card-body p-0">
                <?php if($tenant->stores->isEmpty()): ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">No stores yet</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php $__currentLoopData = $tenant->stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo e($store->name); ?></strong>
                                    <br><small class="text-muted"><?php echo e($store->code); ?></small>
                                </div>
                                <span class="badge <?php echo e($store->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                    <?php echo e($store->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Subscriptions & Payments -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Recent Subscriptions</h5>
                <?php
                    $currentMonth = now()->format('Y-m');
                    $hasCurrentSub = $tenant->subscriptions->contains('billing_month', $currentMonth);
                ?>
                <?php if (! ($hasCurrentSub)): ?>
                    <form action="<?php echo e(route('nexfloit.tenants.create-subscription', $tenant)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="month" value="<?php echo e($currentMonth); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Create subscription for <?php echo e(now()->format('F Y')); ?>">
                            <i class="bi bi-plus-lg me-1"></i><?php echo e(now()->format('M Y')); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $tenant->subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($subscription->billing_month_display); ?></td>
                                    <td>Rs. <?php echo e(number_format($subscription->amount, 2)); ?></td>
                                    <td><?php echo e($subscription->due_date->format('M d')); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($subscription->status_badge['class']); ?>">
                                            <?php echo e($subscription->status_badge['label']); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($subscription->status !== 'paid'): ?>
                                            <a href="<?php echo e(route('nexfloit.subscriptions.record-payment', $subscription)); ?>"
                                               class="btn btn-sm btn-success" title="Record Payment">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No subscriptions yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Recent Payments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $tenant->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                                    <td>Rs. <?php echo e(number_format($payment->amount, 2)); ?></td>
                                    <td><?php echo e($payment->method_display); ?></td>
                                    <td><?php echo e($payment->reference_number ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No payments yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lock Modal -->
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('nexfloit.tenants.lock', $tenant)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Lock Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to lock <strong><?php echo e($tenant->business_name); ?></strong>?</p>
                    <p class="text-muted">This will prevent the tenant from accessing their POS system.</p>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" class="form-control" name="reason" value="Payment overdue" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Lock Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('nexfloit.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/nexfloit/tenants/show.blade.php ENDPATH**/ ?>