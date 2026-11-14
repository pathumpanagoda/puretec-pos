@extends('layouts.app')
@section('title', 'Point of Sale')
@section('body-class', 'pos-body')

@section('topbar-center')
<div class="pos-topbar-controls">
    <div class="topbar-cashier-info" id="topbarCashierInfo">
        <i class="bi bi-person-badge"></i>
        <span>{{ auth()->user()->name }}</span>
        @if($register)
            <span class="cashier-session-badge">Active</span>
        @endif
    </div>
    <!-- Sync Status Indicator -->
    <div class="topbar-sync-status" id="syncStatusIndicator">
        <div class="sync-dot" id="syncDot" title="Online"></div>
        <span class="sync-label" id="syncLabel">Online</span>
        <span class="sync-badge d-none" id="syncPendingBadge" title="Pending sync items">0</span>
        <button class="btn-sync-now d-none" id="btnSyncNow" onclick="PureSync.pushAll()" title="Sync now">
            <i class="bi bi-arrow-repeat"></i>
        </button>
    </div>
    <div class="topbar-display-controls">
        <button class="btn-display-control" id="btnSalesHistory" onclick="openSalesHistoryModal()" title="Recent Sales & Refunds">
            <i class="bi bi-receipt"></i>
            <span>History</span>
        </button>
        <button class="btn-display-control" id="btnDailyReport" onclick="openDailyReportModal()" title="Today's Local Report">
            <i class="bi bi-graph-up"></i>
            <span>Report</span>
        </button>
        <button class="btn-display-control" id="btnDisplayOn" onclick="openCustomerDisplay()" title="Toggle Customer Display">
            <i class="bi bi-display"></i>
            <span>Customer Display</span>
            <i class="bi bi-toggle-off" id="displayToggleIcon"></i>
        </button>
        @if($register)
        <button class="btn-display-control btn-close-register" onclick="openCloseRegisterModal()" title="Close Register & End Shift">
            <i class="bi bi-stop-circle"></i>
            <span>End Shift</span>
        </button>
        @endif
    </div>
</div>
@endsection

@section('content')
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
                @foreach($categories as $cat)
                <button class="cat-btn" data-category="{{ $cat->id }}" style="--cat-color: {{ $cat->color }}">
                    <i class="{{ $cat->icon }}"></i><span>{{ $cat->name }}</span>
                    <span class="cat-count">{{ $cat->products_count }}</span>
                </button>
                @endforeach
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
                    @if($canProcessPayment ?? true)
                    <button class="cart-action-btn" id="pendingPaymentsBtn" title="Pending Payments" onclick="showPendingPaymentsModal()">
                        <i class="bi bi-hourglass-split"></i><span id="pendingBadge" class="held-badge d-none">0</span>
                    </button>
                    @endif
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
                <input type="hidden" id="selectedCustomerUuid">
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

            @if(($userPaymentMode ?? 'full') === 'bill_only' && !($seasonMode ?? false))
            <button class="btn-charge btn-bill-only" id="chargeBtn" onclick="createBill()" disabled>
                <i class="bi bi-receipt me-2"></i>
                Create Bill <span id="chargeBtnAmount">Rs. 0.00</span>
            </button>
            @else
            <button class="btn-charge" id="chargeBtn" onclick="openPayment('cash')" disabled>
                <i class="bi bi-lightning-charge-fill me-2"></i>
                Charge <span id="chargeBtnAmount">Rs. 0.00</span>
            </button>
            @endif
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
                                @foreach([7,8,9,4,5,6,1,2,3] as $n)
                                <button type="button" class="numpad-btn" onclick="numpadInput('{{ $n }}')">{{ $n }}</button>
                                @endforeach
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
                        <small class="d-block" style="font-size:9px;opacity:0.8;">{{
                            match($store?->settings['default_print_format'] ?? 'thermal-80') {
                                'thermal-58' => '58mm Thermal',
                                'thermal-80' => '80mm Thermal',
                                'a5' => 'A5 Invoice',
                                'a4' => 'A4 Invoice',
                                'a3' => 'A3 Invoice',
                                default => '80mm Thermal'
                            }
                        }}</small>
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

<!-- ===== SALES HISTORY MODAL ===== -->
<div class="modal fade" id="salesHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Sales History & Offline Refunds</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="p-3 border-bottom d-flex gap-2">
                <input type="text" id="salesHistorySearch" class="form-control form-control-sm" placeholder="Search by order # or customer...">
                <button class="btn btn-sm btn-outline-secondary" onclick="loadSalesHistory()"><i class="bi bi-arrow-repeat"></i></button>
            </div>
            <div class="modal-body p-0" id="salesHistoryBody" style="max-height: 60vh; overflow-y: auto;">
                <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>Loading sales history...</p></div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== DAILY REPORT MODAL ===== -->
