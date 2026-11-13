<?php $__env->startSection('title', 'Customers'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Customers</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Customers</h1>
        <p class="page-subtitle">Manage your customer database</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('customers.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Customer
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-value"><?php echo e($customers->total()); ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="stat-value"><?php echo e($customers->where('is_active', true)->count()); ?></div>
            <div class="stat-label">Active Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-teal">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">Rs. <?php echo e(number_format($customers->sum('total_purchases'), 0)); ?></div>
            <div class="stat-label">Total Purchases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-star"></i></div>
            <div class="stat-value"><?php echo e(number_format($customers->sum('loyalty_points'), 0)); ?></div>
            <div class="stat-label">Total Loyalty Points</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-5">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email..." value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Group</label>
                <select name="group" class="form-select">
                    <option value="">All Groups</option>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->id); ?>" <?php echo e(request('group') == $group->id ? 'selected' : ''); ?>><?php echo e($group->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="<?php echo e(route('customers.index')); ?>" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-people"></i> All Customers</h5>
        <span class="text-muted"><?php echo e($customers->total()); ?> customers</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Group</th>
                    <th>Total Purchases</th>
                    <th>Loyalty Points</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width: 40px; height: 40px;">
                                <?php echo e(strtoupper(substr($customer->name, 0, 1))); ?>

                            </div>
                            <div>
                                <div class="fw-600"><?php echo e($customer->name); ?></div>
                                <?php if($customer->city): ?>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo e($customer->city); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if($customer->phone): ?>
                            <div><i class="bi bi-telephone text-muted me-1"></i> <?php echo e($customer->phone); ?></div>
                        <?php endif; ?>
                        <?php if($customer->email): ?>
                            <small class="text-muted"><i class="bi bi-envelope me-1"></i> <?php echo e($customer->email); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($customer->group): ?>
                            <span class="status-badge status-refunded"><?php echo e($customer->group->name); ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-600">Rs. <?php echo e(number_format($customer->total_purchases, 2)); ?></span>
                        <br><small class="text-muted"><?php echo e($customer->total_orders); ?> orders</small>
                    </td>
                    <td>
                        <span class="fw-600 text-primary"><?php echo e(number_format($customer->loyalty_points)); ?></span>
                        <?php if($customer->loyalty_tier): ?>
                            <br><small class="text-muted"><?php echo e(ucfirst($customer->loyalty_tier)); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($customer->is_active): ?>
                            <span class="status-badge status-completed">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('customers.show', $customer)); ?>" class="action-btn" title="View Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?php echo e(route('customers.edit', $customer)); ?>" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('customers.destroy', $customer)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this customer?">
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
                            <i class="bi bi-people"></i>
                            <p>No customers found</p>
                            <a href="<?php echo e(route('customers.create')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Customer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($customers->hasPages()): ?>
    <div class="card-body">
        <?php echo e($customers->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/customers/index.blade.php ENDPATH**/ ?>