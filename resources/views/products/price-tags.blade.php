@extends('layouts.app')
@section('title', 'Price Tags')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Price Tags</li>
@endsection
@section('content')
<div class="page-header">
    <div><h2 class="page-title">Price Tags</h2><p class="page-subtitle">{{ $products->count() }} product(s) selected</p></div>
    <div class="page-actions">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-cpos me-2">
            <i class="bi bi-arrow-left me-1"></i>Back to Products
        </a>
        <button type="button" class="btn btn-primary btn-cpos" onclick="printPriceTags()">
            <i class="bi bi-printer me-2"></i>Print Price Tags
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Settings Panel -->
    <div class="col-lg-3">
        <div class="card cpos-card mb-3">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Tag Settings</h6>
            </div>
            <div class="card-body">
                <!-- Tag Size -->
                <div class="mb-3">
                    <label class="form-label fw-600">Tag Size</label>
                    <select id="tagSize" class="form-select" onchange="updatePreview()">
                        <option value="small" selected>Small (50mm × 30mm)</option>
                        <option value="medium">Medium (60mm × 40mm)</option>
                        <option value="large">Large (80mm × 50mm)</option>
                        <option value="shelf">Shelf Label (100mm × 30mm)</option>
                    </select>
                </div>

                <!-- Columns -->
                <div class="mb-3">
                    <label class="form-label fw-600">Columns per Row</label>
                    <select id="tagColumns" class="form-select" onchange="updatePreview()">
                        <option value="2">2 Columns</option>
                        <option value="3" selected>3 Columns</option>
                        <option value="4">4 Columns</option>
                        <option value="5">5 Columns</option>
                    </select>
                </div>

                <!-- Copies per product -->
                <div class="mb-3">
                    <label class="form-label fw-600">Copies Per Product</label>
                    <input type="number" id="tagCopies" class="form-control" value="1" min="1" max="100" onchange="updatePreview()">
                </div>

                <!-- Paper Size -->
                <div class="mb-3">
                    <label class="form-label fw-600">Paper Size</label>
                    <select id="paperSize" class="form-select" onchange="updatePreview()">
                        <option value="a4" selected>A4</option>
                        <option value="letter">Letter</option>
                        <option value="a5">A5</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card cpos-card mb-3">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Display Options</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showStoreName" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showStoreName">Store Name</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showProductName" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showProductName">Product Name</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showSku" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showSku">SKU / Item Code</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showBarcode" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBarcode">Barcode</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showBarcodeNumber" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBarcodeNumber">Barcode Number</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showPrice" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showPrice">Selling Price</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showCategory" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showCategory">Category</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showUnit" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showUnit">Unit</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showBorder" class="form-check-input" checked onchange="updatePreview()">
                    <label class="form-check-label" for="showBorder">Tag Border</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="showDate" class="form-check-input" onchange="updatePreview()">
                    <label class="form-check-label" for="showDate">Print Date</label>
                </div>
            </div>
        </div>

        <!-- Custom Store Name -->
        <div class="card cpos-card mb-3">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-shop me-2"></i>Store Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">Store Name</label>
                    <input type="text" id="customStoreName" class="form-control" value="{{ $store->name ?? 'PUREPOS' }}" onchange="updatePreview()">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-600">Currency Symbol</label>
                    <input type="text" id="currencySymbol" class="form-control" value="{{ $store->currency_symbol ?? 'Rs.' }}" onchange="updatePreview()">
                </div>
            </div>
        </div>

        <!-- Product Filter -->
        <div class="card cpos-card mb-3">
            <div class="card-header modal-cpos-header">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Products</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">Category</label>
                    <select id="filterCategory" class="form-select" onchange="filterProducts()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Search</label>
                    <input type="text" id="filterSearch" class="form-control" placeholder="Search products..." oninput="filterProducts()">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted"><span id="visibleCount">{{ $products->count() }}</span> visible</small>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="selectAllVisible()">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect</button>
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

@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   SETTINGS PANEL STYLES
   ═══════════════════════════════════════════════════════════════ */
.price-tag-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-start;
    transform-origin: top left;
}