<div class="modal fade" id="dailyReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-graph-up me-2"></i>Daily Sales Summary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="dailyReportBody">
                <div class="text-center text-muted py-4"><i class="bi bi-arrow-repeat fs-2 spinner-border"></i><p>Calculating summary...</p></div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary" onclick="printDailyReport()"><i class="bi bi-printer me-1"></i>Print Report</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== ADD CUSTOMER MODAL ===== -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>New Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label small">Name *</label>
                    <input type="text" id="newCustomerName" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Phone</label>
                    <input type="text" id="newCustomerPhone" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Email</label>
                    <input type="email" id="newCustomerEmail" class="form-control form-control-sm">
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary btn-sm" onclick="saveNewCustomer()"><i class="bi bi-check-lg me-1"></i>Save Customer</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== REFUND MODAL ===== -->
<div class="modal fade" id="refundOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Process Return / Refund</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="refundModalBody"></div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-danger" id="confirmRefundBtn" onclick="submitRefund()"><i class="bi bi-check-circle me-1"></i>Confirm Refund</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                                @foreach([7,8,9,4,5,6,1,2,3] as $n)
                                <button type="button" class="numpad-btn" onclick="approveNumpadInput('{{ $n }}')">{{ $n }}</button>
                                @endforeach
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
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pos.css') }}">
<style>
/* Sync Status Indicator */
.topbar-sync-status {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    font-size: 12px;
    margin: 0 8px;
}
.sync-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    transition: all 0.3s;
}
.sync-dot.sync-online { background: #00d26a; box-shadow: 0 0 6px #00d26a; }
.sync-dot.sync-offline {
    background: #ff4757;
    box-shadow: 0 0 6px #ff4757;
    animation: offlinePulse 1.5s infinite;
}
.sync-dot.sync-syncing {
    background: #ffa502;
    box-shadow: 0 0 6px #ffa502;
    animation: syncSpin 1s linear infinite;
}
@keyframes offlinePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
@keyframes syncSpin {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
.sync-label { color: rgba(255,255,255,0.7); font-weight: 500; }
.sync-badge {
    background: #ff4757;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}
.btn-sync-now {
    background: none;
    border: none;
    color: rgba(255,255,255,0.6);
    padding: 2px 4px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.2s;
}
.btn-sync-now:hover { color: #00d26a; transform: rotate(180deg); }
</style>
@php
    $printSettings = $store?->settings ?? [];
@endphp
<style id="dynamicPrintStyles">
/* Dynamic Print Layout Settings */
@media print {
    @page {
        margin: {{ $printSettings['print_margin_top'] ?? 3 }}mm {{ $printSettings['print_margin_right'] ?? 3 }}mm {{ $printSettings['print_margin_bottom'] ?? 3 }}mm {{ $printSettings['print_margin_left'] ?? 3 }}mm;
    }
    #receiptContent {
        padding: {{ $printSettings['print_margin_top'] ?? 3 }}mm {{ $printSettings['print_margin_right'] ?? 3 }}mm {{ $printSettings['print_margin_bottom'] ?? 3 }}mm {{ $printSettings['print_margin_left'] ?? 3 }}mm !important;
        @if(($printSettings['print_receipt_width'] ?? 'auto') !== 'auto')
        max-width: {{ $printSettings['print_receipt_width'] }} !important;
        margin: 0 auto !important;
        @endif
    }
    .receipt-paper {
        line-height: {{ $printSettings['print_line_spacing'] ?? '1.4' }} !important;
    }
    .receipt-header .store-name {
        font-size: {{ $printSettings['print_font_store_name'] ?? '16' }}px !important;
        @if(($printSettings['print_colors'] ?? 'bw') === 'bw')
        color: #000 !important;
        @endif
    }
    .receipt-item, .ri-name, .ri-line, .receipt-meta, .receipt-meta div {
        font-size: {{ $printSettings['print_font_items'] ?? '12' }}px !important;
    }
    .rt-row.rt-total, .receipt-totals .rt-row {
        font-size: {{ $printSettings['print_font_total'] ?? '16' }}px !important;
    }
    .receipt-divider {
        border-top-style: {{ $printSettings['print_divider_style'] ?? 'dashed' }} !important;
        @if(($printSettings['print_divider_style'] ?? 'dashed') === 'none')
        border: none !important;
        margin: 8px 0 !important;
        @endif
    }
    @if(($printSettings['print_colors'] ?? 'bw') === 'bw')
    .receipt-header .store-name,
    .rt-row.rt-total,
    .receipt-footer .receipt-powered {
        color: #000 !important;
    }
    @endif
}
</style>
@endpush

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────
//  Pure POS — POS Screen JavaScript
// ─────────────────────────────────────────────────────────
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const Routes = {
    products       : '{{ route("pos.products.search") }}',
    customers      : '{{ route("pos.customers.search") }}',
    sale           : '{{ route("pos.sale") }}',
    bill           : '{{ route("pos.bill") }}',
    pendingPayments: '{{ route("pos.pending-payments") }}',
    approvePayment : '{{ url("pos/approve-payment") }}',  // + /{id}
    cancelBill     : '{{ url("pos/cancel-bill") }}',  // + /{id}
    hold           : '{{ route("pos.hold") }}',
    held           : '{{ route("pos.held") }}',
    heldRetrieve   : '{{ url("pos/held-orders") }}',  // + /{id}/retrieve
    heldDelete     : '{{ url("pos/held-orders") }}',  // + /{id}
    coupon         : '{{ route("pos.coupon") }}',
    giftCard       : '{{ route("pos.giftcard") }}',
    registerOpen   : '{{ route("pos.register.open") }}',
    registerClose  : '{{ url("pos/register") }}',  // + /{id}/close
    orderReceipt   : '{{ url("orders") }}',  // + /{id}/receipt
    orderInvoice   : '{{ url("orders") }}',  // + /{id}/invoice?size=xx
    salesHistory   : '{{ route("pos.sales-history") }}',
    dailyReport    : '{{ route("pos.daily-report") }}',
};

// Bill-Only Mode Configuration
const userPaymentMode = '{{ $userPaymentMode ?? "full" }}';
const seasonMode = {{ ($seasonMode ?? false) ? 'true' : 'false' }};
const canProcessPayment = {{ ($canProcessPayment ?? true) ? 'true' : 'false' }};
const isBillOnlyMode = userPaymentMode === 'bill_only' && !seasonMode;

// Allow cart price editing (controlled by settings)
const allowCartPriceEdit = {{ ($store?->settings['pos']['allow_price_edit'] ?? false) ? 'true' : 'false' }};
console.log('POS Price Edit Enabled:', allowCartPriceEdit);

// Default print format (thermal-58, thermal-80, a4, a5, a3)
const defaultPrintFormat = '{{ $store?->settings['default_print_format'] ?? 'thermal-80' }}';

// Track current completed order for printing
let lastCompletedOrderId = null;

// Track current held order being edited
let currentHeldOrderId = null;
let currentHeldOrderIsOffline = false;

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
    @if(!$register) openRegisterPrompt(); @endif
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
    // Search for product by barcode in local cache first
    const product = allProducts.find(p =>
        p.barcode === barcode ||
        p.sku === barcode ||
        p.sku?.toLowerCase() === barcode.toLowerCase()
    );

    if (product) {
        addToCart(product);
        showToast(`Added: ${product.name}`, 'success');
    } else {
        const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());
        if (isOffline) {
            try {
                const localProduct = await PureDB.findByBarcode(barcode);
                if (localProduct) {
                    addToCart(localProduct);
                    showToast(`Added (offline): ${localProduct.name}`, 'success');
                } else {
                    showToast('Product not found: ' + barcode, 'warning');
                }
            } catch (dbErr) {
                showToast('Scan error. Try again.', 'danger');
            }
            return;
        }

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
            // ── OFFLINE FALLBACK: Search IndexedDB ──
            try {
                const localProduct = await PureDB.findByBarcode(barcode);
                if (localProduct) {
                    addToCart(localProduct);
                    showToast(`Added (offline): ${localProduct.name}`, 'success');
                } else {
                    showToast('Product not found: ' + barcode, 'warning');
                }
            } catch (dbErr) {
                showToast('Scan error. Try again.', 'danger');
            }
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

    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());
    if (isOffline) {
        if (!append) {
            try {
                const localProducts = await PureDB.searchProducts(search, category || null);
                if (localProducts.length > 0) {
                    allProducts = localProducts;
                    hasMoreProducts = false;
                    renderProducts(localProducts, localProducts.length);
                } else {
                    grid.innerHTML = '<div class="pos-error"><i class="bi bi-box-seam fs-2"></i><p>No products cached in local database</p></div>';
                }
            } catch (dbErr) {
                grid.innerHTML = '<div class="pos-error"><i class="bi bi-wifi-off fs-2"></i><p>Failed to load offline products</p></div>';
            }
        }
        isLoadingProducts = false;
        return;
    }

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
        // ── OFFLINE FALLBACK: Load from IndexedDB ──
        if (!append) {
            try {
                const localProducts = await PureDB.searchProducts(search, category || null);
                if (localProducts.length > 0) {
                    allProducts = localProducts;
                    hasMoreProducts = false;
                    renderProducts(localProducts, localProducts.length);
                    console.log(`[POS] Loaded ${localProducts.length} products from IndexedDB`);
                } else {
                    grid.innerHTML = '<div class="pos-error"><i class="bi bi-wifi-off fs-2"></i><p>Offline — No cached products</p></div>';
                }
            } catch (dbErr) {
                grid.innerHTML = '<div class="pos-error"><i class="bi bi-wifi-off fs-2"></i><p>Failed to load products</p></div>';
            }
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
    currentHeldOrderId = null;
    currentHeldOrderIsOffline = false;
    discount = { type: 'none', value: 0, amount: 0, couponCode: '' };
    document.getElementById('customerSearch').value = '';
    document.getElementById('selectedCustomerId').value = '';
    const uuidEl = document.getElementById('selectedCustomerUuid');
    if (uuidEl) uuidEl.value = '';
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

// Clean up held order after sale completion
async function cleanupCurrentHeldOrder() {
    if (!currentHeldOrderId) return;
    const heldId = currentHeldOrderId;
    const isOfflineHeld = currentHeldOrderIsOffline;

    try {
        if (isOfflineHeld) {
            if (typeof OfflinePOS !== 'undefined' && OfflinePOS.deleteHeldOrder) {
                await OfflinePOS.deleteHeldOrder(heldId);
            }
        } else {
            const res = await fetch(`${Routes.heldDelete}/${heldId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) {
                throw new Error(`Server returned HTTP ${res.status}`);
            }
            const data = await res.json().catch(() => ({}));
            if (data && data.success === false) {
                throw new Error(data.message || 'Server rejected held order deletion');
            }
        }

        currentHeldOrderId = null;
        currentHeldOrderIsOffline = false;

        if (typeof loadHeldOrdersCount === 'function') {
            await loadHeldOrdersCount();
        }
    } catch (cleanupErr) {
        console.warn('[POS] Failed to delete original held bill after sale:', cleanupErr);
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

    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());

    if (isOffline) {
        // Immediate offline processing (no network latency)
        try {
            const offlineOrder = await OfflinePOS.processSale({
                items: cart.map(i => ({
                    product_id: i.product_id,
                    variant_id: i.variant_id || null,
                    name: i.product_name,
                    sku: i.sku || '',
                    barcode: i.barcode || '',
                    quantity: i.quantity,
                    price: i.unit_price,
                    cost_price: i.cost_price || 0,
                    discount: i.discount_amount || 0,
                    tax_rate: parseFloat(i.tax_rate) || 0,
                    tax: (
                        (parseFloat(i.quantity) || 0) *
                        (parseFloat(i.unit_price) || 0)
                    ) * ((parseFloat(i.tax_rate) || 0) / 100),
                    unit: i.unit || 'pcs',
                })),
                customer_id: (currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000) ? currentCustomer.id : null,
                customer_uuid: currentCustomer ? (currentCustomer.uuid || null) : null,
                customer_name: currentCustomer ? currentCustomer.name : 'Walk-in Customer',
                customer_phone: currentCustomer ? (currentCustomer.phone || null) : null,
                customer_email: currentCustomer ? (currentCustomer.email || null) : null,
                payment_method: currentPayMethod,
                payment_amount: paymentAmount || totals.grandTotal,
                card_reference: document.getElementById('cardReference')?.value || null,
                discount_type: discount.type,
                discount_value: discount.value,
                coupon_code: discount.couponCode || null,
                notes: document.getElementById('orderNotes').value,
            });

            bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
            updateCustomerDisplay('sale_complete');
            lastCompletedOrderId = null;

            showReceiptModal({
                id: null,
                order_number: offlineOrder.order_number,
                uuid: offlineOrder.uuid,
                customer: currentCustomer,
                items: offlineOrder.items,
                payments: offlineOrder.payments,
            }, totals, paymentAmount || totals.grandTotal);

            await cleanupCurrentHeldOrder();
            clearCart();
            showToast('✅ Sale recorded offline — will sync when online', 'success');
        } catch (offlineErr) {
            console.error('[POS] Offline sale failed:', offlineErr);
            showToast('Sale failed: ' + offlineErr.message, 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Complete Sale';
        }
        return;
    }

    try {
        const isRealCustomerId = currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000 && currentCustomer.sync_status !== 'local';
        const payload = {
            customer_id    : isRealCustomerId ? currentCustomer.id : null,
            customer_uuid  : currentCustomer ? (currentCustomer.uuid || null) : null,
            customer_name  : currentCustomer ? currentCustomer.name : null,
            customer_phone : currentCustomer ? (currentCustomer.phone || null) : null,
            customer_email : currentCustomer ? (currentCustomer.email || null) : null,
            customer_address: currentCustomer ? (currentCustomer.address || null) : null,
            customer_city  : currentCustomer ? (currentCustomer.city || null) : null,
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

            // If server returned resolved customer with server ID, update PureDB
            if (data.order && data.order.customer && typeof PureDB !== 'undefined' && PureDB.updateCustomerServerId) {
                PureDB.updateCustomerServerId(data.order.customer.uuid, data.order.customer.id).catch(err => {
                    console.warn('[POS] Could not update local customer server ID:', err);
                });
            }

            // Cache order into PureDB for local history, refunds, and reports
            if (typeof PureDB !== 'undefined' && data.order) {
                try {
                    const o = data.order;
                    const orderToCache = {
                        id: o.id,
                        uuid: o.uuid,
                        order_number: o.order_number,
                        order_type: o.order_type || 'sale',
                        status: o.status || 'completed',
                        sync_status: 'synced',
                        customer_id: o.customer_id,
                        customer_name: o.customer?.name || o.customer_name || (currentCustomer ? currentCustomer.name : 'Walk-in Customer'),
                        customer_phone: o.customer?.phone || (currentCustomer ? currentCustomer.phone : null),
                        customer_email: o.customer?.email || (currentCustomer ? currentCustomer.email : null),
                        subtotal: parseFloat(o.subtotal || totals.subtotal || 0),
                        tax_amount: parseFloat(o.tax_amount || totals.taxAmount || 0),
                        discount_amount: parseFloat(o.discount_amount || totals.discountAmount || 0),
                        total_amount: parseFloat(o.total_amount || totals.grandTotal || 0),
                        paid_amount: parseFloat(o.paid_amount || paymentAmount || totals.grandTotal || 0),
                        change_amount: parseFloat(o.change_amount || 0),
                        created_at: o.created_at || new Date().toISOString(),
                        items: (o.items || []).map(it => ({
                            product_id: it.product_id,
                            name: it.product_name || it.name,
                            product_name: it.product_name || it.name,
                            sku: it.sku || '',
                            barcode: it.barcode || '',
                            quantity: parseFloat(it.quantity || 0),
                            price: parseFloat(it.unit_price || it.price || 0),
                            unit_price: parseFloat(it.unit_price || it.price || 0),
                            total: parseFloat(it.total || 0),
                        })),
                        payments: (o.payments || []).map(p => ({
                            method: p.method,
                            amount: parseFloat(p.amount || 0),
                            reference: p.reference_number || p.reference || null,
                        })),
                    };
                    await PureDB.put('orders', orderToCache);
                } catch (cacheErr) {
                    console.warn('[POS] Failed caching order to PureDB:', cacheErr);
                }
            }

            showReceiptModal(data.order, totals, paymentAmount);
            await cleanupCurrentHeldOrder();
            clearCart();
        } else {
            showToast(data.message || 'Sale failed', 'danger');
        }
    } catch (e) {
        // ── OFFLINE FALLBACK ──
        console.warn('[POS] Server unreachable, processing sale offline:', e.message);
        try {
            const offlineOrder = await OfflinePOS.processSale({
                items: cart.map(i => ({
                    product_id: i.product_id,
                    variant_id: i.variant_id || null,
                    name: i.product_name,
                    sku: i.sku || '',
                    barcode: i.barcode || '',
                    quantity: i.quantity,
                    price: i.unit_price,
                    cost_price: i.cost_price || 0,
                    discount: i.discount_amount || 0,
                    tax_rate: parseFloat(i.tax_rate) || 0,
                    tax: (
                        (parseFloat(i.quantity) || 0) *
                        (parseFloat(i.unit_price) || 0)
                    ) * ((parseFloat(i.tax_rate) || 0) / 100),
                    unit: i.unit || 'pcs',
                })),
                customer_id: (currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000) ? currentCustomer.id : null,
                customer_uuid: currentCustomer ? (currentCustomer.uuid || null) : null,
                customer_name: currentCustomer ? currentCustomer.name : 'Walk-in Customer',
                customer_phone: currentCustomer ? (currentCustomer.phone || null) : null,
                customer_email: currentCustomer ? (currentCustomer.email || null) : null,
                payment_method: currentPayMethod,
                payment_amount: paymentAmount || totals.grandTotal,
                card_reference: document.getElementById('cardReference')?.value || null,
                discount_type: discount.type,
                discount_value: discount.value,
                coupon_code: discount.couponCode || null,
                notes: document.getElementById('orderNotes').value,
            });

            bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
            updateCustomerDisplay('sale_complete');
            lastCompletedOrderId = null;

            showReceiptModal({
                id: null,
                order_number: offlineOrder.order_number,
                uuid: offlineOrder.uuid,
                customer: currentCustomer,
                items: offlineOrder.items,
                payments: offlineOrder.payments,
            }, totals, paymentAmount || totals.grandTotal);

            await cleanupCurrentHeldOrder();
            clearCart();
            showToast('✅ Sale saved offline — will sync when online', 'success');
        } catch (offlineErr) {
            console.error('[POS] Offline sale also failed:', offlineErr);
            showToast('Sale failed. Please try again.', 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Complete Sale';
    }
});

// ── Receipt ───────────────────────────────────────────────
const storeInfo = {
    name: @json($store?->name ?? 'My Store'),
    address: @json($store?->address ?? ''),
    phone: @json($store?->phone ?? ''),
    email: @json($store?->email ?? ''),
    header: @json($store?->receipt_header ?? ''),
    footer: @json($store?->receipt_footer ?? 'Thank you for your business!')
};

function showReceiptModal(order, totals, paidAmount) {
    const change = Math.max(0, paidAmount - totals.grandTotal);
    const orderDate = new Date();
    const dateStr = orderDate.toLocaleDateString('en-GB', {day: '2-digit', month: '2-digit', year: 'numeric'});
    const timeStr = orderDate.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    const cashierName = '{{ auth()->user()->name }}';

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
            <p class="receipt-powered">Pure POS &bull; Powered by Puretec</p>
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
        let customers = [];
        try {
            if (navigator.onLine && (typeof PureSync === 'undefined' || PureSync.isOnline())) {
                const res  = await fetch(`${Routes.customers}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                customers = data.customers || [];
            } else {
                customers = await PureDB.searchCustomers(q);
            }
        } catch (e) {
            try {
                customers = await PureDB.searchCustomers(q);
            } catch (dbErr) {
                customers = [];
            }
        }

        let optionsHtml = customers.map(c => `
            <div class="customer-option" onclick="selectCustomer(${JSON.stringify(c).replace(/"/g,'&quot;')})">
                <i class="bi bi-person-circle"></i>
                <div>
                    <div class="co-name">${c.name}</div>
                    <div class="co-meta">${c.phone || ''} ${c.email ? '• ' + c.email : ''}</div>
                </div>
                <div class="co-points">${c.loyalty_points || 0} pts</div>
            </div>
        `).join('');

        // Always provide a quick "+ Add Customer" option
        optionsHtml += `
            <div class="customer-option text-primary border-top" onclick="openAddCustomerModal('${q.replace(/'/g, "\\'")}')">
                <i class="bi bi-person-plus-fill"></i>
                <div>
                    <div class="co-name fw-bold">+ Add "${q}" as new customer</div>
                    <div class="co-meta">Save offline and select for this cart</div>
                </div>
            </div>
        `;

        customerDropdown.innerHTML = optionsHtml;
        customerDropdown.classList.remove('d-none');
    }, 280);
});

function openAddCustomerModal(initialName = '') {
    customerDropdown.classList.add('d-none');
    document.getElementById('newCustomerName').value = initialName;
    document.getElementById('newCustomerPhone').value = '';
    document.getElementById('newCustomerEmail').value = '';
    new bootstrap.Modal(document.getElementById('addCustomerModal')).show();
}

async function saveNewCustomer() {
    const name = document.getElementById('newCustomerName').value.trim();
    const phone = document.getElementById('newCustomerPhone').value.trim();
    const email = document.getElementById('newCustomerEmail').value.trim();

    if (!name) {
        showToast('Customer name is required', 'warning');
        return;
    }

    try {
        const customer = await OfflinePOS.createCustomer({ name, phone, email });
        selectCustomer(customer);
        bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'))?.hide();
        showToast(`Customer "${name}" created & selected!`, 'success');
    } catch (e) {
        showToast('Failed to create customer: ' + e.message, 'danger');
    }
}

function selectCustomer(customer) {
    currentCustomer = customer;
    customerSearch.value = customer.name;
    const isTempId = !customer.id || customer.sync_status === 'local' || Number(customer.id) > 10000000000;
    document.getElementById('selectedCustomerId').value = isTempId ? '' : customer.id;
    const uuidEl = document.getElementById('selectedCustomerUuid');
    if (uuidEl) uuidEl.value = customer.uuid || '';
    document.getElementById('clearCustomer').classList.remove('d-none');
    customerDropdown.classList.add('d-none');
}

document.getElementById('clearCustomer').addEventListener('click', () => {
    currentCustomer = null;
    customerSearch.value = '';
    document.getElementById('selectedCustomerId').value = '';
    const uuidEl = document.getElementById('selectedCustomerUuid');
    if (uuidEl) uuidEl.value = '';
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

    const isRealHoldCustomerId = currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000 && currentCustomer.sync_status !== 'local';
    const payload = {
        customer_id     : isRealHoldCustomerId ? currentCustomer.id : null,
        customer_uuid   : currentCustomer ? (currentCustomer.uuid || null) : null,
        customer_name   : currentCustomer ? currentCustomer.name : null,
        customer_phone  : currentCustomer ? (currentCustomer.phone || null) : null,
        customer_email  : currentCustomer ? (currentCustomer.email || null) : null,
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
        coupon_code     : discount.couponCode || null,
        total_amount    : totals.grandTotal,
        notes           : null,
    };

    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());

    if (isOffline) {
        try {
            await OfflinePOS.holdOrder(payload);
            showToast('Order held locally (offline)!', 'success');
            updateCustomerDisplay('sale_hold');
            currentHeldOrderId = null;
            currentHeldOrderIsOffline = false;
            clearCart();
            loadHeldOrdersCount();
            setTimeout(() => updateCustomerDisplay('ready'), 3000);
        } catch (e) {
            showToast('Failed to hold order offline: ' + e.message, 'danger');
        }
        return;
    }

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
            currentHeldOrderIsOffline = false;
            clearCart();
            loadHeldOrdersCount();
            // Reset customer display after 3 seconds
            setTimeout(() => updateCustomerDisplay('ready'), 3000);
        } else {
            showToast(data.message || 'Failed to hold order', 'danger');
        }
    } catch (e) {
        // Fallback to offline hold
        try {
            await OfflinePOS.holdOrder(payload);
            showToast('Server unreachable — order held locally!', 'warning');
            updateCustomerDisplay('sale_hold');
            currentHeldOrderId = null;
            currentHeldOrderIsOffline = false;
            clearCart();
            loadHeldOrdersCount();
            setTimeout(() => updateCustomerDisplay('ready'), 3000);
        } catch (offlineErr) {
            showToast('Failed to hold order: ' + offlineErr.message, 'danger');
        }
    }
});

