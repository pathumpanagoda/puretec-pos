<?php $__env->startSection('title', 'Cash Balance Sheet'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('reports.index')); ?>">Reports</a></li>
    <li class="breadcrumb-item active">Cash Balance Sheet</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-print-none">
    <div>
        <h1 class="page-title">Cash Balance Sheet</h1>
        <p class="page-subtitle">Detailed Transaction Ledger</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <button onclick="window.print()" class="btn btn-primary btn-cpos"><i class="bi bi-printer me-2"></i>Print</button>
    </div>
</div>


<div class="cpos-card mb-4 d-print-none">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?php echo e($startDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?php echo e($endDate); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo e(route('reports.cash-book', ['from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Week</a>
                    <a href="<?php echo e(route('reports.cash-book', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Month</a>
                    <a href="<?php echo e(route('reports.cash-book', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()])); ?>" class="btn btn-outline-secondary btn-sm">This Year</a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="cash-book-print">
    <div class="cash-book-header">
        <h2 class="cash-book-title">CASH BALANCE SHEET</h2>
        <div class="cash-book-date"><?php echo e(\Carbon\Carbon::parse($endDate)->format('l, F d, Y')); ?></div>
        <div class="cash-book-business"><?php echo e($store->name ?? 'Business Name'); ?></div>
    </div>

    <table class="cash-book-table">
        <thead>
            <tr>
                <th style="width: 80px;">INVOICE NO</th>
                <th style="width: 80px;">VOU. NO</th>
                <th>DESCRIPTION</th>
                <th style="width: 120px;" class="text-end">DEBIT</th>
                <th style="width: 120px;" class="text-end">CREDIT</th>
                <th style="width: 120px;">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            
            <tr class="bf-row">
                <td colspan="2"></td>
                <td><strong><?php echo e(\Carbon\Carbon::parse($startDate)->format('d M Y')); ?> B/F Balance</strong></td>
                <td class="text-end"><?php echo e($bfBalance < 0 ? number_format(abs($bfBalance), 2) : ''); ?></td>
                <td class="text-end"><?php echo e($bfBalance >= 0 ? number_format($bfBalance, 2) : ''); ?></td>
                <td></td>
            </tr>

            <?php $serialNo = 1; ?>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="<?php echo e($txn['type']); ?>">
                <td><?php echo e($txn['invoice_no']); ?></td>
                <td><?php echo e($txn['voucher_no']); ?></td>
                <td><?php echo e($txn['description']); ?></td>
                <td class="text-end"><?php echo e($txn['debit'] > 0 ? number_format($txn['debit'], 2) : ''); ?></td>
                <td class="text-end"><?php echo e($txn['credit'] > 0 ? number_format($txn['credit'], 2) : ''); ?></td>
                <td><?php echo e($txn['remarks']); ?></td>
            </tr>
            <?php $serialNo++; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No transactions found for this period</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-center fw-bold">TOTAL</td>
                <td class="text-end fw-bold"><?php echo e(number_format($totalDebit, 2)); ?></td>
                <td class="text-end fw-bold"><?php echo e(number_format($totalCredit, 2)); ?></td>
                <td></td>
            </tr>
            <tr class="closing-row">
                <td colspan="3" class="text-center fw-bold">CLOSING BALANCE</td>
                <td class="text-end fw-bold"><?php echo e($closingBalance < 0 ? number_format(abs($closingBalance), 2) : ''); ?></td>
                <td class="text-end fw-bold"><?php echo e($closingBalance >= 0 ? number_format($closingBalance, 2) : ''); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="cash-book-footer">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">PREPARED BY</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">CHECKED BY</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">APPROVED BY</div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
/* Screen Styles */
.cash-book-print {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cash-book-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #000;
}

.cash-book-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 5px 0;
    letter-spacing: 2px;
}

.cash-book-date {
    font-size: 14px;
    color: #666;
}

.cash-book-business {
    font-size: 16px;
    font-weight: 600;
    margin-top: 5px;
}

.cash-book-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.cash-book-table th,
.cash-book-table td {
    border: 1px solid #333;
    padding: 8px 10px;
    vertical-align: middle;
}

.cash-book-table th {
    background: #f5f5f5;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
}

.cash-book-table tbody tr:hover {
    background: #f9f9f9;
}

.cash-book-table .bf-row {
    background: #e8f4fc;
    font-weight: 600;
}

.cash-book-table .sale {
    background: #f0fff4;
}

.cash-book-table .expense {
    background: #fff5f5;
}

.cash-book-table .income {
    background: #f0f9ff;
}

.cash-book-table .totals-row {
    background: #fef3c7;
    font-size: 14px;
}

.cash-book-table .closing-row {
    background: #dbeafe;
    font-size: 14px;
}

.cash-book-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
    padding-top: 20px;
}

.signature-box {
    text-align: center;
    width: 200px;
}

.signature-line {
    border-bottom: 1px solid #333;
    height: 40px;
    margin-bottom: 5px;
}

.signature-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }

    .cash-book-print,
    .cash-book-print * {
        visibility: visible;
    }

    .cash-book-print {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
        box-shadow: none;
        border-radius: 0;
    }

    .cash-book-table th,
    .cash-book-table td {
        padding: 6px 8px;
        font-size: 11px;
    }

    .cash-book-table th {
        font-size: 10px;
    }

    .cash-book-table .sale {
        background: #f0fff4 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .cash-book-table .expense {
        background: #fff5f5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .cash-book-table .totals-row,
    .cash-book-table .closing-row,
    .cash-book-table .bf-row {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    @page {
        size: A4;
        margin: 10mm;
    }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/reports/cash-book.blade.php ENDPATH**/ ?>