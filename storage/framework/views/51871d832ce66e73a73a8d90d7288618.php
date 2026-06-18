
<?php $__env->startSection('title', 'Price Tags'); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('products.index')); ?>">Products</a></li>
<li class="breadcrumb-item active">Price Tags</li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div><h2 class="page-title">Price Tags</h2><p class="page-subtitle"><?php echo e($products->count()); ?> product(s) selected</p></div>
    <div class="page-actions">
        <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary btn-cpos me-2">
            <i class="bi bi-arrow-left me-1"></i>Back to Products
        </a>
        <button type="button" class="btn btn-primary btn-cpos" onclick="printPriceTags()">
            <i class="bi bi-printer me-2"></i>Print Price Tags
        </button>
    </div>
</div>

<div class="row g-3 price-tags-page">
    <!-- Settings Panel -->
    <div class="col-lg-3">
        <!-- Product Filter -->
        <div class="card cpos-card settings-card mb-2">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Products</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 compact-field">
                        <label class="form-label fw-600">Category</label>
                        <select id="filterCategory" class="form-select" onchange="filterProducts()">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 compact-field">
                        <label class="form-label fw-600">Search</label>
                        <input type="text" id="filterSearch" class="form-control" placeholder="Search..." oninput="filterProducts()">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted"><span id="visibleCount"><?php echo e($products->count()); ?></span> visible | <span id="selectedCount"><?php echo e($products->count()); ?></span> selected</small>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="selectAllVisible()">Select Visible</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card cpos-card settings-card mb-2">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Tag Settings</h6>
            </div>
            <div class="card-body">
                <!-- Tag Size -->
                <div class="compact-field mb-2">
                    <label class="form-label fw-600">Tag Size Preset</label>
                    <select id="tagSize" class="form-select" onchange="onPresetChange()">
                        <option value="sticker38x25" selected>Sticker (38mm × 25mm)</option>
                        <option value="small">Small (50mm × 30mm)</option>
                        <option value="medium">Medium (60mm × 40mm)</option>
                        <option value="large">Large (80mm × 50mm)</option>
                        <option value="shelf">Shelf Label (100mm × 30mm)</option>
                        <option value="custom">Custom (Manual)</option>
                    </select>
                </div>

                <div class="row g-2">
                    <!-- Columns -->
                    <div class="col-6 compact-field">
                        <label class="form-label fw-600">Columns</label>
                        <select id="tagColumns" class="form-select" onchange="updatePreview()">
                            <option value="1">1 Column</option>
                            <option value="2" selected>2 Columns</option>
                            <option value="3">3 Columns</option>
                            <option value="4">4 Columns</option>
                            <option value="5">5 Columns</option>
                        </select>
                    </div>

                    <!-- Copies per product -->
                    <div class="col-6 compact-field">
                        <label class="form-label fw-600">Copies</label>
                        <div class="input-group input-group-sm copies-control">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustCopies(-1)" title="Decrease copies">-</button>
                            <input type="number" id="tagCopies" class="form-control text-center" value="1" min="1" max="100" onchange="updatePreview()" oninput="updatePreview()">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustCopies(1)" title="Increase copies">+</button>
                        </div>
                    </div>
                </div>

                <!-- Paper Size -->
                <div class="compact-field mt-2">
                    <label class="form-label fw-600">Paper Size</label>
                    <select id="paperSize" class="form-select" onchange="onPaperSizeChange()">
                        <option value="sticker_2col_38x25" selected>Sticker Sheet 2-Col (38×25mm)</option>
                        <option value="single_roll">Roll Paper (Single Column)</option>
                        <option value="a4">A4</option>
                        <option value="letter">Letter</option>
                        <option value="a5">A5</option>
                        <option value="custom">Custom Size</option>
                    </select>
                </div>

                <!-- Advanced Accordion Toggle Button -->
                <div class="mt-2 pt-1 text-center border-top border-secondary">
                    <button class="btn btn-sm btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#customLayoutCollapse" aria-expanded="false" aria-controls="customLayoutCollapse">
                        <i class="bi bi-sliders me-1"></i> Manual Layout & Sizes <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                </div>

                <!-- Collapsible Section -->
                <div class="collapse mt-2" id="customLayoutCollapse">
                    <!-- Manual Tag Dimensions -->
                    <div class="border-top border-secondary pt-2 mt-2">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Tag Dimensions (mm)</h6>
                        <div class="row g-2">
                            <div class="col-6 compact-field">
                                <label class="form-label">Tag Width</label>
                                <input type="number" id="customTagWidth" class="form-control form-control-sm" step="0.1" value="38" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Tag Height</label>
                                <input type="number" id="customTagHeight" class="form-control form-control-sm" step="0.1" value="25" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-12 compact-field">
                                <label class="form-label">Layout Type</label>
                                <select id="layoutType" class="form-select form-select-sm" onchange="onManualDimensionChange()">
                                    <option value="standard" selected>Standard (Stacked)</option>
                                    <option value="shelf">Shelf Label (Row)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Paper Size Dimensions (Conditional) -->
                    <div id="customPaperDimContainer" class="border-top border-secondary pt-2 mt-2" style="display: none;">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Paper Dimensions (mm)</h6>
                        <div class="row g-2">
                            <div class="col-6 compact-field">
                                <label class="form-label">Paper Width</label>
                                <input type="number" id="customPaperWidth" class="form-control form-control-sm" step="0.1" value="80" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Paper Height</label>
                                <input type="number" id="customPaperHeight" class="form-control form-control-sm" step="0.1" value="25" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                        </div>
                    </div>

                    <!-- Page Margins -->
                    <div class="border-top border-secondary pt-2 mt-2">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Page Margins (mm)</h6>
                        <div class="row g-2">
                            <div class="col-3 compact-field">
                                <label class="form-label">Top</label>
                                <input type="number" id="marginTop" class="form-control form-control-sm" step="0.1" value="0" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-3 compact-field">
                                <label class="form-label">Bottom</label>
                                <input type="number" id="marginBottom" class="form-control form-control-sm" step="0.1" value="0" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-3 compact-field">
                                <label class="form-label">Left</label>
                                <input type="number" id="marginLeft" class="form-control form-control-sm" step="0.1" value="1.5" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-3 compact-field">
                                <label class="form-label">Right</label>
                                <input type="number" id="marginRight" class="form-control form-control-sm" step="0.1" value="1.5" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                        </div>
                    </div>

                    <!-- Gaps between tags -->
                    <div class="border-top border-secondary pt-2 mt-2">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Gaps / Spacing (mm)</h6>
                        <div class="row g-2">
                            <div class="col-6 compact-field">
                                <label class="form-label">Horiz. Gap</label>
                                <input type="number" id="gapHorizontal" class="form-control form-control-sm" step="0.1" value="3" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Vert. Gap</label>
                                <input type="number" id="gapVertical" class="form-control form-control-sm" step="0.1" value="2" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                        </div>
                    </div>

                    <!-- Font Sizes -->
                    <div class="border-top border-secondary pt-2 mt-2">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Font Sizes (pt)</h6>
                        <div class="row g-2">
                            <div class="col-6 compact-field">
                                <label class="form-label">Store Font</label>
                                <input type="number" id="fontStore" class="form-control form-control-sm" step="0.5" value="5.5" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Name Font</label>
                                <input type="number" id="fontName" class="form-control form-control-sm" step="0.5" value="7" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">SKU Font</label>
                                <input type="number" id="fontSku" class="form-control form-control-sm" step="0.5" value="5.5" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Price Font</label>
                                <input type="number" id="fontPrice" class="form-control form-control-sm" step="0.5" value="13" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                        </div>
                    </div>

                    <!-- Barcode Settings -->
                    <div class="border-top border-secondary pt-2 mt-2">
                        <h6 class="text-primary font-size-12 fw-bold mb-2">Barcode Settings</h6>
                        <div class="row g-2">
                            <div class="col-12 compact-field">
                                <label class="form-label">Barcode Symbology</label>
                                <select id="barcodeSymbology" class="form-select form-select-sm" onchange="onManualDimensionChange()">
                                    <option value="auto" selected>Auto Detect</option>
                                    <option value="CODE128">Code 128</option>
                                    <option value="EAN13">EAN-13</option>
                                    <option value="EAN8">EAN-8</option>
                                    <option value="UPC">UPC-A</option>
                                    <option value="CODE39">Code 39</option>
                                </select>
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Bar Width</label>
                                <input type="number" id="barcodeBarWidth" class="form-control form-control-sm" step="0.1" value="0.8" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                            <div class="col-6 compact-field">
                                <label class="form-label">Bar Height (mm)</label>
                                <input type="number" id="barcodeBarHeight" class="form-control form-control-sm" step="1" value="14" onchange="onManualDimensionChange()" oninput="onManualDimensionChange()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card cpos-card settings-card mb-2">
            <div class="card-header modal-cpos-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Display Options</h6>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-light" onclick="setDisplayOptions(true)">All</button>
                    <button type="button" class="btn btn-outline-light" onclick="setDisplayOptions(false)">None</button>
                </div>
            </div>
            <div class="card-body">
                <div class="switch-grid">
                <div class="form-check form-switch">
                    <input type="checkbox" id="showStoreName" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showStoreName">Store Name</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showProductName" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showProductName">Product Name</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showSku" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showSku">SKU / Item Code</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showBarcode" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBarcode">Barcode</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showBarcodeNumber" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBarcodeNumber">Barcode Number</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showPrice" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showPrice">Selling Price</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showCategory" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showCategory">Category</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showUnit" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showUnit">Unit</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showBorder" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBorder">Tag Border</label>
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" id="showDate" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showDate">Print Date</label>
                </div>
                </div>
            </div>
        </div>

        <!-- Custom Store Name -->
        <div class="card cpos-card settings-card mb-2">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-shop me-2"></i>Store Info</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-8 compact-field">
                        <label class="form-label fw-600">Store Name</label>
                        <input type="text" id="customStoreName" class="form-control" value="<?php echo e($store->name ?? 'PUREPOS'); ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                    <div class="col-4 compact-field">
                        <label class="form-label fw-600">Currency</label>
                        <input type="text" id="currencySymbol" class="form-control" value="<?php echo e($store->currency_symbol ?? 'Rs.'); ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Panel -->
    <div class="col-lg-9">
        <div class="card cpos-card">
            <div class="card-header modal-cpos-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Price Tag Preview</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="zoomPreview(-1)">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <span class="text-light small d-flex align-items-center" id="zoomLevel">100%</span>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="zoomPreview(1)">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-3" style="background: #f5f5f5; min-height: 400px; max-height: 80vh; overflow-y: auto;" id="previewContainer">
                <div id="priceTagPreview" class="price-tag-grid">
                    <!-- Tags will be rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden print area -->
