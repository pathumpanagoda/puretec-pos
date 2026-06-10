<?php $__env->startSection('title', 'Point of Sale'); ?>
<?php $__env->startSection('body-class', 'pos-body'); ?>

<?php $__env->startSection('topbar-center'); ?>
<div class="pos-topbar-controls">
    <div class="topbar-cashier-info" id="topbarCashierInfo">
        <i class="bi bi-person-badge"></i>
        <span><?php echo e(auth()->user()->name); ?></span>
        <?php if($register): ?>
            <span class="cashier-session-badge">Active</span>
        <?php endif; ?>
    </div>
    <div class="topbar-display-controls">
        <button class="btn-display-control" id="btnDisplayOn" onclick="openCustomerDisplay()" title="Toggle Customer Display">
            <i class="bi bi-display"></i>
            <span>Customer Display</span>
            <i class="bi bi-toggle-off" id="displayToggleIcon"></i>
        </button>
        <?php if($register): ?>
        <button class="btn-display-control btn-close-register" onclick="openCloseRegisterModal()" title="Close Register & End Shift">
            <i class="bi bi-stop-circle"></i>
            <span>End Shift</span>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="pos-wrapper" id="posApp">

    <!-- LEFT: Product Panel -->
    <div class="pos-left">
        <!-- Search & Category Bar -->
        <div class="pos-search-bar">
            <div class="pos-search-wrap">
                <i class="bi bi-search pos-search-icon"></i>
                <input type="text" id="productSearch" class="pos-search-input" placeholder="Search products, scan barcode... (F2)" autocomplete="off">
                <button id="barcodeBtn" class="pos-barcode-btn" title="Barcode Scanner"><i class="bi bi-upc-scan"></i></button>
            </div>
        </div>

        <!-- Category Filter with Slider -->
        <div class="pos-categories-wrapper">
            <button class="cat-slider-btn" id="catSlideLeft" onclick="slideCategories(-1)"><i class="bi bi-chevron-left"></i></button>
            <div class="pos-categories" id="categoriesContainer">
                <button class="cat-btn active" data-category="">
                    <i class="bi bi-grid-3x3-gap"></i><span>All</span>
                </button>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button class="cat-btn" data-category="<?php echo e($cat->id); ?>" style="--cat-color: <?php echo e($cat->color); ?>">
                    <i class="<?php echo e($cat->icon); ?>"></i><span><?php echo e($cat->name); ?></span>
                    <span class="cat-count"><?php echo e($cat->products_count); ?></span>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button class="cat-slider-btn" id="catSlideRight" onclick="slideCategories(1)"><i class="bi bi-chevron-right"></i></button>
        </div>

        <!-- Products Grid -->
        <div class="pos-products-grid" id="productsGrid">
            <div class="pos-loading"><div class="spinner-border text-primary"></div><p>Loading products...</p></div>
        </div>
    </div>

    <!-- RIGHT: Cart Panel -->
    <div class="pos-right">
        <!-- Cart Header -->
        <div class="pos-cart-header">
            <div class="cart-title-row">
                <h5 class="cart-title"><i class="bi bi-cart3 me-2"></i>Current Sale</h5>
                <div class="cart-actions">
                    <?php if($canProcessPayment ?? true): ?>
                    <button class="cart-action-btn" id="pendingPaymentsBtn" title="Pending Payments" onclick="showPendingPaymentsModal()">
                        <i class="bi bi-hourglass-split"></i><span id="pendingBadge" class="held-badge d-none">0</span>
                    </button>
                    <?php endif; ?>
                    <button class="cart-action-btn" id="holdBtn" title="Hold Order (F4)"><i class="bi bi-pause-circle"></i></button>
                    <button class="cart-action-btn" id="heldOrdersBtn" title="Held Orders"><i class="bi bi-clock-history"></i><span id="heldBadge" class="held-badge d-none">0</span></button>
                    <button class="cart-action-btn text-danger" id="clearCartBtn" title="Clear Cart"><i class="bi bi-trash3"></i></button>
                </div>
            </div>

            <!-- Customer Select -->
            <div class="cart-customer-wrap">
                <div class="customer-search-wrap">
                    <i class="bi bi-person-circle customer-icon"></i>
                    <input type="text" id="customerSearch" class="customer-input" placeholder="Walk-in Customer (search by name/phone)">
                    <button id="clearCustomer" class="clear-customer d-none"><i class="bi bi-x"></i></button>
                </div>
                <div id="customerDropdown" class="customer-dropdown d-none"></div>
                <input type="hidden" id="selectedCustomerId">
            </div>
        </div>

        <!-- Cart Items -->
        <div class="pos-cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="bi bi-cart-x"></i>
                <p>Cart is empty</p>
                <small>Search or click a product to add</small>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="pos-cart-footer">
            <!-- Discount Row -->
            <div class="discount-row">
                <div class="discount-tabs">
                    <button class="disc-tab active" data-type="none">No Discount</button>
                    <button class="disc-tab" data-type="percentage">%</button>
                    <button class="disc-tab" data-type="fixed">Rs.</button>
                    <button class="disc-tab" data-type="coupon">Coupon</button>
                </div>
                <div id="discountInput" class="discount-input-wrap d-none">
                    <input type="number" id="discountValue" class="form-control form-control-sm" placeholder="0" min="0">
                </div>
                <div id="couponInput" class="coupon-input-wrap d-none">
                    <input type="text" id="couponCode" class="form-control form-control-sm" placeholder="Enter coupon code">
                    <button id="applyCouponBtn" class="btn btn-sm btn-primary">Apply</button>
                </div>
            </div>

            <!-- Totals -->
            <div class="cart-totals">
                <div class="total-row"><span>Subtotal</span><span id="subtotalDisplay">Rs. 0.00</span></div>
                <div class="total-row" id="discountRow" style="display:none"><span>Discount</span><span id="discountDisplay" class="text-success">- Rs. 0.00</span></div>
                <div class="total-row" id="taxRow" style="display:none"><span>Tax</span><span id="taxDisplay">Rs. 0.00</span></div>
                <div class="total-row total-grand"><span>TOTAL</span><span id="grandTotalDisplay">Rs. 0.00</span></div>
            </div>

            <!-- Payment Buttons -->
            <div class="payment-methods-quick">
                <button class="pay-btn pay-cash" onclick="openPayment('cash')">
                    <i class="bi bi-cash-coin"></i><span>Cash</span>
                </button>
                <button class="pay-btn pay-card" onclick="openPayment('card')">
                    <i class="bi bi-credit-card"></i><span>Card</span>
                </button>
                <button class="pay-btn pay-mobile" onclick="openPayment('mobile_payment')">
                    <i class="bi bi-phone"></i><span>Mobile</span>
                </button>
                <button class="pay-btn pay-split" onclick="openPayment('split')">
                    <i class="bi bi-arrows-angle-expand"></i><span>Split</span>
                </button>
            </div>

            <?php if(($userPaymentMode ?? 'full') === 'bill_only' && !($seasonMode ?? false)): ?>
            <button class="btn-charge btn-bill-only" id="chargeBtn" onclick="createBill()" disabled>
                <i class="bi bi-receipt me-2"></i>
                Create Bill <span id="chargeBtnAmount">Rs. 0.00</span>
            </button>
            <?php else: ?>
            <button class="btn-charge" id="chargeBtn" onclick="openPayment('cash')" disabled>
                <i class="bi bi-lightning-charge-fill me-2"></i>
                Charge <span id="chargeBtnAmount">Rs. 0.00</span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== PAYMENT MODAL ===== -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Process Payment</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-customer-display" onclick="openCustomerDisplay()" title="Open Customer Display">
                        <i class="bi bi-display"></i> Open Display
                    </button>
                    <button type="button" class="btn-customer-display btn-close-display" onclick="closeCustomerDisplay()" title="Close Customer Display" style="background:#ef5350;">
                        <i class="bi bi-x-circle"></i> Close Display
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="payment-modal-grid">
                    <!-- Order Summary -->
                    <div class="payment-summary">
                        <div class="payment-summary-title">Order Summary</div>
                        <div id="paymentOrderItems" class="payment-order-items"></div>
                        <div class="payment-summary-totals">
                            <div class="pt-row"><span>Subtotal</span><span id="pmSubtotal">Rs. 0.00</span></div>
                            <div class="pt-row" id="pmDiscountRow" style="display:none"><span>Discount</span><span id="pmDiscount" class="text-success">- Rs. 0.00</span></div>
                            <div class="pt-row" id="pmTaxRow" style="display:none"><span>Tax</span><span id="pmTax">Rs. 0.00</span></div>
                            <div class="pt-row pt-total"><span>Total Due</span><span id="pmTotal">Rs. 0.00</span></div>
                        </div>
                    </div>

                    <!-- Payment Input -->
                    <div class="payment-input-panel">
                        <div class="payment-method-tabs">
                            <button class="pm-tab active" data-method="cash"><i class="bi bi-cash-coin"></i> Cash</button>
                            <button class="pm-tab" data-method="card"><i class="bi bi-credit-card"></i> Card</button>
                            <button class="pm-tab" data-method="mobile_payment"><i class="bi bi-phone"></i> Mobile</button>
                            <button class="pm-tab" data-method="bank_transfer"><i class="bi bi-bank"></i> Bank</button>
                            <button class="pm-tab" data-method="credit"><i class="bi bi-person-credit-card"></i> Credit</button>
                            <button class="pm-tab" data-method="gift_card"><i class="bi bi-gift"></i> Gift Card</button>
                        </div>

                        <div class="pm-amount-section">
                            <label class="pm-label">Amount Received</label>
                            <div class="pm-amount-display" id="pmAmountDisplay">Rs. 0.00</div>
                            <div class="pm-change-display" id="pmChangeDisplay">Change: Rs. 0.00</div>

                            <!-- Exact Amount Button -->
                            <button type="button" class="exact-amount-btn" id="exactAmountBtn" onclick="setExactAmount()">
                                <i class="bi bi-check-circle"></i> Exact amount
                            </button>

                            <!-- Numpad -->
                            <div class="numpad">
                                <?php $__currentLoopData = [7,8,9,4,5,6,1,2,3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="numpad-btn" onclick="numpadInput('<?php echo e($n); ?>')"><?php echo e($n); ?></button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="numpad-btn numpad-clear" onclick="numpadInput('C')">C</button>
                                <button type="button" class="numpad-btn" onclick="numpadInput('0')">0</button>
                                <button type="button" class="numpad-btn numpad-del" onclick="numpadInput('DEL')"><i class="bi bi-backspace"></i></button>
                            </div>

                            <!-- Manual Amount Input -->
                            <input type="number" class="pm-amount-input" id="manualAmountInput" placeholder="Or type amount here..." min="0" step="0.01" onchange="setManualAmount(this.value)">

                            <!-- Quick amounts -->
                            <div class="quick-amounts" id="quickAmounts"></div>

                            <!-- Card reference field -->
                            <div id="cardReferenceField" class="d-none">
                                <label class="pm-label mt-3">Card / Reference Number</label>
                                <input type="text" id="cardReference" class="form-control" placeholder="Enter reference number">
                            </div>
                        </div>

                        <div class="pm-notes-row">
                            <input type="text" id="orderNotes" class="form-control" placeholder="Order notes (optional)">
                        </div>

                        <button class="btn-complete-sale" id="completeSaleBtn">
                            <i class="bi bi-check-circle-fill me-2"></i>Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== RECEIPT MODAL ===== -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div id="receiptContent" class="receipt-paper"></div>
            </div>
            <div class="receipt-actions p-3">
                <div class="d-flex gap-2 mb-2">
                    <div class="dropdown flex-fill">
                        <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-printer me-1"></i>Print Options
                        </button>
                        <ul class="dropdown-menu w-100">
                            <li><h6 class="dropdown-header"><i class="bi bi-receipt me-1"></i>Thermal Receipt</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="openPrintView('thermal-58')"><i class="bi bi-receipt me-2"></i>58mm (Small)</a></li>
                            <li><a class="dropdown-item" href="#" onclick="openPrintView('thermal-80')"><i class="bi bi-receipt me-2"></i>80mm (Standard)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header"><i class="bi bi-file-earmark-text me-1"></i>Invoice</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="openPrintView('a5')"><i class="bi bi-file-earmark me-2"></i>A5 (Half Page)</a></li>
                            <li><a class="dropdown-item" href="#" onclick="openPrintView('a4')"><i class="bi bi-file-earmark-text me-2"></i>A4 (Full Page)</a></li>
                            <li><a class="dropdown-item" href="#" onclick="openPrintView('a3')"><i class="bi bi-file-text me-2"></i>A3 (Large)</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-success flex-fill" onclick="openDefaultPrintView()" title="Print using default format from Settings">
                        <i class="bi bi-printer-fill me-1"></i>Quick Print
                        <small class="d-block" style="font-size:9px;opacity:0.8;"><?php echo e(match($store?->settings['default_print_format'] ?? 'thermal-80') {
                                'thermal-58' => '58mm Thermal',
                                'thermal-80' => '80mm Thermal',
                                'a5' => 'A5 Invoice',
                                'a4' => 'A4 Invoice',
                                'a3' => 'A3 Invoice',
                                default => '80mm Thermal'
                            }); ?></small>
                    </button>
                </div>
                <button class="btn btn-primary w-100" data-bs-dismiss="modal" onclick="startNewSale()"><i class="bi bi-plus-circle me-1"></i>New Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== PRODUCT VARIANT MODAL ===== -->
<div class="modal fade" id="variantModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title" id="variantModalTitle">Select Variant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="variantModalBody"></div>
        </div>
    </div>
</div>

<!-- ===== HELD ORDERS MODAL ===== -->
<div class="modal fade" id="heldOrdersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Held Orders</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="heldOrdersBody" style="max-height: 60vh; overflow-y: auto;">
                <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No held orders</p></div>
            </div>
            <div class="modal-footer border-top">
                <small class="text-muted me-auto"><i class="bi bi-info-circle me-1"></i>Click "Retrieve" to load an order back to cart</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== PENDING PAYMENTS MODAL ===== -->
<div class="modal fade" id="pendingPaymentsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-hourglass-split me-2"></i>Pending Payments</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="pendingPaymentsBody" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No pending payments</p></div>
            </div>
            <div class="modal-footer border-top">
                <small class="text-muted me-auto"><i class="bi bi-info-circle me-1"></i>Click "Approve & Pay" to process payment for a bill</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== APPROVE PAYMENT MODAL ===== -->
<div class="modal fade" id="approvePaymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="payment-modal-grid">
                    <!-- Order Summary -->
                    <div class="payment-summary">
                        <div class="payment-summary-title">Bill Summary</div>
                        <div id="approveOrderItems" class="payment-order-items"></div>
                        <div class="payment-summary-totals">
                            <div class="pt-row"><span>Subtotal</span><span id="appSubtotal">Rs. 0.00</span></div>
                            <div class="pt-row" id="appDiscountRow" style="display:none"><span>Discount</span><span id="appDiscount" class="text-success">- Rs. 0.00</span></div>
                            <div class="pt-row pt-total"><span>Total Due</span><span id="appTotal">Rs. 0.00</span></div>
                        </div>
                        <div class="p-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>Billed by: <span id="appBilledBy">-</span><br>
                                <i class="bi bi-clock me-1"></i>Billed at: <span id="appBilledAt">-</span>
                            </small>
                        </div>
                    </div>

                    <!-- Payment Input -->
                    <div class="payment-input-panel">
                        <input type="hidden" id="approveOrderId">

                        <div class="payment-method-tabs">
                            <button class="pm-tab active" data-method="cash" onclick="selectApprovePayMethod('cash')"><i class="bi bi-cash-coin"></i> Cash</button>
                            <button class="pm-tab" data-method="card" onclick="selectApprovePayMethod('card')"><i class="bi bi-credit-card"></i> Card</button>
                            <button class="pm-tab" data-method="mobile_payment" onclick="selectApprovePayMethod('mobile_payment')"><i class="bi bi-phone"></i> Mobile</button>
                        </div>

                        <div class="pm-amount-section">
                            <label class="pm-label">Amount Received</label>
                            <div class="pm-amount-display" id="appAmountDisplay">Rs. 0.00</div>
                            <div class="pm-change-display" id="appChangeDisplay">Change: Rs. 0.00</div>

                            <button type="button" class="exact-amount-btn" onclick="setApproveExactAmount()">
                                <i class="bi bi-check-circle"></i> Exact amount
                            </button>

                            <!-- Numpad -->
                            <div class="numpad">
                                <?php $__currentLoopData = [7,8,9,4,5,6,1,2,3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="numpad-btn" onclick="approveNumpadInput('<?php echo e($n); ?>')"><?php echo e($n); ?></button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="numpad-btn numpad-clear" onclick="approveNumpadInput('C')">C</button>
                                <button type="button" class="numpad-btn" onclick="approveNumpadInput('0')">0</button>
                                <button type="button" class="numpad-btn numpad-del" onclick="approveNumpadInput('DEL')"><i class="bi bi-backspace"></i></button>
                            </div>

                            <div id="appCardReferenceField" class="d-none mt-3">
                                <label class="pm-label">Card / Reference Number</label>
                                <input type="text" id="appCardReference" class="form-control" placeholder="Enter reference number">
                            </div>
                        </div>

                        <button class="btn-complete-sale" id="confirmApproveBtn" onclick="confirmApprovePayment()">
                            <i class="bi bi-check-circle-fill me-2"></i>Approve & Complete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Open Register</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">Enter the opening cash balance for this shift.</p>
                <div class="mb-3">
                    <label class="form-label">Opening Balance (Rs.)</label>
                    <input type="number" id="openingBalance" class="form-control form-control-lg" value="0" min="0" step="0.01">
                </div>
                <button class="btn btn-primary w-100 btn-lg" id="openRegisterBtn">
                    <i class="bi bi-unlock me-2"></i>Open Register
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Close Register Modal -->
<div class="modal fade" id="closeRegisterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i>Close Register</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="close-register-summary">
                            <h6 class="mb-3"><i class="bi bi-clock-history me-2"></i>Session Summary</h6>
                            <div class="summary-row">
                                <span>Session Started:</span>
                                <span id="crSessionStart">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Duration:</span>
                                <span id="crDuration">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Opening Balance:</span>
                                <span id="crOpeningBalance">Rs. 0.00</span>
                            </div>
                            <hr>
                            <div class="summary-row">
                                <span>Total Orders:</span>
                                <span id="crTotalOrders">0</span>
                            </div>
                            <div class="summary-row">
                                <span>Total Sales:</span>
                                <span id="crTotalSales" class="fw-bold text-primary">Rs. 0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Cash Payments:</span>
                                <span id="crCashPayments">Rs. 0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Card Payments:</span>
                                <span id="crCardPayments">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="close-register-cash">
                            <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Cash Count</h6>
                            <div class="expected-cash-box mb-3">
                                <div class="text-muted small">Expected Cash in Drawer:</div>
                                <div class="expected-cash-value" id="crExpectedCash">Rs. 0.00</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Actual Cash Count (Rs.)</label>
                                <input type="number" id="actualCashCount" class="form-control form-control-lg" value="0" min="0" step="0.01">
                            </div>
                            <div class="cash-difference mb-3" id="crDifferenceBox">
                                <div class="text-muted small">Difference:</div>
                                <div class="difference-value" id="crDifference">Rs. 0.00</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea id="closeRegisterNotes" class="form-control" rows="2" placeholder="Any notes for this session..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmCloseRegisterBtn">
                    <i class="bi bi-box-arrow-right me-2"></i>Close Register
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pos.css')); ?>">
<?php
    $printSettings = $store?->settings ?? [];
?>
<style id="dynamicPrintStyles">
/* Dynamic Print Layout Settings */
@media print {
    @page {
        margin: <?php echo e($printSettings['print_margin_top'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_right'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_bottom'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_left'] ?? 3); ?>mm;
    }
    #receiptContent {
        padding: <?php echo e($printSettings['print_margin_top'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_right'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_bottom'] ?? 3); ?>mm <?php echo e($printSettings['print_margin_left'] ?? 3); ?>mm !important;
        <?php if(($printSettings['print_receipt_width'] ?? 'auto') !== 'auto'): ?>
        max-width: <?php echo e($printSettings['print_receipt_width']); ?> !important;
        margin: 0 auto !important;
        <?php endif; ?>
    }
    .receipt-paper {
        line-height: <?php echo e($printSettings['print_line_spacing'] ?? '1.4'); ?> !important;
    }
    .receipt-header .store-name {
        font-size: <?php echo e($printSettings['print_font_store_name'] ?? '16'); ?>px !important;
        <?php if(($printSettings['print_colors'] ?? 'bw') === 'bw'): ?>
        color: #000 !important;
        <?php endif; ?>
    }
    .receipt-item, .ri-name, .ri-line, .receipt-meta, .receipt-meta div {
        font-size: <?php echo e($printSettings['print_font_items'] ?? '12'); ?>px !important;
    }
    .rt-row.rt-total, .receipt-totals .rt-row {
        font-size: <?php echo e($printSettings['print_font_total'] ?? '16'); ?>px !important;
    }
    .receipt-divider {
        border-top-style: <?php echo e($printSettings['print_divider_style'] ?? 'dashed'); ?> !important;
        <?php if(($printSettings['print_divider_style'] ?? 'dashed') === 'none'): ?>
        border: none !important;
        margin: 8px 0 !important;
        <?php endif; ?>
    }
    <?php if(($printSettings['print_colors'] ?? 'bw') === 'bw'): ?>
    .receipt-header .store-name,
    .rt-row.rt-total,
    .receipt-footer .receipt-powered {
        color: #000 !important;
    }
    <?php endif; ?>
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ─────────────────────────────────────────────────────────
//  Ceyloan POS — POS Screen JavaScript
// ─────────────────────────────────────────────────────────
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const Routes = {
    products       : '<?php echo e(route("pos.products.search")); ?>',
    customers      : '<?php echo e(route("pos.customers.search")); ?>',
    sale           : '<?php echo e(route("pos.sale")); ?>',
    bill           : '<?php echo e(route("pos.bill")); ?>',
    pendingPayments: '<?php echo e(route("pos.pending-payments")); ?>',
    approvePayment : '<?php echo e(url("pos/approve-payment")); ?>',  // + /{id}
    hold           : '<?php echo e(route("pos.hold")); ?>',
    held           : '<?php echo e(route("pos.held")); ?>',
    heldRetrieve   : '<?php echo e(url("pos/held-orders")); ?>',  // + /{id}/retrieve
    heldDelete     : '<?php echo e(url("pos/held-orders")); ?>',  // + /{id}
    coupon         : '<?php echo e(route("pos.coupon")); ?>',
    giftCard       : '<?php echo e(route("pos.giftcard")); ?>',
    registerOpen   : '<?php echo e(route("pos.register.open")); ?>',
    registerClose  : '<?php echo e(url("pos/register")); ?>',  // + /{id}/close
    orderReceipt   : '<?php echo e(url("orders")); ?>',  // + /{id}/receipt
    orderInvoice   : '<?php echo e(url("orders")); ?>',  // + /{id}/invoice?size=xx
};

// Bill-Only Mode Configuration
const userPaymentMode = '<?php echo e($userPaymentMode ?? "full"); ?>';
const seasonMode = <?php echo e(($seasonMode ?? false) ? 'true' : 'false'); ?>;
const canProcessPayment = <?php echo e(($canProcessPayment ?? true) ? 'true' : 'false'); ?>;
const isBillOnlyMode = userPaymentMode === 'bill_only' && !seasonMode;

// Allow cart price editing (controlled by settings)
const allowCartPriceEdit = <?php echo e(($store?->settings['pos']['allow_price_edit'] ?? false) ? 'true' : 'false'); ?>;
console.log('POS Price Edit Enabled:', allowCartPriceEdit);

// Default print format (thermal-58, thermal-80, a4, a5, a3)
const defaultPrintFormat = '<?php echo e($store?->settings['default_print_format'] ?? 'thermal-80'); ?>';

// Track current completed order for printing
let lastCompletedOrderId = null;

// Track current held order being edited
let currentHeldOrderId = null;

// State
let cart        = [];
let currentCustomer = null;
let discount    = { type: 'none', value: 0, amount: 0, couponCode: '' };
let paymentAmount = 0;
let currentPayMethod = 'cash';
let allProducts = [];
let currentCategoryFilter = '';

// ── Init ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    setupBarcodeScanner();
    loadHeldOrdersCount();
    <?php if(!$register): ?> openRegisterPrompt(); <?php endif; ?>
    updateCartUI();
});

// ── Barcode Scanner ────────────────────────────────────────
// Works with any USB barcode scanner (they act like keyboard input)
let barcodeBuffer = '';
let barcodeTimeout = null;

function setupBarcodeScanner() {
    document.addEventListener('keydown', handleBarcodeInput);
}

function handleBarcodeInput(e) {
    // Skip if user is typing in an input field (except search)
    const activeEl = document.activeElement;
    const isSearchInput = activeEl && activeEl.id === 'productSearch';
    const isInputField = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA');

    // Allow barcode scanning even when focused on search
    if (isInputField && !isSearchInput) return;

    // Barcode scanners type very fast and end with Enter
    if (e.key === 'Enter' && barcodeBuffer.length >= 4) {
        e.preventDefault();
        processBarcodeInput(barcodeBuffer);
        barcodeBuffer = '';
        return;
    }

    // Only allow alphanumeric characters for barcode
    if (e.key.length === 1 && /[a-zA-Z0-9\-]/.test(e.key)) {
        // Don't capture if typing normally in search
        if (isSearchInput && barcodeBuffer.length === 0) return;

        barcodeBuffer += e.key;

        // Clear buffer after 100ms of no input (barcode scanners are fast)
        clearTimeout(barcodeTimeout);
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 100);
    }
}

async function processBarcodeInput(barcode) {
    // Search for product by barcode
    const product = allProducts.find(p =>
        p.barcode === barcode ||
        p.sku === barcode ||
        p.sku?.toLowerCase() === barcode.toLowerCase()
    );

    if (product) {
        addToCart(product);
        showToast(`Added: ${product.name}`, 'success');
    } else {
        // Try to fetch from server
        try {
            const res = await fetch(`${Routes.products}?q=${encodeURIComponent(barcode)}`);
            const data = await res.json();
            if (data.products.length === 1) {
                addToCart(data.products[0]);
                showToast(`Added: ${data.products[0].name}`, 'success');
            } else if (data.products.length > 1) {
                // Multiple matches - show in grid
                renderProducts(data.products);
                showToast('Multiple products found', 'info');
            } else {
                showToast('Product not found: ' + barcode, 'warning');
            }
        } catch (e) {
            showToast('Scan error. Try again.', 'danger');
        }
    }
}

// ── Load Products with Pagination ─────────────────────────
let currentPage = 1;
let isLoadingProducts = false;
let hasMoreProducts = true;

async function loadProducts(category = '', search = '', append = false) {
    if (isLoadingProducts) return;

    const grid = document.getElementById('productsGrid');

    if (!append) {
        currentPage = 1;
        hasMoreProducts = true;
        grid.innerHTML = '<div class="pos-loading"><div class="spinner-border text-primary"></div><p>Loading...</p></div>';
    }

    if (!hasMoreProducts && append) return;

    isLoadingProducts = true;

    try {
        const res = await fetch(`${Routes.products}?q=${encodeURIComponent(search)}&category_id=${category}&page=${currentPage}`);
        const data = await res.json();

        hasMoreProducts = data.has_more;

        if (append) {
            allProducts = [...allProducts, ...data.products];
            appendProducts(data.products);
        } else {
            allProducts = data.products;
            renderProducts(data.products, data.total);
        }

        currentPage++;
    } catch (e) {
        if (!append) {
            grid.innerHTML = '<div class="pos-error"><i class="bi bi-wifi-off fs-2"></i><p>Failed to load products</p></div>';
        }
    } finally {
        isLoadingProducts = false;
    }
}

// Infinite scroll for products
document.getElementById('productsGrid')?.addEventListener('scroll', function() {
    if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100) {
        loadProducts(currentCategoryFilter, document.getElementById('productSearch')?.value || '', true);
    }
});