document.getElementById('heldOrdersBtn').addEventListener('click', async () => {
    await showHeldOrdersModal();
});

async function showHeldOrdersModal() {
    const modal = new bootstrap.Modal(document.getElementById('heldOrdersModal'));
    const body  = document.getElementById('heldOrdersBody');
    body.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-arrow-repeat fs-2 spinner-border"></i><p>Loading held orders...</p></div>';
    modal.show();

    let orders = [];
    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());

    if (!isOffline) {
        try {
            const res   = await fetch(Routes.held);
            const data  = await res.json();
            orders = data.orders || [];
        } catch (e) {
            console.warn('[POS] Failed to fetch server held orders, using local');
        }
    }

    // Always merge offline held orders
    try {
        const localHeld = await OfflinePOS.getHeldOrders();
        const localFormatted = (localHeld || []).map(lh => ({
            id: lh.uuid || lh.id,
            uuid: lh.uuid,
            is_offline: true,
            order_number: lh.order_number || `HOLD-LOCAL-${lh.uuid ? lh.uuid.slice(0, 8) : (lh.id || '')}`,
            created_at: lh.held_at || lh.created_at,
            customer: lh.customer_name ? { name: lh.customer_name } : null,
            items: lh.items || [],
            total_amount: lh.total_amount || 0,
        }));
        orders = [...orders, ...localFormatted];
    } catch (dbErr) {
        console.warn('[POS] Failed to fetch offline held orders:', dbErr);
    }

    if (!orders.length) {
        body.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No held orders</p></div>';
    } else {
        body.innerHTML = orders.map(o => `
            <div class="held-order-item" data-order-id="${o.id}">
                <div class="held-order-info">
                    <div class="held-order-header">
                        <strong>${o.order_number}</strong>
                        ${o.is_offline ? '<span class="badge bg-secondary ms-1">Offline</span>' : ''}
                        <span class="held-order-time text-muted">${formatTimeAgo(o.created_at)}</span>
                    </div>
                    <div class="text-muted small">${o.customer?.name || 'Walk-in'} • ${o.items?.length || 0} items</div>
                    <div class="held-order-items-preview">${o.items?.slice(0, 3).map(i => i.product_name || i.name).join(', ') || ''}${o.items?.length > 3 ? '...' : ''}</div>
                </div>
                <div class="held-order-total fw-bold">Rs. ${parseFloat(o.total_amount).toFixed(2)}</div>
                <div class="held-order-actions">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="retrieveHeldOrder('${o.id}', 'replace', ${!!o.is_offline})" title="Replace current cart">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Replace
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="retrieveHeldOrder('${o.id}', 'add', ${!!o.is_offline})" title="Add to current cart">
                            <i class="bi bi-plus-circle me-1"></i>Add
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteHeldOrder('${o.id}', ${!!o.is_offline})" title="Delete order">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Retrieve held order into cart
async function retrieveHeldOrder(orderId, mode = 'replace', isOffline = false) {
    // Check if cart has items when replacing
    if (mode === 'replace' && cart.length > 0) {
        if (!confirm('Current cart has items. This will replace them. Continue?')) {
            return;
        }
    }

    if (isOffline) {
        try {
            const heldOrder = await OfflinePOS.retrieveHeldOrder(orderId);
            if (heldOrder) {
                async function resolveProductStock(productId, variantId) {
                    let prod = (allProducts || []).find(p => p.id == productId);
                    if (!prod && typeof PureDB !== 'undefined' && PureDB.get) {
                        try {
                            prod = await PureDB.get('products', productId);
                            if (!prod && typeof productId === 'string' && !isNaN(Number(productId))) {
                                prod = await PureDB.get('products', Number(productId));
                            } else if (!prod && typeof productId === 'number') {
                                prod = await PureDB.get('products', String(productId));
                            }
                        } catch (e) {
                            console.warn('[POS] Failed to fetch product from PureDB:', e);
                        }
                    }

                    if (!prod) {
                        console.warn(`[POS] Stock metadata could not be recovered for product ID ${productId}`);
                        return { stock: 0, track_stock: false };
                    }

                    const variant = variantId ? (prod.variants || []).find(v => v.id == variantId) : null;
                    const stock = parseFloat(variant && variant.stock !== undefined ? variant.stock : (prod.stock_quantity ?? prod.stock ?? 0));
                    const track_stock = Boolean(prod.track_stock);

                    return { stock, track_stock };
                }

                if (mode === 'replace') {
                    cart = [];
                    currentCustomer = null;
                    discount = { type: 'none', value: 0, amount: 0, couponCode: '' };

                    for (const i of (heldOrder.items || [])) {
                        const stockMeta = await resolveProductStock(i.product_id, i.variant_id);
                        cart.push({
                            product_id: i.product_id,
                            variant_id: i.variant_id || null,
                            product_name: i.product_name || i.name,
                            sku: i.sku || '',
                            barcode: i.barcode || '',
                            unit_price: parseFloat(i.unit_price || i.price || 0),
                            quantity: parseFloat(i.quantity || 1),
                            total: parseFloat(i.unit_price || i.price || 0) * parseFloat(i.quantity || 1),
                            cost_price: parseFloat(i.cost_price || 0),
                            tax_rate: parseFloat(i.tax_rate || 0),
                            discount_amount: parseFloat(i.discount_amount || i.discount || 0),
                            unit: i.unit || 'pcs',
                            stock: stockMeta.stock,
                            track_stock: stockMeta.track_stock
                        });
                    }

                    // Restore customer
                    let restoredCustomer = null;
                    const custUuid = heldOrder.customer_uuid || (heldOrder.customer && heldOrder.customer.uuid);
                    const custId = heldOrder.customer_id || (heldOrder.customer && heldOrder.customer.id);

                    if (typeof PureDB !== 'undefined') {
                        if (custUuid && PureDB.getCustomerByUUID) {
                            try {
                                restoredCustomer = await PureDB.getCustomerByUUID(custUuid);
                            } catch (e) {
                                console.warn('[POS] Failed to fetch customer by UUID from PureDB:', e);
                            }
                        }
                        if (!restoredCustomer && custId && PureDB.get) {
                            try {
                                restoredCustomer = await PureDB.get('customers', custId);
                                if (!restoredCustomer && typeof custId === 'string' && !isNaN(Number(custId))) {
                                    restoredCustomer = await PureDB.get('customers', Number(custId));
                                } else if (!restoredCustomer && typeof custId === 'number') {
                                    restoredCustomer = await PureDB.get('customers', String(custId));
                                }
                            } catch (e) {
                                console.warn('[POS] Failed to fetch customer by ID from PureDB:', e);
                            }
                        }
                    }

                    if (!restoredCustomer) {
                        const custName = heldOrder.customer_name || (heldOrder.customer && heldOrder.customer.name);
                        if (custName) {
                            restoredCustomer = {
                                id: custId || null,
                                uuid: custUuid || null,
                                name: custName,
                                phone: heldOrder.customer_phone || (heldOrder.customer && heldOrder.customer.phone) || '',
                                email: heldOrder.customer_email || (heldOrder.customer && heldOrder.customer.email) || '',
                                sync_status: (custId && Number(custId) < 10000000000) ? 'synced' : 'local'
                            };
                        }
                    }

                    if (restoredCustomer) {
                        selectCustomer(restoredCustomer);
                    } else {
                        currentCustomer = null;
                        if (customerSearch) customerSearch.value = '';
                        const idEl = document.getElementById('selectedCustomerId');
                        if (idEl) idEl.value = '';
                        const uuidEl = document.getElementById('selectedCustomerUuid');
                        if (uuidEl) uuidEl.value = '';
                        const clearBtn = document.getElementById('clearCustomer');
                        if (clearBtn) clearBtn.classList.add('d-none');
                    }

                    // Restore bill-level discount
                    const savedType = heldOrder.discount_type;
                    if (savedType === 'percentage') {
                        discount = {
                            type: 'percentage',
                            value: parseFloat(heldOrder.discount_value || 0),
                            amount: 0,
                            couponCode: ''
                        };
                    } else if (savedType === 'fixed') {
                        discount = {
                            type: 'fixed',
                            value: parseFloat(heldOrder.discount_value || 0),
                            amount: 0,
                            couponCode: ''
                        };
                    } else if (savedType === 'coupon') {
                        discount = {
                            type: 'coupon',
                            value: 0,
                            amount: parseFloat(heldOrder.discount_amount || 0),
                            couponCode: heldOrder.coupon_code || heldOrder.couponCode || ''
                        };
                    } else {
                        discount = { type: 'none', value: 0, amount: 0, couponCode: '' };
                    }

                    // Synchronize discount UI
                    document.querySelectorAll('.disc-tab').forEach(t => t.classList.remove('active'));
                    const activeTab = document.querySelector(`.disc-tab[data-type="${discount.type}"]`) || document.querySelector('.disc-tab[data-type="none"]');
                    if (activeTab) activeTab.classList.add('active');

                    const discInputEl = document.getElementById('discountInput');
                    if (discInputEl) discInputEl.classList.toggle('d-none', !['percentage', 'fixed'].includes(discount.type));

                    const couponInputEl = document.getElementById('couponInput');
                    if (couponInputEl) couponInputEl.classList.toggle('d-none', discount.type !== 'coupon');

                    const discValEl = document.getElementById('discountValue');
                    if (discValEl) {
                        discValEl.value = ['percentage', 'fixed'].includes(discount.type) ? discount.value : '';
                    }

                    const couponCodeEl = document.getElementById('couponCode');
                    if (couponCodeEl) {
                        couponCodeEl.value = discount.type === 'coupon' ? (discount.couponCode || '') : '';
                    }
                } else {
                    for (const heldItem of (heldOrder.items || [])) {
                        const existing = cart.find(c => c.product_id === heldItem.product_id && (c.variant_id || null) === (heldItem.variant_id || null));
                        if (existing) {
                            existing.quantity += parseFloat(heldItem.quantity || 1);
                            existing.discount_amount = (parseFloat(existing.discount_amount) || 0) + (parseFloat(heldItem.discount_amount || heldItem.discount || 0) || 0);
                            existing.total = existing.quantity * existing.unit_price;
                        } else {
                            const stockMeta = await resolveProductStock(heldItem.product_id, heldItem.variant_id);
                            cart.push({
                                product_id: heldItem.product_id,
                                variant_id: heldItem.variant_id || null,
                                product_name: heldItem.product_name || heldItem.name,
                                sku: heldItem.sku || '',
                                barcode: heldItem.barcode || '',
                                unit_price: parseFloat(heldItem.unit_price || heldItem.price || 0),
                                quantity: parseFloat(heldItem.quantity || 1),
                                total: parseFloat(heldItem.unit_price || heldItem.price || 0) * parseFloat(heldItem.quantity || 1),
                                cost_price: parseFloat(heldItem.cost_price || 0),
                                tax_rate: parseFloat(heldItem.tax_rate || 0),
                                discount_amount: parseFloat(heldItem.discount_amount || heldItem.discount || 0),
                                unit: heldItem.unit || 'pcs',
                                stock: stockMeta.stock,
                                track_stock: stockMeta.track_stock
                            });
                        }
                    }
                }

                currentHeldOrderId = orderId;
                currentHeldOrderIsOffline = true;
                updateCartUI();
                loadHeldOrdersCount();
                bootstrap.Modal.getInstance(document.getElementById('heldOrdersModal'))?.hide();
                showToast(mode === 'add' ? 'Items added from offline hold' : 'Offline held order retrieved!', 'success');
                return;
            }
        } catch (e) {
            showToast('Failed to retrieve offline held order: ' + e.message, 'danger');
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

                // Set customer if exists
                if (data.customer) {
                    selectCustomer(data.customer);
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

            currentHeldOrderId = orderId;
            currentHeldOrderIsOffline = false;

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
async function deleteHeldOrder(orderId, isOffline = false) {
    if (!confirm('Delete this held order? This cannot be undone.')) {
        return;
    }

    if (isOffline) {
        try {
            await OfflinePOS.deleteHeldOrder(orderId);
            showToast('Offline held order deleted', 'success');
            loadHeldOrdersCount();
            showHeldOrdersModal();
        } catch (e) {
            showToast('Failed to delete offline order: ' + e.message, 'danger');
        }
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
        // Fallback: try deleting local
        try {
            await OfflinePOS.deleteHeldOrder(orderId);
            showToast('Held order deleted locally', 'success');
            loadHeldOrdersCount();
            showHeldOrdersModal();
        } catch (dbErr) {
            showToast('Network error. Please try again.', 'danger');
        }
    }
}

// Load held orders count for badge
async function loadHeldOrdersCount() {
    let count = 0;
    try {
        if (navigator.onLine && (typeof PureSync === 'undefined' || PureSync.isOnline())) {
            const res = await fetch(Routes.held);
            const data = await res.json();
            count += (data.orders || []).length;
        }
    } catch (e) {}

    try {
        const local = await OfflinePOS.getHeldOrders();
        count += (local || []).length;
    } catch (e) {}

    const badge = document.getElementById('heldBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
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
        const isRealBillCustomerId = currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000 && currentCustomer.sync_status !== 'local';
        const payload = {
            customer_id    : isRealBillCustomerId ? currentCustomer.id : null,
            customer_uuid  : currentCustomer ? (currentCustomer.uuid || null) : null,
            customer_name  : currentCustomer ? currentCustomer.name : null,
            customer_phone : currentCustomer ? (currentCustomer.phone || null) : null,
            customer_email : currentCustomer ? (currentCustomer.email || null) : null,
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
            if (data.order && data.order.customer && typeof PureDB !== 'undefined' && PureDB.updateCustomerServerId) {
                PureDB.updateCustomerServerId(data.order.customer.uuid, data.order.customer.id).catch(err => {
                    console.warn('[POS] Could not update local customer server ID:', err);
                });
            }
            showBillReceipt(data.order, totals);
            await cleanupCurrentHeldOrder();
            clearCart();
        } else {
            showToast(data.message || 'Failed to create bill', 'danger');
        }
    } catch (e) {
        // ── OFFLINE FALLBACK ──
        console.warn('[POS] Server unreachable, creating bill offline:', e.message);
        try {
            const offlineBill = await OfflinePOS.createBill({
                items: cart.map(i => ({
                    product_id: i.product_id,
                    variant_id: i.variant_id || null,
                    name: i.product_name,
                    quantity: i.quantity,
                    price: i.unit_price,
                    discount: i.discount_amount || 0,
                    tax: i.tax_amount || 0,
                })),
                customer_id: (currentCustomer && currentCustomer.id && Number(currentCustomer.id) < 10000000000) ? currentCustomer.id : null,
                customer_uuid: currentCustomer ? (currentCustomer.uuid || null) : null,
                customer_name: currentCustomer ? currentCustomer.name : 'Walk-in Customer',
                customer_phone: currentCustomer ? (currentCustomer.phone || null) : null,
                customer_email: currentCustomer ? (currentCustomer.email || null) : null,
                discount_type: discount.type,
                discount_value: discount.value,
                coupon_code: discount.couponCode || null,
                notes: document.getElementById('orderNotes')?.value || '',
            });
            showToast('📋 Bill saved offline — will sync when online', 'success');
            await cleanupCurrentHeldOrder();
            clearCart();
        } catch (offlineErr) {
            showToast('Failed to create bill. Please try again.', 'danger');
        }
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
    const cashierName = '{{ auth()->user()->name }}';

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
            <p class="receipt-powered">Pure POS</p>
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
                        <button class="btn btn-outline-danger me-2" onclick="cancelPendingPayment(${o.id})">
                            <i class="bi bi-x-circle me-1"></i>Cancel Bill
                        </button>
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
// Cancel pending payment bill
async function cancelPendingPayment(orderId) {
    if (!confirm('Are you sure you want to cancel this pending bill? Restoring items to inventory cannot be undone.')) {
        return;
    }
    try {
        const res = await fetch(`${Routes.cancelBill}/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ reason: 'Cancelled from POS pending payments queue' })
        });
        const data = await res.json();
        if (data.success) {
            toast('Success', data.message || 'Bill cancelled successfully', 'success');
            showPendingPaymentsModal();
            loadPendingPaymentsCount();
        } else {
            toast('Error', data.message || 'Failed to cancel bill', 'danger');
        }
    } catch (e) {
        toast('Error', 'Network error while cancelling bill', 'danger');
    }
}

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
            <div><span class="meta-label">Cashier:</span> {{ auth()->user()->name }}</div>
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
            <p class="receipt-powered">Pure POS</p>
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
const currentRegisterId = {{ $register?->id ?? 'null' }};
const registerOpenedAt = '{{ $register?->opened_at?->format("M d, Y h:i A") ?? "" }}';
const registerOpeningBalance = {{ $register?->opening_balance ?? 0 }};

