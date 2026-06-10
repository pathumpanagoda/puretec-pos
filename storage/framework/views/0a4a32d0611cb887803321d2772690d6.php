<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Orders</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">View and manage all sales orders</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('pos.index')); ?>" class="btn-cpos btn-primary">
            <i class="bi bi-cart-plus"></i> New Sale
        </a>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?php echo e($orders->total()); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value"><?php echo e($orders->where('status', 'completed')->count()); ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-value"><?php echo e($orders->where('status', 'pending')->count()); ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div class="stat-value"><?php echo e($orders->where('status', 'refunded')->count()); ?></div>
            <div class="stat-label">Refunded</div>
        </div>
    </div>
</div>


<div class="cpos-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search order number..." value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control" placeholder="From" value="<?php echo e(request('from')); ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control" placeholder="To" value="<?php echo e(request('to')); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>


<?php if(auth()->user()->role === 'super_admin'): ?>
<div class="cpos-card mb-3 bulk-actions-bar" style="display: none;">
    <div class="card-body py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-600"><span id="selectedCount">0</span> orders selected</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                <i class="bi bi-x-lg me-1"></i>Clear Selection
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkDeleteOrders()">
                <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-receipt"></i> All Orders</h5>
        <span class="text-muted"><?php echo e($orders->total()); ?> orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <?php if(auth()->user()->role === 'super_admin'): ?>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="selectAllOrders" onchange="toggleSelectAll(this)">
                    </th>
                    <?php endif; ?>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="order-row" data-order-id="<?php echo e($order->id); ?>">
                    <?php if(auth()->user()->role === 'super_admin'): ?>
                    <td>
                        <input type="checkbox" class="form-check-input order-checkbox" value="<?php echo e($order->id); ?>" onchange="updateBulkActions()">
                    </td>
                    <?php endif; ?>
                    <td>
                        <span class="fw-600 text-mono" style="color: var(--cp-primary);">
                            <?php echo e($order->order_number); ?>

                        </span>
                    </td>
                    <td>
                        <div><?php echo e($order->created_at->format('M d, Y')); ?></div>
                        <small class="text-muted"><?php echo e($order->created_at->format('h:i A')); ?></small>
                    </td>
                    <td>
                        <?php if($order->customer): ?>
                            <div class="fw-600"><?php echo e($order->customer->name); ?></div>
                            <?php if($order->customer->phone): ?>
                                <small class="text-muted"><?php echo e($order->customer->phone); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">Walk-in Customer</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($order->user?->name ?? '-'); ?></td>
                    <td>
                        <span class="badge bg-light text-dark"><?php echo e($order->items_count ?? $order->items()->count()); ?> items</span>
                    </td>
                    <td>
                        <span class="fw-600">Rs. <?php echo e(number_format($order->total_amount, 2)); ?></span>
                    </td>
                    <td>
                        <?php
                            $statusClass = match($order->status) {
                                'completed' => 'status-completed',
                                'pending' => 'status-pending',
                                'cancelled' => 'status-cancelled',
                                'refunded' => 'status-refunded',
                                default => 'status-pending'
                            };
                        ?>
                        <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($order->status)); ?></span>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="<?php echo e(route('orders.show', $order)); ?>" class="action-btn" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?php echo e(route('orders.edit', $order)); ?>" class="action-btn" title="Edit Order" style="color: #f59e0b;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <div class="dropdown d-inline">
                                <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Print">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?php echo e(route('orders.receipt', $order)); ?>" target="_blank"><i class="bi bi-receipt me-2"></i>Thermal Receipt</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a4'])); ?>" target="_blank"><i class="bi bi-file-text me-2"></i>A4 Invoice</a></li>
                                    <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a5'])); ?>" target="_blank"><i class="bi bi-file-earmark me-2"></i>A5 Invoice</a></li>
                                    <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a3'])); ?>" target="_blank"><i class="bi bi-file-richtext me-2"></i>A3 Invoice</a></li>
                                </ul>
                            </div>
                            <?php if($order->status !== 'completed' && $order->status !== 'refunded'): ?>
                            <form action="<?php echo e(route('orders.destroy', $order)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this order?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(auth()->user()->role === 'super_admin' ? 9 : 8); ?>" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-receipt"></i>
                            <p>No orders found</p>
                            <a href="<?php echo e(route('pos.index')); ?>" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i> Create First Sale
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($orders->hasPages()): ?>
    <div class="card-body">
        <?php echo e($orders->links()); ?>

    </div>
    <?php endif; ?>
</div>


<?php if(auth()->user()->role === 'super_admin'): ?>
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Bulk Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning!</strong> You are about to delete <span id="deleteCount" class="fw-bold">0</span> orders.
                    This action cannot be undone!
                </div>
                <p>All related data (order items, payments) will also be deleted.</p>
                <div class="form-group mb-3">
                    <label class="form-label">Type <strong>DELETE</strong> to confirm:</label>
                    <input type="text" class="form-control" id="confirmDeleteText" placeholder="Type DELETE">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete" disabled>
                    <i class="bi bi-trash me-1"></i>Delete Orders
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<?php if(auth()->user()->role === 'super_admin'): ?>
<script>
let selectedOrders = [];

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    selectedOrders = Array.from(checkboxes).map(cb => cb.value);

    const bulkBar = document.querySelector('.bulk-actions-bar');
    const countSpan = document.getElementById('selectedCount');

    if (selectedOrders.length > 0) {
        bulkBar.style.display = 'block';
        countSpan.textContent = selectedOrders.length;
    } else {
        bulkBar.style.display = 'none';
    }

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.order-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllOrders');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllOrders').checked = false;
    selectedOrders = [];
    document.querySelector('.bulk-actions-bar').style.display = 'none';
}

function bulkDeleteOrders() {
    if (selectedOrders.length === 0) return;

    document.getElementById('deleteCount').textContent = selectedOrders.length;
    document.getElementById('confirmDeleteText').value = '';
    document.getElementById('confirmBulkDelete').disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteText')?.addEventListener('input', function() {
    const confirmBtn = document.getElementById('confirmBulkDelete');
    confirmBtn.disabled = this.value !== 'DELETE';
});

document.getElementById('confirmBulkDelete')?.addEventListener('click', function() {
    if (selectedOrders.length === 0) return;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

    fetch('<?php echo e(route("orders.bulk-delete")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({ order_ids: selectedOrders })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete orders');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Orders';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting orders');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Orders';
    });
});
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/orders/index.blade.php ENDPATH**/ ?>