<div id="printArea" style="display: none;"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ═══════════════════════════════════════════════════════════════
   SETTINGS PANEL STYLES
   ═══════════════════════════════════════════════════════════════ */
.price-tag-grid {
    display: grid;
    grid-template-columns: repeat(3, max-content);
    gap: 8px;
    justify-content: flex-start;
    transform-origin: top left;
}

.price-tags-page .settings-card {
    border-radius: 8px;
}
.price-tags-page .card-header.modal-cpos-header {
    background: var(--cp-surface-alt, #141414);
    border-bottom: 1px solid var(--cp-border, #2a2a2a);
    color: var(--cp-text, #f0f0f0);
}
.price-tags-page .card-header.modal-cpos-header h6 {
    color: var(--cp-text, #f0f0f0);
    font-weight: 700;
}
.price-tags-page .card-header.modal-cpos-header i {
    color: var(--cp-primary, #c9a227);
}
.price-tags-page .settings-card .card-header {
    padding: 8px 12px;
}
.price-tags-page .settings-card .card-body {
    padding: 10px 12px;
}
.price-tags-page .compact-field .form-label {
    font-size: 12px;
    line-height: 1.2;
    margin-bottom: 5px;
}
.price-tags-page .settings-card .form-select,
.price-tags-page .settings-card .form-control,
.price-tags-page .settings-card .btn {
    font-size: 13px;
}
.price-tags-page .settings-card .form-select,
.price-tags-page .settings-card .form-control {
    background-color: var(--cp-surface, #1a1a1a);
    border-color: var(--cp-border-light, #333333);
    color: var(--cp-text, #f0f0f0);
    min-height: 34px;
    padding: 5px 9px;
}
.price-tags-page .settings-card .form-control::placeholder {
    color: var(--cp-text-muted, #888888);
    opacity: 1;
}
.price-tags-page .settings-card .text-muted {
    color: var(--cp-text-muted, #b0b0b0) !important;
}
.price-tags-page .settings-card .btn-outline-secondary {
    border-color: var(--cp-border-light, #555555);
    color: var(--cp-text, #f0f0f0);
}
.price-tags-page .settings-card .btn-outline-secondary:hover {
    background: var(--cp-border-light, #333333);
    color: var(--cp-text, #ffffff);
}
.price-tags-page .settings-card .btn-outline-primary {
    border-color: var(--cp-primary, #c9a227);
    color: var(--cp-primary, #c9a227);
}
.price-tags-page .settings-card .btn-outline-primary:hover {
    background: var(--cp-primary, #c9a227);
    color: #0d0d0d;
}
.price-tags-page .settings-card .btn-outline-light {
    border-color: rgba(240, 240, 240, 0.65);
    color: var(--cp-text, #f0f0f0);
}
.price-tags-page .settings-card .btn-outline-light:hover {
    background: var(--cp-text, #f0f0f0);
    color: var(--cp-bg, #0d0d0d);
}
.price-tags-page .copies-control .btn {
    width: 32px;
    padding-left: 0;
    padding-right: 0;
}
.switch-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 7px 10px;
}

/* Form switches in settings */
.price-tags-page .card-body .form-check.form-switch {
    min-height: 20px;
    padding-left: 2.35em;
}
.price-tags-page .card-body .form-check.form-switch .form-check-input {
    width: 32px;
    height: 16px;
    margin-left: -2.35em;
    cursor: pointer;
}
.price-tags-page .card-body .form-check-label {
    font-size: 12px;
    line-height: 1.25;
    cursor: pointer;
}

@media (max-width: 1199.98px) {
    .switch-grid {
        grid-template-columns: 1fr;
    }
}

/* ═══════════════════════════════════════════════════════════════
   PRICE TAG PREVIEW CARD
   ═══════════════════════════════════════════════════════════════ */
.price-tag {
    background: #ffffff;
    border: 1px dashed #ccc;
    border-radius: 4px;
    padding: 6px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: box-shadow 0.2s, border-color 0.2s;
    page-break-inside: avoid;
}
.price-tag:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border-color: #f59e0b;
}
.price-tag.tag-deselected {
    opacity: 0.35;
    border-color: #e5e5e5;
}
.price-tag.tag-deselected:hover {
    opacity: 0.6;
}
.price-tag .tag-store {
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #333;
    margin-bottom: 1px;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}
.price-tag .tag-name {
    font-size: 9px;
    font-weight: 700;
    color: #111;
    line-height: 1.2;
    margin-bottom: 2px;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    width: 100%;
}
.price-tag .tag-sku {
    font-size: 7px;
    color: #666;
    font-family: 'JetBrains Mono', monospace;
    margin-bottom: 2px;
}
.price-tag .tag-barcode-visual {
    margin: 2px 0;
}
.price-tag .tag-barcode-visual svg {
    max-width: 100%;
}
.price-tag .tag-barcode-num {
    font-size: 7px;
    font-family: 'JetBrains Mono', monospace;
    color: #444;
    letter-spacing: 1px;
}
.price-tag .tag-price {
    font-size: 16px;
    font-weight: 900;
    color: #000;
    line-height: 1;
    margin: 2px 0;
}
.price-tag .tag-category {
    font-size: 6px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.price-tag .tag-unit {
    font-size: 6px;
    color: #888;
}
.price-tag .tag-date {
    font-size: 5px;
    color: #aaa;
    position: absolute;
    bottom: 2px;
    right: 4px;
}
.price-tag.no-border {
    border: none;
}

/* Tag sizes */
.price-tag.tag-sticker38x25 { width: 143px; height: 94px; }
.price-tag.tag-small  { width: 180px; height: 110px; }
.price-tag.tag-medium { width: 220px; height: 150px; }
.price-tag.tag-large  { width: 300px; height: 190px; }
.price-tag.tag-shelf  { width: 370px; height: 110px; flex-direction: row; gap: 8px; text-align: left; align-items: center; }

/* Shelf label layout */
.price-tag.tag-shelf .tag-left {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
}
.price-tag.tag-shelf .tag-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
}
.price-tag.tag-shelf .tag-price {
    font-size: 22px;
}
.price-tag.tag-shelf .tag-name {
    font-size: 10px;
}

/* Larger tag adjustments */
.price-tag.tag-medium .tag-store { font-size: 8px; }
.price-tag.tag-medium .tag-name  { font-size: 11px; }
.price-tag.tag-medium .tag-sku   { font-size: 8px; }
.price-tag.tag-medium .tag-price { font-size: 20px; }
.price-tag.tag-medium .tag-barcode-num { font-size: 8px; }
.price-tag.tag-medium .tag-category { font-size: 7px; }

.price-tag.tag-large .tag-store { font-size: 9px; }
.price-tag.tag-large .tag-name  { font-size: 13px; -webkit-line-clamp: 2; }
.price-tag.tag-large .tag-sku   { font-size: 9px; }
.price-tag.tag-large .tag-price { font-size: 26px; }
.price-tag.tag-large .tag-barcode-num { font-size: 9px; }
.price-tag.tag-large .tag-category { font-size: 8px; }

/* Sticker 38x25 adjustments */
.price-tag.tag-sticker38x25 .tag-store { font-size: 5.5px; letter-spacing: 0.5px; }
.price-tag.tag-sticker38x25 .tag-name  { font-size: 7px; -webkit-line-clamp: 1; margin-bottom: 1px; }
.price-tag.tag-sticker38x25 .tag-sku   { font-size: 5.5px; margin-bottom: 1px; }
.price-tag.tag-sticker38x25 .tag-price { font-size: 13px; margin: 1px 0; }
.price-tag.tag-sticker38x25 .tag-barcode-num { font-size: 5.5px; }
.price-tag.tag-sticker38x25 .tag-category { font-size: 5px; }
.price-tag.tag-sticker38x25 .tag-unit { font-size: 5px; }
.price-tag.tag-sticker38x25 .tag-barcode-visual svg { max-height: 16px; }

</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- JsBarcode for barcode generation -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════
// PRODUCT DATA
// ═══════════════════════════════════════════════════════════════
const allProducts = <?php echo json_encode($productsJson, 15, 512) ?>;

let currentZoom = 100;

// ═══════════════════════════════════════════════════════════════
// RENDER PREVIEW
// ═══════════════════════════════════════════════════════════════
// ═══════════════════════════════════════════════════════════════
// PRESETS CONFIGURATION
// ═══════════════════════════════════════════════════════════════
const presets = {
    sticker38x25: {
        w: 38,
        h: 25,
        storeFont: 5.5,
        nameFont: 7,
        skuFont: 5.5,
        priceFont: 13,
        barcodeW: 0.8,
        barcodeH: 14,
        layoutType: 'standard',
        gapH: 3,
        gapV: 2,
        marginTop: 0,
        marginBottom: 0,
        marginLeft: 1.5,
        marginRight: 1.5,
        paperSize: 'sticker_2col_38x25'
    },
    small: {
        w: 50,
        h: 30,
        storeFont: 6,
        nameFont: 8,
        skuFont: 6,
        priceFont: 14,
        barcodeW: 1.0,
        barcodeH: 20,
        layoutType: 'standard',
        gapH: 2,
        gapV: 2,
        marginTop: 0,
        marginBottom: 0,
        marginLeft: 0,
        marginRight: 0,
        paperSize: 'a4'
    },
    medium: {
        w: 60,
        h: 40,
        storeFont: 7,
        nameFont: 10,
        skuFont: 7,
        priceFont: 18,
        barcodeW: 1.2,
        barcodeH: 25,
        layoutType: 'standard',
        gapH: 2,
        gapV: 2,
        marginTop: 0,
        marginBottom: 0,
        marginLeft: 0,
        marginRight: 0,
        paperSize: 'a4'
    },
    large: {
        w: 80,
        h: 50,
        storeFont: 8,
        nameFont: 12,
        skuFont: 8,
        priceFont: 22,
        barcodeW: 1.4,
        barcodeH: 35,
        layoutType: 'standard',
        gapH: 2,
        gapV: 2,
        marginTop: 0,
        marginBottom: 0,
        marginLeft: 0,
        marginRight: 0,
        paperSize: 'a4'
    },
    shelf: {
        w: 100,
        h: 30,
        storeFont: 6,
        nameFont: 10,
        skuFont: 6,
        priceFont: 20,
        barcodeW: 1.0,
        barcodeH: 18,
        layoutType: 'shelf',
        gapH: 2,
        gapV: 2,
        marginTop: 0,
        marginBottom: 0,
        marginLeft: 0,
        marginRight: 0,
        paperSize: 'a4'
    }
};

function getConfig() {
    return {
        w: parseFloat(document.getElementById('customTagWidth').value) || 38,
        h: parseFloat(document.getElementById('customTagHeight').value) || 25,
        layoutType: document.getElementById('layoutType').value || 'standard',
        storeFont: parseFloat(document.getElementById('fontStore').value) || 5.5,
        nameFont: parseFloat(document.getElementById('fontName').value) || 7,
        skuFont: parseFloat(document.getElementById('fontSku').value) || 5.5,
        priceFont: parseFloat(document.getElementById('fontPrice').value) || 13,
        barcodeW: parseFloat(document.getElementById('barcodeBarWidth').value) || 0.8,
        barcodeH: parseInt(document.getElementById('barcodeBarHeight').value, 10) || 14,
        gapH: parseFloat(document.getElementById('gapHorizontal').value) || 3,
        gapV: parseFloat(document.getElementById('gapVertical').value) || 2,
        marginTop: parseFloat(document.getElementById('marginTop').value) || 0,
        marginBottom: parseFloat(document.getElementById('marginBottom').value) || 0,
        marginLeft: parseFloat(document.getElementById('marginLeft').value) || 1.5,
        marginRight: parseFloat(document.getElementById('marginRight').value) || 1.5,
        symbology: document.getElementById('barcodeSymbology').value || 'auto'
    };
}

function onPresetChange() {
    const tagSize = document.getElementById('tagSize').value;
    if (tagSize === 'custom') return;

    const preset = presets[tagSize];
    if (preset) {
        document.getElementById('customTagWidth').value = preset.w;
        document.getElementById('customTagHeight').value = preset.h;
        document.getElementById('layoutType').value = preset.layoutType;
        document.getElementById('fontStore').value = preset.storeFont;
        document.getElementById('fontName').value = preset.nameFont;
        document.getElementById('fontSku').value = preset.skuFont;
        document.getElementById('fontPrice').value = preset.priceFont;
        document.getElementById('barcodeBarWidth').value = preset.barcodeW;
        document.getElementById('barcodeBarHeight').value = preset.barcodeH;
        document.getElementById('gapHorizontal').value = preset.gapH;
        document.getElementById('gapVertical').value = preset.gapV;
        document.getElementById('marginTop').value = preset.marginTop;
        document.getElementById('marginBottom').value = preset.marginBottom;
        document.getElementById('marginLeft').value = preset.marginLeft;
        document.getElementById('marginRight').value = preset.marginRight;
        
        if (preset.paperSize) {
            document.getElementById('paperSize').value = preset.paperSize;
            onPaperSizeChange(true);
        }
    }
    updatePreview();
}

function onPaperSizeChange(fromPreset = false) {
    const paperSize = document.getElementById('paperSize').value;
    const container = document.getElementById('customPaperDimContainer');
    
    if (paperSize === 'custom') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
    updatePreview();
}

function onManualDimensionChange() {
    document.getElementById('tagSize').value = 'custom';
    const paperSize = document.getElementById('paperSize').value;
    const container = document.getElementById('customPaperDimContainer');
    if (paperSize === 'custom') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
    updatePreview();
}

// ═══════════════════════════════════════════════════════════════
// RENDER PREVIEW
// ═══════════════════════════════════════════════════════════════
function updatePreview() {
    const container = document.getElementById('priceTagPreview');
    const columns = parseInt(document.getElementById('tagColumns').value) || 3;
    const copies = parseInt(document.getElementById('tagCopies').value) || 1;
    const showStoreName = document.getElementById('showStoreName').checked;
    const showProductName = document.getElementById('showProductName').checked;
    const showSku = document.getElementById('showSku').checked;
    const showBarcode = document.getElementById('showBarcode').checked;
    const showBarcodeNumber = document.getElementById('showBarcodeNumber').checked;
    const showPrice = document.getElementById('showPrice').checked;
    const showCategory = document.getElementById('showCategory').checked;
    const showUnit = document.getElementById('showUnit').checked;
    const showBorder = document.getElementById('showBorder').checked;
    const showDate = document.getElementById('showDate').checked;
    const storeName = document.getElementById('customStoreName').value;
    const currency = document.getElementById('currencySymbol').value;
    const filterCat = document.getElementById('filterCategory').value;
    const filterSearch = document.getElementById('filterSearch').value.toLowerCase();

    const config = getConfig();
    const mmToPx = 3.78;

    container.style.gridTemplateColumns = `repeat(${columns}, max-content)`;
    container.style.gap = `${config.gapV * mmToPx}px ${config.gapH * mmToPx}px`;

    let html = '';
    let visibleCount = 0;

    allProducts.forEach((product, idx) => {
        if (!productMatchesFilter(product, filterCat, filterSearch)) return;

        visibleCount++;

        for (let c = 0; c < copies; c++) {
            const borderClass = showBorder ? '' : 'no-border';
            const selectedClass = product.selected ? '' : 'tag-deselected';
            const barcodeId = `barcode-${product.id}-${c}`;

            if (config.layoutType === 'shelf') {
                html += `<div class="price-tag ${borderClass} ${selectedClass}" 
                             style="width:${config.w * mmToPx}px; height:${config.h * mmToPx}px; flex-direction: row; gap: 8px; text-align: left; align-items: center;" 
                             onclick="toggleProduct(${idx})" title="Click to toggle">
                    <div class="tag-left" style="flex: 1; display: flex; flex-direction: column; align-items: flex-start; min-width: 0; padding-right: 4px;">
                        ${showStoreName ? `<div class="tag-store" style="font-size:${config.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.3mm; color:#333; margin-bottom: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">${escHtml(storeName)}</div>` : ''}
                        ${showProductName ? `<div class="tag-name" style="font-size:${config.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin-bottom: 2px; word-break: break-word; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; width: 100%;">${escHtml(product.name)}</div>` : ''}
                        ${showSku && product.sku ? `<div class="tag-sku" style="font-size:${config.skuFont}pt; color:#666; font-family: 'JetBrains Mono', monospace; margin-bottom: 2px;">${escHtml(product.sku)}</div>` : ''}
                        ${showCategory && product.category ? `<div class="tag-category" style="font-size:${config.skuFont}pt; color:#888; text-transform:uppercase; letter-spacing:0.5px;">${escHtml(product.category)}</div>` : ''}
                        ${showUnit ? `<div class="tag-unit" style="font-size:${config.skuFont}pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
                        ${showBarcode && product.barcode ? `<div class="tag-barcode-visual" style="margin: 2px 0;"><svg id="${barcodeId}"></svg></div>` : ''}
                        ${showBarcodeNumber && product.barcode ? `<div class="tag-barcode-num" style="font-size:${config.skuFont}pt; font-family: 'JetBrains Mono', monospace; color:#444; letter-spacing: 1px;">${escHtml(product.barcode)}</div>` : ''}
                    </div>
                    <div class="tag-right" style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
                        ${showPrice ? `<div class="tag-price" style="font-size:${config.priceFont}pt; font-weight: 900; color: #000; line-height: 1; margin: 2px 0;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    </div>
                    ${showDate ? `<div class="tag-date" style="font-size: 5px; color: #aaa; position: absolute; bottom: 2px; right: 4px;">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            } else {
                html += `<div class="price-tag ${borderClass} ${selectedClass}" 
                             style="width:${config.w * mmToPx}px; height:${config.h * mmToPx}px;" 
                             onclick="toggleProduct(${idx})" title="Click to toggle">
                    ${showStoreName ? `<div class="tag-store" style="font-size:${config.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.5mm; color:#333; margin-bottom: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">${escHtml(storeName)}</div>` : ''}
                    ${showProductName ? `<div class="tag-name" style="font-size:${config.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin-bottom: 2px; word-break: break-word; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; width: 100%;">${escHtml(product.name)}</div>` : ''}
                    ${showSku && product.sku ? `<div class="tag-sku" style="font-size:${config.skuFont}pt; color:#666; font-family: 'JetBrains Mono', monospace; margin-bottom: 2px;">${escHtml(product.sku)}</div>` : ''}
                    ${showBarcode && product.barcode ? `<div class="tag-barcode-visual" style="margin: 2px 0;"><svg id="${barcodeId}"></svg></div>` : ''}
                    ${showBarcodeNumber && product.barcode ? `<div class="tag-barcode-num" style="font-size:${config.skuFont}pt; font-family: 'JetBrains Mono', monospace; color:#444; letter-spacing: 1px;">${escHtml(product.barcode)}</div>` : ''}
                    ${showPrice ? `<div class="tag-price" style="font-size:${config.priceFont}pt; font-weight: 900; color: #000; line-height: 1; margin: 2px 0;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    ${showCategory && product.category ? `<div class="tag-category" style="font-size:${config.skuFont}pt; color:#888; text-transform:uppercase; letter-spacing:0.5px;">${escHtml(product.category)}</div>` : ''}
                    ${showUnit ? `<div class="tag-unit" style="font-size:${config.skuFont}pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
                    ${showDate ? `<div class="tag-date" style="font-size: 5px; color: #aaa; position: absolute; bottom: 2px; right: 4px;">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            }
        }
    });

    container.innerHTML = html || '<div class="text-center text-muted py-5"><i class="bi bi-tag fs-1 d-block mb-2"></i>No products to display</div>';
    document.getElementById('visibleCount').textContent = visibleCount;
    document.getElementById('selectedCount').textContent = allProducts.filter(p => p.selected).length;

    // Render barcodes
    setTimeout(() => {
        allProducts.forEach((product, idx) => {
            if (product.barcode && document.getElementById('showBarcode').checked) {
                for (let c = 0; c < copies; c++) {
                    const el = document.getElementById(`barcode-${product.id}-${c}`);
                    if (el) {
                        try {
                            JsBarcode(el, product.barcode, {
                                format: config.symbology === 'auto' ? detectBarcodeFormat(product.barcode) : config.symbology,
                                width: config.barcodeW,
                                height: config.barcodeH,
                                displayValue: false,
                                margin: 0,
                                background: 'transparent',
                            });
                        } catch (e) {
                            try {
                                JsBarcode(el, product.barcode, {
                                    format: 'CODE128',
                                    width: 1,
                                    height: config.barcodeH,
                                    displayValue: false,
                                    margin: 0,
                                    background: 'transparent',
                                });
                            } catch(e2) {
                                el.remove();
                            }
                        }
                    }
                }
            }
        });
    }, 50);

    // Save settings
    saveSettings();
}

function detectBarcodeFormat(barcode) {
    if (!barcode) return 'CODE128';
    const clean = barcode.replace(/\D/g, '');
    if (clean.length === 13) return 'EAN13';
    if (clean.length === 8) return 'EAN8';
    if (clean.length === 12) return 'UPC';
    return 'CODE128';
}

function formatPrice(price) {
    return Number(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function productMatchesFilter(product, filterCat = null, filterSearch = null) {
    const category = filterCat ?? document.getElementById('filterCategory').value;
    const search = filterSearch ?? document.getElementById('filterSearch').value.toLowerCase();

    if (category && String(product.category_id || '') !== String(category)) return false;
    if (search && !product.name.toLowerCase().includes(search) && !(product.sku || '').toLowerCase().includes(search)) return false;

    return true;
}

function adjustCopies(delta) {
    const input = document.getElementById('tagCopies');
    const min = parseInt(input.min, 10) || 1;
    const max = parseInt(input.max, 10) || 100;
    const nextValue = Math.max(min, Math.min(max, (parseInt(input.value, 10) || min) + delta));

    input.value = nextValue;
    updatePreview();
}

function setDisplayOptions(checked) {
    [
        'showStoreName',
        'showProductName',
        'showSku',
        'showBarcode',
        'showBarcodeNumber',
        'showPrice',
        'showCategory',
        'showUnit',
        'showBorder',
        'showDate',
    ].forEach(id => {
        document.getElementById(id).checked = checked;
    });

    updatePreview();
}

// ═══════════════════════════════════════════════════════════════
// TOGGLE & FILTER
// ═══════════════════════════════════════════════════════════════
function toggleProduct(index) {
    allProducts[index].selected = !allProducts[index].selected;
    updatePreview();
}

function selectAllVisible() {
    allProducts.forEach(p => {
        if (productMatchesFilter(p)) p.selected = true;
    });
    updatePreview();
}

function deselectAll() {
    allProducts.forEach(p => p.selected = false);
    updatePreview();
}

function filterProducts() {
    updatePreview();
}

// ═══════════════════════════════════════════════════════════════
// ZOOM
// ═══════════════════════════════════════════════════════════════
function zoomPreview(dir) {
    currentZoom = Math.max(50, Math.min(200, currentZoom + (dir * 10)));
    document.getElementById('priceTagPreview').style.transform = `scale(${currentZoom / 100})`;
    document.getElementById('zoomLevel').textContent = currentZoom + '%';
}

// ═══════════════════════════════════════════════════════════════
// BUILD TAG HTML (unified tag generator for screen and print)
// ═══════════════════════════════════════════════════════════════
function buildTagHtml(product, barcodeId, config, showStoreName, showProductName, showSku, showBarcode, showBarcodeNumber, showPrice, showCategory, showUnit, showBorder, showDate, storeName, currency) {
    const borderStyle = showBorder ? 'border: 0.3mm dashed #999;' : 'border: none;';
    
    if (config.layoutType === 'shelf') {
        return `
        <div class="ptag ptag-shelf" style="width:${config.w}mm; height:${config.h}mm; ${borderStyle}">
            <div class="ptag-left" style="flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: flex-start;">
                ${showStoreName ? `<div style="font-size:${config.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.3mm; color:#333; margin-bottom:0.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">${escHtml(storeName)}</div>` : ''}
                ${showProductName ? `<div style="font-size:${config.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin:0.5mm 0; overflow:hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; max-height: ${config.nameFont * 2.5}pt; width: 100%;">${escHtml(product.name)}</div>` : ''}
                ${showSku && product.sku ? `<div style="font-size:${config.skuFont}pt; color:#666; font-family:monospace; margin-bottom:0.5mm;">${escHtml(product.sku)}</div>` : ''}
                ${showCategory && product.category ? `<div style="font-size:${config.skuFont}pt; color:#888; text-transform:uppercase;">${escHtml(product.category)}</div>` : ''}
                ${showUnit ? `<div style="font-size:${config.skuFont}pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
                ${showBarcode && product.barcode ? `<div style="margin: 0.5mm 0;"><svg id="${barcodeId}"></svg></div>` : ''}
                ${showBarcodeNumber && product.barcode ? `<div style="font-size:${config.skuFont}pt; font-family:monospace; color:#444; letter-spacing:0.3mm;">${escHtml(product.barcode)}</div>` : ''}
            </div>
            <div class="ptag-right" style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 0.5mm;">
                ${showPrice ? `<div style="font-size:${config.priceFont}pt; font-weight:900; color:#000; line-height: 1;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
            </div>
            ${showDate ? `<div style="position:absolute; bottom:0.5mm; right:2mm; font-size:4pt; color:#aaa;">${new Date().toLocaleDateString()}</div>` : ''}
        </div>`;
    } else {
        return `
        <div class="ptag" style="width:${config.w}mm; height:${config.h}mm; ${borderStyle}">
            ${showStoreName ? `<div style="font-size:${config.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.5mm; color:#333; margin-bottom:0.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">${escHtml(storeName)}</div>` : ''}
            ${showProductName ? `<div style="font-size:${config.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin:0.5mm 0; overflow:hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; max-height: ${config.nameFont * 2.5}pt; width: 100%;">${escHtml(product.name)}</div>` : ''}
            ${showSku && product.sku ? `<div style="font-size:${config.skuFont}pt; color:#666; font-family:monospace; margin-bottom:0.5mm;">${escHtml(product.sku)}</div>` : ''}
            ${showBarcode && product.barcode ? `<div style="margin:0.5mm 0;"><svg id="${barcodeId}"></svg></div>` : ''}
            ${showBarcodeNumber && product.barcode ? `<div style="font-size:${config.skuFont}pt; font-family:monospace; color:#444; letter-spacing:0.3mm;">${escHtml(product.barcode)}</div>` : ''}
            ${showPrice ? `<div style="font-size:${config.priceFont}pt; font-weight:900; color:#000; margin:0.5mm 0; line-height: 1;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
            ${showCategory && product.category ? `<div style="font-size:${config.skuFont}pt; color:#888; text-transform:uppercase; margin-bottom:0.5mm;">${escHtml(product.category)}</div>` : ''}
            ${showUnit ? `<div style="font-size:${config.skuFont}pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
            ${showDate ? `<div style="position:absolute; bottom:0.5mm; right:2mm; font-size:4pt; color:#aaa;">${new Date().toLocaleDateString()}</div>` : ''}
        </div>`;
    }
}

// ═══════════════════════════════════════════════════════════════
// PRINT
// ═══════════════════════════════════════════════════════════════
function printPriceTags() {
    const columns = parseInt(document.getElementById('tagColumns').value) || 3;
    const copies = parseInt(document.getElementById('tagCopies').value) || 1;
    const paperSize = document.getElementById('paperSize').value;
    const showStoreName = document.getElementById('showStoreName').checked;
    const showProductName = document.getElementById('showProductName').checked;
    const showSku = document.getElementById('showSku').checked;
    const showBarcode = document.getElementById('showBarcode').checked;
    const showBarcodeNumber = document.getElementById('showBarcodeNumber').checked;
    const showPrice = document.getElementById('showPrice').checked;
    const showCategory = document.getElementById('showCategory').checked;
    const showUnit = document.getElementById('showUnit').checked;
    const showBorder = document.getElementById('showBorder').checked;
    const showDate = document.getElementById('showDate').checked;
    const storeName = document.getElementById('customStoreName').value;
    const currency = document.getElementById('currencySymbol').value;

    const config = getConfig();

    // Build selected products
    const selectedProducts = allProducts.filter(p => p.selected);
    if (selectedProducts.length === 0) {
        alert('No products selected! Click on tags to select/deselect products.');
        return;
    }

    // Paper size configuration
    let paper = { w: '210mm', h: '297mm' }; // Default A4
    const isStickerSheet = (paperSize === 'sticker_2col_38x25');
    const isSingleRoll = (paperSize === 'single_roll');

    if (isStickerSheet) {
        // Calculate page size dynamically based on sticker dimensions, gaps, and margins
        paper.w = `${(config.w * 2) + config.gapH + config.marginLeft + config.marginRight}mm`;
        paper.h = `${config.h + config.marginTop + config.marginBottom}mm`;
    } else if (isSingleRoll) {
        // Roll label width/height is label dimension + margins
        paper.w = `${config.w + config.marginLeft + config.marginRight}mm`;
        paper.h = `${config.h + config.marginTop + config.marginBottom}mm`;
    } else if (paperSize === 'custom') {
        const customW = parseFloat(document.getElementById('customPaperWidth').value) || 80;
        const customH = parseFloat(document.getElementById('customPaperHeight').value) || 25;
        paper.w = `${customW}mm`;
        paper.h = `${customH}mm`;
    } else {
        const paperSizes = {
            a4: { w: '210mm', h: '297mm' },
            letter: { w: '8.5in', h: '11in' },
            a5: { w: '148mm', h: '210mm' }
        };
        paper = paperSizes[paperSize] || paperSizes.a4;
    }

    // Build tags HTML
    let tagsHtml = '';
    if (isStickerSheet) {
        // For sticker sheets: build rows of 2 labels, each row is a separate page
        const allTags = [];
        selectedProducts.forEach(product => {
            for (let c = 0; c < copies; c++) {
                const barcodeId = `print-barcode-${product.id}-${c}`;
                allTags.push({product, barcodeId});
            }
        });

        // Group into rows of 2
        for (let i = 0; i < allTags.length; i += 2) {
            const isLastRow = (i + 2 >= allTags.length);
            tagsHtml += `<div class="sticker-row" ${!isLastRow ? 'style="page-break-after: always;"' : ''}>`;

            // Left label
            const left = allTags[i];
            tagsHtml += buildTagHtml(left.product, left.barcodeId, config, showStoreName, showProductName, showSku, showBarcode, showBarcodeNumber, showPrice, showCategory, showUnit, showBorder, showDate, storeName, currency);

            // Right label (if exists)
            if (i + 1 < allTags.length) {
                const right = allTags[i + 1];
                tagsHtml += buildTagHtml(right.product, right.barcodeId, config, showStoreName, showProductName, showSku, showBarcode, showBarcodeNumber, showPrice, showCategory, showUnit, showBorder, showDate, storeName, currency);
            } else {
                // Empty placeholder for alignment
                tagsHtml += `<div class="ptag" style="width:${config.w}mm; height:${config.h}mm; visibility:hidden; border: none;"></div>`;
            }

            tagsHtml += `</div>`;
        }
    } else if (isSingleRoll) {
        // Build individual tags, each on its own page
        const totalTags = [];
        selectedProducts.forEach(product => {
            for (let c = 0; c < copies; c++) {
                const barcodeId = `print-barcode-${product.id}-${c}`;
                totalTags.push({product, barcodeId});
            }
        });

        totalTags.forEach((tagItem, index) => {
            const isLast = (index === totalTags.length - 1);
            tagsHtml += `<div class="roll-tag-wrapper" style="width:100%; height:100%; display:flex; justify-content:center; align-items:center; box-sizing:border-box; ${!isLast ? 'page-break-after: always;' : ''}">
                ${buildTagHtml(tagItem.product, tagItem.barcodeId, config, showStoreName, showProductName, showSku, showBarcode, showBarcodeNumber, showPrice, showCategory, showUnit, showBorder, showDate, storeName, currency)}
            </div>`;
        });
    } else {
        // Sheet mode (grid layout)
        selectedProducts.forEach(product => {
            for (let c = 0; c < copies; c++) {
                const barcodeId = `print-barcode-${product.id}-${c}`;
                tagsHtml += buildTagHtml(product, barcodeId, config, showStoreName, showProductName, showSku, showBarcode, showBarcodeNumber, showPrice, showCategory, showUnit, showBorder, showDate, storeName, currency);
            }
        });
    }

    // Build barcode rendering script
    let barcodeScript = '';
    selectedProducts.forEach(product => {
        if (product.barcode && showBarcode) {
            for (let c = 0; c < copies; c++) {
                const id = `print-barcode-${product.id}-${c}`;
                const format = config.symbology === 'auto' ? detectBarcodeFormat(product.barcode) : config.symbology;
                const safeBarcode = product.barcode.replace(/'/g, "\\'");
                barcodeScript += `
                try {
                    JsBarcode('#${id}', '${safeBarcode}', {
                        format: '${format}',
                        width: ${config.barcodeW},
                        height: ${config.barcodeH},
                        displayValue: false,
                        margin: 0,
                        background: 'transparent',
                    });
                } catch(e) {
                    try {
                        JsBarcode('#${id}', '${safeBarcode}', {
                            format: 'CODE128',
                            width: 1,
                            height: ${config.barcodeH},
                            displayValue: false,
                            margin: 0,
                            background: 'transparent',
                        });
                    } catch(e2) {}
                }
                `;
            }
        }
    });

    // Build print window
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
        <title>Price Tags - ${storeName}</title>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
        <style>
            @page {
                size: ${paper.w} ${paper.h};
                margin: ${isStickerSheet || isSingleRoll ? '0' : `${config.marginTop}mm ${config.marginRight}mm ${config.marginBottom}mm ${config.marginLeft}mm`};
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
                padding: 0;
                background: #fff;
            }
            ${isStickerSheet ? `
            .sticker-row {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: row;
                align-items: stretch;
                justify-content: center;
                gap: ${config.gapH}mm;
                padding: ${config.marginTop}mm ${config.marginRight}mm ${config.marginBottom}mm ${config.marginLeft}mm;
                overflow: hidden;
            }
            .sticker-row .ptag {
                flex: 1;
                max-width: ${config.w}mm;
                height: ${config.h}mm;
                overflow: hidden;
                position: relative;
                border-radius: 0;
            }
            ` : isSingleRoll ? `
            .roll-tag-wrapper {
                padding: ${config.marginTop}mm ${config.marginRight}mm ${config.marginBottom}mm ${config.marginLeft}mm;
                overflow: hidden;
            }
            .roll-tag-wrapper .ptag {
                width: ${config.w}mm;
                height: ${config.h}mm;
                overflow: hidden;
                position: relative;
                border-radius: 0;
            }
            ` : `
            .ptag-grid {
                display: flex;
                flex-wrap: wrap;
                gap: ${config.gapV}mm ${config.gapH}mm;
                justify-content: flex-start;
            }
            .ptag {
                overflow: hidden;
                position: relative;
                border-radius: 1mm;
                page-break-inside: avoid;
            }
            `}
            
            /* Unified classes inside print container */
            .ptag {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            .ptag-shelf {
                display: flex;
                flex-direction: row;
                align-items: center;
            }
            svg { max-width: 100%; max-height: 100%; }
            @media print {
                body { padding: 0; margin: 0; }
            }
        </style>
    </head>
    <body>
        ${isStickerSheet ? tagsHtml : (isSingleRoll ? `<div class="roll-container">${tagsHtml}</div>` : `<div class="ptag-grid">${tagsHtml}</div>`)}
        <script>
            ${barcodeScript}
            // Auto print
            setTimeout(() => { window.print(); }, 600);
        <\/script>
    </body>
    </html>
    `);
    printWindow.document.close();
}

// ═══════════════════════════════════════════════════════════════
// SAVE & LOAD SETTINGS (localStorage Persistence)
// ═══════════════════════════════════════════════════════════════
const storageKey = 'cpos_price_tag_settings';

function saveSettings() {
    const settings = {
        tagSize: document.getElementById('tagSize').value,
        tagColumns: document.getElementById('tagColumns').value,
        tagCopies: document.getElementById('tagCopies').value,
        paperSize: document.getElementById('paperSize').value,
        customTagWidth: document.getElementById('customTagWidth').value,
        customTagHeight: document.getElementById('customTagHeight').value,
        layoutType: document.getElementById('layoutType').value,
        customPaperWidth: document.getElementById('customPaperWidth').value,
        customPaperHeight: document.getElementById('customPaperHeight').value,
        marginTop: document.getElementById('marginTop').value,
        marginBottom: document.getElementById('marginBottom').value,
        marginLeft: document.getElementById('marginLeft').value,
        marginRight: document.getElementById('marginRight').value,
        gapHorizontal: document.getElementById('gapHorizontal').value,
        gapVertical: document.getElementById('gapVertical').value,
        fontStore: document.getElementById('fontStore').value,
        fontName: document.getElementById('fontName').value,
        fontSku: document.getElementById('fontSku').value,
        fontPrice: document.getElementById('fontPrice').value,
        barcodeSymbology: document.getElementById('barcodeSymbology').value,
        barcodeBarWidth: document.getElementById('barcodeBarWidth').value,
        barcodeBarHeight: document.getElementById('barcodeBarHeight').value,
        
        // Display options
        showStoreName: document.getElementById('showStoreName').checked,
        showProductName: document.getElementById('showProductName').checked,
        showSku: document.getElementById('showSku').checked,
        showBarcode: document.getElementById('showBarcode').checked,
        showBarcodeNumber: document.getElementById('showBarcodeNumber').checked,
        showPrice: document.getElementById('showPrice').checked,
        showCategory: document.getElementById('showCategory').checked,
        showUnit: document.getElementById('showUnit').checked,
        showBorder: document.getElementById('showBorder').checked,
        showDate: document.getElementById('showDate').checked,
        
        // Store Info
        customStoreName: document.getElementById('customStoreName').value,
        currencySymbol: document.getElementById('currencySymbol').value
    };
    
    localStorage.setItem(storageKey, JSON.stringify(settings));
}

function loadSettings() {
    try {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        
        const settings = JSON.parse(saved);
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el && val !== undefined) el.value = val;
        };
        const setCheck = (id, val) => {
            const el = document.getElementById(id);
            if (el && val !== undefined) el.checked = !!val;
        };
        
        setVal('tagSize', settings.tagSize);
        setVal('tagColumns', settings.tagColumns);
        setVal('tagCopies', settings.tagCopies);
        setVal('paperSize', settings.paperSize);
        setVal('customTagWidth', settings.customTagWidth);
        setVal('customTagHeight', settings.customTagHeight);
        setVal('layoutType', settings.layoutType);
        setVal('customPaperWidth', settings.customPaperWidth);
        setVal('customPaperHeight', settings.customPaperHeight);
        setVal('marginTop', settings.marginTop);
        setVal('marginBottom', settings.marginBottom);
        setVal('marginLeft', settings.marginLeft);
        setVal('marginRight', settings.marginRight);
        setVal('gapHorizontal', settings.gapHorizontal);
        setVal('gapVertical', settings.gapVertical);
        setVal('fontStore', settings.fontStore);
        setVal('fontName', settings.fontName);
        setVal('fontSku', settings.fontSku);
        setVal('fontPrice', settings.fontPrice);
        setVal('barcodeSymbology', settings.barcodeSymbology);
        setVal('barcodeBarWidth', settings.barcodeBarWidth);
        setVal('barcodeBarHeight', settings.barcodeBarHeight);
        
        setCheck('showStoreName', settings.showStoreName);
        setCheck('showProductName', settings.showProductName);
        setCheck('showSku', settings.showSku);
        setCheck('showBarcode', settings.showBarcode);
        setCheck('showBarcodeNumber', settings.showBarcodeNumber);
        setCheck('showPrice', settings.showPrice);
        setCheck('showCategory', settings.showCategory);
        setCheck('showUnit', settings.showUnit);
        setCheck('showBorder', settings.showBorder);
        setCheck('showDate', settings.showDate);
        
        setVal('customStoreName', settings.customStoreName);
        setVal('currencySymbol', settings.currencySymbol);
        
        if (settings.paperSize === 'custom') {
            document.getElementById('customPaperDimContainer').style.display = 'block';
        } else {
            document.getElementById('customPaperDimContainer').style.display = 'none';
        }
    } catch (e) {
        console.error("Error loading settings from localStorage:", e);
    }
}

// Initial render
document.addEventListener('DOMContentLoaded', () => {
    loadSettings();
    updatePreview();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/products/price-tags.blade.php ENDPATH**/ ?>