function openRegisterPrompt() {
    new bootstrap.Modal(document.getElementById('registerModal')).show();
}
document.getElementById('openRegisterBtn').addEventListener('click', async () => {
    const balance = parseFloat(document.getElementById('openingBalance').value) || 0;
    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());

    if (isOffline) {
        try {
            await OfflinePOS.openRegister({ opening_balance: balance, notes: 'Opened offline' });
            bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
            showToast('✅ Register opened offline — will sync when online', 'success');
        } catch (err) {
            showToast('Failed to open register offline: ' + err.message, 'danger');
        }
        return;
    }

    try {
        await fetch(Routes.registerOpen, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ opening_balance: balance })
        });
        bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
        showToast('Register opened!', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (err) {
        try {
            await OfflinePOS.openRegister({ opening_balance: balance, notes: 'Opened offline (fallback)' });
            bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
            showToast('✅ Register opened offline (server unreachable)', 'warning');
        } catch (offlineErr) {
            showToast('Failed to open register: ' + offlineErr.message, 'danger');
        }
    }
});

// Close Register
async function openCloseRegisterModal() {
    let activeReg = null;
    try {
        activeReg = await OfflinePOS.getCurrentRegister();
    } catch (e) {}

    if (!currentRegisterId && !activeReg) {
        showToast('No active register session', 'warning');
        return;
    }

    const openedAt = activeReg ? new Date(activeReg.opened_at).toLocaleString('en-GB') : registerOpenedAt;
    const openingBal = activeReg ? (activeReg.opening_balance || 0) : registerOpeningBalance;

    document.getElementById('crSessionStart').textContent = openedAt || 'Today';
    document.getElementById('crOpeningBalance').textContent = `Rs. ${openingBal.toFixed(2)}`;

    // Calculate duration
    const startTime = activeReg ? new Date(activeReg.opened_at) : new Date('{{ $register?->opened_at ?? "" }}');
    const now = new Date();
    const diffMs = Math.max(0, now - (isNaN(startTime.getTime()) ? now : startTime));
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const mins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    document.getElementById('crDuration').textContent = `${hours}h ${mins}m`;

    // Local report calculation for expected cash
    try {
        const report = await OfflinePOS.getDailyReport();
        const expected = openingBal + (report.payments?.cash || 0) - (report.totals?.refunds || 0);
        document.getElementById('crExpectedCash').textContent = `Rs. ${expected.toFixed(2)}`;
        document.getElementById('actualCashCount').value = expected.toFixed(2);
    } catch (e) {
        document.getElementById('crExpectedCash').textContent = `Rs. ${openingBal.toFixed(2)}`;
        document.getElementById('actualCashCount').value = openingBal.toFixed(2);
    }

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
    const actualCash = parseFloat(document.getElementById('actualCashCount').value) || 0;
    const notes = document.getElementById('closeRegisterNotes').value;

    const btn = document.getElementById('confirmCloseRegisterBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Closing...';

    const isOffline = !navigator.onLine || (typeof PureSync !== 'undefined' && !PureSync.isOnline());

    if (isOffline || !currentRegisterId) {
        try {
            const closedReg = await OfflinePOS.closeRegister({ closing_balance: actualCash, notes: notes });
            bootstrap.Modal.getInstance(document.getElementById('closeRegisterModal'))?.hide();
            showToast('✅ Register closed offline — will sync when online', 'success');
            if (closedReg) {
                alert(`Offline Session Summary:\n\nExpected Cash: Rs. ${Number(closedReg.expected_cash || 0).toFixed(2)}\nActual Cash: Rs. ${Number(actualCash).toFixed(2)}\nDifference: Rs. ${Number(closedReg.difference || 0).toFixed(2)}`);
            }
        } catch (err) {
            showToast('Failed to close register offline: ' + err.message, 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i>Close Register';
        }
        return;
    }

    try {
        const url = `${Routes.registerClose}/${currentRegisterId}/close`;
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ closing_balance: actualCash, notes: notes })
        });

        if (!res.ok) throw new Error(`Server error: ${res.status}`);
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('closeRegisterModal'))?.hide();
            showToast('Register closed successfully!', 'success');

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
        try {
            const closedReg = await OfflinePOS.closeRegister({ closing_balance: actualCash, notes: notes });
            bootstrap.Modal.getInstance(document.getElementById('closeRegisterModal'))?.hide();
            showToast('✅ Register closed offline (server unreachable)', 'warning');
        } catch (offlineErr) {
            showToast('Error: ' + e.message, 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i>Close Register';
    }
});

