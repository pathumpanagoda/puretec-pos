/**
 * =============================================================
 * OfflinePOS v2.5 — Complete Production-Reliable Offline POS
 * =============================================================
 *
 * CAPABILITIES:
 *   - Offline sales with cash, card, mobile, and split payments
 *   - Offline refund & return processing with item quantity validation
 *   - Local stock deduction and return restoration in IndexedDB
 *   - Offline held bills (save, list, retrieve, delete)
 *   - Customer search and offline customer creation
 *   - Offline sales history lookup and receipt viewing
 *   - Offline daily and shift report generation
 *   - Offline cash register open/close operations with variance
 *   - Offline authorized user verification with SHA-256 credentials
 *   - Thermal 58mm & 80mm receipt generation and printing
 *   - Safe double-write persistence and queueing into PureDB
 *
 * Author: Puretec | Version: 2.5.0
 * =============================================================
 */

'use strict';

const OfflinePOS = (function () {
    let _storeConfig = null;

    // ── Initialize ───────────────────────────────────────────────

    async function init(config = {}) {
        _storeConfig = config;

        const savedConfig = await PureDB.getSetting('store_config');
        if (savedConfig) {
            _storeConfig = { ...savedConfig, ...config };
        }

        console.log('[OfflinePOS] Initialized v2.5');
    }

    async function saveStoreConfig(config) {
        _storeConfig = config;
        await PureDB.setSetting('store_config', config);
    }

    // ── Offline User Authentication ──────────────────────────────

    async function cacheCurrentUser(user, rawCredential = null) {
        return await PureDB.saveAuthorizedUser(user, rawCredential);
    }

    async function verifyOfflineUser(login, credential) {
        return await PureDB.verifyOfflineUser(login, credential);
    }

    // ── Offline Sale Processing (Single & Split Payments) ────────

    /**
     * Process a complete sale offline.
     * Supports single or split payments, cash change, and individual stock deduction UUIDs.
     */
    async function processSale(saleData) {
        const uuid = saleData.uuid || PureDB.generateUUID();
        const now = new Date().toISOString();
        const orderNumber = _generateOrderNumber('OFF');

        // Generate stock movement UUIDs per item (for idempotent cloud stock deduction)
        const itemsWithMovementUUIDs = (saleData.items || []).map(item => {
            const rawTax = (item.tax !== undefined && item.tax !== null && item.tax !== '')
                ? item.tax
                : ((item.tax_amount !== undefined && item.tax_amount !== null && item.tax_amount !== '') ? item.tax_amount : 0);

            return {
                product_id: item.product_id,
                variant_id: item.variant_id || null,
                product_name: item.name || item.product_name,
                sku: item.sku || '',
                barcode: item.barcode || '',
                quantity: parseFloat(item.quantity),
                unit: item.unit || 'pcs',
                unit_price: parseFloat(item.price || item.unit_price),
                cost_price: parseFloat(item.cost_price || 0),
                discount_amount: parseFloat(item.discount || item.discount_amount || 0),
                tax_rate: parseFloat(item.tax_rate) || 0,
                tax_amount: parseFloat(rawTax) || 0,
                total_price: (parseFloat(item.quantity) * parseFloat(item.price || item.unit_price)) - parseFloat(item.discount || item.discount_amount || 0),
                stock_movement_uuid: PureDB.generateUUID(),
            };
        });

        const totals = _calculateTotals(itemsWithMovementUUIDs, saleData);

        // Normalize payments (support split payments array or single payment)
        let payments = [];
        let totalPaid = 0;

        if (Array.isArray(saleData.payments) && saleData.payments.length > 0) {
            payments = saleData.payments.map(p => {
                const amt = parseFloat(p.amount || 0);
                totalPaid += amt;
                return {
                    uuid: p.uuid || PureDB.generateUUID(),
                    method: p.method || 'cash',
                    amount: amt,
                    reference: p.reference || null,
                    status: 'completed',
                    created_at: now,
                };
            });
        } else {
            totalPaid = parseFloat(saleData.payment_amount || totals.total);
            payments = [{
                uuid: PureDB.generateUUID(),
                method: saleData.payment_method || 'cash',
                amount: totalPaid,
                reference: saleData.card_reference || saleData.reference || null,
                status: 'completed',
                created_at: now,
            }];
        }

        const changeAmount = Math.max(0, totalPaid - totals.total);
        const dueAmount = Math.max(0, totals.total - totalPaid);

        const isRealCustomerId = saleData.customer_id && Number(saleData.customer_id) < 10000000000;
        const order = {
            uuid: uuid,
            order_number: orderNumber,
            store_id: _storeConfig?.store_id || null,
            user_id: _storeConfig?.user_id || null,
            customer_id: isRealCustomerId ? saleData.customer_id : (saleData.customer?.id && Number(saleData.customer.id) < 10000000000 ? saleData.customer.id : null),
            customer_uuid: saleData.customer_uuid || (saleData.customer && saleData.customer.uuid) || null,
            customer_name: saleData.customer_name || (saleData.customer && saleData.customer.name) || null,
            customer_phone: saleData.customer_phone || (saleData.customer && saleData.customer.phone) || null,
            customer_email: saleData.customer_email || (saleData.customer && saleData.customer.email) || null,
            status: 'completed',
            order_type: saleData.order_type || 'sale',
            subtotal: totals.subtotal,
            tax_amount: totals.tax,
            discount_amount: totals.discount,
            total_amount: totals.total,
            paid_amount: totalPaid,
            change_amount: changeAmount,
            due_amount: dueAmount,
            profit: totals.profit,
            discount_type: saleData.discount_type || null,
            discount_value: saleData.discount_value || 0,
            coupon_code: saleData.coupon_code || null,
            notes: saleData.notes || '',
            table_number: saleData.table_number || null,
            source: 'offline',
            device_id: localStorage.getItem('purepos_device_id') || _storeConfig?.device_id || null,
            sync_status: 'local',
            completed_at: now,
            created_at: now,
            items: itemsWithMovementUUIDs,
            payments: payments,
        };

        // 1. Save order to IndexedDB
        await PureDB.saveOfflineOrder(order);

        // 2. Deduct stock locally in IndexedDB
        for (const item of itemsWithMovementUUIDs) {
            if (item.product_id) {
                await PureDB.updateProductStock(item.product_id, -item.quantity);
            }
        }

        // 3. Queue into sync queue (idempotent via UUID)
        const alreadyQueued = await PureDB.syncQueueHasUUID(uuid);
        if (!alreadyQueued) {
            await PureDB.addToSyncQueue('order', uuid, 'create', {
                items: itemsWithMovementUUIDs,
                payments: order.payments,
                customer_id: order.customer_id,
                customer_uuid: order.customer_uuid,
                customer_name: order.customer_name || null,
                customer_phone: order.customer_phone || null,
                customer_email: order.customer_email || null,
                discount_type: order.discount_type,
                discount_value: order.discount_value,
                notes: order.notes,
                table_number: order.table_number,
                order_type: order.order_type,
                source: 'offline',
            });
        }

        console.log('[OfflinePOS] Sale processed offline:', orderNumber, uuid);

        if (typeof PureSync !== 'undefined' && PureSync.isOnline && PureSync.isOnline()) {
            setTimeout(() => PureSync.pushAll(), 1000);
        }

        return order;
    }

    // ── Offline Refund & Return Processing ───────────────────────

    /**
     * Process an offline refund / return with validation.
     * Restores product stock in IndexedDB, generates return order with UUID,
     * updates original order status, and queues return transaction for sync.
     */
    async function processRefund(refundData) {
        const originalUuid = refundData.order_uuid;
        if (!originalUuid) {
            throw new Error('Original order UUID is required for refund.');
        }

        const originalOrder = await PureDB.get('orders', originalUuid);
        if (!originalOrder) {
            throw new Error('Original order not found in local records.');
        }

        if (originalOrder.status === 'refunded') {
            throw new Error('This order has already been fully refunded.');
        }

        const refundItems = refundData.items || [];
        if (refundItems.length === 0) {
            throw new Error('No items specified for refund.');
        }

        // Validate quantities against original order items
        for (const item of refundItems) {
            const origItem = (originalOrder.items || []).find(i => i.product_id == item.product_id);
            if (!origItem) {
                throw new Error(`Product ID ${item.product_id} was not in the original order.`);
            }
            if (parseFloat(item.quantity) > parseFloat(origItem.quantity)) {
                throw new Error(`Refund quantity (${item.quantity}) cannot exceed purchased quantity (${origItem.quantity}) for ${origItem.product_name || 'item'}.`);
            }
        }

        const uuid = PureDB.generateUUID();
        const paymentUuid = PureDB.generateUUID();
        const now = new Date().toISOString();
        const refundNumber = _generateOrderNumber('RET');

        let refundSubtotal = 0;
        let refundTax = 0;

        const returnItems = refundItems.map(item => {
            const qty = parseFloat(item.quantity);
            const price = parseFloat(item.unit_price || item.price || 0);
            const taxRate = parseFloat(item.tax_rate || 0);
            const lineSubtotal = qty * price;
            const lineTax = (lineSubtotal * taxRate) / 100;
            refundSubtotal += lineSubtotal;
            refundTax += lineTax;

            return {
                product_id: item.product_id,
                product_name: item.name || item.product_name,
                sku: item.sku || '',
                quantity: qty,
                unit_price: price,
                tax_rate: taxRate,
                tax_amount: lineTax,
                discount_amount: 0,
                total_price: lineSubtotal + lineTax,
                cost_price: parseFloat(item.cost_price || 0),
                stock_movement_uuid: PureDB.generateUUID(),
            };
        });

        const refundTotal = refundSubtotal + refundTax;

        const returnOrder = {
            uuid: uuid,
            order_number: refundNumber,
            reference_uuid: originalUuid,
            reference_order_number: originalOrder.order_number,
            store_id: _storeConfig?.store_id || originalOrder.store_id || null,
            user_id: _storeConfig?.user_id || originalOrder.user_id || null,
            customer_id: originalOrder.customer_id || null,
            customer_name: originalOrder.customer_name || null,
            status: 'completed',
            order_type: 'return',
            subtotal: _round(refundSubtotal),
            tax_amount: _round(refundTax),
            discount_amount: 0,
            total_amount: _round(refundTotal),
            paid_amount: _round(refundTotal),
            change_amount: 0,
            due_amount: 0,
            profit: 0,
            notes: refundData.reason || 'Offline Customer Return',
            source: 'offline',
            device_id: localStorage.getItem('purepos_device_id') || null,
            sync_status: 'local',
            completed_at: now,
            created_at: now,
            items: returnItems,
            payments: [{
                uuid: paymentUuid,
                method: refundData.payment_method || 'cash',
                amount: refundTotal,
                status: 'completed',
                created_at: now,
            }],
        };

        // 1. Save return order in IndexedDB
        await PureDB.saveOfflineOrder(returnOrder);

        // 2. Restore stock locally (+quantity)
        for (const item of returnItems) {
            if (item.product_id) {
                await PureDB.updateProductStock(item.product_id, item.quantity);
            }
        }

        // 3. Mark original order status in IndexedDB
        originalOrder.status = 'refunded';
        originalOrder.refund_order_uuid = uuid;
        originalOrder.refunded_at = now;
        await PureDB.put('orders', originalOrder);

        // 4. Queue return order into sync queue
        await PureDB.addToSyncQueue('order', uuid, 'create', {
            items: returnItems,
            payments: returnOrder.payments,
            customer_id: returnOrder.customer_id,
            order_type: 'return',
            reference_uuid: originalUuid,
            notes: returnOrder.notes,
            source: 'offline',
        });

        console.log('[OfflinePOS] Refund processed offline:', refundNumber, uuid);

        if (typeof PureSync !== 'undefined' && PureSync.isOnline && PureSync.isOnline()) {
            setTimeout(() => PureSync.pushAll(), 1000);
        }

        return returnOrder;
    }

    // ── Offline Bill (Pending Payment) ───────────────────────────

    async function createBill(billData) {
        const uuid = PureDB.generateUUID();
        const now = new Date().toISOString();
        const orderNumber = _generateOrderNumber('BILL');
        const totals = _calculateTotals(billData.items, billData);

        const itemsWithMovementUUIDs = billData.items.map(item => {
            const quantity = parseFloat(item.quantity);
            const unitPrice = parseFloat(item.price || item.unit_price);
            const discountAmount = parseFloat(item.discount || item.discount_amount || 0);
            const totalPrice = Math.max(0, (quantity * unitPrice) - discountAmount);

            return {
                product_id: item.product_id,
                variant_id: item.variant_id || null,
                product_name: item.name || item.product_name,
                quantity: quantity,
                unit_price: unitPrice,
                discount_amount: discountAmount,
                tax_amount: parseFloat(item.tax || item.tax_amount || 0),
                total_price: totalPrice,
                stock_movement_uuid: PureDB.generateUUID(),
            };
        });

        const isRealBillCustomerId = billData.customer_id && Number(billData.customer_id) < 10000000000;
        const order = {
            uuid: uuid,
            order_number: orderNumber,
            store_id: _storeConfig?.store_id || null,
            user_id: _storeConfig?.user_id || null,
            customer_id: isRealBillCustomerId ? billData.customer_id : null,
            customer_uuid: billData.customer_uuid || null,
            customer_name: billData.customer_name || null,
            customer_phone: billData.customer_phone || null,
            customer_email: billData.customer_email || null,
            status: 'pending',
            order_type: 'sale',
            subtotal: totals.subtotal,
            tax_amount: totals.tax,
            discount_amount: totals.discount,
            total_amount: totals.total,
            paid_amount: 0,
            change_amount: 0,
            due_amount: totals.total,
            profit: totals.profit,
            discount_type: billData.discount_type || null,
            discount_value: billData.discount_value || 0,
            coupon_code: billData.coupon_code || null,
            notes: billData.notes || '',
            table_number: billData.table_number || null,
            source: 'offline',
            device_id: localStorage.getItem('purepos_device_id'),
            sync_status: 'local',
            created_at: now,
            items: itemsWithMovementUUIDs,
            payments: [],
        };

        await PureDB.saveOfflineOrder(order);

        const alreadyQueued = await PureDB.syncQueueHasUUID(uuid);
        if (!alreadyQueued) {
            await PureDB.addToSyncQueue('order', uuid, 'create', {
                items: itemsWithMovementUUIDs,
                customer_id: order.customer_id,
                customer_uuid: order.customer_uuid,
                customer_name: order.customer_name,
                customer_phone: order.customer_phone,
                customer_email: order.customer_email,
                discount_type: order.discount_type,
                discount_value: order.discount_value,
                coupon_code: order.coupon_code,
                notes: order.notes,
                table_number: order.table_number,
                status: 'pending',
                source: 'offline',
            });
        }

        return order;
    }

    // ── Offline Held Orders ──────────────────────────────────────

    async function holdOrder(holdData) {
        const uuid = holdData.uuid || PureDB.generateUUID();
        const now = new Date().toISOString();
        const orderNumber = _generateOrderNumber('HOLD');
        const totals = _calculateTotals(holdData.items, holdData);

        const isRealHoldCustomerId = holdData.customer_id && Number(holdData.customer_id) < 10000000000;
        const order = {
            uuid: uuid,
            order_number: orderNumber,
            store_id: _storeConfig?.store_id || null,
            user_id: _storeConfig?.user_id || null,
            customer_id: isRealHoldCustomerId ? holdData.customer_id : null,
            customer_uuid: holdData.customer_uuid || null,
            customer_name: holdData.customer_name || null,
            status: 'on_hold',
            order_type: 'sale',
            subtotal: totals.subtotal,
            tax_amount: totals.tax,
            discount_amount: totals.discount,
            total_amount: totals.total,
            paid_amount: 0,
            change_amount: 0,
            due_amount: totals.total,
            discount_type: holdData.discount_type || null,
            discount_value: holdData.discount_value || 0,
            coupon_code: holdData.coupon_code || holdData.couponCode || null,
            notes: holdData.notes || '',
            table_number: holdData.table_number || null,
            source: 'offline',
            device_id: localStorage.getItem('purepos_device_id'),
            sync_status: 'local',
            created_at: now,
            items: holdData.items.map(item => ({
                product_id: item.product_id,
                variant_id: item.variant_id || null,
                product_name: item.name || item.product_name,
                sku: item.sku || '',
                barcode: item.barcode || '',
                unit: item.unit || 'pcs',
                quantity: parseFloat(item.quantity),
                unit_price: parseFloat(item.price || item.unit_price),
                discount_amount: parseFloat(item.discount || item.discount_amount || 0),
                tax_rate: parseFloat(item.tax_rate || 0),
                tax_amount: parseFloat(item.tax || item.tax_amount || 0),
                total_price: parseFloat(item.quantity) * parseFloat(item.price || item.unit_price),
                cost_price: parseFloat(item.cost_price || 0),
            })),
            payments: [],
        };

        await PureDB.saveHeldOrder(order);
        return order;
    }

    async function getHeldOrders() {
        return await PureDB.getHeldOrders();
    }

    async function retrieveHeldOrder(uuid) {
        if (!uuid) return null;
        return await PureDB.get('held_orders', uuid);
    }

    async function deleteHeldOrder(uuid) {
        return await PureDB.removeHeldOrder(uuid);
    }

    // ── Offline Customers ────────────────────────────────────────

    async function createCustomer(customerData) {
        return await PureDB.createCustomerOffline(customerData);
    }

    async function searchCustomers(query) {
        return await PureDB.searchCustomers(query);
    }

    // ── Offline Sales History & Reports ──────────────────────────

    async function getSalesHistory(limit = 50, filters = {}) {
        return await PureDB.getSalesHistory(limit, filters);
    }

    async function getDailyReport(dateStr = null) {
        return await PureDB.getDailyReport(dateStr);
    }

    // ── Offline Register Operations ──────────────────────────────

    async function openRegister(openingBalance, notes = '') {
        return await PureDB.openRegisterOffline({
            opening_balance: openingBalance,
            notes: notes,
            user_id: _storeConfig?.user_id,
        });
    }

    async function closeRegister(actualCash, notes = '') {
        return await PureDB.closeRegisterOffline(actualCash, notes);
    }

    async function getCurrentRegister() {
        return await PureDB.getActiveRegister();
    }

    // ── Receipt Generation & Printing ────────────────────────────

    /**
     * Generate HTML thermal receipt (supports thermal-58, thermal-80, split payments, refunds).
     */
    function generateReceipt(order, format = 'thermal-80') {
        const store = _storeConfig || {};
        const currency = store.currency_symbol || 'Rs.';
        const now = new Date(order.created_at || Date.now());
        const width = format === 'thermal-58' ? '220px' : '300px';
        const fontSize = format === 'thermal-58' ? '11px' : '12px';
        const isReturn = order.order_type === 'return';

        let html = `
        <div style="font-family: 'Courier New', monospace; max-width: ${width}; margin: 0 auto; padding: 8px; font-size: ${fontSize}; color: #000; line-height: 1.3;">
            <div style="text-align: center; margin-bottom: 8px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: bold;">${store.store_name || 'POS Store'}</h3>
                ${store.address ? `<p style="margin: 2px 0; font-size: 10px;">${store.address}</p>` : ''}
                ${store.phone ? `<p style="margin: 2px 0; font-size: 10px;">Tel: ${store.phone}</p>` : ''}
                ${isReturn ? `<div style="margin: 5px 0; font-weight: bold; font-size: 13px; border: 1px solid #000; padding: 2px;">*** REFUND / RETURN ***</div>` : ''}
                <hr style="border: none; border-top: 1px dashed #333; margin: 6px 0;">
            </div>

            <div style="margin-bottom: 5px; font-size: 11px;">
                <div><strong>Order:</strong> ${order.order_number}</div>
                <div><strong>Date:</strong> ${now.toLocaleDateString()} ${now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                ${order.reference_order_number ? `<div><strong>Ref Order:</strong> ${order.reference_order_number}</div>` : ''}
                ${order.customer_name ? `<div><strong>Customer:</strong> ${order.customer_name}</div>` : ''}
                ${order.table_number ? `<div><strong>Table:</strong> ${order.table_number}</div>` : ''}
            </div>

            <hr style="border: none; border-top: 1px dashed #333; margin: 6px 0;">

            <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="text-align: left; padding: 2px 0;">Item</th>
                        <th style="text-align: center; padding: 2px 0;">Qty</th>
                        <th style="text-align: right; padding: 2px 0;">Price</th>
                        <th style="text-align: right; padding: 2px 0;">Total</th>
                    </tr>
                </thead>
                <tbody>`;

        (order.items || []).forEach(item => {
            const lineTotal = (parseFloat(item.quantity) * parseFloat(item.unit_price)) - parseFloat(item.discount_amount || 0);
            html += `
                    <tr>
                        <td style="padding: 2px 0;">${item.product_name || 'Item'}</td>
                        <td style="text-align: center; padding: 2px 0;">${item.quantity}</td>
                        <td style="text-align: right; padding: 2px 0;">${_formatMoney(item.unit_price, currency)}</td>
                        <td style="text-align: right; padding: 2px 0;">${_formatMoney(lineTotal, currency)}</td>
                    </tr>`;
        });

        html += `
                </tbody>
            </table>

            <hr style="border: none; border-top: 1px dashed #333; margin: 6px 0;">

            <div style="text-align: right; font-size: 11px;">
                <div>Subtotal: ${_formatMoney(order.subtotal, currency)}</div>
                ${order.discount_amount > 0 ? `<div>Discount: -${_formatMoney(order.discount_amount, currency)}</div>` : ''}
                ${order.tax_amount > 0 ? `<div>Tax: ${_formatMoney(order.tax_amount, currency)}</div>` : ''}
                <div style="font-size: 14px; font-weight: bold; margin-top: 4px;">
                    ${isReturn ? 'REFUND TOTAL:' : 'TOTAL:'} ${_formatMoney(order.total_amount, currency)}
                </div>
            </div>

            <hr style="border: none; border-top: 1px dashed #333; margin: 6px 0;">

            <div style="font-size: 11px;">`;

        if (Array.isArray(order.payments) && order.payments.length > 0) {
            order.payments.forEach(p => {
                const methodLabel = (p.method || 'cash').toUpperCase().replace(/_/g, ' ');
                const ref = p.reference ? ` (Ref: ${p.reference})` : '';
                html += `<div>Paid (${methodLabel}${ref}): ${_formatMoney(p.amount, currency)}</div>`;
            });
        } else {
            html += `<div>Paid: ${_formatMoney(order.paid_amount, currency)}</div>`;
        }

        if (order.change_amount > 0) {
            html += `<div>Change: ${_formatMoney(order.change_amount, currency)}</div>`;
        }

        html += `
            </div>

            <div style="text-align: center; margin-top: 10px; font-size: 10px;">
                ${order.sync_status === 'local' ? '<div style="color: #b78103; font-weight: bold;">⚠ OFFLINE TRANSACTION — Saved Locally</div>' : ''}
                <p style="margin: 4px 0;">${store.receipt_footer || 'Thank you for your business!'}</p>
                <p style="margin: 2px 0; color: #666;">Powered by Puretec POS</p>
            </div>
        </div>`;

        return html;
    }

    function printReceipt(order, format = 'thermal-80') {
        const receiptHtml = generateReceipt(order, format);
        const printWindow = window.open('', '_blank', 'width=380,height=650');
        if (printWindow) {
            printWindow.document.write(`
                <!DOCTYPE html>
                <html><head>
                    <meta charset="utf-8">
                    <title>Receipt - ${order.order_number}</title>
                    <style>
                        body { margin: 0; padding: 10px; }
                        @media print {
                            body { margin: 0; padding: 0; }
                            @page { margin: 0; }
                        }
                    </style>
                </head>
                <body onload="window.print(); setTimeout(() => window.close(), 500);">
                    ${receiptHtml}
                </body></html>
            `);
            printWindow.document.close();
        }
    }

    // ── Offline Stock Movements (Standalone Adjustments) ─────────

    async function recordStockMovement(movementData) {
        const uuid = movementData.uuid || PureDB.generateUUID();
        const now = new Date().toISOString();
        const productId = movementData.product_id;
        const quantityChange = parseFloat(movementData.quantity || 0);

        if (!productId || isNaN(quantityChange) || quantityChange === 0) {
            throw new Error('Valid product_id and non-zero quantity change are required.');
        }

        const product = await PureDB.get('products', productId);
        const currentStock = product ? parseFloat(product.stock_quantity || 0) : 0;
        const newStock = Math.max(0, currentStock + quantityChange);

        const movement = {
            uuid: uuid,
            product_id: productId,
            product_uuid: product ? product.uuid : null,
            quantity: quantityChange,
            quantity_before: currentStock,
            quantity_after: newStock,
            type: movementData.type || 'adjustment',
            reference_type: movementData.reference_type || 'offline_adjustment',
            reference_id: movementData.reference_id || null,
            reference_uuid: movementData.reference_uuid || null,
            unit_cost: parseFloat(movementData.unit_cost || product?.cost_price || 0),
            notes: movementData.notes || '',
            device_id: localStorage.getItem('purepos_device_id') || null,
            created_at: now,
        };

        if (product && product.track_stock) {
            await PureDB.updateProductStock(productId, quantityChange);
        }

        const alreadyQueued = await PureDB.syncQueueHasUUID(uuid);
        if (!alreadyQueued) {
            await PureDB.addToSyncQueue('inventory_movement', uuid, 'create', movement);
        }

        console.log('[OfflinePOS] Inventory movement recorded offline:', movement);

        if (typeof PureSync !== 'undefined' && PureSync.isOnline && PureSync.isOnline()) {
            setTimeout(() => PureSync.pushAll(), 1000);
        }

        return movement;
    }

    // ── Calculation Helpers ──────────────────────────────────────

    function _calculateTotals(items, saleData) {
        let subtotal = 0;
        let taxTotal = 0;
        let profitTotal = 0;

        items.forEach(item => {
            const qty = parseFloat(item.quantity);
            const price = parseFloat(item.price || item.unit_price);
            const itemDiscount = parseFloat(item.discount || item.discount_amount || 0);
            const itemTax = parseFloat(item.tax || item.tax_amount || 0);
            const costPrice = parseFloat(item.cost_price || 0);

            subtotal += (qty * price) - itemDiscount;
            taxTotal += itemTax;
            profitTotal += (price - costPrice) * qty - itemDiscount;
        });

        let orderDiscount = 0;
        if (saleData.discount_type && saleData.discount_value) {
            const discountVal = parseFloat(saleData.discount_value);
            if (saleData.discount_type === 'percentage') {
                orderDiscount = (subtotal * discountVal) / 100;
            } else {
                orderDiscount = discountVal;
            }
        }

        const total = Math.max(0, subtotal - orderDiscount + taxTotal);

        return {
            subtotal: _round(subtotal),
            tax: _round(taxTotal),
            discount: _round(orderDiscount),
            total: _round(total),
            profit: _round(profitTotal - orderDiscount),
        };
    }

    function _generateOrderNumber(prefix = 'OFF') {
        const date = new Date();
        const ymd = date.getFullYear().toString().slice(-2) +
                    String(date.getMonth() + 1).padStart(2, '0') +
                    String(date.getDate()).padStart(2, '0');
        const random = Math.random().toString(36).substring(2, 7).toUpperCase();
        return `${prefix}-${ymd}-${random}`;
    }

    function _formatMoney(amount, symbol = 'Rs.') {
        return symbol + ' ' + parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function _round(num) {
        return Math.round((num + Number.EPSILON) * 100) / 100;
    }

    // ── Public API ───────────────────────────────────────────────
    return {
        init,
        saveStoreConfig,
        // Auth
        cacheCurrentUser,
        verifyOfflineUser,
        // Sales & Bills
        processSale,
        processRefund,
        createBill,
        // Held Orders
        holdOrder,
        getHeldOrders,
        retrieveHeldOrder,
        deleteHeldOrder,
        // Customers
        createCustomer,
        searchCustomers,
        // History & Reports
        getSalesHistory,
        getDailyReport,
        // Register Shifts
        openRegister,
        closeRegister,
        getCurrentRegister,
        // Inventory
        recordStockMovement,
        // Receipts & Print
        generateReceipt,
        printReceipt,
    };
})();