function renderProducts(products, total = 0) {
    const grid = document.getElementById('productsGrid');
    if (!products.length) {
        grid.innerHTML = '<div class="pos-no-results"><i class="bi bi-search fs-3"></i><p>No products found</p></div>';
        return;
    }
    grid.innerHTML = products.map(p => productCardHTML(p)).join('');
    if (hasMoreProducts) {
        grid.innerHTML += '<div class="load-more-indicator"><div class="spinner-border spinner-border-sm text-primary"></div> Scroll for more...</div>';
    }
}

function appendProducts(products) {
    const grid = document.getElementById('productsGrid');
    // Remove loading indicator
    const loadIndicator = grid.querySelector('.load-more-indicator');
    if (loadIndicator) loadIndicator.remove();

    // Append new products
    products.forEach(p => {
        grid.insertAdjacentHTML('beforeend', productCardHTML(p));
    });

    // Add loading indicator if more products
    if (hasMoreProducts) {
        grid.insertAdjacentHTML('beforeend', '<div class="load-more-indicator"><div class="spinner-border spinner-border-sm text-primary"></div> Scroll for more...</div>');
    }
}

function productCardHTML(p) {
    return `
        <div class="product-card ${p.track_stock && p.stock <= 0 && !p.allow_negative ? 'out-of-stock' : ''}"
             onclick="addToCart(${JSON.stringify(p).replace(/"/g,'&quot;')})"
             data-product-id="${p.id}">
            <div class="product-img">
                ${p.image ? `<img src="${p.image}" alt="${p.name}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><i class="bi bi-box-seam" style="display:none"></i>` : `<i class="bi bi-box-seam"></i>`}
                ${p.track_stock && p.stock <= 0 ? '<div class="out-badge">Out of Stock</div>' : ''}
                ${p.track_stock && p.stock > 0 && p.stock <= 5 ? `<div class="low-badge">${p.stock} left</div>` : ''}
            </div>
            <div class="product-info">
                <div class="product-name">${p.name}</div>
                <div class="product-sku">${p.sku || p.barcode || ''}</div>
                <div class="product-price">Rs. ${parseFloat(p.selling_price).toLocaleString('en-LK', {minimumFractionDigits:2})}</div>
                ${p.track_stock ? `<div class="product-stock">Stock: ${p.stock} ${p.unit}</div>` : ''}
            </div>
        </div>
    `;
}

// ── Add to Cart ───────────────────────────────────────────
function addToCart(product) {
    if (product.track_stock && product.stock <= 0 && !product.allow_negative) {
        showToast('Out of stock!', 'danger'); return;
    }
    if (product.has_variants && product.variants.length) {
        showVariantModal(product); return;
    }
    const existing = cart.find(i => i.product_id === product.id && !i.variant_id);
    if (existing) {
        if (product.track_stock && existing.quantity >= product.stock && !product.allow_negative) {
            showToast('Not enough stock!', 'warning'); return;
        }
        existing.quantity++;
        existing.total = existing.quantity * existing.unit_price;
    } else {
        cart.push({
            product_id   : product.id,
            variant_id   : null,
            product_name : product.name,
            sku          : product.sku,
            barcode      : product.barcode,
            quantity     : 1,
            unit         : product.unit,
            unit_price   : parseFloat(product.selling_price),
            cost_price   : parseFloat(product.cost_price),
            tax_rate     : parseFloat(product.tax_rate) || 0,
            stock        : parseFloat(product.stock),
            track_stock  : product.track_stock,
            discount_amount: 0,
            total        : parseFloat(product.selling_price),
        });
    }
    updateCartUI();
    animateCartAdd();
    updateCustomerDisplay('item_added');
}

function addVariantToCart(product, variant) {
    const existing = cart.find(i => i.product_id === product.id && i.variant_id === variant.id);
    if (existing) { existing.quantity++; existing.total = existing.quantity * existing.unit_price; }
    else {
        cart.push({
            product_id: product.id, variant_id: variant.id,
            product_name: `${product.name} - ${variant.name}`,
            quantity: 1, unit: product.unit,
            unit_price: parseFloat(variant.price),
            cost_price: parseFloat(product.cost_price),
            tax_rate: parseFloat(product.tax_rate) || 0,
            stock: parseFloat(variant.stock),
            track_stock: product.track_stock, discount_amount: 0,
            total: parseFloat(variant.price),
        });
    }
    bootstrap.Modal.getInstance(document.getElementById('variantModal'))?.hide();
    updateCartUI();
}

// ── Cart UI ───────────────────────────────────────────────
function updateCartUI() {
    const cartItems  = document.getElementById('cartItems');
    const cartEmpty  = document.getElementById('cartEmpty');
    const chargeBtn  = document.getElementById('chargeBtn');
    const totals     = calcTotals();

    if (!cart.length) {
        cartEmpty.style.display = 'flex';
        chargeBtn.disabled = true;
    } else {
        cartEmpty.style.display = 'none';
        chargeBtn.disabled = false;
    }

    // Render cart items
    const itemsHtml = cart.map((item, i) => `
        <div class="cart-item" id="cartItem${i}">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.product_name}</div>
                <div class="cart-item-price">
                    ${allowCartPriceEdit ? `
                        <span class="editable-price" onclick="editItemPrice(${i})" title="Click to edit price">
                            Rs. ${item.unit_price.toFixed(2)}
                            <i class="bi bi-pencil-fill edit-price-icon"></i>
                        </span>
                    ` : `Rs. ${item.unit_price.toFixed(2)}`}
                    × ${item.quantity}
                </div>
            </div>
            <div class="cart-item-controls">
                <button class="qty-btn qty-minus" onclick="changeQty(${i}, -1)"><i class="bi bi-dash"></i></button>
                <input type="number" class="qty-input" value="${item.quantity}" min="0.001" step="1"
                       onchange="setQty(${i}, this.value)" onclick="this.select()">
                <button class="qty-btn qty-plus" onclick="changeQty(${i}, 1)"><i class="bi bi-plus"></i></button>
            </div>
            <div class="cart-item-total">
                <div class="cart-item-subtotal">Rs. ${(item.quantity * item.unit_price).toFixed(2)}</div>
                <button class="cart-item-remove" onclick="removeFromCart(${i})"><i class="bi bi-x-circle-fill"></i></button>
            </div>
        </div>
    `).join('');
    cartItems.innerHTML = (cart.length ? itemsHtml : '') + `<div class="cart-empty" id="cartEmpty" style="display:${cart.length?'none':'flex'}">
        <i class="bi bi-cart-x"></i><p>Cart is empty</p><small>Search or click a product to add</small>
    </div>`;

    // Update totals
    document.getElementById('subtotalDisplay').textContent  = `Rs. ${totals.subtotal.toFixed(2)}`;
    document.getElementById('grandTotalDisplay').textContent = `Rs. ${totals.grandTotal.toFixed(2)}`;
    document.getElementById('chargeBtnAmount').textContent   = `Rs. ${totals.grandTotal.toFixed(2)}`;

    if (totals.discountAmount > 0) {
        document.getElementById('discountDisplay').textContent = `- Rs. ${totals.discountAmount.toFixed(2)}`;
        document.getElementById('discountRow').style.display = 'flex';
    } else { document.getElementById('discountRow').style.display = 'none'; }

    if (totals.taxAmount > 0) {
        document.getElementById('taxDisplay').textContent = `Rs. ${totals.taxAmount.toFixed(2)}`;
        document.getElementById('taxRow').style.display = 'flex';
    } else { document.getElementById('taxRow').style.display = 'none'; }
}

function calcTotals() {
    const subtotal      = cart.reduce((s, i) => s + (i.quantity * i.unit_price - (i.discount_amount || 0)), 0);
    const taxAmount     = cart.reduce((s, i) => s + ((i.quantity * i.unit_price) * (i.tax_rate / 100)), 0);
    let discountAmount  = 0;

    if (discount.type === 'percentage' && discount.value > 0) {
        discountAmount = subtotal * discount.value / 100;
    } else if (discount.type === 'fixed' && discount.value > 0) {
        discountAmount = Math.min(discount.value, subtotal);
    } else if (discount.type === 'coupon') {
        discountAmount = discount.amount || 0;
    }

    const grandTotal = subtotal + taxAmount - discountAmount;
    return { subtotal, taxAmount, discountAmount, grandTotal: Math.max(0, grandTotal) };
}

function changeQty(index, delta) {
    const item = cart[index];
    const newQty = item.quantity + delta;
    if (newQty <= 0) { removeFromCart(index); return; }
    if (item.track_stock && newQty > item.stock) { showToast('Not enough stock!', 'warning'); return; }
    item.quantity = newQty;
    item.total = newQty * item.unit_price;
    updateCartUI();
    updateCustomerDisplay();
}

function setQty(index, value) {
    const qty = parseFloat(value);
    if (isNaN(qty) || qty <= 0) { removeFromCart(index); return; }
    cart[index].quantity = qty;
    cart[index].total    = qty * cart[index].unit_price;
    updateCartUI();
    updateCustomerDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartUI();
    // Show ready state if cart becomes empty
    updateCustomerDisplay(cart.length === 0 ? 'ready' : 'normal');
}

function clearCart() {
    cart = [];
    currentCustomer = null;
    discount = { type: 'none', value: 0, amount: 0, couponCode: '' };
    document.getElementById('customerSearch').value = '';
    document.getElementById('selectedCustomerId').value = '';
    document.getElementById('clearCustomer').classList.add('d-none');
    document.querySelectorAll('.disc-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.disc-tab[data-type="none"]').classList.add('active');
    document.getElementById('discountInput').classList.add('d-none');
    document.getElementById('couponInput').classList.add('d-none');
    updateCartUI();
}

// ── Edit Item Price ────────────────────────────────────────
function editItemPrice(index) {
    if (!allowCartPriceEdit) return;
    const item = cart[index];
    const currentPrice = item.unit_price.toFixed(2);

    // Create modal for price editing
    const modal = document.createElement('div');
    modal.className = 'price-edit-modal';
    modal.innerHTML = `
        <div class="price-edit-content">
            <div class="price-edit-header">
                <h6><i class="bi bi-pencil-square me-2"></i>Edit Price</h6>
                <button class="btn-close-price-edit" onclick="closePriceEdit()"><i class="bi bi-x"></i></button>
            </div>
            <div class="price-edit-body">
                <div class="price-edit-product">${item.product_name}</div>
                <div class="price-edit-original">Original: Rs. ${item.original_price ? item.original_price.toFixed(2) : currentPrice}</div>
                <div class="price-edit-input-wrap">
                    <span class="price-currency">Rs.</span>
                    <input type="number" id="newPriceInput" class="price-edit-input" value="${currentPrice}"
                           min="0" step="0.01" autofocus onkeydown="if(event.key==='Enter')applyNewPrice(${index})">
                </div>
            </div>
            <div class="price-edit-footer">
                <button class="btn btn-secondary btn-sm" onclick="closePriceEdit()">Cancel</button>
                <button class="btn btn-primary btn-sm" onclick="applyNewPrice(${index})">
                    <i class="bi bi-check-lg"></i> Apply
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Store original price if not already stored
    if (!item.original_price) {
        item.original_price = item.unit_price;
    }

    // Focus and select input
    setTimeout(() => {
        const input = document.getElementById('newPriceInput');
        if (input) { input.focus(); input.select(); }
    }, 100);
}

