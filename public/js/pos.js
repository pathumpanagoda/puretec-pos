/**
 * =============================================================
 * PURE POS — Point of Sale Terminal JavaScript
 * Author: Nexfloit | Version: 1.0.0
 * =============================================================
 *
 * State Management: Pure JS (no frameworks)
 * Features:
 *   - Product search & barcode scanning
 *   - Customer search & selection
 *   - Cart management (add, remove, qty, discount per item)
 *   - Multiple payment methods (cash, card, mobile, split)
 *   - Coupon & gift card validation
 *   - Order hold / recall
 *   - Receipt generation & printing
 *   - Register open/close
 *   - Keyboard shortcuts
 * =============================================================
 */

'use strict';

// ── POS STATE ────────────────────────────────────────────────
const POSState = {
    cart:         [],     // { productId, name, sku, barcode, qty, unitPrice, costPrice, taxRate, discount, image }
    customer:     null,   // { id, name, phone, loyaltyPoints, currentBalance }
    discount:     { type: 'percentage', value: 0, amount: 0 },
    coupon:       null,   // { code, discountAmount }
    payments:     [],     // [{ method, amount, reference, provider }]
    currentOrder: null,   // fulfilled Order object after sale
    config:       {},     // window.POS_CONFIG
    products:     [],     // loaded products cache
    selectedCat:  '',     // selected category ID
    payMethod:    'cash', // active payment method
    discountType: 'percentage',
    splitPayments:[],     // for split payment mode
    tableNumber:  '',
};

