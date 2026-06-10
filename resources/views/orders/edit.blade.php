@extends('layouts.app')
@section('title', 'Edit Order')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Order</h1>
        <p class="page-subtitle">{{ $order->order_number }} - {{ $order->created_at->format('M d, Y h:i A') }}</p>
    </div>
    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary btn-cpos">
        <i class="bi bi-arrow-left me-2"></i>Cancel
    </a>
</div>

<form action="{{ route('orders.update', $order) }}" method="POST" id="orderEditForm">
@csrf
@method('PUT')

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Order Items --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-cart"></i> Order Items</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addNewItem()">
                    <i class="bi bi-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-cpos mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%">Product</th>
                                <th style="width: 15%">Quantity</th>
                                <th style="width: 20%">Unit Price</th>
                                <th style="width: 20%">Total</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($order->items as $index => $item)
                            <tr class="item-row" data-index="{{ $index }}">
                                <td>
                                    <select name="items[{{ $index }}][product_id]" class="form-select product-select" required onchange="updateItemPrice(this)">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-price="{{ $product->selling_price }}"
                                                data-cost="{{ $product->cost_price }}"
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]"
                                           class="form-control item-qty"
                                           value="{{ $item->quantity }}"
                                           min="0.01" step="0.01" required
                                           onchange="calculateItemTotal(this)">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" name="items[{{ $index }}][unit_price]"
                                               class="form-control item-price"
                                               value="{{ $item->unit_price }}"
                                               min="0" step="0.01" required
                                               onchange="calculateItemTotal(this)">
                                    </div>
                                </td>
                                <td>
                                    <span class="item-total fw-600">Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="cpos-card">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-journal-text"></i> Notes</h5>
            </div>
            <div class="card-body p-4">
                <textarea name="notes" class="form-control" rows="3" placeholder="Order notes...">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Customer --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-person"></i> Customer</h5>
            </div>
            <div class="card-body p-4">
                <select name="customer_id" class="form-select">
                    <option value="">Walk-in Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $order->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} @if($customer->phone)({{ $customer->phone }})@endif
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Discount --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-tag"></i> Discount</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-2">
                    <div class="col-6">
                        <select name="discount_type" id="discountType" class="form-select" onchange="calculateTotals()">
                            <option value="">No Discount</option>
                            <option value="fixed" {{ $order->discount_type == 'fixed' ? 'selected' : '' }}>Fixed (Rs.)</option>
                            <option value="percentage" {{ $order->discount_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="number" name="discount_value" id="discountValue"
                               class="form-control" value="{{ $order->discount_value ?? 0 }}"
                               min="0" step="0.01" onchange="calculateTotals()">
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="cpos-card mb-4">
            <div class="cpos-card-header">
                <h5 class="card-title"><i class="bi bi-calculator"></i> Summary</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span id="displaySubtotal">Rs. {{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success" id="discountRow" style="{{ $order->discount_amount > 0 ? '' : 'display:none' }}">
                    <span>Discount:</span>
                    <span id="displayDiscount">- Rs. {{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @if($order->store?->tax_rate > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax ({{ $order->store->tax_rate }}%):</span>
                    <span id="displayTax">Rs. {{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-600">NEW TOTAL:</span>
                    <span class="fw-600 fs-5" style="color: var(--cp-primary);" id="displayTotal">Rs. {{ number_format($order->total_amount, 2) }}</span>
                </div>

                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Already Paid:</span>
                    <span class="text-success fw-600">Rs. {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Payment Status:</span>
                    <span id="paymentStatus" class="fw-600"></span>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg">
            <i class="bi bi-check-lg me-2"></i> Save Changes
        </button>

        <div class="alert alert-info mt-3 small">
            <i class="bi bi-info-circle me-1"></i>
            Editing will recalculate totals. Existing payments remain unchanged.
        </div>
    </div>
</div>
</form>

{{-- Hidden template for new item row --}}
<template id="itemRowTemplate">
    <tr class="item-row" data-index="__INDEX__">
        <td>
            <select name="items[__INDEX__][product_id]" class="form-select product-select" required onchange="updateItemPrice(this)">
                <option value="">Select Product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}"
                        data-price="{{ $product->selling_price }}"
                        data-cost="{{ $product->cost_price }}">
                    {{ $product->name }} ({{ $product->sku }})
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][quantity]"
                   class="form-control item-qty"
                   value="1" min="0.01" step="0.01" required
                   onchange="calculateItemTotal(this)">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" name="items[__INDEX__][unit_price]"
                       class="form-control item-price"
                       value="0" min="0" step="0.01" required
                       onchange="calculateItemTotal(this)">
            </div>
        </td>
        <td>
            <span class="item-total fw-600">Rs. 0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Remove">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('styles')
<style>
.btn-warning {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #fff;
}
.btn-warning:hover {
    background: #d97706;
    border-color: #d97706;
    color: #fff;
}
.item-row:hover {
    background: var(--cp-bg-alt);
}
.product-select {
    min-width: 200px;
}
</style>
@endpush

@push('scripts')
<script>
let itemIndex = {{ $order->items->count() }};
const taxRate = {{ $order->store?->tax_rate ?? 0 }};
const paidAmount = {{ $order->paid_amount }};

function addNewItem() {
    const template = document.getElementById('itemRowTemplate').innerHTML;
    const newRow = template.replace(/__INDEX__/g, itemIndex);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', newRow);
    itemIndex++;
    calculateTotals();
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) {
        alert('Order must have at least one item!');
        return;
    }
    btn.closest('tr').remove();
    calculateTotals();
}

function updateItemPrice(select) {
    const row = select.closest('tr');
    const selected = select.options[select.selectedIndex];
    const price = selected.dataset.price || 0;
    row.querySelector('.item-price').value = price;
    calculateItemTotal(select);
}

function calculateItemTotal(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = qty * price;
    row.querySelector('.item-total').textContent = 'Rs. ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        subtotal += qty * price;
    });

    const discountType = document.getElementById('discountType').value;
    const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;

    let discountAmount = 0;
    if (discountType === 'percentage') {
        discountAmount = subtotal * (discountValue / 100);
    } else if (discountType === 'fixed') {
        discountAmount = discountValue;
    }

    const taxableAmount = subtotal - discountAmount;
    const taxAmount = taxableAmount * (taxRate / 100);
    const total = subtotal - discountAmount + taxAmount;

    document.getElementById('displaySubtotal').textContent = 'Rs. ' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('displayDiscount').textContent = '- Rs. ' + discountAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('discountRow').style.display = discountAmount > 0 ? '' : 'none';

    if (taxRate > 0) {
        document.getElementById('displayTax').textContent = 'Rs. ' + taxAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    document.getElementById('displayTotal').textContent = 'Rs. ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Update payment status
    const statusEl = document.getElementById('paymentStatus');
    if (paidAmount >= total) {
        statusEl.textContent = 'Fully Paid';
        statusEl.style.color = 'green';
    } else if (paidAmount > 0) {
        statusEl.textContent = 'Partially Paid (Due: Rs. ' + (total - paidAmount).toFixed(2) + ')';
        statusEl.style.color = 'orange';
    } else {
        statusEl.textContent = 'Unpaid';
        statusEl.style.color = 'red';
    }
}

// Initial calculation
document.addEventListener('DOMContentLoaded', calculateTotals);
</script>
@endpush
@endsection
