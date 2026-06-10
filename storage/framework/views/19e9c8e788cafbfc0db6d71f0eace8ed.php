<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Receipt <?php echo e($order->order_number); ?></title>
<?php $size = request('size', '80'); ?>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; }
body.size-58 { width: 58mm; font-size: 10px; padding: 5px; }
body.size-58 .store-logo { max-width: 80px; max-height: 40px; }
body.size-58 .store-name { font-size: 12px; }
body.size-58 .item-row { margin: 2px 0; font-size: 9px; }
body.size-58 hr { margin: 4px 0; }
body.size-58 .grand-total { font-size: 12px; }
body.size-80 { width: 80mm; }
body.large-format { width: 100%; max-width: 210mm; font-size: 14px; padding: 20px 40px; }
body.large-format .store-logo { max-width: 180px; max-height: 100px; }
body.large-format .store-name { font-size: 24px; }
body.large-format .item-row { margin: 8px 0; }
body.large-format hr { margin: 12px 0; }
body.large-format .grand-total { font-size: 20px; }
.text-center { text-align: center; }
.bold { font-weight: bold; }
hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
.item-row { display: flex; justify-content: space-between; margin: 3px 0; }
.total-row { display: flex; justify-content: space-between; font-weight: bold; }
.grand-total { font-size: 16px; font-weight: bold; }
.branding { font-size: 9px; color: #666; margin-top: 8px; letter-spacing: 0.5px; }
.store-logo { max-width: 120px; max-height: 60px; margin-bottom: 8px; }
.print-options { position: fixed; top: 10px; right: 10px; background: #fff; border: 1px solid #ccc; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; font-family: Arial, sans-serif; }
.print-options label { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; cursor: pointer; font-size: 13px; }
.print-options input[type="checkbox"] { width: 16px; height: 16px; }
.print-options button { width: 100%; padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; margin-top: 8px; }
.print-options button:hover { background: #1d4ed8; }
@media print { body { margin:0; } .branding { color: #888; } .print-options { display: none !important; } }
</style></head>
<body class="size-<?php echo e($size); ?>">

<div class="print-options" id="printOptions">
    <div style="font-weight:bold; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:8px;">Print Options</div>
    <label>
        Paper Size:
    </label>
    <select id="receiptSize" onchange="changeReceiptSize(this.value)" style="width:100%; padding:5px; margin-bottom:8px; border-radius:4px; border:1px solid #ddd;">
        <option value="58" <?php echo e($size == '58' ? 'selected' : ''); ?>>58mm (Small)</option>
        <option value="80" <?php echo e($size == '80' ? 'selected' : ''); ?>>80mm (Standard)</option>
    </select>
    <label>
        <input type="checkbox" id="showLogo" <?php echo e($order->store?->logo ? '' : 'disabled'); ?>>
        Show Business Logo
    </label>
    <label>
        <input type="checkbox" id="largeFormat">
        Large Format (A4/A3)
    </label>
    <button onclick="printReceipt()">Print Receipt</button>
</div>

<div class="text-center">
    <?php if($order->store?->logo): ?>
    <img src="<?php echo e(asset('storage/' . $order->store->logo)); ?>" class="store-logo" id="storeLogo" style="display:none;" alt="Logo" onerror="this.style.display='none'">
    <?php endif; ?>
    <div class="bold store-name" style="font-size:16px"><?php echo e($order->store?->name ?? 'My Store'); ?></div>
    <?php if($order->store?->address): ?><div><?php echo e($order->store->address); ?></div><?php endif; ?>
    <?php if($order->store?->phone): ?><div>Tel: <?php echo e($order->store->phone); ?></div><?php endif; ?>
    <?php if($order->store?->email): ?><div><?php echo e($order->store->email); ?></div><?php endif; ?>
    <?php if($order->store?->receipt_header): ?><div style="margin-top:4px;font-style:italic"><?php echo e($order->store->receipt_header); ?></div><?php endif; ?>
</div>
<hr>
<div>Order: <strong><?php echo e($order->order_number); ?></strong></div>
<div>Date: <?php echo e($order->created_at->format('d/m/Y H:i')); ?></div>
<div>Cashier: <?php echo e($order->user?->name ?? '—'); ?></div>
<?php if($order->customer): ?><div>Customer: <?php echo e($order->customer->name); ?></div><?php endif; ?>
<hr>
<?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div><?php echo e($item->product_name); ?></div>
<div class="item-row"><span><?php echo e($item->quantity); ?> x Rs.<?php echo e(number_format($item->unit_price,2)); ?></span><span>Rs.<?php echo e(number_format($item->total,2)); ?></span></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<hr>
<div class="item-row"><span>Subtotal</span><span>Rs.<?php echo e(number_format($order->subtotal,2)); ?></span></div>
<?php if($order->discount_amount > 0): ?><div class="item-row"><span>Discount</span><span>-Rs.<?php echo e(number_format($order->discount_amount,2)); ?></span></div><?php endif; ?>
<?php if($order->tax_amount > 0): ?><div class="item-row"><span>Tax</span><span>Rs.<?php echo e(number_format($order->tax_amount,2)); ?></span></div><?php endif; ?>
<hr>
<div class="item-row grand-total"><span>TOTAL</span><span>Rs.<?php echo e(number_format($order->total_amount,2)); ?></span></div>
<?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="item-row"><span><?php echo e(ucfirst(str_replace('_',' ',$p->method))); ?></span><span>Rs.<?php echo e(number_format($p->amount,2)); ?></span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($order->change_amount > 0): ?><div class="item-row"><span>Change</span><span>Rs.<?php echo e(number_format($order->change_amount,2)); ?></span></div><?php endif; ?>
<hr>
<div class="text-center">
<p><?php echo e($order->store?->receipt_footer ?? 'Thank you for your business!'); ?></p>
<p class="branding">Ceyloan POS &bull; Powered by Nexfloit</p>
</div>
<script>
function printReceipt() {
    document.getElementById('printOptions').style.display = 'none';
    window.print();
    setTimeout(() => {
        document.getElementById('printOptions').style.display = 'block';
    }, 500);
}

function changeReceiptSize(size) {
    // Remove all size classes
    document.body.classList.remove('size-58', 'size-80', 'large-format');
    // Add new size class
    document.body.classList.add('size-' + size);
    // Update URL
    const url = new URL(window.location.href);
    url.searchParams.set('size', size);
    window.history.replaceState({}, '', url);
    // Save preference
    localStorage.setItem('receiptSize', size);
}

document.getElementById('showLogo').addEventListener('change', function() {
    const logo = document.getElementById('storeLogo');
    if (logo) {
        logo.style.display = this.checked ? 'inline-block' : 'none';
    }
});

document.getElementById('largeFormat').addEventListener('change', function() {
    document.body.classList.toggle('large-format', this.checked);
    // Auto-enable logo for large format
    if (this.checked && document.getElementById('storeLogo')) {
        document.getElementById('showLogo').checked = true;
        document.getElementById('storeLogo').style.display = 'inline-block';
    }
});

// Load saved preferences
const savedLogo = localStorage.getItem('receiptShowLogo') === 'true';
const savedLargeFormat = localStorage.getItem('receiptLargeFormat') === 'true';

if (savedLogo && document.getElementById('storeLogo')) {
    document.getElementById('showLogo').checked = true;
    document.getElementById('storeLogo').style.display = 'inline-block';
}
if (savedLargeFormat) {
    document.getElementById('largeFormat').checked = true;
    document.body.classList.add('large-format');
}

// Save preferences on change
document.getElementById('showLogo').addEventListener('change', function() {
    localStorage.setItem('receiptShowLogo', this.checked);
});
document.getElementById('largeFormat').addEventListener('change', function() {
    localStorage.setItem('receiptLargeFormat', this.checked);
});
</script>
</body></html>
<?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/orders/receipt.blade.php ENDPATH**/ ?>