// ── MAIN INIT ────────────────────────────────────────────────
window.PurePOS = {
    init(config) {
        POSState.config = config;
        this._bindEvents();
        this._loadProducts();
        this._updateDateTime();

        // Check register
        if (!config.hasRegister) {
            const modal = new bootstrap.Modal(document.getElementById('registerModal'), { keyboard: false, backdrop: 'static' });
            modal.show();
        }
    },

    // ── EVENT BINDINGS ─────────────────────────────────────
    _bindEvents() {
        // Product search
        const searchInput = document.getElementById('productSearch');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(e => {
                const q = e.target.value.trim();
                document.getElementById('searchClear').classList.toggle('d-none', !q);
                this._loadProducts(q, POSState.selectedCat);
            }, 280));
            // Barcode scan: Enter key
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = e.target.value.trim();
                    if (val) this._loadProducts(val, POSState.selectedCat, true);
                }
            });
        }

        // Category buttons
        document.querySelectorAll('.pos-cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                POSState.selectedCat = btn.dataset.cat;
                const q = document.getElementById('productSearch')?.value.trim() || '';
                this._loadProducts(q, POSState.selectedCat);
            });
        });

        // Customer search
        const custInput = document.getElementById('customerSearch');
        if (custInput) {
            custInput.addEventListener('input', debounce(e => {
                const q = e.target.value.trim();
                if (q.length >= 2) this._searchCustomers(q);
                else document.getElementById('customerDropdown').classList.add('d-none');
            }, 300));
        }

        // Cash input -> calculate change
        document.getElementById('cashReceived')?.addEventListener('input', e => {
            this._updateChangeDisplay(parseFloat(e.target.value) || 0);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', e => {
            if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                if (e.key === 'F2') { e.preventDefault(); document.getElementById('productSearch')?.focus(); }
                if (e.key === 'F4') { e.preventDefault(); document.getElementById('customerSearch')?.focus(); }
                if (e.key === 'F8') { e.preventDefault(); if (POSState.cart.length > 0) showPaymentModal(); }
                if (e.key === 'Escape') clearSearch();
            }
        });
    },

    // ── PRODUCTS ───────────────────────────────────────────
    async _loadProducts(q = '', catId = '', barcodeScan = false) {
        const container = document.getElementById('productsContainer');
        const loading   = document.getElementById('productsLoading');
        const empty     = document.getElementById('productsEmpty');

        loading.classList.remove('d-none');
        loading.style.display = 'flex';
        empty.classList.add('d-none');
        if (container) container.innerHTML = '';

        try {
            const params  = new URLSearchParams();
            if (q)     params.set('q', q);
            if (catId) params.set('category_id', catId);

            const url  = `${POSState.config.routes.searchProducts}?${params}`;
            const data = await posRequest(url);
            const prods = data.products || [];

            loading.style.display = 'none';
            POSState.products = prods;

            if (prods.length === 0) {
                empty.classList.remove('d-none');
                return;
            }

            // Auto-add if barcode scan returns exactly 1 result
            if (barcodeScan && prods.length === 1 && !prods[0].has_variants) {
                this.addToCart(prods[0]);
                document.getElementById('productSearch').value = '';
                document.getElementById('searchClear').classList.add('d-none');
                showToast(`${prods[0].name} added`, 'success', 1500);
                this._loadProducts('', POSState.selectedCat);
                return;
            }

            container.innerHTML = prods.map(p => this._renderProductCard(p)).join('');

            // Bind click events
            container.querySelectorAll('.pos-product-card').forEach((card, i) => {
                const prod = prods[i];
                if (card.classList.contains('out-of-stock') && !prod.allow_negative) return;
                card.addEventListener('click', () => {
                    if (prod.has_variants) {
                        this._showVariantPicker(prod);
                    } else {
                        this.addToCart(prod);
                        card.classList.add('adding');
                        setTimeout(() => card.classList.remove('adding'), 200);
                    }
                });
            });
        } catch (err) {
            loading.style.display = 'none';
            empty.classList.remove('d-none');
            console.error('Product load error:', err);
        }
    },

    _renderProductCard(p) {
        const outOfStock = p.track_stock && p.stock <= 0 && !p.allow_negative;
        const lowStock   = p.track_stock && p.stock > 0 && p.stock <= 5;
        const stockBadge = outOfStock
            ? `<span class="pos-product-stock out"><i class="bi bi-x-circle-fill me-1"></i>Out</span>`
            : (lowStock
                ? `<span class="pos-product-stock low"><i class="bi bi-exclamation-triangle me-1"></i>${p.stock}</span>`
                : (p.track_stock ? `<span class="pos-product-stock"><i class="bi bi-check-circle me-1"></i>${p.stock}</span>` : ''));

        const imgHtml = p.image
            ? `<img src="${p.image}" class="pos-product-img" alt="${p.name}" loading="lazy">`
            : `<div class="pos-product-img-placeholder"><i class="bi bi-box"></i></div>`;

        return `
        <div class="pos-product-card${outOfStock ? ' out-of-stock' : ''}" data-id="${p.id}">
            ${imgHtml}
            <div class="pos-product-name">${escapeHtml(p.name)}</div>
            <div class="pos-product-price">${formatCurrency(p.selling_price)}</div>
            ${stockBadge}
        </div>`;
    },

    _showVariantPicker(product) {
        // Simple variant picker as alert for now
        if (!product.variants || product.variants.length === 0) {
            this.addToCart(product);
            return;
        }
        // Create a quick modal-like dropdown
        const variantHtml = product.variants.map(v =>
            `<div class="customer-dropdown-item" onclick="PurePOS._addVariant(${JSON.stringify(product).replace(/"/g, '&quot;')}, ${v.id})">
                <div class="cdi-name">${v.name}</div>
                <div class="cdi-pts">${formatCurrency(v.price)}</div>
            </div>`
        ).join('');
        showToast('Please select a variant', 'info');
    },

    _addVariant(product, variantId) {
        const variant = product.variants.find(v => v.id === variantId);
        if (!variant) return;
        this.addToCart({ ...product, id: product.id, variantId, name: `${product.name} - ${variant.name}`, selling_price: variant.price, stock: variant.stock });
    },

    // ── CART ───────────────────────────────────────────────
    addToCart(product) {
        const existing = POSState.cart.find(item =>
            item.productId === product.id && item.variantId === (product.variantId || null)
        );

        if (existing) {
            existing.qty += 1;
        } else {
            POSState.cart.push({
                productId:  product.id,
                variantId:  product.variantId || null,
                name:       product.name,
                sku:        product.sku,
                barcode:    product.barcode,
                qty:        1,
                unitPrice:  parseFloat(product.selling_price),
                costPrice:  parseFloat(product.cost_price || 0),
                taxRate:    parseFloat(product.tax_rate || 0),
                discount:   0,
                unit:       product.unit || 'pcs',
                image:      product.image,
                trackStock: product.track_stock,
            });
        }
        this._renderCart();
        this._calcTotals();
    },

    updateQty(index, delta) {
        const item = POSState.cart[index];
        if (!item) return;
        item.qty = Math.max(0, item.qty + delta);
        if (item.qty === 0) {
            POSState.cart.splice(index, 1);
        }
        this._renderCart();
        this._calcTotals();
    },

    removeItem(index) {
        POSState.cart.splice(index, 1);
        this._renderCart();
        this._calcTotals();
    },

    setQtyDirect(index, val) {
        const qty = parseFloat(val);
        if (isNaN(qty) || qty <= 0) {
            POSState.cart.splice(index, 1);
        } else {
            POSState.cart[index].qty = qty;
        }
        this._renderCart();
        this._calcTotals();
    },

    _renderCart() {
        const list   = document.getElementById('orderItemsList');
        const empty  = document.getElementById('orderEmpty');
        if (!list) return;

        if (POSState.cart.length === 0) {
            empty.style.display = 'flex';
            list.innerHTML      = '';
            document.getElementById('checkoutBtn').disabled = true;
            return;
        }

        empty.style.display = 'none';
        document.getElementById('checkoutBtn').disabled = false;

        list.innerHTML = POSState.cart.map((item, i) => `
        <div class="order-item-row" id="cart-item-${i}">
            <div class="oir-name">
                <span class="oir-name-text">${escapeHtml(item.name)}</span>
                <span class="oir-price">${formatCurrency(item.unitPrice)} / ${item.unit}</span>
            </div>
            <div class="oir-qty-control">
                <button class="oir-qty-btn remove" onclick="PurePOS.updateQty(${i}, -1)" title="Decrease">
                    <i class="bi bi-dash"></i>
                </button>
                <input type="number" class="oir-qty-val" value="${item.qty}"
                    style="width:44px;border:1.5px solid var(--border);border-radius:var(--radius-sm);text-align:center;background:var(--bg-input);color:var(--text-primary);font-weight:700;font-size:.875rem;padding:2px;"
                    min="0.001" step="any"
                    onchange="PurePOS.setQtyDirect(${i}, this.value)"
                    onclick="this.select()">
                <button class="oir-qty-btn" onclick="PurePOS.updateQty(${i}, 1)" title="Increase">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="oir-total">${formatCurrency(item.unitPrice * item.qty - item.discount)}</div>
            <button class="oir-del" onclick="PurePOS.removeItem(${i})" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </div>`).join('');
    },

    _calcTotals() {
        let subtotal = 0;
        let taxAmt   = 0;

        POSState.cart.forEach(item => {
            const lineTotal = item.unitPrice * item.qty - item.discount;
            subtotal += lineTotal;
            taxAmt   += lineTotal * item.taxRate / 100;
        });

        let discountAmt = 0;
        if (POSState.discount.value > 0) {
            discountAmt = POSState.discount.type === 'percentage'
                ? subtotal * POSState.discount.value / 100
                : POSState.discount.value;
            POSState.discount.amount = discountAmt;
        }
        if (POSState.coupon?.discountAmount) {
            discountAmt += POSState.coupon.discountAmount;
        }

        const grand = subtotal + taxAmt - discountAmt;

        // Update display
        setElText('subtotalDisplay', formatCurrency(subtotal));
        setElText('grandTotalDisplay', formatCurrency(Math.max(0, grand)));
        setElText('checkoutTotal', formatCurrency(Math.max(0, grand)));
        setElText('paymentTotalDisplay', formatCurrency(Math.max(0, grand)));

        // Discount row
        const discRow = document.getElementById('discountRow');
        if (discountAmt > 0) {
            discRow.style.display = 'flex';
            setElText('discountDisplay', formatNumber(discountAmt, 2));
        } else {
            discRow.style.display = 'none';
        }

        // Tax row
        const taxRow = document.getElementById('taxRow');
        if (taxAmt > 0) {
            taxRow.style.display = 'flex';
            setElText('taxDisplay', formatNumber(taxAmt, 2));
        } else {
            taxRow.style.display = 'none';
        }

        // Quick cash buttons
        this._updateQuickCash(Math.max(0, grand));
    },

    _updateQuickCash(total) {
        const grid = document.getElementById('quickCashGrid');
        if (!grid) return;
        const roundUps = [
            Math.ceil(total),
            Math.ceil(total / 50) * 50,
            Math.ceil(total / 100) * 100,
            Math.ceil(total / 500) * 500,
            Math.ceil(total / 1000) * 1000,
            Math.ceil(total / 5000) * 5000,
        ].filter((v, i, a) => a.indexOf(v) === i && v >= total).slice(0, 8);

        grid.innerHTML = roundUps.map(v =>
            `<button onclick="PurePOS._setCash(${v})">Rs. ${formatNumber(v)}</button>`
        ).join('');
    },

    _setCash(amount) {
        const input = document.getElementById('cashReceived');
        if (input) { input.value = amount; this._updateChangeDisplay(amount); }
    },

    _updateChangeDisplay(received) {
        const grand  = this._getGrandTotal();
        const change = Math.max(0, received - grand);
        setElText('changeAmount', formatCurrency(change));
    },

    _getGrandTotal() {
        let subtotal = 0;
        let taxAmt   = 0;
        POSState.cart.forEach(item => {
            const lineTotal = item.unitPrice * item.qty - item.discount;
            subtotal += lineTotal;
            taxAmt   += lineTotal * item.taxRate / 100;
        });
        let disc = POSState.discount.amount || 0;
        if (POSState.coupon?.discountAmount) disc += POSState.coupon.discountAmount;
        return Math.max(0, subtotal + taxAmt - disc);
    },

    // ── CUSTOMERS ──────────────────────────────────────────
    async _searchCustomers(q) {
        try {
            const data   = await posRequest(`${POSState.config.routes.searchCustomers}?q=${encodeURIComponent(q)}`);
            const dd     = document.getElementById('customerDropdown');
            const customers = data.customers || [];

            if (customers.length === 0) { dd.classList.add('d-none'); return; }

            dd.innerHTML = customers.map(c => `
            <div class="customer-dropdown-item" onclick="PurePOS.selectCustomer(${c.id}, '${escapeHtml(c.name)}', '${escapeHtml(c.phone || '')}', ${c.loyalty_points || 0}, ${c.current_balance || 0})">
                <div class="cdi-avatar">${c.name.substring(0, 2).toUpperCase()}</div>
                <div>
                    <div class="cdi-name">${escapeHtml(c.name)}</div>
                    <div class="cdi-phone">${c.phone || c.email || '—'}</div>
                </div>
                ${c.loyalty_points > 0 ? `<div class="cdi-pts"><i class="bi bi-star-fill me-1"></i>${c.loyalty_points} pts</div>` : ''}
            </div>`).join('');
            dd.classList.remove('d-none');
        } catch (err) { console.error(err); }
    },

    selectCustomer(id, name, phone, points, balance) {
        POSState.customer = { id, name, phone, loyaltyPoints: points, currentBalance: balance };
        document.getElementById('customerDropdown').classList.add('d-none');
        document.getElementById('customerSearch').value = '';
        document.querySelector('.pos-customer-search').classList.add('d-none');
        const sel = document.getElementById('customerSelected');
        sel.classList.remove('d-none');
        setElText('customerName', name);
        setElText('customerMeta', phone || 'No phone');
    },

    _updateDateTime() {
        const el = document.getElementById('posDateTime');
        if (!el) return;
        const update = () => {
            const now = new Date();
            el.textContent = now.toLocaleString('en-LK', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
        };
        update();
        setInterval(update, 60000);
    },
};

