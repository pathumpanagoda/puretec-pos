<?php $__env->startSection('title', 'New Purchase Order'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('purchases.index')); ?>">Purchase Orders</a></li>
    <li class="breadcrumb-item active">New Order</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 class="page-title">New Purchase Order</h1>
        <p class="page-subtitle">Create a new purchase order for stock</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('purchases.index')); ?>" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="<?php echo e(route('purchases.store')); ?>" method="POST" id="purchaseForm">
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Order Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select" id="supplierSelect">
                                <option value="">-- Select Supplier --</option>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?><?php echo e($supplier->company ? ' - ' . $supplier->company : ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Order Date</label>
                            <input type="date" name="order_date" class="form-control" value="<?php echo e(today()->toDateString()); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_date" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-cart"></i> Order Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                        <i class="bi bi-plus-lg"></i> Add Item
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-cpos mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Product</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Unit</th>
                                    <th style="width: 15%;">Unit Cost</th>
                                    <th style="width: 10%;">Total</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-600">Subtotal:</td>
                                    <td class="fw-600" id="subtotalDisplay">Rs. 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center py-4" id="emptyState">
                        <i class="bi bi-box fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No items added yet</p>
                        <button type="button" class="btn-cpos btn-primary btn-sm" onclick="addItemRow()">
                            <i class="bi bi-plus-lg"></i> Add First Item
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes for this order..."></textarea>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-calculator"></i> Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items:</span>
                        <span class="fw-600" id="itemCountDisplay">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Quantity:</span>
                        <span class="fw-600" id="totalQtyDisplay">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-600">Total Amount:</span>
                        <span class="fw-600 fs-5" style="color: var(--cp-primary);" id="totalAmountDisplay">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            
            <div class="cpos-card mb-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-flag"></i> Status</h5>
                </div>
                <div class="card-body p-4">
                    <select name="status" class="form-select">
                        <option value="ordered">Ordered</option>
                        <option value="draft">Draft</option>
                    </select>
                    <small class="text-muted">Select 'Draft' to save without sending</small>
                </div>
            </div>

            
            <button type="submit" class="btn-cpos btn-primary w-100 btn-lg" id="submitBtn" disabled>
                <i class="bi bi-check-lg"></i> Create Purchase Order
            </button>
        </div>
    </div>
</form>

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
#itemsTable tbody tr td {
    vertical-align: middle;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Products data from server
const products = <?php echo json_encode($products, 15, 512) ?>;
let itemCount = 0;

// Add new item row
function addItemRow() {
    itemCount++;
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = `item-row-${itemCount}`;
    row.innerHTML = `
        <td>
            <select name="items[${itemCount}][product_id]" class="form-select product-select" onchange="productSelected(this, ${itemCount})" required>
                <option value="">-- Select Product --</option>
                ${products.map(p => `<option value="${p.id}" data-cost="${p.cost_price || 0}" data-unit="${p.unit || 'pcs'}">${p.sku} - ${p.name}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemCount}][ordered_quantity]" class="form-control qty-input" min="1" value="1" onchange="calculateTotals()" required>
        </td>
        <td>
            <input type="text" name="items[${itemCount}][unit]" class="form-control unit-input" value="pcs" readonly>
        </td>
        <td>
            <input type="number" name="items[${itemCount}][unit_cost]" class="form-control cost-input" min="0" step="0.01" value="0" onchange="calculateTotals()" required>
        </td>
        <td class="row-total">Rs. 0.00</td>
        <td>
            <button type="button" class="action-btn action-btn-danger" onclick="removeItemRow(${itemCount})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    updateEmptyState();
    calculateTotals();
}

// When product is selected
function productSelected(select, rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    const option = select.options[select.selectedIndex];

    if (option.value) {
        const cost = parseFloat(option.dataset.cost) || 0;
        const unit = option.dataset.unit || 'pcs';
        row.querySelector('.cost-input').value = cost.toFixed(2);
        row.querySelector('.unit-input').value = unit;
    }

    calculateTotals();
}

// Remove item row
function removeItemRow(rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    if (row) row.remove();
    updateEmptyState();
    calculateTotals();
}

// Update empty state visibility
function updateEmptyState() {
    const tbody = document.getElementById('itemsBody');
    const emptyState = document.getElementById('emptyState');
    const tableContainer = document.querySelector('#itemsTable').closest('.table-responsive');

    if (tbody.children.length === 0) {
        emptyState.style.display = 'block';
        tableContainer.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        tableContainer.style.display = 'block';
    }
}

// Calculate all totals
function calculateTotals() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let subtotal = 0;
    let totalQty = 0;
    let itemCount = 0;

    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input')?.value) || 0;
        const rowTotal = qty * cost;

        row.querySelector('.row-total').textContent = `Rs. ${rowTotal.toFixed(2)}`;
        subtotal += rowTotal;
        totalQty += qty;
        itemCount++;
    });

    document.getElementById('subtotalDisplay').textContent = `Rs. ${subtotal.toFixed(2)}`;
    document.getElementById('totalAmountDisplay').textContent = `Rs. ${subtotal.toFixed(2)}`;
    document.getElementById('itemCountDisplay').textContent = itemCount;
    document.getElementById('totalQtyDisplay').textContent = totalQty;

    // Enable/disable submit button
    document.getElementById('submitBtn').disabled = itemCount === 0;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateEmptyState();
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/purchases/create.blade.php ENDPATH**/ ?>