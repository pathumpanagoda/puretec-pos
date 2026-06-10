<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo e($order->order_number); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: <?php echo e($size ?? 'A4'); ?>;
            margin: 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1565c0;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #1565c0;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #1565c0;
            letter-spacing: 2px;
        }

        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-date {
            font-size: 12px;
            color: #888;
            margin-top: 3px;
        }

        /* Info Section */
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-box {
            width: 48%;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #1565c0;
        }

        .info-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #1565c0;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .info-box p {
            margin: 3px 0;
            font-size: 12px;
        }

        .info-box .name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #1565c0;
            color: #fff;
        }

        .items-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: center;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .items-table td {
            padding: 12px 15px;
            font-size: 12px;
        }

        .item-name {
            font-weight: 500;
        }

        .item-sku {
            font-size: 10px;
            color: #888;
        }

        /* Totals Section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-box {
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .total-row.subtotal {
            border-bottom: none;
        }

        .total-row.discount {
            color: #2e7d32;
        }

        .total-row.grand-total {
            border-top: 2px solid #1565c0;
            border-bottom: none;
            font-size: 18px;
            font-weight: 700;
            color: #1565c0;
            padding-top: 12px;
            margin-top: 5px;
        }

        .total-label {
            color: #666;
        }

        .total-value {
            font-weight: 600;
        }

        /* Payment Info */
        .payment-info {
            margin-top: 30px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }

        .payment-info h4 {
            font-size: 12px;
            color: #2e7d32;
            margin-bottom: 10px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 3px 0;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #fff3e0;
            border-radius: 8px;
            border-left: 4px solid #ff9800;
        }

        .notes-section h4 {
            font-size: 12px;
            color: #e65100;
            margin-bottom: 8px;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #888;
            font-size: 11px;
        }

        .invoice-footer .thank-you {
            font-size: 16px;
            color: #1565c0;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .powered-by {
            margin-top: 15px;
            font-size: 10px;
            color: #aaa;
        }

        /* Print Options Panel */
        .print-options {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            width: 280px;
        }

        .print-options h5 {
            margin-bottom: 15px;
            color: #1565c0;
            font-size: 14px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .print-options label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 13px;
        }

        .print-options input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .print-options select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .print-options button {
            width: 100%;
            padding: 12px;
            background: #1565c0;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .print-options button:hover {
            background: #0d47a1;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-refunded { background: #ffebee; color: #c62828; }

        @media print {
            .print-options { display: none !important; }
            body { background: #fff; }
            .invoice-container { padding: 0; max-width: 100%; }
        }

        /* Size Adjustments */
        .size-a3 .invoice-container { max-width: 297mm; }
        .size-a3 .company-name { font-size: 30px; }
        .size-a3 .invoice-title { font-size: 40px; }
        .size-a3 .items-table th, .size-a3 .items-table td { padding: 15px 20px; font-size: 14px; }

        .size-a5 .invoice-container { max-width: 148mm; }
        .size-a5 .company-name { font-size: 18px; }
        .size-a5 .invoice-title { font-size: 24px; }
        .size-a5 .items-table th, .size-a5 .items-table td { padding: 8px 10px; font-size: 10px; }
        .size-a5 .info-box { padding: 10px; }
    </style>
</head>
<body class="size-<?php echo e(strtolower($size ?? 'a4')); ?>">

    <!-- Print Options Panel -->
    <div class="print-options" id="printOptions">
        <h5><i class="bi bi-printer"></i> Print Options</h5>
        <label>
            Paper Size:
        </label>
        <select id="paperSize" onchange="changePaperSize(this.value)">
            <option value="a4" <?php echo e(($size ?? 'a4') == 'a4' ? 'selected' : ''); ?>>A4</option>
            <option value="a5" <?php echo e(($size ?? '') == 'a5' ? 'selected' : ''); ?>>A5</option>
            <option value="a3" <?php echo e(($size ?? '') == 'a3' ? 'selected' : ''); ?>>A3</option>
        </select>
        <label>
            <input type="checkbox" id="showLogo" <?php echo e($order->store?->logo ? 'checked' : 'disabled'); ?>>
            Show Company Logo
        </label>
        <button onclick="printInvoice()">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Invoice
        </button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <?php if($order->store?->logo): ?>
                <img src="<?php echo e(asset('storage/' . $order->store->logo)); ?>" alt="Logo" class="company-logo" id="companyLogo" onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="company-name"><?php echo e($order->store?->name ?? 'Business Name'); ?></div>
                <div class="company-details">
                    <?php if($order->store?->address): ?><?php echo e($order->store->address); ?><br><?php endif; ?>
                    <?php if($order->store?->city): ?><?php echo e($order->store->city); ?>, <?php endif; ?>
                    <?php if($order->store?->country): ?><?php echo e($order->store->country); ?><br><?php endif; ?>
                    <?php if($order->store?->phone): ?>Tel: <?php echo e($order->store->phone); ?><br><?php endif; ?>
                    <?php if($order->store?->email): ?><?php echo e($order->store->email); ?><?php endif; ?>
                </div>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number"># <?php echo e($order->order_number); ?></div>
                <div class="invoice-date"><?php echo e($order->created_at->format('F d, Y')); ?></div>
                <div style="margin-top: 10px;">
                    <?php
                        $statusClass = match($order->status) {
                            'completed' => 'status-completed',
                            'pending' => 'status-pending',
                            'refunded' => 'status-refunded',
                            default => 'status-pending'
                        };
                    ?>
                    <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($order->status)); ?></span>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="invoice-info">
            <div class="info-box">
                <h4>Bill To</h4>
                <?php if($order->customer): ?>
                    <p class="name"><?php echo e($order->customer->name); ?></p>
                    <?php if($order->customer->address): ?><p><?php echo e($order->customer->address); ?></p><?php endif; ?>
                    <?php if($order->customer->phone): ?><p>Tel: <?php echo e($order->customer->phone); ?></p><?php endif; ?>
                    <?php if($order->customer->email): ?><p><?php echo e($order->customer->email); ?></p><?php endif; ?>
                <?php else: ?>
                    <p class="name">Walk-in Customer</p>
                <?php endif; ?>
            </div>
            <div class="info-box">
                <h4>Invoice Details</h4>
                <p><strong>Date:</strong> <?php echo e($order->created_at->format('d/m/Y')); ?></p>
                <p><strong>Time:</strong> <?php echo e($order->created_at->format('h:i A')); ?></p>
                <p><strong>Cashier:</strong> <?php echo e($order->user?->name ?? '-'); ?></p>
                <?php if($order->store?->code): ?>
                <p><strong>BRN:</strong> <?php echo e($order->store->code); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 15%">Qty</th>
                    <th style="width: 15%">Unit Price</th>
                    <th style="width: 20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="item-name"><?php echo e($item->product_name); ?></div>
                        <?php if($item->sku): ?><div class="item-sku">SKU: <?php echo e($item->sku); ?></div><?php endif; ?>
                    </td>
                    <td><?php echo e($item->quantity); ?> <?php echo e($item->unit); ?></td>
                    <td>Rs. <?php echo e(number_format($item->unit_price, 2)); ?></td>
                    <td>Rs. <?php echo e(number_format($item->total, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row subtotal">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">Rs. <?php echo e(number_format($order->subtotal, 2)); ?></span>
                </div>
                <?php if($order->discount_amount > 0): ?>
                <div class="total-row discount">
                    <span class="total-label">Discount</span>
                    <span class="total-value">- Rs. <?php echo e(number_format($order->discount_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
                <?php if($order->tax_amount > 0): ?>
                <div class="total-row">
                    <span class="total-label">Tax</span>
                    <span class="total-value">Rs. <?php echo e(number_format($order->tax_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>TOTAL</span>
                    <span>Rs. <?php echo e(number_format($order->total_amount, 2)); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <?php if($order->payments && $order->payments->count() > 0): ?>
        <div class="payment-info">
            <h4>Payment Information</h4>
            <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="payment-row">
                <span><?php echo e(ucfirst(str_replace('_', ' ', $payment->method))); ?></span>
                <span>Rs. <?php echo e(number_format($payment->amount, 2)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($order->change_amount > 0): ?>
            <div class="payment-row">
                <span>Change Given</span>
                <span>Rs. <?php echo e(number_format($order->change_amount, 2)); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if($order->notes): ?>
        <div class="notes-section">
            <h4>Notes</h4>
            <p><?php echo e($order->notes); ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="thank-you">Thank You for Your Business!</div>
            <p><?php echo e($order->store?->receipt_footer ?? 'We appreciate your purchase.'); ?></p>
            <div class="powered-by">
                Powered by Ceylon POS | Generated on <?php echo e(now()->format('d/m/Y H:i:s')); ?>

            </div>
        </div>
    </div>

    <script>
        function changePaperSize(size) {
            document.body.className = 'size-' + size;
            // Update URL
            const url = new URL(window.location.href);
            url.searchParams.set('size', size);
            window.history.replaceState({}, '', url);
        }

        // Toggle logo visibility
        document.getElementById('showLogo')?.addEventListener('change', function() {
            const logo = document.getElementById('companyLogo');
            if (logo) {
                logo.style.display = this.checked ? 'block' : 'none';
            }
        });

        function printInvoice() {
            document.getElementById('printOptions').style.display = 'none';
            window.print();
            setTimeout(() => {
                document.getElementById('printOptions').style.display = 'block';
            }, 500);
        }

        // Logo is shown by default on invoice - no localStorage check needed
    </script>
</body>
</html>
<?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/orders/invoice.blade.php ENDPATH**/ ?>