// ── GLOBAL POS FUNCTIONS ──────────────────────────────────────

window.clearSearch = () => {
    const input = document.getElementById('productSearch');
    if (input) { input.value = ''; input.focus(); }
    document.getElementById('searchClear')?.classList.add('d-none');
    PurePOS._loadProducts('', POSState.selectedCat);
};

window.clearCustomer = () => {
    POSState.customer = null;
    document.querySelector('.pos-customer-search')?.classList.remove('d-none');
    document.getElementById('customerSelected')?.classList.add('d-none');
    document.getElementById('customerSearch').value = '';
};

window.clearOrder = () => {
    if (POSState.cart.length === 0) return;
    if (!confirm('Clear all items from this order?')) return;
    POSState.cart = [];
    POSState.discount = { type: 'percentage', value: 0, amount: 0 };
    POSState.coupon   = null;
    PurePOS._renderCart();
    PurePOS._calcTotals();
    clearCustomer();
};

window.removeDiscount = () => {
    POSState.discount = { type: 'percentage', value: 0, amount: 0 };
    POSState.coupon   = null;
    PurePOS._calcTotals();
};

// ── PAYMENT MODAL ─────────────────────────────────────────────
window.showPaymentModal = () => {
    if (POSState.cart.length === 0) { showToast('Cart is empty!', 'warning'); return; }
    const grand = PurePOS._getGrandTotal();
    setElText('paymentTotalDisplay', formatCurrency(grand));
    PurePOS._updateQuickCash(grand);
    const cashInput = document.getElementById('cashReceived');
    if (cashInput) { cashInput.value = ''; }
    selectPaymentMethod('cash');
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
};

