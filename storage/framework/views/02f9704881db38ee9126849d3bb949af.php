<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="text-muted">Welcome back! Here's your platform overview.</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($totalTenants); ?></h3>
                <p class="stat-label">Total Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($activeTenants); ?></h3>
                <p class="stat-label">Active Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($lockedTenants); ?></h3>
                <p class="stat-label">Locked Tenants</p>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo e($trialTenants); ?></h3>
                <p class="stat-label">On Trial</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-calendar me-1"></i><?php echo e(\Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y')); ?>

                </h6>
                <h4 class="card-title mb-0">Expected Revenue</h4>
                <p class="display-6 text-primary mb-0">Rs. <?php echo e(number_format($expectedRevenue, 2)); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-check2-circle me-1"></i>Collected
                </h6>
                <h4 class="card-title mb-0">Received</h4>
                <p class="display-6 text-success mb-0">Rs. <?php echo e(number_format($collectedRevenue, 2)); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-exclamation-circle me-1"></i>Awaiting
                </h6>
                <h4 class="card-title mb-0">Pending Payments</h4>
                <p class="display-6 text-warning mb-0"><?php echo e($pendingPayments); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Overdue Subscriptions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Overdue Subscriptions
                </h5>
                <a href="<?php echo e(route('nexfloit.subscriptions.index', ['status' => 'overdue'])); ?>" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if($overdueSubscriptions->isEmpty()): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No overdue subscriptions!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $overdueSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($subscription->tenant->business_name); ?></strong>
                                            <br><small class="text-muted"><?php echo e($subscription->tenant->code); ?></small>
                                        </td>
                                        <td><?php echo e($subscription->billing_month_display); ?></td>
                                        <td>Rs. <?php echo e(number_format($subscription->amount, 2)); ?></td>
                                        <td>
                                            <span class="text-danger"><?php echo e($subscription->due_date->format('M d, Y')); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('nexfloit.subscriptions.record-payment', $subscription)); ?>"
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Payments
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($recentPayments->isEmpty()): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No recent payments</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo e($payment->tenant->business_name); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo e($payment->payment_date->format('M d, Y')); ?> via <?php echo e($payment->method_display); ?>

                                        </small>
                                    </div>
                                    <span class="badge bg-success">Rs. <?php echo e(number_format($payment->amount, 2)); ?></span>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tenants -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>Recent Tenants
                </h5>
                <a href="<?php echo e(route('nexfloit.tenants.create')); ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add Tenant
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Business</th>
                                <th>Plan</th>
                                <th>Monthly Fee</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $recentTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><code><?php echo e($tenant->code); ?></code></td>
                                    <td>
                                        <a href="<?php echo e(route('nexfloit.tenants.show', $tenant)); ?>">
                                            <?php echo e($tenant->business_name); ?>

                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($tenant->plan_badge['class']); ?>"><?php echo e($tenant->plan_badge['label']); ?></span>
                                    </td>
                                    <td>Rs. <?php echo e(number_format($tenant->monthly_fee, 2)); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($tenant->status_badge['class']); ?>">
                                            <?php echo e($tenant->status_badge['label']); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($tenant->created_at->format('M d, Y')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('nexfloit.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/nexfloit/dashboard.blade.php ENDPATH**/ ?>