function applyNewPrice(index) {
    const input = document.getElementById('newPriceInput');
    const newPrice = parseFloat(input.value);
    if (isNaN(newPrice) || newPrice < 0) {
        showToast('Please enter a valid price', 'warning');
        return;
    }
    cart[index].unit_price = newPrice;
    cart[index].total = cart[index].quantity * newPrice;
    closePriceEdit();
    updateCartUI();
    showToast('Price updated', 'success');
}

function closePriceEdit() {
    const modal = document.querySelector('.price-edit-modal');
    if (modal) modal.remove();
}

// ── Payment Modal ─────────────────────────────────────────
function openPayment(method) {
    if (!cart.length) { showToast('Cart is empty!', 'warning'); return; }
    const totals = calcTotals();
    currentPayMethod = method;
    paymentAmount = 0;
    paymentAmountStr = '0';
    document.getElementById('manualAmountInput').value = '';

    // Populate order summary
    const pmItems = cart.map(i => `
        <div class="pm-item">
            <span>${i.product_name} × ${i.quantity}</span>
            <span>Rs. ${(i.quantity * i.unit_price).toFixed(2)}</span>
        </div>
    `).join('');
    document.getElementById('paymentOrderItems').innerHTML = pmItems;

    document.getElementById('pmSubtotal').textContent = `Rs. ${totals.subtotal.toFixed(2)}`;
    document.getElementById('pmTotal').textContent    = `Rs. ${totals.grandTotal.toFixed(2)}`;
    if (totals.discountAmount > 0) {
        document.getElementById('pmDiscount').textContent = `- Rs. ${totals.discountAmount.toFixed(2)}`;
        document.getElementById('pmDiscountRow').style.display = 'flex';
    }
    if (totals.taxAmount > 0) {
        document.getElementById('pmTax').textContent = `Rs. ${totals.taxAmount.toFixed(2)}`;
        document.getElementById('pmTaxRow').style.display = 'flex';
    }

    // Quick amounts - rounded values
    const grand = totals.grandTotal;
    const roundedAmounts = [
        Math.ceil(grand / 100) * 100,
        Math.ceil(grand / 500) * 500,
        Math.ceil(grand / 1000) * 1000,
        Math.ceil(grand / 5000) * 5000,
        10000,
        20000
    ].filter((v, i, a) => a.indexOf(v) === i && v > grand).slice(0, 5);
    document.getElementById('quickAmounts').innerHTML = roundedAmounts.map(a =>
        `<button type="button" class="quick-amt" onclick="setPaymentAmount(${a})">Rs. ${a.toLocaleString()}</button>`
    ).join('');

    // Update exact amount button text
    document.getElementById('exactAmountBtn').innerHTML =
        `<i class="bi bi-check-circle"></i> Exact amount (Rs. ${grand.toLocaleString('en-LK', {minimumFractionDigits:2})})`;

    // Set method tab
    document.querySelectorAll('.pm-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.pm-tab[data-method="${method}"]`)?.classList.add('active');
    document.getElementById('cardReferenceField').classList.toggle('d-none', method === 'cash');

    updatePaymentDisplay(totals.grandTotal);
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

// Payment amount as string for proper numpad handling
let paymentAmountStr = '0';

function numpadInput(key) {
    if (key === 'C') {
        paymentAmountStr = '0';
    } else if (key === 'DEL') {
        paymentAmountStr = paymentAmountStr.slice(0, -1) || '0';
    } else {
        // Allow typing any digit including 0
        if (paymentAmountStr === '0') {
            paymentAmountStr = key;
        } else {
            paymentAmountStr += key;
        }
    }
    // Convert string to amount (divide by 100 for cents)
    paymentAmount = parseFloat((parseInt(paymentAmountStr) / 100).toFixed(2));
    updatePaymentDisplay();
    // Sync with manual input
    document.getElementById('manualAmountInput').value = paymentAmount > 0 ? paymentAmount : '';
}

function setPaymentAmount(amount) {
    paymentAmount = parseFloat(amount);
    paymentAmountStr = Math.round(paymentAmount * 100).toString();
    document.getElementById('manualAmountInput').value = paymentAmount > 0 ? paymentAmount : '';
    updatePaymentDisplay();
}

function setExactAmount() {
    const totals = calcTotals();
    setPaymentAmount(totals.grandTotal);
    showToast('Exact amount set', 'success');
}

function setManualAmount(value) {
    const amount = parseFloat(value) || 0;
    paymentAmount = amount;
    paymentAmountStr = Math.round(amount * 100).toString();
    updatePaymentDisplay();
}

function updatePaymentDisplay(fallback = null) {
    const amount = paymentAmount || fallback || 0;
    const totals = calcTotals();
    const change = Math.max(0, amount - totals.grandTotal);
    const due = Math.max(0, totals.grandTotal - amount);

    document.getElementById('pmAmountDisplay').textContent = `Rs. ${amount.toLocaleString('en-LK', {minimumFractionDigits:2})}`;

    if (amount >= totals.grandTotal) {
        if (change > 0) {
            document.getElementById('pmChangeDisplay').textContent = `Change: Rs. ${change.toLocaleString('en-LK', {minimumFractionDigits:2})}`;
            document.getElementById('pmChangeDisplay').className = 'pm-change-display change-green';
        } else {
            document.getElementById('pmChangeDisplay').textContent = '✓ Exact amount';
            document.getElementById('pmChangeDisplay').className = 'pm-change-display change-green';
        }
    } else {
        document.getElementById('pmChangeDisplay').textContent = `Still due: Rs. ${due.toLocaleString('en-LK', {minimumFractionDigits:2})}`;
        document.getElementById('pmChangeDisplay').className = 'pm-change-display change-red';
    }
}

// ── Complete Sale ──────────────────────────────────────────
document.getElementById('completeSaleBtn').addEventListener('click', async () => {
    const totals = calcTotals();
    if (paymentAmount <= 0 && currentPayMethod === 'cash') {
        showToast('Enter payment amount!', 'warning'); return;
    }

    const btn = document.getElementById('completeSaleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
        const payload = {
            customer_id    : document.getElementById('selectedCustomerId').value || null,
            items          : cart.map(i => ({ product_id: i.product_id, variant_id: i.variant_id, quantity: i.quantity, unit_price: i.unit_price, discount_amount: i.discount_amount || 0, tax_rate: i.tax_rate })),
            payments       : [{ method: currentPayMethod, amount: paymentAmount || totals.grandTotal, reference: document.getElementById('cardReference')?.value }],
            subtotal       : totals.subtotal,
            tax_amount     : totals.taxAmount,
            discount_amount: totals.discountAmount,
            discount_type  : discount.type,
            discount_value : discount.value,
            coupon_code    : discount.couponCode,
            paid_amount    : paymentAmount || totals.grandTotal,
            notes          : document.getElementById('orderNotes').value,
        };

        const res  = await fetch(Routes.sale, { method:'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify(payload) });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
            updateCustomerDisplay('sale_complete');
            lastCompletedOrderId = data.order.id;
            showReceiptModal(data.order, totals, paymentAmount);
            clearCart();
        } else {
            showToast(data.message || 'Sale failed', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Complete Sale';
    }
});

// ── Receipt ───────────────────────────────────────────────
const storeInfo = {
    name: <?php echo json_encode($store?->name ?? 'My Store', 15, 512) ?>,
    address: <?php echo json_encode($store?->address ?? '', 15, 512) ?>,
    phone: <?php echo json_encode($store?->phone ?? '', 15, 512) ?>,
    email: <?php echo json_encode($store?->email ?? '', 15, 512) ?>,
    header: <?php echo json_encode($store?->receipt_header ?? '', 15, 512) ?>,
    footer: <?php echo json_encode($store?->receipt_footer ?? 'Thank you for your business!', 15, 512) ?>
};

function showReceiptModal(order, totals, paidAmount) {
    const change = Math.max(0, paidAmount - totals.grandTotal);
    const orderDate = new Date();
    const dateStr = orderDate.toLocaleDateString('en-GB', {day: '2-digit', month: '2-digit', year: 'numeric'});
    const timeStr = orderDate.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    const cashierName = '<?php echo e(auth()->user()->name); ?>';

    // Receipt Format - Matches screenshot exactly
    const html = `
        <div class="receipt-header">
            <h3 class="store-name">${storeInfo.name}</h3>
            ${storeInfo.address ? `<p class="store-detail">${storeInfo.address}</p>` : ''}
            ${storeInfo.phone ? `<p class="store-detail">Tel: ${storeInfo.phone}</p>` : ''}
            ${storeInfo.email ? `<p class="store-detail">${storeInfo.email}</p>` : ''}
            ${storeInfo.header ? `<p class="store-header">${storeInfo.header}</p>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-meta">
            <div><span class="meta-label">Order:</span> ${order.order_number}</div>
            <div><span class="meta-label">Date:</span> ${dateStr}, ${timeStr}</div>
            <div><span class="meta-label">Cashier:</span> ${cashierName}</div>
            ${order.customer ? `<div><span class="meta-label">Customer:</span> ${order.customer.name}</div>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-items">
            ${cart.map(i => `
                <div class="receipt-item">
                    <div class="ri-name">${i.product_name}</div>
                    <div class="ri-line">
                        <span class="ri-qty">${i.quantity} × Rs.${i.unit_price.toFixed(2)}</span>
                        <span class="ri-total">Rs.${(i.quantity * i.unit_price).toFixed(2)}</span>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-totals">
            <div class="rt-row"><span>Subtotal</span><span>Rs. ${totals.subtotal.toFixed(2)}</span></div>
            ${totals.discountAmount > 0 ? `<div class="rt-row rt-discount"><span>Discount</span><span>- Rs. ${totals.discountAmount.toFixed(2)}</span></div>` : ''}
            ${totals.taxAmount > 0 ? `<div class="rt-row"><span>Tax</span><span>Rs. ${totals.taxAmount.toFixed(2)}</span></div>` : ''}
            <div class="rt-row rt-total"><span>TOTAL</span><span>Rs. ${totals.grandTotal.toFixed(2)}</span></div>
            <div class="rt-row"><span>Paid (${currentPayMethod.replace(/_/g,' ')})</span><span>Rs. ${paidAmount.toFixed(2)}</span></div>
            ${change > 0 ? `<div class="rt-row rt-change"><span>Change</span><span>Rs. ${change.toFixed(2)}</span></div>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-footer">
            <p class="footer-thanks">${storeInfo.footer}</p>
            <p class="receipt-powered">Ceyloan POS &bull; Powered by Nexfloit</p>
        </div>
    `;

    document.getElementById('receiptContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

function printReceipt() {
    window.print();
    // Reset customer display to welcome after print
    setTimeout(() => {
        updateCustomerDisplay('ready');
    }, 1000);
}

function startNewSale() {
    clearCart();
    loadProducts();
    // Reset customer display for new sale
    updateCustomerDisplay('ready');
}

// ── Print View Functions ─────────────────────────────────
function openPrintView(format) {
    if (!lastCompletedOrderId) {
        showToast('No order to print', 'warning');
        return;
    }

    let url;
    // Handle thermal formats (thermal, thermal-58, thermal-80)
    if (format === 'thermal' || format.startsWith('thermal-')) {
        const size = format.includes('-') ? format.split('-')[1] : '80';
        url = `${Routes.orderReceipt}/${lastCompletedOrderId}/receipt?size=${size}`;
    } else {
        // Invoice formats: a4, a5, a3
        url = `${Routes.orderInvoice}/${lastCompletedOrderId}/invoice?size=${format}`;
    }
    window.open(url, '_blank');
}

function openDefaultPrintView() {
    openPrintView(defaultPrintFormat);
}

// ── Customer Search ───────────────────────────────────────
const customerSearch  = document.getElementById('customerSearch');
const customerDropdown = document.getElementById('customerDropdown');
let customerTimer;

customerSearch.addEventListener('input', () => {
    clearTimeout(customerTimer);
    const q = customerSearch.value.trim();
    if (!q) { customerDropdown.classList.add('d-none'); return; }
    customerTimer = setTimeout(async () => {
        const res  = await fetch(`${Routes.customers}?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (!data.customers.length) { customerDropdown.classList.add('d-none'); return; }
        customerDropdown.innerHTML = data.customers.map(c => `
            <div class="customer-option" onclick="selectCustomer(${JSON.stringify(c).replace(/"/g,'&quot;')})">
                <i class="bi bi-person-circle"></i>
                <div>
                    <div class="co-name">${c.name}</div>
                    <div class="co-meta">${c.phone || ''} ${c.email ? '• ' + c.email : ''}</div>
                </div>
                <div class="co-points">${c.loyalty_points || 0} pts</div>
            </div>
        `).join('');
        customerDropdown.classList.remove('d-none');
    }, 300);
});

function selectCustomer(customer) {
    currentCustomer = customer;
    customerSearch.value = customer.name;
    document.getElementById('selectedCustomerId').value = customer.id;
    document.getElementById('clearCustomer').classList.remove('d-none');
    customerDropdown.classList.add('d-none');
}

document.getElementById('clearCustomer').addEventListener('click', () => {
    currentCustomer = null;
    customerSearch.value = '';
    document.getElementById('selectedCustomerId').value = '';
    document.getElementById('clearCustomer').classList.add('d-none');
});

// ── Discount Tabs ─────────────────────────────────────────
document.querySelectorAll('.disc-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.disc-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const type = tab.dataset.type;
        discount.type = type;
        discount.value = 0;
        discount.amount = 0;
        document.getElementById('discountInput').classList.toggle('d-none', !['percentage','fixed'].includes(type));
        document.getElementById('couponInput').classList.toggle('d-none', type !== 'coupon');
        if (type !== 'coupon') { document.getElementById('discountValue').value = ''; }
        updateCartUI();
    });
});

document.getElementById('discountValue').addEventListener('input', e => {
    discount.value = parseFloat(e.target.value) || 0;
    updateCartUI();
});

document.getElementById('applyCouponBtn').addEventListener('click', async () => {
    const code = document.getElementById('couponCode').value.trim();
    if (!code) return;
    const totals = calcTotals();
    const res    = await fetch(Routes.coupon, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({ code, subtotal: totals.subtotal }) });
    const data   = await res.json();
    if (data.success) {
        discount.amount = data.discount_amount;
        discount.couponCode = code;
        showToast(data.message, 'success');
        updateCartUI();
    } else {
        showToast(data.message, 'danger');
    }
});

// ── Category Slider ──────────────────────────────────────
function slideCategories(direction) {
    const container = document.getElementById('categoriesContainer');
    const scrollAmount = 150;
    container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
}

// ── Category Filter ───────────────────────────────────────
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategoryFilter = btn.dataset.category;
        loadProducts(currentCategoryFilter, document.getElementById('productSearch').value);
    });
});

// ── Product Search ────────────────────────────────────────
let searchTimer;
document.getElementById('productSearch').addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadProducts(currentCategoryFilter, e.target.value), 350);
});

// ── Payment Method Tabs ───────────────────────────────────
document.querySelectorAll('.pm-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.pm-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentPayMethod = tab.dataset.method;
        document.getElementById('cardReferenceField').classList.toggle('d-none', currentPayMethod === 'cash');
        paymentAmount = 0;
        paymentAmountStr = '0';
        document.getElementById('manualAmountInput').value = '';
        updatePaymentDisplay();
    });
});

// ── Hold Order ────────────────────────────────────────────
document.getElementById('holdBtn').addEventListener('click', async () => {
    if (!cart.length) return;
    const totals = calcTotals();

    const payload = {
        customer_id     : document.getElementById('selectedCustomerId').value || null,
        items           : cart.map(i => ({
            product_id      : i.product_id,
            variant_id      : i.variant_id,
            product_name    : i.product_name,
            sku             : i.sku,
            barcode         : i.barcode,
            quantity        : i.quantity,
            unit            : i.unit,
            unit_price      : i.unit_price,
            cost_price      : i.cost_price,
            tax_rate        : i.tax_rate,
            discount_amount : i.discount_amount || 0,
        })),
        subtotal        : totals.subtotal,
        tax_amount      : totals.taxAmount,
        discount_amount : totals.discountAmount,
        discount_type   : discount.type,
        discount_value  : discount.value,
        total_amount    : totals.grandTotal,
        notes           : null,
    };

    try {
        const res = await fetch(Routes.hold, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast('Order held successfully!', 'success');
            updateCustomerDisplay('sale_hold');
            currentHeldOrderId = null;
            clearCart();
            loadHeldOrdersCount();
            // Reset customer display after 3 seconds
            setTimeout(() => updateCustomerDisplay('ready'), 3000);
        } else {
            showToast(data.message || 'Failed to hold order', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    }
});

document.getElementById('heldOrdersBtn').addEventListener('click', async () => {
    await showHeldOrdersModal();
});

async function showHeldOrdersModal() {
    const modal = new bootstrap.Modal(document.getElementById('heldOrdersModal'));
    const res   = await fetch(Routes.held);
    const data  = await res.json();
    const body  = document.getElementById('heldOrdersBody');

    if (!data.orders.length) {
        body.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No held orders</p></div>';
    } else {
        body.innerHTML = data.orders.map(o => `
            <div class="held-order-item" data-order-id="${o.id}">
                <div class="held-order-info">
                    <div class="held-order-header">
                        <strong>${o.order_number}</strong>
                        <span class="held-order-time text-muted">${formatTimeAgo(o.created_at)}</span>
                    </div>
                    <div class="text-muted small">${o.customer?.name || 'Walk-in'} • ${o.items?.length || 0} items</div>
                    <div class="held-order-items-preview">${o.items?.slice(0, 3).map(i => i.product_name).join(', ') || ''}${o.items?.length > 3 ? '...' : ''}</div>
                </div>
                <div class="held-order-total fw-bold">Rs. ${parseFloat(o.total_amount).toFixed(2)}</div>
                <div class="held-order-actions">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="retrieveHeldOrder(${o.id}, 'replace')" title="Replace current cart">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Replace
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="retrieveHeldOrder(${o.id}, 'add')" title="Add to current cart">
                            <i class="bi bi-plus-circle me-1"></i>Add
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteHeldOrder(${o.id})" title="Delete order">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    modal.show();
}

// Retrieve held order into cart
async function retrieveHeldOrder(orderId, mode = 'replace') {
    // Check if cart has items when replacing
    if (mode === 'replace' && cart.length > 0) {
        if (!confirm('Current cart has items. This will replace them. Continue?')) {
            return;
        }
    }

    try {
        const res = await fetch(`${Routes.heldRetrieve}/${orderId}/retrieve`);
        const data = await res.json();

        if (data.success) {
            if (mode === 'replace') {
                // Clear current cart completely
                cart = [];
                currentCustomer = null;
                discount = { type: 'none', value: 0, amount: 0, couponCode: '' };

                // Load items into cart
                cart = data.items;
                currentHeldOrderId = orderId;

                // Set customer if exists
                if (data.customer) {
                    currentCustomer = data.customer;
                    document.getElementById('customerSearch').value = data.customer.name;
                    document.getElementById('selectedCustomerId').value = data.customer.id;
                    document.getElementById('clearCustomer').classList.remove('d-none');
                }

                // Set discount if exists
                if (data.discount && data.discount.type !== 'none') {
                    discount = data.discount;
                    // Update discount tabs
                    document.querySelectorAll('.disc-tab').forEach(t => t.classList.remove('active'));
                    const discTab = document.querySelector(`.disc-tab[data-type="${discount.type}"]`);
                    if (discTab) discTab.classList.add('active');
                    if (['percentage', 'fixed'].includes(discount.type)) {
                        document.getElementById('discountInput').classList.remove('d-none');
                        document.getElementById('discountValue').value = discount.value;
                    }
                }
            } else {
                // Add mode - merge items with existing cart
                data.items.forEach(heldItem => {
                    // Check if item already exists in cart
                    const existing = cart.find(c => c.product_id === heldItem.product_id && c.variant_id === heldItem.variant_id);
                    if (existing) {
                        // Increase quantity
                        existing.quantity += heldItem.quantity;
                        existing.total = existing.quantity * existing.unit_price;
                    } else {
                        // Add new item
                        cart.push(heldItem);
                    }
                });
            }

            // Delete the held order since we're loading it
            await fetch(`${Routes.heldDelete}/${orderId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF }
            });

            updateCartUI();
            loadHeldOrdersCount();

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('heldOrdersModal'))?.hide();

            if (mode === 'add') {
                showToast(`${data.items.length} items added to cart!`, 'success');
            } else {
                showToast('Order retrieved! Continue editing or process payment.', 'success');
            }
        } else {
            showToast(data.message || 'Failed to retrieve order', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    }
}

// Delete held order
async function deleteHeldOrder(orderId) {
    if (!confirm('Delete this held order? This cannot be undone.')) {
        return;
    }

    try {
        const res = await fetch(`${Routes.heldDelete}/${orderId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        if (data.success) {
            showToast('Held order deleted', 'success');
            loadHeldOrdersCount();
            // Refresh the modal
            showHeldOrdersModal();
        } else {
            showToast(data.message || 'Failed to delete order', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    }
}

// Load held orders count for badge
async function loadHeldOrdersCount() {
    try {
        const res = await fetch(Routes.held);
        const data = await res.json();
        const badge = document.getElementById('heldBadge');
        if (data.orders.length > 0) {
            badge.textContent = data.orders.length;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    } catch (e) {
        console.error('Failed to load held orders count');
    }
}

// Format time ago helper
function formatTimeAgo(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

// ═══════════════════════════════════════════════════════════
// BILL-ONLY MODE FUNCTIONS
// ═══════════════════════════════════════════════════════════

// Create bill without payment (for bill-only cashiers)
async function createBill() {
    if (!cart.length) { showToast('Cart is empty!', 'warning'); return; }

    const totals = calcTotals();
    const btn = document.getElementById('chargeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Bill...';

    try {
        const payload = {
            customer_id    : document.getElementById('selectedCustomerId').value || null,
            items          : cart.map(i => ({ product_id: i.product_id, variant_id: i.variant_id, quantity: i.quantity, unit_price: i.unit_price, discount_amount: i.discount_amount || 0, tax_rate: i.tax_rate })),
            subtotal       : totals.subtotal,
            tax_amount     : totals.taxAmount,
            discount_amount: totals.discountAmount,
            discount_type  : discount.type,
            discount_value : discount.value,
            coupon_code    : discount.couponCode,
        };

        const res = await fetch(Routes.bill, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            showToast('Bill created! Awaiting payment approval.', 'success');
            showBillReceipt(data.order, totals);
            clearCart();
        } else {
            showToast(data.message || 'Failed to create bill', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-receipt me-2"></i>Create Bill <span id="chargeBtnAmount">Rs. 0.00</span>';
        updateCartUI();
    }
}

// Show bill receipt (pending payment)
function showBillReceipt(order, totals) {
    const orderDate = new Date();
    const dateStr = orderDate.toLocaleDateString('en-GB', {day: '2-digit', month: '2-digit', year: 'numeric'});
    const timeStr = orderDate.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    const cashierName = '<?php echo e(auth()->user()->name); ?>';

    const html = `
        <div class="receipt-header">
            <h3 class="store-name">${storeInfo.name}</h3>
            ${storeInfo.address ? `<p class="store-detail">${storeInfo.address}</p>` : ''}
            ${storeInfo.phone ? `<p class="store-detail">Tel: ${storeInfo.phone}</p>` : ''}
            <p class="store-header text-warning"><strong>*** BILL - PAYMENT PENDING ***</strong></p>
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-meta">
            <div><span class="meta-label">Bill No:</span> ${order.order_number}</div>
            <div><span class="meta-label">Date:</span> ${dateStr}, ${timeStr}</div>
            <div><span class="meta-label">Cashier:</span> ${cashierName}</div>
            ${order.customer ? `<div><span class="meta-label">Customer:</span> ${order.customer.name}</div>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-items">
            ${cart.map(i => `
                <div class="receipt-item">
                    <div class="ri-name">${i.product_name}</div>
                    <div class="ri-line">
                        <span class="ri-qty">${i.quantity} x Rs.${i.unit_price.toFixed(2)}</span>
                        <span class="ri-total">Rs.${(i.quantity * i.unit_price).toFixed(2)}</span>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-totals">
            <div class="rt-row"><span>Subtotal</span><span>Rs. ${totals.subtotal.toFixed(2)}</span></div>
            ${totals.discountAmount > 0 ? `<div class="rt-row rt-discount"><span>Discount</span><span>- Rs. ${totals.discountAmount.toFixed(2)}</span></div>` : ''}
            ${totals.taxAmount > 0 ? `<div class="rt-row"><span>Tax</span><span>Rs. ${totals.taxAmount.toFixed(2)}</span></div>` : ''}
            <div class="rt-row rt-total"><span>TOTAL DUE</span><span>Rs. ${totals.grandTotal.toFixed(2)}</span></div>
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-footer">
            <p class="footer-thanks text-warning">Please proceed to main counter for payment</p>
            <p class="receipt-powered">Ceyloan POS</p>
        </div>
    `;

    document.getElementById('receiptContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

// Show pending payments modal
async function showPendingPaymentsModal() {
    const modal = new bootstrap.Modal(document.getElementById('pendingPaymentsModal'));
    const body = document.getElementById('pendingPaymentsBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
    modal.show();

    try {
        const res = await fetch(Routes.pendingPayments);
        const data = await res.json();

        if (!data.orders.length) {
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No pending payments</p></div>';
        } else {
            body.innerHTML = data.orders.map(o => `
                <div class="held-order-item pending-payment-item" data-order-id="${o.id}">
                    <div class="held-order-info">
                        <div class="held-order-header">
                            <strong>${o.order_number}</strong>
                            <span class="badge bg-warning text-dark ms-2">Pending Payment</span>
                            <span class="held-order-time text-muted">${formatTimeAgo(o.created_at)}</span>
                        </div>
                        <div class="text-muted small">
                            ${o.customer?.name || 'Walk-in'} | ${o.items?.length || 0} items
                            | Billed by: ${o.billed_by?.name || '-'}
                        </div>
                        <div class="held-order-items-preview">${o.items?.slice(0, 3).map(i => i.product_name).join(', ') || ''}${o.items?.length > 3 ? '...' : ''}</div>
                    </div>
                    <div class="held-order-total fw-bold text-primary fs-5">Rs. ${parseFloat(o.total_amount).toLocaleString('en-LK', {minimumFractionDigits: 2})}</div>
                    <div class="held-order-actions">
                        <button class="btn btn-success" onclick="openApprovePaymentModal(${JSON.stringify(o).replace(/"/g, '&quot;')})">
                            <i class="bi bi-check-circle me-1"></i>Approve & Pay
                        </button>
                    </div>
                </div>
            `).join('');
        }
    } catch (e) {
        body.innerHTML = '<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-2"></i><p>Failed to load pending payments</p></div>';
    }
}

// Load pending payments count
async function loadPendingPaymentsCount() {
    if (!canProcessPayment) return;
    try {
        const res = await fetch(Routes.pendingPayments);
        const data = await res.json();
        const badge = document.getElementById('pendingBadge');
        if (badge) {
            if (data.orders.length > 0) {
                badge.textContent = data.orders.length;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    } catch (e) {
        console.error('Failed to load pending payments count');
    }
}

// Approve payment modal state
let approveOrderData = null;
let approvePaymentAmount = 0;
let approvePaymentAmountStr = '0';
let approvePayMethod = 'cash';

function openApprovePaymentModal(order) {
    approveOrderData = order;
    approvePaymentAmount = 0;
    approvePaymentAmountStr = '0';
    approvePayMethod = 'cash';

    document.getElementById('approveOrderId').value = order.id;

    // Populate order summary
    const itemsHtml = (order.items || []).map(i => `
        <div class="pm-item">
            <span>${i.product_name} x ${i.quantity}</span>
            <span>Rs. ${parseFloat(i.total).toFixed(2)}</span>
        </div>
    `).join('');
    document.getElementById('approveOrderItems').innerHTML = itemsHtml;

    document.getElementById('appSubtotal').textContent = `Rs. ${parseFloat(order.subtotal).toFixed(2)}`;
    document.getElementById('appTotal').textContent = `Rs. ${parseFloat(order.total_amount).toFixed(2)}`;

    if (parseFloat(order.discount_amount) > 0) {
        document.getElementById('appDiscount').textContent = `- Rs. ${parseFloat(order.discount_amount).toFixed(2)}`;
        document.getElementById('appDiscountRow').style.display = 'flex';
    } else {
        document.getElementById('appDiscountRow').style.display = 'none';
    }

    // Billed by info
    document.getElementById('appBilledBy').textContent = order.billed_by?.name || '-';
    document.getElementById('appBilledAt').textContent = order.billed_at ? new Date(order.billed_at).toLocaleString('en-GB') : '-';

    // Reset payment tabs
    document.querySelectorAll('#approvePaymentModal .pm-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('#approvePaymentModal .pm-tab[data-method="cash"]').classList.add('active');
    document.getElementById('appCardReferenceField').classList.add('d-none');

    updateApprovePaymentDisplay();

    // Hide pending modal, show approve modal
    bootstrap.Modal.getInstance(document.getElementById('pendingPaymentsModal'))?.hide();
    new bootstrap.Modal(document.getElementById('approvePaymentModal')).show();
}

function selectApprovePayMethod(method) {
    approvePayMethod = method;
    document.querySelectorAll('#approvePaymentModal .pm-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`#approvePaymentModal .pm-tab[data-method="${method}"]`)?.classList.add('active');
    document.getElementById('appCardReferenceField').classList.toggle('d-none', method === 'cash');
}

function approveNumpadInput(key) {
    if (key === 'C') {
        approvePaymentAmountStr = '0';
    } else if (key === 'DEL') {
        approvePaymentAmountStr = approvePaymentAmountStr.slice(0, -1) || '0';
    } else {
        if (approvePaymentAmountStr === '0') {
            approvePaymentAmountStr = key;
        } else {
            approvePaymentAmountStr += key;
        }
    }
    approvePaymentAmount = parseFloat((parseInt(approvePaymentAmountStr) / 100).toFixed(2));
    updateApprovePaymentDisplay();
}

function setApproveExactAmount() {
    if (!approveOrderData) return;
    approvePaymentAmount = parseFloat(approveOrderData.total_amount);
    approvePaymentAmountStr = Math.round(approvePaymentAmount * 100).toString();
    updateApprovePaymentDisplay();
    showToast('Exact amount set', 'success');
}

function updateApprovePaymentDisplay() {
    const total = parseFloat(approveOrderData?.total_amount || 0);
    const amount = approvePaymentAmount || 0;
    const change = Math.max(0, amount - total);
    const due = Math.max(0, total - amount);

    document.getElementById('appAmountDisplay').textContent = `Rs. ${amount.toLocaleString('en-LK', {minimumFractionDigits: 2})}`;

    if (amount >= total) {
        if (change > 0) {
            document.getElementById('appChangeDisplay').textContent = `Change: Rs. ${change.toLocaleString('en-LK', {minimumFractionDigits: 2})}`;
            document.getElementById('appChangeDisplay').className = 'pm-change-display change-green';
        } else {
            document.getElementById('appChangeDisplay').textContent = 'Exact amount';
            document.getElementById('appChangeDisplay').className = 'pm-change-display change-green';
        }
    } else {
        document.getElementById('appChangeDisplay').textContent = `Still due: Rs. ${due.toLocaleString('en-LK', {minimumFractionDigits: 2})}`;
        document.getElementById('appChangeDisplay').className = 'pm-change-display change-red';
    }
}

async function confirmApprovePayment() {
    if (!approveOrderData) return;

    const total = parseFloat(approveOrderData.total_amount);
    if (approvePaymentAmount <= 0 && approvePayMethod === 'cash') {
        showToast('Enter payment amount!', 'warning');
        return;
    }

    const btn = document.getElementById('confirmApproveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
        const payload = {
            payments: [{
                method: approvePayMethod,
                amount: approvePaymentAmount || total,
                reference: document.getElementById('appCardReference')?.value || ''
            }],
            paid_amount: approvePaymentAmount || total
        };

        const res = await fetch(`${Routes.approvePayment}/${approveOrderData.id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('approvePaymentModal'))?.hide();
            showToast('Payment approved! Sale completed.', 'success');

            // Show receipt
            showApprovedReceipt(data.order, approvePaymentAmount || total);

            // Refresh pending count
            loadPendingPaymentsCount();
        } else {
            showToast(data.message || 'Failed to approve payment', 'danger');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Approve & Complete';
    }
}

function showApprovedReceipt(order, paidAmount) {
    const change = Math.max(0, paidAmount - parseFloat(order.total_amount));
    const orderDate = new Date(order.completed_at || order.updated_at);
    const dateStr = orderDate.toLocaleDateString('en-GB', {day: '2-digit', month: '2-digit', year: 'numeric'});
    const timeStr = orderDate.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'});

    const html = `
        <div class="receipt-header">
            <h3 class="store-name">${storeInfo.name}</h3>
            ${storeInfo.address ? `<p class="store-detail">${storeInfo.address}</p>` : ''}
            ${storeInfo.phone ? `<p class="store-detail">Tel: ${storeInfo.phone}</p>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-meta">
            <div><span class="meta-label">Order:</span> ${order.order_number}</div>
            <div><span class="meta-label">Date:</span> ${dateStr}, ${timeStr}</div>
            <div><span class="meta-label">Cashier:</span> <?php echo e(auth()->user()->name); ?></div>
            ${order.customer ? `<div><span class="meta-label">Customer:</span> ${order.customer.name}</div>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-items">
            ${(order.items || []).map(i => `
                <div class="receipt-item">
                    <div class="ri-name">${i.product_name}</div>
                    <div class="ri-line">
                        <span class="ri-qty">${i.quantity} x Rs.${parseFloat(i.unit_price).toFixed(2)}</span>
                        <span class="ri-total">Rs.${parseFloat(i.total).toFixed(2)}</span>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-totals">
            <div class="rt-row"><span>Subtotal</span><span>Rs. ${parseFloat(order.subtotal).toFixed(2)}</span></div>
            ${parseFloat(order.discount_amount) > 0 ? `<div class="rt-row rt-discount"><span>Discount</span><span>- Rs. ${parseFloat(order.discount_amount).toFixed(2)}</span></div>` : ''}
            ${parseFloat(order.tax_amount) > 0 ? `<div class="rt-row"><span>Tax</span><span>Rs. ${parseFloat(order.tax_amount).toFixed(2)}</span></div>` : ''}
            <div class="rt-row rt-total"><span>TOTAL</span><span>Rs. ${parseFloat(order.total_amount).toFixed(2)}</span></div>
            <div class="rt-row"><span>Paid (${approvePayMethod.replace(/_/g,' ')})</span><span>Rs. ${paidAmount.toFixed(2)}</span></div>
            ${change > 0 ? `<div class="rt-row rt-change"><span>Change</span><span>Rs. ${change.toFixed(2)}</span></div>` : ''}
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-footer">
            <p class="footer-thanks">${storeInfo.footer}</p>
            <p class="receipt-powered">Ceyloan POS</p>
        </div>
    `;

    document.getElementById('receiptContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

// Load pending payments count on init
document.addEventListener('DOMContentLoaded', () => {
    if (canProcessPayment) {
        loadPendingPaymentsCount();
    }
});

// ── Clear Cart ────────────────────────────────────────────
document.getElementById('clearCartBtn').addEventListener('click', () => {
    if (cart.length && confirm('Clear the cart?')) {
        updateCustomerDisplay('sale_cancel');
        clearCart();
        // Reset customer display after 2 seconds
        setTimeout(() => updateCustomerDisplay('ready'), 2000);
    }
});

// ── Variant Modal ─────────────────────────────────────────
function showVariantModal(product) {
    document.getElementById('variantModalTitle').textContent = `Select: ${product.name}`;
    document.getElementById('variantModalBody').innerHTML = product.variants.map(v => `
        <div class="variant-option ${v.stock <= 0 ? 'variant-out' : ''}" onclick="addVariantToCart(${JSON.stringify(product).replace(/"/g,'&quot;')}, ${JSON.stringify(v).replace(/"/g,'&quot;')})">
            <div class="variant-name">${v.name}</div>
            <div class="variant-price">Rs. ${parseFloat(v.price).toFixed(2)}</div>
            <div class="variant-stock ${v.stock <= 0 ? 'text-danger' : 'text-muted'}">${v.stock <= 0 ? 'Out of stock' : `${v.stock} in stock`}</div>
        </div>
    `).join('');
    new bootstrap.Modal(document.getElementById('variantModal')).show();
}

// ── Register ──────────────────────────────────────────────
const currentRegisterId = <?php echo e($register?->id ?? 'null'); ?>;
const registerOpenedAt = '<?php echo e($register?->opened_at?->format("M d, Y h:i A") ?? ""); ?>';
const registerOpeningBalance = <?php echo e($register?->opening_balance ?? 0); ?>;

function openRegisterPrompt() {
    new bootstrap.Modal(document.getElementById('registerModal')).show();
}
document.getElementById('openRegisterBtn').addEventListener('click', async () => {
    const balance = parseFloat(document.getElementById('openingBalance').value) || 0;
    await fetch(Routes.registerOpen, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({ opening_balance: balance }) });
    bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
    showToast('Register opened!', 'success');
    setTimeout(() => location.reload(), 1000);
});

// Close Register
async function openCloseRegisterModal() {
    if (!currentRegisterId) {
        showToast('No active register session', 'warning');
        return;
    }

    // Show loading state
    document.getElementById('crSessionStart').textContent = registerOpenedAt;
    document.getElementById('crOpeningBalance').textContent = `Rs. ${registerOpeningBalance.toFixed(2)}`;

    // Calculate duration
    const startTime = new Date('<?php echo e($register?->opened_at ?? ""); ?>');
    const now = new Date();
    const diffMs = now - startTime;
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const mins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    document.getElementById('crDuration').textContent = `${hours}h ${mins}m`;

    // For now, show estimates (actual data would come from server)
    // We'll update these when we have the session data endpoint
    document.getElementById('crExpectedCash').textContent = `Rs. ${registerOpeningBalance.toFixed(2)}`;
    document.getElementById('actualCashCount').value = registerOpeningBalance;

    new bootstrap.Modal(document.getElementById('closeRegisterModal')).show();
}

// Update difference when actual cash changes
document.getElementById('actualCashCount')?.addEventListener('input', function() {
    const expected = parseFloat(document.getElementById('crExpectedCash').textContent.replace(/[^0-9.-]/g, '')) || 0;
    const actual = parseFloat(this.value) || 0;
    const diff = actual - expected;

    const diffEl = document.getElementById('crDifference');
    const diffBox = document.getElementById('crDifferenceBox');

    diffEl.textContent = `${diff >= 0 ? '+' : ''}Rs. ${diff.toFixed(2)}`;

    if (diff < -1) {
        diffBox.className = 'cash-difference mb-3 diff-negative';
    } else if (diff > 1) {
        diffBox.className = 'cash-difference mb-3 diff-positive';
    } else {
        diffBox.className = 'cash-difference mb-3 diff-exact';
    }
});

// Confirm close register
document.getElementById('confirmCloseRegisterBtn')?.addEventListener('click', async () => {
    if (!currentRegisterId) return;

    const actualCash = parseFloat(document.getElementById('actualCashCount').value) || 0;
    const notes = document.getElementById('closeRegisterNotes').value;

    const btn = document.getElementById('confirmCloseRegisterBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Closing...';

    try {
        const url = `${Routes.registerClose}/${currentRegisterId}/close`;
        console.log('Closing register:', url);

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ closing_balance: actualCash, notes: notes })
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Close register error:', res.status, errorText);
            throw new Error(`Server error: ${res.status}`);
        }

        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('closeRegisterModal'))?.hide();
            showToast('Register closed successfully!', 'success');

            // Show summary
            if (data.summary) {
                setTimeout(() => {
                    alert(`Session Summary:\n\nTotal Orders: ${data.summary.total_orders}\nTotal Sales: Rs. ${Number(data.summary.total_sales).toFixed(2)}\nCash: Rs. ${Number(data.summary.cash_payments).toFixed(2)}\nCard: Rs. ${Number(data.summary.card_payments).toFixed(2)}\nDifference: Rs. ${Number(data.summary.difference).toFixed(2)}`);
                    location.reload();
                }, 500);
            } else {
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showToast(data.message || 'Failed to close register', 'danger');
        }
    } catch (e) {
        console.error('Close register exception:', e);
        showToast('Error: ' + e.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i>Close Register';
    }
});

// ── Keyboard Shortcuts ────────────────────────────────────
document.addEventListener('keydown', e => {
    // Check if payment modal is open
    const paymentModal = document.getElementById('paymentModal');
    const isPaymentModalOpen = paymentModal && paymentModal.classList.contains('show');

    // If payment modal is open, allow numpad input
    if (isPaymentModalOpen) {
        const activeEl = document.activeElement;
        const isInputField = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA');

        // Don't intercept if user is in manual input field
        if (!isInputField) {
            if (e.key >= '0' && e.key <= '9') {
                e.preventDefault();
                numpadInput(e.key);
            } else if (e.key === 'Backspace') {
                e.preventDefault();
                numpadInput('DEL');
            } else if (e.key === 'Delete' || e.key === 'c' || e.key === 'C') {
                e.preventDefault();
                numpadInput('C');
            } else if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('completeSaleBtn').click();
            }
        }
        return;
    }

    // Normal shortcuts when payment modal is closed
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('productSearch').focus(); }
    if (e.key === 'F4') { e.preventDefault(); if (cart.length) document.getElementById('holdBtn').click(); }
    if (e.key === 'F9') { e.preventDefault(); if (cart.length) openPayment('cash'); }
    if (e.key === 'Escape') { document.getElementById('productSearch').blur(); }
});

// ── Customer Display (Persistent Second Screen) ──────────────────────
// Uses localStorage for persistent sync - display stays open across page navigations
const CUSTOMER_DISPLAY_KEY = 'ceylon_pos_customer_display';
const CUSTOMER_DISPLAY_ENABLED_KEY = 'ceylon_pos_display_enabled';
let customerDisplayWindow = null;

// Auto-open customer display on POS load if was enabled
document.addEventListener('DOMContentLoaded', () => {
    const wasEnabled = localStorage.getItem(CUSTOMER_DISPLAY_ENABLED_KEY) === 'true';
    if (wasEnabled) {
        setTimeout(() => {
            openCustomerDisplay(true); // true = auto-open (silent)
        }, 1000);
    }
    // Always sync to localStorage so external display can pick it up
    syncCustomerDisplay('ready');
});

function openCustomerDisplay(silent = false) {
    // Open the dedicated customer display page
    const displayUrl = '<?php echo e(route("pos.customer-display")); ?>';

    // Check if already open
    if (customerDisplayWindow && !customerDisplayWindow.closed) {
        // Just focus it
        customerDisplayWindow.focus();
        if (!silent) showToast('Customer display is already open', 'info');
        return;
    }

    // Open new window
    customerDisplayWindow = window.open(
        displayUrl,
        'CeylonPOSCustomerDisplay',
        'width=' + screen.width + ',height=' + screen.height + ',menubar=no,toolbar=no,location=no,status=no'
    );

    // Save enabled state
    localStorage.setItem(CUSTOMER_DISPLAY_ENABLED_KEY, 'true');
    updateDisplayToggleUI(true);

    if (!silent) {
        showToast('Customer display opened - press F11 for fullscreen', 'success');
    }

    // Sync current state
    syncCustomerDisplay(cart.length === 0 ? 'ready' : 'normal');

    // Monitor if closed
    const checkInterval = setInterval(() => {
        if (!customerDisplayWindow || customerDisplayWindow.closed) {
            localStorage.setItem(CUSTOMER_DISPLAY_ENABLED_KEY, 'false');
            updateDisplayToggleUI(false);
            customerDisplayWindow = null;
            clearInterval(checkInterval);
        }
    }, 2000);
}

function closeCustomerDisplay() {
    localStorage.setItem(CUSTOMER_DISPLAY_ENABLED_KEY, 'false');
    if (customerDisplayWindow && !customerDisplayWindow.closed) {
        customerDisplayWindow.close();
        customerDisplayWindow = null;
    }
    updateDisplayToggleUI(false);
    showToast('Customer display closed', 'info');
}

function updateDisplayToggleUI(isOn) {
    const icon = document.getElementById('displayToggleIcon');
    const btn = document.getElementById('btnDisplayOn');
    if (icon && btn) {
        if (isOn) {
            icon.className = 'bi bi-toggle-on text-success';
            btn.classList.add('display-active');
        } else {
            icon.className = 'bi bi-toggle-off';
            btn.classList.remove('display-active');
        }
    }
}

// Sync data to localStorage for customer display
function syncCustomerDisplay(state = 'normal') {
    const totals = calcTotals();
    const data = {
        timestamp: Date.now(),
        state: state,
        cart: cart,
        totals: totals,
        paymentAmount: paymentAmount,
        paymentMethod: currentPayMethod,
        storeName: storeInfo.name
    };
    localStorage.setItem(CUSTOMER_DISPLAY_KEY, JSON.stringify(data));
}

function updateCustomerDisplay(state = 'normal') {
    // Always sync to localStorage - the customer display page will pick it up
    syncCustomerDisplay(state);
}


// Update customer display when payment changes
var _origUpdatePayment = updatePaymentDisplay;
updatePaymentDisplay = function(fallback) {
    _origUpdatePayment(fallback);
    updateCustomerDisplay();
};

// ── Helpers ───────────────────────────────────────────────
function animateCartAdd() {
    const btn = document.getElementById('chargeBtn');
    btn.classList.add('cart-pulse');
    setTimeout(() => btn.classList.remove('cart-pulse'), 300);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':type==='danger'?'x-circle':'info-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/pos/index.blade.php ENDPATH**/ ?>