// ═══════════════════════════════════════════════════════════
// SALES HISTORY, REFUNDS & DAILY REPORTS
// ═══════════════════════════════════════════════════════════
let currentSalesHistory = [];
let currentRefundOrder = null;

async function openSalesHistoryModal() {
    new bootstrap.Modal(document.getElementById('salesHistoryModal')).show();
    await loadSalesHistory();
}

async function loadSalesHistory() {
    const query = document.getElementById('salesHistorySearch')?.value?.trim()?.toLowerCase() || '';
    const body = document.getElementById('salesHistoryBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div> <p class="mt-2 text-muted">Loading orders...</p></div>';

    try {
        let serverOrders = [];
        const isOnline = navigator.onLine && (typeof PureSync === 'undefined' || PureSync.isOnline());

        if (isOnline && typeof Routes !== 'undefined' && Routes.salesHistory) {
            try {
                const url = `${Routes.salesHistory}?limit=50${query ? '&q=' + encodeURIComponent(query) : ''}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.orders)) {
                        serverOrders = data.orders;
                        // Cache server orders in PureDB
                        if (typeof PureDB !== 'undefined' && PureDB.put) {
                            for (const ord of serverOrders) {
                                try {
                                    await PureDB.put('orders', ord);
                                } catch (_) {}
                            }
                        }
                    }
                }
            } catch (netErr) {
                console.warn('[POS] Failed to fetch sales history from server:', netErr);
            }
        }

        // Get local/offline orders from PureDB
        let localOrders = [];
        try {
            localOrders = await OfflinePOS.getSalesHistory(50);
        } catch (_) {}

        // Merge orders: keep any local pending sync orders, then server orders, deduplicate by UUID
        const mergedMap = new Map();

        // Local pending sync orders have priority so they show with pending badge
        localOrders.forEach(o => {
            if (o.sync_status === 'local' || o.sync_status === 'pending') {
                mergedMap.set(o.uuid, o);
            }
        });

        // Add server orders
        serverOrders.forEach(o => {
            if (!mergedMap.has(o.uuid)) {
                mergedMap.set(o.uuid, o);
            }
        });

        // If server returned nothing or offline, include all local orders
        if (serverOrders.length === 0) {
            localOrders.forEach(o => {
                if (!mergedMap.has(o.uuid)) {
                    mergedMap.set(o.uuid, o);
                }
            });
        }

        currentSalesHistory = Array.from(mergedMap.values());
        currentSalesHistory.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        let filtered = currentSalesHistory;
        if (query) {
            filtered = currentSalesHistory.filter(o =>
                (o.order_number && o.order_number.toLowerCase().includes(query)) ||
                (o.customer_name && o.customer_name.toLowerCase().includes(query)) ||
                (o.uuid && o.uuid.toLowerCase().includes(query))
            );
        }

        if (!filtered.length) {
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><p>No sales found</p></div>';
            return;
        }

        body.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th>Order #</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(o => {
                            const isReturn = o.order_type === 'return';
                            const isRefunded = o.status === 'refunded';
                            const isOfflineOnly = o.sync_status === 'pending' || o.sync_status === 'local';
                            const itemsCount = (o.items || []).reduce((sum, it) => sum + (parseFloat(it.quantity) || 1), 0);
                            const total = parseFloat(o.total_amount || o.total || 0);

                            return `
                                <tr>
                                    <td>
                                        <div class="fw-bold">${o.order_number || o.uuid?.slice(0,8)}</div>
                                        ${isReturn ? '<span class="badge bg-danger">Return</span>' : ''}
                                        ${isOfflineOnly ? '<span class="badge bg-warning text-dark"><i class="bi bi-cloud-arrow-up"></i> Pending Sync</span>' : '<span class="badge bg-success-subtle text-success">Saved</span>'}
                                    </td>
                                    <td class="small text-muted">${formatTimeAgo(o.created_at)}</td>
                                    <td>${o.customer_name || 'Walk-in'}</td>
                                    <td>${itemsCount}</td>
                                    <td class="fw-bold ${isReturn ? 'text-danger' : 'text-primary'}">Rs. ${total.toFixed(2)}</td>
                                    <td>
                                        <span class="badge ${isRefunded ? 'bg-danger' : 'bg-success'}">${o.status || 'completed'}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary" onclick="reprintOfflineReceipt('${o.uuid}')" title="Reprint Receipt">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            ${(!isReturn && !isRefunded) ? `
                                                <button class="btn btn-outline-danger" onclick="openRefundModal('${o.uuid}')" title="Return / Refund">
                                                    <i class="bi bi-arrow-return-left"></i>
                                                </button>
                                            ` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (e) {
        body.innerHTML = `<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-2"></i><p>Failed to load sales history: ${e.message}</p></div>`;
    }
}

document.getElementById('salesHistorySearch')?.addEventListener('input', () => {
    loadSalesHistory();
});

async function reprintOfflineReceipt(orderUuid) {
    try {
        const order = currentSalesHistory.find(o => o.uuid === orderUuid) || await PureDB.getOrder(orderUuid);
        if (!order) {
            showToast('Order not found', 'danger');
            return;
        }

        const totals = {
            subtotal: parseFloat(order.subtotal || order.total_amount || 0),
            discountAmount: parseFloat(order.discount_amount || 0),
            taxAmount: parseFloat(order.tax_amount || 0),
            grandTotal: parseFloat(order.total_amount || 0),
        };

        const paid = (order.payments && order.payments.length)
            ? order.payments.reduce((acc, p) => acc + parseFloat(p.amount || 0), 0)
            : totals.grandTotal;

        showReceiptModal(order, totals, paid);
    } catch (e) {
        showToast('Failed to generate receipt: ' + e.message, 'danger');
    }
}

async function openRefundModal(orderUuid) {
    currentRefundOrder = currentSalesHistory.find(o => o.uuid === orderUuid) || await PureDB.getOrder(orderUuid);
    if (!currentRefundOrder) {
        showToast('Order not found', 'danger');
        return;
    }

    const body = document.getElementById('refundModalBody');
    const items = currentRefundOrder.items || [];

    body.innerHTML = `
        <div class="mb-3">
            <strong>Order:</strong> ${currentRefundOrder.order_number} &bull; <span class="text-muted">${currentRefundOrder.customer_name || 'Walk-in'}</span>
        </div>
        <div class="table-responsive mb-3">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Orig Qty</th>
                        <th class="text-center" style="width: 100px;">Refund Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map((item, idx) => `
                        <tr>
                            <td>
                                <div class="fw-bold">${item.name || item.product_name}</div>
                            </td>
                            <td class="text-center">${item.quantity}</td>
                            <td class="text-center">
                                <input type="number" class="form-control form-control-sm text-center refund-qty-input"
                                       data-idx="${idx}" data-price="${item.price || item.unit_price}"
                                       min="0" max="${item.quantity}" step="1" value="${item.quantity}"
                                       oninput="updateRefundTotals()">
                            </td>
                            <td class="text-end">Rs. ${parseFloat(item.price || item.unit_price).toFixed(2)}</td>
                            <td class="text-end fw-bold item-refund-subtotal" id="refundSubtotal${idx}">
                                Rs. ${(parseFloat(item.price || item.unit_price) * parseFloat(item.quantity)).toFixed(2)}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-3">
            <span class="fw-bold">Total Refund Amount:</span>
            <span class="fs-5 fw-bold text-danger" id="totalRefundDisplay">Rs. 0.00</span>
        </div>
        <div class="mb-2">
            <label class="form-label small fw-bold">Reason for Return</label>
            <select id="refundReason" class="form-select form-select-sm">
                <option value="Customer Return">Customer Return</option>
                <option value="Defective / Damaged">Defective / Damaged</option>
                <option value="Wrong Item">Wrong Item</option>
                <option value="Overcharged">Overcharged / Pricing Error</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="refundRestockCheck" checked>
            <label class="form-check-label small" for="refundRestockCheck">
                Restock returned items to local inventory
            </label>
        </div>
    `;

    updateRefundTotals();
    new bootstrap.Modal(document.getElementById('refundOrderModal')).show();
}

function updateRefundTotals() {
    if (!currentRefundOrder) return;
    let totalRefund = 0;
    const inputs = document.querySelectorAll('.refund-qty-input');
    inputs.forEach(input => {
        const idx = input.dataset.idx;
        const price = parseFloat(input.dataset.price) || 0;
        const qty = parseFloat(input.value) || 0;
        const sub = price * qty;
        totalRefund += sub;
        const subEl = document.getElementById(`refundSubtotal${idx}`);
        if (subEl) subEl.textContent = `Rs. ${sub.toFixed(2)}`;
    });
    const totalEl = document.getElementById('totalRefundDisplay');
    if (totalEl) totalEl.textContent = `Rs. ${totalRefund.toFixed(2)}`;
}

async function submitRefund() {
    if (!currentRefundOrder) return;
    const btn = document.getElementById('confirmRefundBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    try {
        const itemsToRefund = [];
        const inputs = document.querySelectorAll('.refund-qty-input');
        const restock = document.getElementById('refundRestockCheck')?.checked ?? true;
        const reason = document.getElementById('refundReason')?.value || 'Customer Return';

        inputs.forEach(input => {
            const idx = parseInt(input.dataset.idx);
            const qty = parseFloat(input.value) || 0;
            if (qty > 0) {
                const item = currentRefundOrder.items[idx];
                itemsToRefund.push({
                    product_id: item.product_id,
                    name: item.name || item.product_name,
                    quantity: qty,
                    price: parseFloat(item.price || item.unit_price || 0),
                    refund_amount: parseFloat(item.price || item.unit_price || 0) * qty,
                    restock: restock,
                });
            }
        });

        if (!itemsToRefund.length) {
            showToast('Please specify at least one item quantity to refund', 'warning');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Refund';
            return;
        }

        const refundOrder = await OfflinePOS.processRefund({
            original_order_id: currentRefundOrder.id || null,
            original_order_uuid: currentRefundOrder.uuid,
            items: itemsToRefund,
            reason: reason,
            notes: `Refund for ${currentRefundOrder.order_number}`,
        });

        bootstrap.Modal.getInstance(document.getElementById('refundOrderModal'))?.hide();
        showToast(`✅ Refund processed: ${refundOrder.order_number}`, 'success');

        // Show refund receipt
        showReceiptModal(refundOrder, {
            subtotal: parseFloat(refundOrder.subtotal || 0),
            discountAmount: 0,
            taxAmount: 0,
            grandTotal: parseFloat(refundOrder.total_amount || 0),
        }, parseFloat(refundOrder.total_amount || 0));

        // Refresh sales history if modal is open
        await loadSalesHistory();
        // Refresh product list in POS to reflect returned stock
        loadProducts(currentCategoryFilter, document.getElementById('productSearch')?.value || '');

    } catch (e) {
        showToast('Refund failed: ' + e.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Refund';
    }
}

// ── Daily Report ──────────────────────────────────────────
async function openDailyReportModal() {
    new bootstrap.Modal(document.getElementById('dailyReportModal')).show();
    const body = document.getElementById('dailyReportBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Calculating daily totals...</p></div>';

    try {
        const todayStr = new Date().toISOString().slice(0, 10);
        let report = null;
        const isOnline = navigator.onLine && (typeof PureSync === 'undefined' || PureSync.isOnline());

        if (isOnline && typeof Routes !== 'undefined' && Routes.dailyReport) {
            try {
                const res = await fetch(`${Routes.dailyReport}?date=${todayStr}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        report = data;
                    }
                }
            } catch (netErr) {
                console.warn('[POS] Failed to fetch daily report from server:', netErr);
            }
        }

        // If offline or server fetch failed, fallback to local PureDB report
        if (!report) {
            report = await OfflinePOS.getDailyReport(todayStr);
        }

        const totals = report.totals ?? report;
        const payments = report.payments ?? {};
        const amount = (value) => Number(value || 0).toFixed(2);
        const orderCount = report.order_count ?? report.total_orders ?? 0;
        const pendingSyncCount = (typeof PureDB !== 'undefined' && PureDB.getPendingSyncCount)
            ? await PureDB.getPendingSyncCount()
            : (report.pending_sync_count ?? report.pending_sync ?? 0);

        body.innerHTML = `
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block">Gross Sales</small>
                        <strong class="fs-5 text-success">Rs. ${amount(totals.gross_sales)}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block">Refunds / Returns</small>
                        <strong class="fs-5 text-danger">- Rs. ${amount(totals.refunds ?? totals.returns_amount)}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block">Total Discounts</small>
                        <strong class="fs-6 text-warning">Rs. ${amount(totals.discounts)}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block">Net Sales</small>
                        <strong class="fs-5 text-primary">Rs. ${amount(totals.net_sales)}</strong>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white py-2 small fw-bold">Payment Methods Breakdown</div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span><i class="bi bi-cash-stack text-success me-2"></i>Cash</span>
                        <strong>Rs. ${amount(payments.cash)}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span><i class="bi bi-credit-card text-primary me-2"></i>Card</span>
                        <strong>Rs. ${amount(payments.card)}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span><i class="bi bi-phone text-info me-2"></i>Mobile / QR</span>
                        <strong>Rs. ${amount(payments.mobile ?? payments.mobile_payment)}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span><i class="bi bi-bank text-secondary me-2"></i>Bank Transfer</span>
                        <strong>Rs. ${amount(payments.bank_transfer)}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light small">
                <span>Total Orders Today: <strong>${orderCount}</strong></span>
                <span>Pending Sync: <strong class="${pendingSyncCount > 0 ? 'text-warning' : 'text-success'}">${pendingSyncCount}</strong></span>
            </div>
        `;
    } catch (e) {
        body.innerHTML = `<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-2"></i><p>Failed to generate report: ${e.message}</p></div>`;
    }
}