/* Form switches in settings */
.card-body .form-check.form-switch {
    padding-left: 2.8em;
}
.card-body .form-check.form-switch .form-check-input {
    width: 36px;
    height: 18px;
    cursor: pointer;
}
.card-body .form-check-label {
    font-size: 13px;
    cursor: pointer;
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

</style>
@endpush

@push('scripts')
<!-- JsBarcode for barcode generation -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════
// PRODUCT DATA
// ═══════════════════════════════════════════════════════════════
const allProducts = @json($productsJson);

let currentZoom = 100;

// ═══════════════════════════════════════════════════════════════
// RENDER PREVIEW
// ═══════════════════════════════════════════════════════════════
function updatePreview() {
    const container = document.getElementById('priceTagPreview');
    const tagSize = document.getElementById('tagSize').value;
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

    let html = '';
    let visibleCount = 0;

    allProducts.forEach((product, idx) => {
        // Apply filters
        if (filterCat && product.category_id && product.category_id != filterCat) return;
        if (filterSearch && !product.name.toLowerCase().includes(filterSearch) && !(product.sku || '').toLowerCase().includes(filterSearch)) return;

        visibleCount++;

        for (let c = 0; c < copies; c++) {
            const borderClass = showBorder ? '' : 'no-border';
            const selectedClass = product.selected ? '' : 'tag-deselected';
            const barcodeId = `barcode-${product.id}-${c}`;

            if (tagSize === 'shelf') {
                html += `<div class="price-tag tag-${tagSize} ${borderClass} ${selectedClass}" onclick="toggleProduct(${idx})" title="Click to toggle">
                    <div class="tag-left">
                        ${showStoreName ? `<div class="tag-store">${escHtml(storeName)}</div>` : ''}
                        ${showProductName ? `<div class="tag-name">${escHtml(product.name)}</div>` : ''}
                        ${showSku && product.sku ? `<div class="tag-sku">${escHtml(product.sku)}</div>` : ''}
                        ${showCategory && product.category ? `<div class="tag-category">${escHtml(product.category)}</div>` : ''}
                        ${showUnit ? `<div class="tag-unit">per ${escHtml(product.unit)}</div>` : ''}
                        ${showBarcode && product.barcode ? `<div class="tag-barcode-visual"><svg id="${barcodeId}"></svg></div>` : ''}
                        ${showBarcodeNumber && product.barcode ? `<div class="tag-barcode-num">${escHtml(product.barcode)}</div>` : ''}
                    </div>
                    <div class="tag-right">
                        ${showPrice ? `<div class="tag-price">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    </div>
                    ${showDate ? `<div class="tag-date">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            } else {
                html += `<div class="price-tag tag-${tagSize} ${borderClass} ${selectedClass}" onclick="toggleProduct(${idx})" title="Click to toggle">
                    ${showStoreName ? `<div class="tag-store">${escHtml(storeName)}</div>` : ''}
                    ${showProductName ? `<div class="tag-name">${escHtml(product.name)}</div>` : ''}
                    ${showSku && product.sku ? `<div class="tag-sku">${escHtml(product.sku)}</div>` : ''}
                    ${showBarcode && product.barcode ? `<div class="tag-barcode-visual"><svg id="${barcodeId}"></svg></div>` : ''}
                    ${showBarcodeNumber && product.barcode ? `<div class="tag-barcode-num">${escHtml(product.barcode)}</div>` : ''}
                    ${showPrice ? `<div class="tag-price">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    ${showCategory && product.category ? `<div class="tag-category">${escHtml(product.category)}</div>` : ''}
                    ${showUnit ? `<div class="tag-unit">per ${escHtml(product.unit)}</div>` : ''}
                    ${showDate ? `<div class="tag-date">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            }
        }
    });

    container.innerHTML = html || '<div class="text-center text-muted py-5"><i class="bi bi-tag fs-1 d-block mb-2"></i>No products to display</div>';
    document.getElementById('visibleCount').textContent = visibleCount;

    // Render barcodes
    setTimeout(() => {
        allProducts.forEach((product, idx) => {
            if (product.barcode && document.getElementById('showBarcode').checked) {
                for (let c = 0; c < copies; c++) {
                    const el = document.getElementById(`barcode-${product.id}-${c}`);
                    if (el) {
                        try {
                            JsBarcode(el, product.barcode, {
                                format: detectBarcodeFormat(product.barcode),
                                width: tagSize === 'small' ? 1 : (tagSize === 'medium' ? 1.2 : 1.5),
                                height: tagSize === 'small' ? 20 : (tagSize === 'medium' ? 25 : 35),
                                displayValue: false,
                                margin: 0,
                                background: 'transparent',
                            });
                        } catch (e) {
                            // If barcode format fails, try CODE128
                            try {
                                JsBarcode(el, product.barcode, {
                                    format: 'CODE128',
                                    width: 1,
                                    height: 20,
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

// ═══════════════════════════════════════════════════════════════
// TOGGLE & FILTER
// ═══════════════════════════════════════════════════════════════
function toggleProduct(index) {
    allProducts[index].selected = !allProducts[index].selected;
    updatePreview();
}

function selectAllVisible() {
    allProducts.forEach(p => p.selected = true);
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
// PRINT
// ═══════════════════════════════════════════════════════════════
function printPriceTags() {
    const tagSize = document.getElementById('tagSize').value;
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

    // Size configurations (in mm)
    const sizes = {
        small:  { w: 50, h: 30, nameFont: 8, priceFont: 14, storeFont: 6, skuFont: 6, barcodeW: 1, barcodeH: 18 },
        medium: { w: 60, h: 40, nameFont: 10, priceFont: 18, storeFont: 7, skuFont: 7, barcodeW: 1.2, barcodeH: 22 },
        large:  { w: 80, h: 50, nameFont: 12, priceFont: 22, storeFont: 8, skuFont: 8, barcodeW: 1.4, barcodeH: 28 },
        shelf:  { w: 100, h: 30, nameFont: 10, priceFont: 20, storeFont: 6, skuFont: 6, barcodeW: 1, barcodeH: 18 },
    };
    const sz = sizes[tagSize];

    // Build selected products
    const selectedProducts = allProducts.filter(p => p.selected);
    if (selectedProducts.length === 0) {
        alert('No products selected! Click on tags to select/deselect products.');
        return;
    }

    // Build tags HTML
    let tagsHtml = '';
    selectedProducts.forEach(product => {
        for (let c = 0; c < copies; c++) {
            const barcodeId = `print-barcode-${product.id}-${c}`;
            if (tagSize === 'shelf') {
                tagsHtml += `
                <div class="ptag ptag-shelf" style="width:${sz.w}mm; height:${sz.h}mm; ${showBorder ? 'border: 0.3mm dashed #999;' : ''}" >
                    <div class="ptag-left">
                        ${showStoreName ? `<div style="font-size:${sz.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.5mm; color:#333;">${escHtml(storeName)}</div>` : ''}
                        ${showProductName ? `<div style="font-size:${sz.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin:0.5mm 0;">${escHtml(product.name)}</div>` : ''}
                        ${showSku && product.sku ? `<div style="font-size:${sz.skuFont}pt; color:#666; font-family:monospace;">${escHtml(product.sku)}</div>` : ''}
                        ${showCategory && product.category ? `<div style="font-size:5pt; color:#888; text-transform:uppercase;">${escHtml(product.category)}</div>` : ''}
                        ${showUnit ? `<div style="font-size:5pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
                    </div>
                    <div class="ptag-right">
                        ${showBarcode && product.barcode ? `<svg id="${barcodeId}"></svg>` : ''}
                        ${showBarcodeNumber && product.barcode ? `<div style="font-size:${sz.skuFont}pt; font-family:monospace; color:#444; letter-spacing:0.3mm;">${escHtml(product.barcode)}</div>` : ''}
                        ${showPrice ? `<div style="font-size:${sz.priceFont}pt; font-weight:900; color:#000;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    </div>
                    ${showDate ? `<div style="position:absolute; bottom:1mm; right:2mm; font-size:4pt; color:#aaa;">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            } else {
                tagsHtml += `
                <div class="ptag" style="width:${sz.w}mm; height:${sz.h}mm; ${showBorder ? 'border: 0.3mm dashed #999;' : ''}">
                    ${showStoreName ? `<div style="font-size:${sz.storeFont}pt; font-weight:800; text-transform:uppercase; letter-spacing:0.5mm; color:#333;">${escHtml(storeName)}</div>` : ''}
                    ${showProductName ? `<div style="font-size:${sz.nameFont}pt; font-weight:700; color:#111; line-height:1.2; margin:0.5mm 0; overflow:hidden; max-height:${sz.nameFont * 2.5}pt;">${escHtml(product.name)}</div>` : ''}
                    ${showSku && product.sku ? `<div style="font-size:${sz.skuFont}pt; color:#666; font-family:monospace;">${escHtml(product.sku)}</div>` : ''}
                    ${showBarcode && product.barcode ? `<div style="margin:1mm 0;"><svg id="${barcodeId}"></svg></div>` : ''}
                    ${showBarcodeNumber && product.barcode ? `<div style="font-size:${sz.skuFont}pt; font-family:monospace; color:#444; letter-spacing:0.3mm;">${escHtml(product.barcode)}</div>` : ''}
                    ${showPrice ? `<div style="font-size:${sz.priceFont}pt; font-weight:900; color:#000; margin:0.5mm 0;">${escHtml(currency)} ${formatPrice(product.selling_price)}</div>` : ''}
                    ${showCategory && product.category ? `<div style="font-size:5pt; color:#888; text-transform:uppercase;">${escHtml(product.category)}</div>` : ''}
                    ${showUnit ? `<div style="font-size:5pt; color:#888;">per ${escHtml(product.unit)}</div>` : ''}
                    ${showDate ? `<div style="position:absolute; bottom:1mm; right:2mm; font-size:4pt; color:#aaa;">${new Date().toLocaleDateString()}</div>` : ''}
                </div>`;
            }
        }
    });

    // Paper size mapping
    const paperSizes = {
        a4: { w: '210mm', h: '297mm' },
        letter: { w: '8.5in', h: '11in' },
        a5: { w: '148mm', h: '210mm' },
    };
    const paper = paperSizes[paperSize];

    // Build barcode rendering script
    let barcodeScript = '';
    selectedProducts.forEach(product => {
        if (product.barcode && showBarcode) {
            for (let c = 0; c < copies; c++) {
                const id = `print-barcode-${product.id}-${c}`;
                const format = detectBarcodeFormat(product.barcode);
                const safeBarcode = product.barcode.replace(/'/g, "\\'");
                barcodeScript += `
                try {
                    JsBarcode('#${id}', '${safeBarcode}', {
                        format: '${format}',
                        width: ${sz.barcodeW},
                        height: ${sz.barcodeH},
                        displayValue: false,
                        margin: 0,
                        background: 'transparent',
                    });
                } catch(e) {
                    try {
                        JsBarcode('#${id}', '${safeBarcode}', {
                            format: 'CODE128',
                            width: 1,
                            height: ${sz.barcodeH},
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
                margin: 5mm;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
                padding: 5mm;
            }
            .ptag-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 2mm;
                justify-content: flex-start;
            }
            .ptag {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                overflow: hidden;
                position: relative;
                border-radius: 1mm;
                padding: 1.5mm 2mm;
                page-break-inside: avoid;
            }
            .ptag-shelf {
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 3mm;
                text-align: left;
                overflow: hidden;
                position: relative;
                border-radius: 1mm;
                padding: 1.5mm 3mm;
                page-break-inside: avoid;
            }
            .ptag-shelf .ptag-left {
                flex: 1;
                min-width: 0;
            }
            .ptag-shelf .ptag-right {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 0.5mm;
            }
            svg { max-width: 100%; }
            @media print {
                body { padding: 0; }
            }
        </style>
    </head>
    <body>
        <div class="ptag-grid">${tagsHtml}</div>
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

// Initial render
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