window.selectPaymentMethod = (method) => {
    POSState.payMethod = method;
    document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.pay-method-btn[data-method="${method}"]`)?.classList.add('active');
    // Show/hide sections
    document.getElementById('cashPaymentSection').classList.toggle('d-none', method !== 'cash');
    document.getElementById('cardPaymentSection').classList.toggle('d-none', !['card','mobile_payment','bank_transfer','cheque'].includes(method));
    document.getElementById('giftCardSection').classList.toggle('d-none', method !== 'gift_card');
    document.getElementById('splitPaymentSection').classList.toggle('d-none', method !== 'mixed');
    if (method === 'mixed') initSplitPayments();
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.pay-method-btn').forEach(btn => {
        btn.addEventListener('click', () => selectPaymentMethod(btn.dataset.method));
    });
});

window.initSplitPayments = () => {
    const grand = PurePOS._getGrandTotal();
    POSState.splitPayments = [{ method: 'cash', amount: grand }];
    renderSplitPayments();
};

window.addSplitPayment = () => {
    POSState.splitPayments.push({ method: 'card', amount: 0 });
    renderSplitPayments();
};

window.renderSplitPayments = () => {
    const methods = ['cash','card','mobile_payment','bank_transfer','cheque','credit','gift_card'];
    const list = document.getElementById('splitPaymentsList');
    list.innerHTML = POSState.splitPayments.map((sp, i) => `
    <div class="split-payment-row">
        <select onchange="POSState.splitPayments[${i}].method = this.value">
            ${methods.map(m => `<option value="${m}" ${sp.method === m ? 'selected' : ''}>${m.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>`).join('')}
        </select>
        <input type="number" value="${sp.amount}" min="0" step="0.01" placeholder="Amount"
            onchange="POSState.splitPayments[${i}].amount = parseFloat(this.value)||0; updateSplitRemaining()">
        ${i > 0 ? `<button onclick="POSState.splitPayments.splice(${i},1); renderSplitPayments()"><i class="bi bi-x"></i></button>` : ''}
    </div>`).join('');
    updateSplitRemaining();
};

window.updateSplitRemaining = () => {
    const grand = PurePOS._getGrandTotal();
    const paid  = POSState.splitPayments.reduce((s, p) => s + (parseFloat(p.amount) || 0), 0);
    const rem   = Math.max(0, grand - paid);
    setElText('splitRemaining', formatCurrency(rem));
};

window.confirmPayment = async () => {
    if (POSState.cart.length === 0) { showToast('Cart is empty!', 'error'); return; }

    const grand     = PurePOS._getGrandTotal();
    const method    = POSState.payMethod;
    const payments  = [];

    if (method === 'cash') {
        const received = parseFloat(document.getElementById('cashReceived').value) || 0;
        if (received < grand) { showToast('Cash received is less than the total!', 'error'); return; }
        payments.push({ method: 'cash', amount: received });
    } else if (method === 'mixed') {
        const total = POSState.splitPayments.reduce((s, p) => s + (parseFloat(p.amount)||0), 0);
        if (total < grand) { showToast('Split payments total is less than the order total!', 'error'); return; }
        POSState.splitPayments.forEach(p => payments.push({ method: p.method, amount: p.amount }));
    } else {
        payments.push({
            method,
            amount:    grand,
            reference: document.getElementById('cardReference')?.value || '',
            provider:  document.getElementById('cardProvider')?.value  || '',
        });
    }

    const items = POSState.cart.map(item => ({
        product_id:    item.productId,
        variant_id:    item.variantId,
        quantity:      item.qty,
        unit_price:    item.unitPrice,
        discount_amount: item.discount || 0,
        tax_rate:      item.taxRate || 0,
    }));

    const body = {
        customer_id:     POSState.customer?.id || null,
        items,
        payments,
        discount_amount: POSState.discount.amount + (POSState.coupon?.discountAmount || 0),
        discount_type:   POSState.discount.type,
        discount_value:  POSState.discount.value,
        coupon_code:     POSState.coupon?.code || null,
        notes:           document.getElementById('orderNotes')?.value || '',
        table_number:    POSState.tableNumber || null,
    };

    // Disable button
    const btn = document.getElementById('confirmPayBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1 spin-anim"></i> Processing...';

    try {
        const res = await posRequest(POSState.config.routes.processSale, 'POST', body);
        POSState.currentOrder = res.order;

        // Close payment modal
        bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();

        // Show receipt
        showReceiptModal(res.order, payments[0]);

        // Reset order
        POSState.cart     = [];
        POSState.discount = { type: 'percentage', value: 0, amount: 0 };
        POSState.coupon   = null;
        POSState.customer = null;
        PurePOS._renderCart();
        PurePOS._calcTotals();
        clearCustomer();

    } catch (err) {
        showToast(err.message || 'Payment failed. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Complete Sale';
    }
};

// ── RECEIPT ───────────────────────────────────────────────────
window.showReceiptModal = (order, payment) => {
    const received = payment?.method === 'cash' ? (parseFloat(document.getElementById('cashReceived')?.value) || order.total_amount) : order.total_amount;
    const change   = Math.max(0, received - order.total_amount);
    const date     = new Date().toLocaleString('en-LK', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });

    const items = (order.items || []).map(item =>
        `<div class="receipt-row"><span>${item.product_name} x${item.quantity}</span><span>Rs. ${formatNumber(item.total, 2)}</span></div>`
    ).join('');

    document.getElementById('receiptContent').innerHTML = `
    <div class="receipt-header">
        <div style="font-size:1.1rem;font-weight:bold">Pure POS</div>
        <div style="font-size:0.75rem;color:#666">by Nexfloit</div>
        <div class="receipt-divider"></div>
        <div>${date}</div>
        <div>Order: ${order.order_number}</div>
        ${order.customer?.name ? `<div>Customer: ${order.customer.name}</div>` : ''}
    </div>
    <div class="receipt-divider"></div>
    ${items}
    <div class="receipt-divider"></div>
    <div class="receipt-row"><span>Subtotal</span><span>Rs. ${formatNumber(order.subtotal, 2)}</span></div>
    ${order.discount_amount > 0 ? `<div class="receipt-row"><span>Discount</span><span>- Rs. ${formatNumber(order.discount_amount, 2)}</span></div>` : ''}
    ${order.tax_amount > 0 ? `<div class="receipt-row"><span>Tax</span><span>Rs. ${formatNumber(order.tax_amount, 2)}</span></div>` : ''}
    <div class="receipt-divider"></div>
    <div class="receipt-row receipt-total"><span><strong>TOTAL</strong></span><span><strong>Rs. ${formatNumber(order.total_amount, 2)}</strong></span></div>
    <div class="receipt-row"><span>Payment (${payment?.method || '—'})</span><span>Rs. ${formatNumber(received, 2)}</span></div>
    ${payment?.method === 'cash' && change > 0 ? `<div class="receipt-row"><span>Change</span><span>Rs. ${formatNumber(change, 2)}</span></div>` : ''}
    <div class="receipt-divider"></div>
    <div style="text-align:center;font-size:0.75rem;color:#666">Thank you for your business!</div>`;

    new bootstrap.Modal(document.getElementById('receiptModal')).show();
};

window.printReceipt = () => {
    const content = document.getElementById('receiptContent')?.innerHTML;
    if (!content) return;
    const win = window.open('', '_blank', 'width=320,height=600');
    win.document.write(`<!DOCTYPE html><html><head><title>Receipt</title>
    <style>body{font-family:'Courier New',monospace;font-size:12px;padding:10px;width:280px;margin:0 auto}
    .receipt-row{display:flex;justify-content:space-between;margin:3px 0}
    .receipt-divider{border-top:1px dashed #999;margin:6px 0}
    .receipt-header{text-align:center;margin-bottom:8px}
    .receipt-total{font-size:14px;font-weight:bold}</style></head>
    <body>${content}<script>window.onload=()=>{window.print();window.close()}<\/script></body></html>`);
    win.document.close();
};

window.newOrder = () => {
    POSState.cart = [];
    POSState.discount = { type: 'percentage', value: 0, amount: 0 };
    POSState.coupon = null;
    clearCustomer();
    PurePOS._renderCart();
    PurePOS._calcTotals();
    document.getElementById('productSearch')?.focus();
};

// ── DISCOUNT ──────────────────────────────────────────────────
window.showDiscountModal = () => {
    if (POSState.cart.length === 0) { showToast('Add items first', 'warning'); return; }
    document.getElementById('discountValue').value = POSState.discount.value || '';
    new bootstrap.Modal(document.getElementById('discountModal')).show();
};

window.selectDiscountType = (type, el) => {
    POSState.discountType = type;
    document.querySelectorAll('.disc-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    setElText('discountLabel', type === 'percentage' ? 'Discount (%)' : 'Discount Amount (Rs.)');
    setElText('discountSymbol', type === 'percentage' ? '%' : 'Rs.');
    document.getElementById('discountValue').max = type === 'percentage' ? 100 : '';
};

window.setDiscount = (val) => { document.getElementById('discountValue').value = val; };

window.applyDiscount = () => {
    const val = parseFloat(document.getElementById('discountValue').value) || 0;
    if (val <= 0) { showToast('Enter a valid discount', 'warning'); return; }
    if (POSState.discountType === 'percentage' && val > 100) { showToast('Discount cannot exceed 100%', 'error'); return; }
    POSState.discount = { type: POSState.discountType, value: val, amount: 0 };
    PurePOS._calcTotals();
    bootstrap.Modal.getInstance(document.getElementById('discountModal'))?.hide();
    showToast(`Discount applied: ${val}${POSState.discountType === 'percentage' ? '%' : ' Rs.'}`, 'success');
};

// ── COUPON ────────────────────────────────────────────────────
window.showCouponModal = () => {
    document.getElementById('couponCode').value = '';
    document.getElementById('couponMessage').innerHTML = '';
    new bootstrap.Modal(document.getElementById('couponModal')).show();
};

window.applyCoupon = async () => {
    const code    = document.getElementById('couponCode').value.trim().toUpperCase();
    const msgEl   = document.getElementById('couponMessage');
    if (!code) { msgEl.innerHTML = '<span class="text-danger">Enter a coupon code</span>'; return; }

    const subtotal = POSState.cart.reduce((s, i) => s + i.unitPrice * i.qty, 0);

    try {
        const res = await posRequest(POSState.config.routes.applyCoupon, 'POST', { code, subtotal });
        POSState.coupon = { code, discountAmount: res.discount_amount };
        PurePOS._calcTotals();
        bootstrap.Modal.getInstance(document.getElementById('couponModal'))?.hide();
        showToast(res.message, 'success');
    } catch (err) {
        msgEl.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${err.message}</span>`;
    }
};

