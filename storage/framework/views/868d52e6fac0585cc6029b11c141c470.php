


<div class="export-buttons">
    
    <div class="dropdown">
        <button class="btn-cpos btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-printer"></i> Print
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">Paper Size</h6></li>
            <li><a class="dropdown-item" href="#" onclick="printReport('a4')"><i class="bi bi-file-earmark"></i> A4 (210 x 297mm)</a></li>
            <li><a class="dropdown-item" href="#" onclick="printReport('a5')"><i class="bi bi-file-earmark"></i> A5 (148 x 210mm)</a></li>
            <li><a class="dropdown-item" href="#" onclick="printReport('a3')"><i class="bi bi-file-earmark"></i> A3 (297 x 420mm)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">Thermal Receipt</h6></li>
            <li><a class="dropdown-item" href="#" onclick="printReport('thermal80')"><i class="bi bi-receipt"></i> 80mm Thermal</a></li>
            <li><a class="dropdown-item" href="#" onclick="printReport('thermal58')"><i class="bi bi-receipt"></i> 58mm Thermal</a></li>
        </ul>
    </div>

    
    <div class="dropdown">
        <button class="btn-cpos btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-file-pdf"></i> PDF
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" onclick="exportPDF('a4')"><i class="bi bi-file-earmark"></i> A4 Size</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportPDF('a5')"><i class="bi bi-file-earmark"></i> A5 Size</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportPDF('a3')"><i class="bi bi-file-earmark"></i> A3 Size</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="exportPDF('thermal80')"><i class="bi bi-receipt"></i> 80mm Receipt</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportPDF('thermal58')"><i class="bi bi-receipt"></i> 58mm Receipt</a></li>
        </ul>
    </div>

    
    <?php if(isset($report)): ?>
    <a href="<?php echo e(route('reports.export', array_merge(['report' => $report, 'format' => 'excel'], $params ?? []))); ?>" class="btn-cpos btn-secondary">
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
    <?php endif; ?>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.export-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.export-buttons .dropdown-menu {
    min-width: 200px;
    padding: 8px 0;
    border-radius: var(--cp-radius-md);
    box-shadow: var(--cp-shadow-lg);
}
.export-buttons .dropdown-item {
    padding: 10px 16px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.export-buttons .dropdown-item:hover {
    background: var(--cp-bg-alt);
}
.export-buttons .dropdown-header {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--cp-text-muted);
    padding: 8px 16px 4px;
}

/* Print Size Styles */
@media print {
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .no-print, .sidebar, .topbar, .export-buttons,
    .page-actions, nav, .cpos-sidebar, #sidebar,
    .sidebar-overlay, .topbar-toggle, .breadcrumb,
    form, .btn-group {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 15px !important;
        width: 100% !important;
    }
    .page-content {
        padding: 0 !important;
    }
    .page-header {
        margin-bottom: 15px !important;
        padding-bottom: 10px !important;
        border-bottom: 2px solid #000 !important;
    }
    .cpos-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 15px !important;
    }
    .stat-card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .table { font-size: 12px !important; }

    /* A4 Default */
    @page {
        size: A4;
        margin: 10mm;
    }
}

/* Specific print sizes - applied via JS */
body.print-a3 { }
body.print-a5 { }
body.print-thermal80 { }
body.print-thermal58 { }

@media print {
    body.print-a3 @page { size: A3; margin: 15mm; }
    body.print-a5 @page { size: A5; margin: 8mm; }
    body.print-thermal80 @page { size: 80mm auto; margin: 3mm; }
    body.print-thermal58 @page { size: 58mm auto; margin: 2mm; }

    /* Thermal receipt styles */
    body.print-thermal80, body.print-thermal58 {
        font-size: 11px !important;
    }
    body.print-thermal80 .stat-card,
    body.print-thermal58 .stat-card {
        padding: 8px !important;
    }
    body.print-thermal80 .cpos-card,
    body.print-thermal58 .cpos-card {
        margin-bottom: 8px !important;
    }
    body.print-thermal80 .row,
    body.print-thermal58 .row {
        flex-direction: column !important;
    }
    body.print-thermal80 .col-md-3,
    body.print-thermal80 .col-lg-6,
    body.print-thermal58 .col-md-3,
    body.print-thermal58 .col-lg-6 {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Print with specific paper size
function printReport(size) {
    // Remove any existing print class
    document.body.classList.remove('print-a4', 'print-a5', 'print-a3', 'print-thermal80', 'print-thermal58');

    // Add the selected print class
    document.body.classList.add('print-' + size);

    // Create style element for page size
    let printStyle = document.getElementById('print-size-style');
    if (!printStyle) {
        printStyle = document.createElement('style');
        printStyle.id = 'print-size-style';
        document.head.appendChild(printStyle);
    }

    // Set page size based on selection
    let pageSize = 'A4';
    let margin = '10mm';
    switch(size) {
        case 'a3': pageSize = 'A3'; margin = '15mm'; break;
        case 'a5': pageSize = 'A5'; margin = '8mm'; break;
        case 'thermal80': pageSize = '80mm 297mm'; margin = '3mm'; break;
        case 'thermal58': pageSize = '58mm 297mm'; margin = '2mm'; break;
        default: pageSize = 'A4'; margin = '10mm';
    }

    printStyle.innerHTML = `@media print { @page { size: ${pageSize}; margin: ${margin}; } }`;

    // Print
    setTimeout(() => {
        window.print();
    }, 100);
}

// Export as PDF (uses browser print to PDF)
function exportPDF(size) {
    // Same as print - user can choose "Save as PDF" in print dialog
    printReport(size);
}
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/components/report-export.blade.php ENDPATH**/ ?>