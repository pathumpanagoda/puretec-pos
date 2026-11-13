

<?php $__env->startSection('title', 'Password Reset Requests'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item active">Password Reset Requests</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-key me-2"></i>Password Reset Requests
        </h1>
        <p class="text-muted mb-0">Review and manage password reset requests from users</p>
    </div>
    <?php if($pendingCount > 0): ?>
    <span class="badge bg-warning text-dark fs-6">
        <i class="bi bi-clock me-1"></i><?php echo e($pendingCount); ?> Pending
    </span>
    <?php endif; ?>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <?php if(session('reset_url')): ?>
            <div class="mt-2 p-2 bg-dark rounded">
                <small class="text-muted d-block mb-1">Reset URL (share with <strong><?php echo e(session('approved_user', 'the user')); ?></strong>):</small>
                <code class="text-success" style="word-break: break-all; font-size: 0.75rem;"><?php echo e(session('reset_url')); ?></code>
                <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyResetUrl(this)" data-url="<?php echo e(session('reset_url')); ?>">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('password-reset-requests.index')); ?>"
               class="btn btn-sm <?php echo e(!request('status') ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                All
            </a>
            <a href="<?php echo e(route('password-reset-requests.index', ['status' => 'pending'])); ?>"
               class="btn btn-sm <?php echo e(request('status') == 'pending' ? 'btn-warning' : 'btn-outline-secondary'); ?>">
                <i class="bi bi-clock me-1"></i>Pending
                <?php if($pendingCount > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo e($pendingCount); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('password-reset-requests.index', ['status' => 'approved'])); ?>"
               class="btn btn-sm <?php echo e(request('status') == 'approved' ? 'btn-success' : 'btn-outline-secondary'); ?>">
                <i class="bi bi-check-circle me-1"></i>Approved
            </a>
            <a href="<?php echo e(route('password-reset-requests.index', ['status' => 'rejected'])); ?>"
               class="btn btn-sm <?php echo e(request('status') == 'rejected' ? 'btn-danger' : 'btn-outline-secondary'); ?>">
                <i class="bi bi-x-circle me-1"></i>Rejected
            </a>
            <a href="<?php echo e(route('password-reset-requests.index', ['status' => 'completed'])); ?>"
               class="btn btn-sm <?php echo e(request('status') == 'completed' ? 'btn-info' : 'btn-outline-secondary'); ?>">
                <i class="bi bi-check-all me-1"></i>Completed
            </a>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-body p-0">
        <?php if($requests->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($req->id); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                    <span class="text-primary fw-bold" style="font-size:0.75rem;"><?php echo e(strtoupper(substr($req->user->name ?? '?', 0, 1))); ?></span>
                                </div>
                                <span class="fw-medium"><?php echo e($req->user->name ?? 'Unknown'); ?></span>
                            </div>
                        </td>
                        <td><small class="text-muted"><?php echo e($req->user->email ?? '-'); ?></small></td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-25 text-light">
                                <?php echo e(ucfirst(str_replace('_', ' ', $req->user->role ?? '-'))); ?>

                            </span>
                        </td>
                        <td>
                            <?php if($req->reason): ?>
                                <small class="text-muted" title="<?php echo e($req->reason); ?>">
                                    <?php echo e(Str::limit($req->reason, 30)); ?>

                                </small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php switch($req->status):
                                case ('pending'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                    <?php break; ?>
                                <?php case ('approved'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    <?php if($req->isExpired()): ?>
                                        <span class="badge bg-secondary ms-1">Expired</span>
                                    <?php endif; ?>
                                    <?php break; ?>
                                <?php case ('rejected'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    <?php break; ?>
                                <?php case ('completed'): ?>
                                    <span class="badge bg-info"><i class="bi bi-check-all me-1"></i>Completed</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?php echo e($req->created_at->diffForHumans()); ?></small>
                        </td>
                        <td>
                            <?php if($req->isPending()): ?>
                                <div class="d-flex gap-1">
                                    <form action="<?php echo e(route('password-reset-requests.approve', $req->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve password reset for <?php echo e($req->user->name); ?>?')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form action="<?php echo e(route('password-reset-requests.reject', $req->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject password reset for <?php echo e($req->user->name); ?>?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php elseif($req->isApproved() && !$req->isExpired()): ?>
                                <small class="text-success">
                                    <i class="bi bi-clock me-1"></i>Expires <?php echo e($req->expires_at->diffForHumans()); ?>

                                </small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <?php echo e($requests->withQueryString()->links()); ?>

        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-4 text-muted"></i>
            <p class="text-muted mt-3">No password reset requests found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function copyResetUrl(btn) {
    const url = btn.dataset.url;
    navigator.clipboard.writeText(url).then(() => {
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        }, 2000);
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/admin/password-reset-requests.blade.php ENDPATH**/ ?>