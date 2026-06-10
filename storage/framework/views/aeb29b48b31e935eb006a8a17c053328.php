<?php $__env->startSection('title', 'Order Details'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('orders.index')); ?>">Orders</a></li>
    <li class="breadcrumb-item active"><?php echo e($order->order_number); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Order Details</h1>
        <p class="page-subtitle"><?php echo e($order->order_number); ?></p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('orders.edit', $order)); ?>" class="btn-cpos btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit Order
        </a>
        <div class="btn-group me-2">
            <a href="<?php echo e(route('orders.receipt', $order)); ?>" class="btn-cpos btn-primary" target="_blank">
                <i class="bi bi-printer"></i> Receipt
            </a>
            <button type="button" class="btn-cpos btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?php echo e(route('orders.receipt', $order)); ?>" target="_blank"><i class="bi bi-receipt me-2"></i>Thermal Receipt</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a4'])); ?>" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>A4 Invoice</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a5'])); ?>" target="_blank"><i class="bi bi-file-earmark me-2"></i>A5 Invoice</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('orders.invoice', [$order, 'size' => 'a3'])); ?>" target="_blank"><i class="bi bi-file-earmark-richtext me-2"></i>A3 Invoice</a></li>
            </ul>
        </div>
        <a href="<?php echo e(route('orders.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-8">
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-cart"></i> Order Items</h5>
                <span class="text-muted"><?php echo e($order->items->count()); ?> items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-cpos mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar" style="width: 40px; height: 40px; background: var(--cp-blue-100); color: var(--cp-blue-600);">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600"><?php echo e($item->product_name); ?></div>
                                        <?php if($item->product): ?>
                                            <small class="text-muted text-mono"><?php echo e($item->product->sku); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark"><?php echo e($item->quantity); ?> <?php echo e($item->unit); ?></span>
                            </td>
                            <td class="text-end">Rs. <?php echo e(number_format($item->unit_price, 2)); ?></td>
                            <td class="text-end fw-600">Rs. <?php echo e(number_format($item->total, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php if($order->notes): ?>
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0"><?php echo e($order->notes); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="col-lg-4">
        
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Order Info</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Status:</span>
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
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Date:</span>
                    <span><?php echo e($order->created_at->format('M d, Y')); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Time:</span>
                    <span><?php echo e($order->created_at->format('h:i A')); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Cashier:</span>
                    <span><?php echo e($order->user?->name ?? '-'); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Customer:</span>
                    <span><?php echo e($order->customer?->name ?? 'Walk-in'); ?></span>
                </div>
            </div>
        </div>

        
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span>Rs. <?php echo e(number_format($order->subtotal, 2)); ?></span>
                </div>
                <?php if($order->discount_amount > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount:</span>
                    <span>- Rs. <?php echo e(number_format($order->discount_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
                <?php if($order->tax_amount > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax:</span>
                    <span>Rs. <?php echo e(number_format($order->tax_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-600">TOTAL:</span>
                    <span class="fw-600 fs-5" style="color: var(--cp-primary);">Rs. <?php echo e(number_format($order->total_amount, 2)); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid:</span>
                    <span class="text-success">Rs. <?php echo e(number_format($order->paid_amount, 2)); ?></span>
                </div>
                <?php if($order->change_amount > 0): ?>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Change:</span>
                    <span>Rs. <?php echo e(number_format($order->change_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($order->payments && $order->payments->count() > 0): ?>
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> Payments</h5>
            </div>
            <div class="card-body p-4">
                <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex justify-content-between align-items-center <?php echo e(!$loop->last ? 'mb-3' : ''); ?>">
                    <div class="d-flex align-items-center gap-2">
                        <?php
                            $paymentIcon = match($payment->method) {
                                'cash' => 'bi-cash',
                                'card' => 'bi-credit-card',
                                'bank_transfer' => 'bi-bank',
                                default => 'bi-wallet2'
                            };
                        ?>
                        <i class="<?php echo e($paymentIcon); ?> text-muted"></i>
                        <span><?php echo e(ucfirst(str_replace('_', ' ', $payment->method))); ?></span>
                    </div>
                    <span class="fw-600">Rs. <?php echo e(number_format($payment->amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($order->status === 'completed'): ?>
        <div class="cpos-card">
            <div class="card-body p-4">
                <button type="button" class="btn btn-outline-danger w-100" onclick="showRefundModal()">
                    <i class="bi bi-arrow-return-left me-2"></i> Process Refund
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.btn-secondary {
    background: var(--cp-surface);
    border: 1px solid var(--cp-border);
    color: var(--cp-text);
}
.btn-secondary:hover {
    background: var(--cp-bg-alt);
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/orders/show.blade.php ENDPATH**/ ?>