// ── GIFT CARD ─────────────────────────────────────────────────
window.validateGiftCard = async () => {
    const code = document.getElementById('giftCardCode')?.value.trim();
    if (!code) return;
    try {
        const res = await posRequest(POSState.config.routes.validateGiftCard, 'POST', { code });
        document.getElementById('giftCardInfo').innerHTML =
            `<div class="alert alert-success py-2 small mb-0"><i class="bi bi-check-circle me-1"></i>${res.message}</div>`;
    } catch (err) {
        document.getElementById('giftCardInfo').innerHTML =
            `<div class="alert alert-danger py-2 small mb-0"><i class="bi bi-x-circle me-1"></i>${err.message}</div>`;
    }
};

// ── HOLD ORDERS ───────────────────────────────────────────────
window.holdOrder = async () => {
    if (POSState.cart.length === 0) { showToast('Cart is empty!', 'warning'); return; }
    try {
        const total = PurePOS._getGrandTotal();
        await posRequest(POSState.config.routes.holdOrder, 'POST', {
            customer_id:  POSState.customer?.id,
            items:        POSState.cart,
            subtotal:     total,
            total_amount: total,
            notes:        `Held at ${new Date().toLocaleTimeString()}`,
        });
        showToast('Order held successfully!', 'success');
        newOrder();
    } catch (err) {
        showToast('Failed to hold order: ' + err.message, 'error');
    }
};

