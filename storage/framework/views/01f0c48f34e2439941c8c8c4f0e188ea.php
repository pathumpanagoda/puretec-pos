<?php $__env->startSection('title', 'Users'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Users</li>
<?php $__env->stopSection(); ?>

<?php
    $seasonMode = auth()->user()->store->settings['season_mode'] ?? false;
?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage user accounts and permissions</p>
    </div>
    <div class="page-actions d-flex align-items-center gap-2">
        <?php if($seasonMode): ?>
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-sun-fill me-1"></i> Season Mode ON
            </span>
        <?php endif; ?>
        <a href="<?php echo e(route('users.create')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add User
        </a>
    </div>
</div>

<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-people"></i> All Users</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Payment Mode</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">
                                <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                            </div>
                            <div>
                                <div class="fw-600"><?php echo e($user->name); ?></div>
                                <small class="text-muted"><?php echo e($user->username); ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($user->email); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo e($user->role === 'admin' ? 'completed' : ($user->role === 'super_admin' ? 'refunded' : 'pending')); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?>

                        </span>
                    </td>
                    <td>
                        <?php if($user->isSuperAdmin() || $user->isAdmin() || $user->isManager()): ?>
                            <span class="badge bg-success-soft text-success">
                                <i class="bi bi-star-fill me-1"></i>Main Cashier
                            </span>
                        <?php elseif($user->payment_mode === 'bill_only'): ?>
                            <span class="badge bg-warning-soft text-warning">
                                <i class="bi bi-receipt me-1"></i>Bill Only
                            </span>
                            <?php if($seasonMode): ?>
                                <br><small class="text-success"><i class="bi bi-check-circle"></i> Full access (Season)</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-primary-soft text-primary">
                                <i class="bi bi-cash-stack me-1"></i>Full Access
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($user->is_active): ?>
                            <span class="status-badge status-completed">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($user->last_login_at): ?>
                            <?php echo e($user->last_login_at->diffForHumans()); ?>

                        <?php else: ?>
                            <span class="text-muted">Never</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <?php if(!$user->isSuperAdmin()): ?>
                                <a href="<?php echo e(route('users.edit', $user)); ?>" class="action-btn" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($user->id !== auth()->id()): ?>
                                    <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                                data-confirm="Are you sure you want to delete this user?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        No users found
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($users->hasPages()): ?>
<div class="d-flex justify-content-center mt-4">
    <?php echo e($users->links()); ?>

</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/users/index.blade.php ENDPATH**/ ?>