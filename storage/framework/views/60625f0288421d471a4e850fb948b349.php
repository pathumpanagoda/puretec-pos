<?php $__env->startSection('title', 'Tenants'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Tenants</h1>
        <p class="text-muted">Manage all tenant accounts</p>
    </div>
    <a href="<?php echo e(route('nexfloit.tenants.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Tenant
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?php echo e(route('nexfloit.tenants.index')); ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search"
                       value="<?php echo e(request('search')); ?>"
                       placeholder="Business name, email, or code...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="locked" <?php echo e(request('status') == 'locked' ? 'selected' : ''); ?>>Locked</option>
                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    <option value="trial" <?php echo e(request('status') == 'trial' ? 'selected' : ''); ?>>On Trial</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Plan</label>
                <select class="form-select" name="plan">
                    <option value="">All Plans</option>
                    <option value="basic" <?php echo e(request('plan') == 'basic' ? 'selected' : ''); ?>>Basic</option>
                    <option value="standard" <?php echo e(request('plan') == 'standard' ? 'selected' : ''); ?>>Standard</option>
                    <option value="premium" <?php echo e(request('plan') == 'premium' ? 'selected' : ''); ?>>Premium</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tenants Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Business</th>
                        <th>Owner</th>
                        <th>Plan</th>
                        <th>Monthly Fee</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><code><?php echo e($tenant->code); ?></code></td>
                            <td>
                                <a href="<?php echo e(route('nexfloit.tenants.show', $tenant)); ?>" class="fw-medium">
                                    <?php echo e($tenant->business_name); ?>

                                </a>
                                <br><small class="text-muted"><?php echo e($tenant->email); ?></small>
                            </td>
                            <td><?php echo e($tenant->owner_name); ?></td>
                            <td>
                                <span class="badge <?php echo e($tenant->plan_badge['class']); ?>">
                                    <?php if($tenant->is_locked): ?>
                                        <i class="bi bi-lock-fill me-1"></i>
                                    <?php elseif(!$tenant->is_active): ?>
                                        <i class="bi bi-pause-circle me-1"></i>
                                    <?php elseif($tenant->isOnTrial()): ?>
                                        <i class="bi bi-hourglass-split me-1"></i>
                                    <?php endif; ?>
                                    <?php echo e($tenant->plan_badge['label']); ?>

                                </span>
                            </td>
                            <td>Rs. <?php echo e(number_format($tenant->monthly_fee, 2)); ?></td>
                            <td><?php echo e($tenant->created_at->format('M d, Y')); ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="<?php echo e(route('nexfloit.tenants.show', $tenant)); ?>"
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('nexfloit.tenants.edit', $tenant)); ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if($tenant->is_locked): ?>
                                        <form action="<?php echo e(route('nexfloit.tenants.unlock', $tenant)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Unlock">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Lock"
                                                data-bs-toggle="modal" data-bs-target="#lockModal<?php echo e($tenant->id); ?>">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Lock Modal -->
                                <div class="modal fade" id="lockModal<?php echo e($tenant->id); ?>" tabindex="-1">
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
                                                        <input type="text" class="form-control" name="reason"
                                                               value="Payment overdue" required>
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
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No tenants found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($tenants->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($tenants->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('nexfloit.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/nexfloit/tenants/index.blade.php ENDPATH**/ ?>