window.showHeldOrders = async () => {
    try {
        const data    = await posRequest(POSState.config.routes.heldOrders);
        const orders  = data.orders || [];
        const listEl  = document.getElementById('heldOrdersList');

        if (orders.length === 0) {
            listEl.innerHTML = `<div class="text-center text-muted p-5"><i class="bi bi-clock-history fs-1 opacity-25"></i><p class="mt-3">No held orders</p></div>`;
        } else {
            listEl.innerHTML = orders.map(o => `
            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <div class="fw-600">${o.order_number}</div>
                    <div class="text-muted small">${o.customer?.name || 'Walk-in'} — ${o.items?.length || 0} items</div>
                </div>
                <div class="fw-bold text-primary">${formatCurrency(o.total_amount)}</div>
                <button class="btn-primary-pos btn-sm py-1 px-3" onclick="recallOrder(${o.id})">Recall</button>
            </div>`).join('');
        }

        new bootstrap.Modal(document.getElementById('heldOrdersModal')).show();
    } catch (err) {
        showToast('Failed to load held orders', 'error');
    }
};

// ── REGISTER ──────────────────────────────────────────────────
window.openRegister = async () => {
    const balance = parseFloat(document.getElementById('openingBalance').value) || 0;
    try {
        const res = await posRequest(POSState.config.routes.openRegister, 'POST', { opening_balance: balance });
        POSState.config.hasRegister = true;
        POSState.config.registerId = res.register.id;
        bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
        showToast('Register opened! Ready to process sales.', 'success');
    } catch (err) {
        showToast('Failed to open register: ' + err.message, 'error');
    }
};

window.activateBarcodeScan = () => {
    const input = document.getElementById('productSearch');
    if (input) { input.focus(); input.select(); }
    showToast('Ready for barcode scan — scan or type barcode', 'info', 2000);
};

// ── HELPERS ───────────────────────────────────────────────────
function setElText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