function printDailyReport() {
    window.print();
}

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
const CUSTOMER_DISPLAY_KEY = 'pure_pos_customer_display';
const CUSTOMER_DISPLAY_ENABLED_KEY = 'pure_pos_display_enabled';
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
    const displayUrl = '{{ route("pos.customer-display") }}';

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
        'PurePOSCustomerDisplay',
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

// ═══════════════════════════════════════════════════════════
//  OFFLINE-FIRST INITIALIZATION
// ═══════════════════════════════════════════════════════════

(async function initOfflineEngine() {
    try {
        // 1. Initialize IndexedDB
        await PureDB.init();
        console.log('[POS] PureDB initialized');

        // 2. Initialize Sync Engine
        PureSync.init({
            apiBase: '/api/v1',
            csrfToken: CSRF,
        });

        // 3. Initialize Offline POS with store config & cache authorized user
        await OfflinePOS.init();
        await OfflinePOS.saveStoreConfig({
            store_id: {{ $store?->id ?? 'null' }},
            user_id: {{ auth()->id() }},
            store_name: @json($store?->name ?? 'My Store'),
            address: @json($store?->address ?? ''),
            phone: @json($store?->phone ?? ''),
            currency: @json($store?->currency ?? 'LKR'),
            currency_symbol: @json($store?->currency_symbol ?? 'Rs.'),
            tax_rate: {{ $store?->tax_rate ?? 0 }},
        });

        @if(auth()->check())
        @php
            $authUser = auth()->user();
            $userPerms = !empty($authUser->permissions) ? $authUser->permissions : $authUser->getRolePermissions();
            $userPinHash = $authUser->pin ? hash('sha256', (string)$authUser->pin) : null;
        @endphp
        await OfflinePOS.cacheCurrentUser({
            id: {{ $authUser->id }},
            name: @json($authUser->name),
            email: @json($authUser->email),
            username: @json($authUser->username),
            role: @json($authUser->role ?? 'cashier'),
            permissions: @json($userPerms),
            store_id: {{ $store?->id ?? 'null' }},
            pin_hash: @json($userPinHash),
        });
        @endif

        // 4. Hydrate if needed (download products/categories/customers to IndexedDB)
        const needsHydration = await PureDB.needsHydration();
        if (needsHydration && PureSync.isOnline()) {
            console.log('[POS] Hydrating local database...');
            showToast('Syncing product data...', 'info');
            const stats = await PureSync.pull('products,customers,categories');
            if (stats) {
                showToast(`Synced: ${stats.products || 0} products, ${stats.customers || 0} customers`, 'success');
            }
        }

        // 4b. Pre-cache recent orders into PureDB for offline history and refunds
        if (PureSync.isOnline() && typeof Routes !== 'undefined' && Routes.salesHistory) {
            fetch(`${Routes.salesHistory}?limit=50`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            }).then(r => r.json()).then(async d => {
                if (d.success && Array.isArray(d.orders) && typeof PureDB !== 'undefined' && PureDB.put) {
                    for (const ord of d.orders) {
                        try { await PureDB.put('orders', ord); } catch (_) {}
                    }
                }
            }).catch(() => {});
        }

        // 5. Wire up sync status indicator & offline detection
        const syncDot = document.getElementById('syncDot');
        const syncLabel = document.getElementById('syncLabel');
        const syncBadge = document.getElementById('syncPendingBadge');
        const syncBtn = document.getElementById('btnSyncNow');
        const offlineBanner = document.getElementById('offlineBanner');
        const syncIndicator = document.getElementById('syncStatusIndicator');

        let _prevOnlineState = PureSync.isOnline();

        function updateSyncUI(status, detail) {
            if (!syncDot) return;

            // Offline banner
            if (offlineBanner) {
                if (status === 'offline') {
                    offlineBanner.classList.remove('d-none');
                } else {
                    offlineBanner.classList.add('d-none');
                }
            }

            // Sync dot + label
            syncDot.classList.remove('sync-online', 'sync-offline', 'sync-syncing', 'sync-failed', 'sync-auth-expired');
            if (status === 'online') {
                syncDot.classList.add('sync-online');
                syncDot.title = 'Online — Connected';
                syncLabel.textContent = 'Online';
                syncLabel.className = 'sync-label';
            } else if (status === 'offline') {
                syncDot.classList.add('sync-offline');
                syncDot.title = 'Offline — Local mode';
                syncLabel.textContent = 'Offline';
                syncLabel.className = 'sync-label sync-label-offline';
            } else if (status === 'syncing') {
                syncDot.classList.add('sync-syncing');
                syncDot.title = 'Synchronizing...';
                syncLabel.textContent = 'Syncing';
                syncLabel.className = 'sync-label sync-label-syncing';
                // Animate sync button
                if (syncBtn) syncBtn.classList.add('sync-spinning');
            } else if (status === 'failed') {
                syncDot.classList.add('sync-failed');
                syncDot.title = 'Sync failed — click to retry';
                syncLabel.textContent = 'Sync Failed';
                syncLabel.className = 'sync-label sync-label-failed';
            } else if (status === 'auth_expired') {
                syncDot.classList.add('sync-auth-expired');
                syncDot.title = 'Session expired — re-login required';
                syncLabel.textContent = 'Session Expired';
                syncLabel.className = 'sync-label sync-label-auth';
            } else if (status === 'conflict') {
                syncDot.classList.add('sync-failed');
                syncDot.title = 'Sync conflicts detected';
                syncLabel.textContent = 'Conflicts';
                syncLabel.className = 'sync-label sync-label-failed';
            }

            // Stop sync button animation after sync ends
            if (status !== 'syncing' && syncBtn) {
                syncBtn.classList.remove('sync-spinning');
            }
        }

        // ── PureSync Event Listeners ──────────────────────────────

        PureSync.on('online', () => {
            updateSyncUI('online');
            // If was offline, show reconnection toast
            if (!_prevOnlineState) {
                showToast('✅ Connection restored — syncing...', 'success');
            }
            _prevOnlineState = true;
        });

        PureSync.on('offline', () => {
            updateSyncUI('offline');
            if (_prevOnlineState) {
                showToast('⚠️ Offline — POS continues working locally', 'warning');
            }
            _prevOnlineState = false;
        });

        PureSync.on('sync_start', () => {
            updateSyncUI('syncing');
        });

        PureSync.on('sync_complete', (data) => {
            updateSyncUI('online');
            if (data.pushed > 0) {
                showToast(`✅ ${data.pushed} transaction(s) synced!`, 'success');
            }
            if (data.conflicts > 0) {
                showToast(`⚠️ ${data.conflicts} sync conflict(s) detected`, 'warning');
                updateSyncUI('conflict');
            }
            if (data.failed > 0 && data.pushed === 0) {
                showToast(`❌ ${data.failed} transaction(s) failed to sync`, 'danger');
                updateSyncUI('failed');
            }
        });

        PureSync.on('sync_error', (data) => {
            updateSyncUI('failed');
            console.warn('[POS] Sync error:', data.error);
            showToast('Sync error: ' + (data.error || 'Unknown'), 'danger');
        });

        PureSync.on('auth_expired', () => {
            updateSyncUI('auth_expired');
            showToast('🔒 Session expired. Please re-login to sync.', 'danger');
        });

        PureSync.on('sync_progress', (progress) => {
            if (syncLabel && progress.total > 0) {
                syncLabel.textContent = `Syncing ${progress.current}/${progress.total}`;
            }
        });

        PureSync.on('pull_complete', (stats) => {
            if (stats) {
                const parts = [];
                if (stats.products) parts.push(`${stats.products} products`);
                if (stats.customers) parts.push(`${stats.customers} customers`);
                if (stats.categories) parts.push(`${stats.categories} categories`);
                if (parts.length > 0) {
                    console.log('[POS] Data refreshed:', parts.join(', '));
                }
            }
        });

        PureSync.on('pending_update', (data) => {
            if (syncBadge) {
                if (data.count > 0) {
                    syncBadge.textContent = data.count;
                    syncBadge.classList.remove('d-none');
                    if (syncBtn) syncBtn.classList.remove('d-none');
                } else {
                    syncBadge.classList.add('d-none');
                    if (syncBtn) syncBtn.classList.add('d-none');
                }
            }
            // Show failed count in badge if any
            if (data.failed > 0 && syncBadge) {
                syncBadge.textContent = `${data.count}`;
                syncBadge.title = `${data.count} pending, ${data.failed} failed`;
            }
        });

        PureSync.on('state_change', (fullStatus) => {
            console.log('[POS] Sync state:', fullStatus.connectionState, fullStatus.syncState,
                'Pending:', fullStatus.pendingCount, 'Failed:', fullStatus.failedCount);
        });

        // Set initial status
        updateSyncUI(PureSync.isOnline() ? 'online' : 'offline');

        // 6. Log DB stats
        const stats = await PureDB.getStats();
        console.log('[POS] Local DB:', stats);

    } catch (err) {
        console.error('[POS] Offline engine init failed:', err);
        // POS still works in online-only mode even if offline engine fails
    }
})();
</script>
@endpush
