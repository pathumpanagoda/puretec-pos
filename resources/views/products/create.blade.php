@extends('layouts.app')
@section('title','Add Product')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Add Product</li>
@endsection
@section('content')
<div class="page-header">
    <div><h2 class="page-title">Add Product</h2></div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-cpos"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Product Name</label>
                    <input type="text" name="name" id="productName" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="off">
                    <small class="text-muted" id="categorySuggestion" style="display:none;">
                        <i class="bi bi-lightbulb text-warning"></i> Suggested: <span id="suggestedCategory"></span>
                        <a href="#" onclick="applySuggestedCategory(); return false;" class="ms-2">Apply</a>
                    </small>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Code (SKU)</label>
                        <div class="input-group">
                            <input type="text" name="sku" id="skuInput" class="form-control text-mono" value="{{ old('sku', $nextSku) }}">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateSku()" title="Generate new code">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <small class="text-muted">Auto-generated. You can edit manually.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode <small class="text-muted">(scan to auto-fill)</small></label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white" id="scannerIcon">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input type="text" name="barcode" id="barcodeInput" class="form-control text-mono" value="{{ old('barcode') }}" placeholder="Scan barcode here..." autocomplete="off" autofocus>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()" title="Auto-generate barcode">
                                <i class="bi bi-upc"></i>
                            </button>
                        </div>
                        <small class="text-muted" id="barcodeStatus">
                            <i class="bi bi-info-circle"></i> Scan product barcode to auto-fill details
                        </small>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional product description">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Pricing</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Selling Price (Rs.)</label>
                        <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', 0) }}" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price (Rs.)</label>
                        <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', 0) }}" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Wholesale Price (Rs.)</label>
                        <input type="number" name="wholesale_price" class="form-control" value="{{ old('wholesale_price') }}" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Min Selling Price (Rs.)</label>
                        <input type="number" name="min_selling_price" class="form-control" value="{{ old('min_selling_price') }}" step="0.01" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Stock & Inventory</h5></div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="track_stock" class="form-check-input" id="trackStock" value="1" {{ old('track_stock', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="trackStock">Track Stock</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" step="0.001" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min Stock (Alert)</label>
                        <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 0) }}" step="0.001" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', 0) }}" step="0.001" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select">
                            @foreach(['pcs','kg','g','litre','ml','m','cm','box','pack','dozen','pair','set'] as $u)
                            <option value="{{ $u }}" {{ old('unit','pcs') === $u ? 'selected' : '' }}>{{ strtoupper($u) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Storage Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g., Shelf A-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                    </div>
                </div>
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" name="allow_negative_stock" class="form-check-input" id="allowNegative" value="1" {{ old('allow_negative_stock') ? 'checked' : '' }}>
                    <label class="form-check-label" for="allowNegative">Allow selling when out of stock</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Categorisation</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <div class="input-group">
                        <select name="category_id" id="categorySelect" class="form-select">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" data-code="{{ $cat->code }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary" onclick="openCategoryModal('category')" title="Add new category">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3" id="subCategoryWrapper" style="display:none;">
                    <label class="form-label">Sub-Category</label>
                    <div class="input-group">
                        <select name="sub_category_id" id="subCategorySelect" class="form-select">
                            <option value="">— Select Sub-Category —</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary" onclick="openCategoryModal('subcategory')" title="Add new sub-category">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mb-3">
                    <i class="bi bi-info-circle"></i> Item code format: <strong>CAT-SUB-PROD-001</strong>
                </small>
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">— No Supplier —</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tax</label>
                    <select name="tax_id" class="form-select">
                        <option value="">No Tax</option>
                        @foreach($taxes as $tax)
                        <option value="{{ $tax->id }}" {{ old('tax_id') == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Product Image</h5></div>
            <div class="card-body">
                <div class="image-upload-zone" id="imageZone" onclick="document.getElementById('productImage').click()">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <i class="bi bi-image fs-3 text-muted"></i>
                        <p class="text-muted mb-0">Click to upload image</p>
                        <small class="text-muted">JPG, PNG, WebP • Max 2MB</small>
                    </div>
                    <img id="imagePreview" class="img-preview d-none">
                </div>
                <input type="file" name="image" id="productImage" accept="image/*" class="d-none" onchange="previewImage(this)">
            </div>
        </div>

        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Options</h5></div>
            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label class="form-check-label">Active (visible in POS)</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_taxable" value="0">
                    <input type="checkbox" name="is_taxable" class="form-check-input" value="1" {{ old('is_taxable', 1) ? 'checked' : '' }}>
                    <label class="form-check-label">Taxable</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" class="form-check-input" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                    <label class="form-check-label">Featured Product</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg"><i class="bi bi-check-lg me-2"></i>Save Product</button>
    </div>
</div>
</form>

<!-- Quick Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="newCategoryType" value="category">

                <div class="mb-3">
                    <label class="form-label required">Name</label>
                    <input type="text" id="newCategoryName" class="form-control" placeholder="e.g., Electronics, Phones">
                </div>

                <div class="mb-3">
                    <label class="form-label required">Code (3-4 letters)</label>
                    <div class="input-group">
                        <input type="text" id="newCategoryCode" class="form-control text-mono" placeholder="e.g., ELEC, PHN" maxlength="10" style="text-transform:uppercase;">
                        <button type="button" class="btn btn-outline-secondary" onclick="suggestCategoryCode()" title="Auto-generate">
                            <i class="bi bi-magic"></i>
                        </button>
                    </div>
                    <small class="text-muted">Used in item code: ELEC-PHN-PROD-001</small>
                </div>

                <div class="mb-3" id="parentCategoryWrapper" style="display:none;">
                    <label class="form-label">Parent Category</label>
                    <input type="text" id="parentCategoryName" class="form-control" readonly>
                    <input type="hidden" id="parentCategoryId">
                </div>

                <div id="categoryError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickCategory()">
                    <i class="bi bi-plus-lg me-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Barcode Scanner Styles */
#scannerIcon {
    transition: all 0.3s ease;
}
#scannerIcon.bg-success {
    animation: pulse-scan 1.5s ease-in-out infinite;
}
@keyframes pulse-scan {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
#barcodeInput:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}
.barcode-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 10px;
    color: #fff;
    font-weight: 600;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.barcode-toast.show {
    transform: translateX(0);
}
.barcode-toast.success {
    background: linear-gradient(135deg, #28a745, #20c997);
}
.barcode-toast.info {
    background: linear-gradient(135deg, #17a2b8, #6f42c1);
}
.barcode-toast.warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #333;
}
</style>
@endsection
@push('scripts')
<script>
/*
   Smart Product Form Features:
   1. Auto SKU: CAT-SUB-PROD-001 format
   2. Dynamic sub-categories
   3. Category suggestion from product name
   4. Barcode scanner support
*/

let suggestedCategoryId = null;

// ═══ IMAGE PREVIEW ═══
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('d-none');
            document.getElementById('uploadPlaceholder').classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ═══ GENERATE BARCODE (EAN-13) ═══
async function generateBarcode() {
    const res = await fetch('{{ route("products.barcode") }}');
    const data = await res.json();
    document.getElementById('barcodeInput').value = data.barcode;
    document.getElementById('barcodeStatus').innerHTML = '<i class="bi bi-check-circle text-success"></i> Barcode generated';
}

// ═══ GENERATE SMART SKU ═══
// Format: CAT-SUB-PROD-001 (e.g., ELEC-PHN-IPHN-001)
async function generateSku() {
    const categoryId = document.getElementById('categorySelect').value;
    const subCategoryId = document.getElementById('subCategorySelect').value;
    const productName = document.getElementById('productName').value.trim();

    // Build URL with parameters
    let params = new URLSearchParams();
    if (categoryId) params.append('category_id', categoryId);
    if (subCategoryId) params.append('sub_category_id', subCategoryId);
    if (productName) params.append('product_name', productName);

    const url = '{{ route("products.sku") }}?' + params.toString();
    const res = await fetch(url);
    const data = await res.json();
    document.getElementById('skuInput').value = data.sku;
}

// ═══ LOAD SUB-CATEGORIES ═══
async function loadSubCategories(parentId) {
    const wrapper = document.getElementById('subCategoryWrapper');
    const select = document.getElementById('subCategorySelect');

    if (!parentId) {
        wrapper.style.display = 'none';
        select.innerHTML = '<option value="">— Select Sub-Category —</option>';
        return;
    }

    // Fetch sub-categories
    const res = await fetch('{{ route("products.sub-categories") }}?parent_id=' + parentId);
    const data = await res.json();

    if (data.sub_categories && data.sub_categories.length > 0) {
        // Build options
        let options = '<option value="">— Select Sub-Category —</option>';
        data.sub_categories.forEach(sub => {
            options += `<option value="${sub.id}" data-code="${sub.code}">${sub.name}</option>`;
        });
        select.innerHTML = options;
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
        select.innerHTML = '<option value="">— No Sub-Categories —</option>';
    }
}

// ═══ CATEGORY CHANGE ═══
document.getElementById('categorySelect').addEventListener('change', function() {
    loadSubCategories(this.value);
    generateSku();
});

// ═══ SUB-CATEGORY CHANGE ═══
document.getElementById('subCategorySelect').addEventListener('change', function() {
    generateSku();
});

// ═══ PRODUCT NAME CHANGE → SUGGEST CATEGORY & UPDATE SKU ═══
let nameTimeout = null;
document.getElementById('productName').addEventListener('input', function() {
    const name = this.value.trim();

    if (nameTimeout) clearTimeout(nameTimeout);

    if (name.length >= 3) {
        nameTimeout = setTimeout(async () => {
            // Suggest category
            const res = await fetch('{{ route("products.suggest-category") }}?name=' + encodeURIComponent(name));
            const data = await res.json();

            if (data.category_id) {
                suggestedCategoryId = data.category_id;
                document.getElementById('suggestedCategory').textContent = data.category_name;
                document.getElementById('categorySuggestion').style.display = 'block';
            } else {
                document.getElementById('categorySuggestion').style.display = 'none';
            }

            // Update SKU with product name
            generateSku();
        }, 500);
    } else {
        document.getElementById('categorySuggestion').style.display = 'none';
    }
});

// ═══ APPLY SUGGESTED CATEGORY ═══
function applySuggestedCategory() {
    if (suggestedCategoryId) {
        document.getElementById('categorySelect').value = suggestedCategoryId;
        document.getElementById('categorySuggestion').style.display = 'none';
        loadSubCategories(suggestedCategoryId);
        generateSku();
    }
}

// ═══ BARCODE SCANNER & MANUAL INPUT SUPPORT ═══
// Detects both USB scanner input (rapid typing) and manual entry

let barcodeBuffer = '';
let lastKeyTime = 0;
let scannerTimeout = null;
const SCANNER_SPEED_THRESHOLD = 50; // Scanner types faster than 50ms per character

const barcodeInput = document.getElementById('barcodeInput');
const scannerIcon = document.getElementById('scannerIcon');

// Visual feedback for scanner mode
barcodeInput.addEventListener('focus', function() {
    scannerIcon.classList.add('bg-success');
    scannerIcon.classList.remove('bg-primary');
    document.getElementById('barcodeStatus').innerHTML = '<i class="bi bi-upc-scan text-success"></i> Ready to scan or type barcode...';
});

barcodeInput.addEventListener('blur', function() {
    scannerIcon.classList.remove('bg-success');
    scannerIcon.classList.add('bg-primary');
});

// Handle barcode input (scanner or manual)
barcodeInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = this.value.trim();
        if (barcode.length >= 8) {
            lookupBarcode(barcode);
        }
    }
});

// Also lookup on blur (when clicking away after typing)
barcodeInput.addEventListener('blur', function() {
    const barcode = this.value.trim();
    if (barcode.length >= 8) {
        // Small delay to avoid double-lookup
        setTimeout(() => {
            if (document.getElementById('productName').value === '') {
                lookupBarcode(barcode);
            }
        }, 200);
    }
});

// Search button next to barcode
barcodeInput.addEventListener('input', function() {
    const barcode = this.value.trim();
    if (barcode.length >= 12) {
        // Auto-lookup for long barcodes (likely scanned)
        clearTimeout(scannerTimeout);
        scannerTimeout = setTimeout(() => lookupBarcode(barcode), 300);
    }
});

// ═══ LOOKUP BARCODE - Check local DB + External API ═══
async function lookupBarcode(barcode) {
    const statusEl = document.getElementById('barcodeStatus');
    const iconEl = document.getElementById('scannerIcon');

    // Show loading state
    statusEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Looking up product...';
    iconEl.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        // Step 1: Check if exists in our database
        const localRes = await fetch('{{ route("products.search-barcode") }}?barcode=' + encodeURIComponent(barcode));
        const localData = await localRes.json();

        if (localData.found && localData.exists_in_store) {
            // Product already exists in store
            statusEl.innerHTML = '<i class="bi bi-exclamation-triangle text-warning"></i> Already exists: <strong>' + localData.product.name + '</strong>';
            iconEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
            iconEl.classList.remove('bg-success', 'bg-primary');
            iconEl.classList.add('bg-warning');
            return;
        }

        // Step 2: Lookup in external database (Open Food Facts)
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Searching product database...';

        const externalProduct = await fetchProductFromAPI(barcode);

        if (externalProduct) {
            // Found in external database - auto-fill form!
            autoFillProductForm(externalProduct);
            statusEl.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <strong>Product found!</strong> Details auto-filled.';
            iconEl.innerHTML = '<i class="bi bi-check-lg"></i>';
            iconEl.classList.remove('bg-primary', 'bg-warning');
            iconEl.classList.add('bg-success');

            // Show toast notification
            showBarcodeToast('Product found: ' + externalProduct.name, 'success');
        } else {
            // Not found - user can fill manually
            statusEl.innerHTML = '<i class="bi bi-info-circle text-info"></i> New product - please fill details manually';
            iconEl.innerHTML = '<i class="bi bi-upc-scan"></i>';
            iconEl.classList.remove('bg-success', 'bg-warning');
            iconEl.classList.add('bg-primary');
        }

    } catch (err) {
        console.error('Barcode lookup error:', err);
        statusEl.innerHTML = '<i class="bi bi-info-circle"></i> Barcode entered - fill product details';
        iconEl.innerHTML = '<i class="bi bi-upc-scan"></i>';
    }
}

// ═══ FETCH FROM EXTERNAL API (Open Food Facts) ═══
async function fetchProductFromAPI(barcode) {
    try {
        // Try Open Food Facts API (free, no API key needed)
        const response = await fetch(`https://world.openfoodfacts.org/api/v0/product/${barcode}.json`);
        const data = await response.json();

        if (data.status === 1 && data.product) {
            const p = data.product;
            return {
                name: p.product_name || p.product_name_en || '',
                brand: p.brands || '',
                description: p.generic_name || p.ingredients_text || '',
                category: p.categories || '',
                image: p.image_url || p.image_front_url || null,
                quantity: p.quantity || '',
                weight: p.product_quantity || ''
            };
        }

        // If not found in Open Food Facts, try UPC Database
        // (You can add more APIs here if needed)

        return null;
    } catch (err) {
        console.error('External API error:', err);
        return null;
    }
}

// ═══ AUTO-FILL PRODUCT FORM ═══
function autoFillProductForm(product) {
    // Fill product name
    if (product.name) {
        let fullName = product.name;
        if (product.brand && !product.name.toLowerCase().includes(product.brand.toLowerCase())) {
            fullName = product.brand + ' ' + product.name;
        }
        document.getElementById('productName').value = fullName;

        // Trigger category suggestion
        const event = new Event('input', { bubbles: true });
        document.getElementById('productName').dispatchEvent(event);
    }

    // Fill description
    if (product.description) {
        const descField = document.querySelector('textarea[name="description"]');
        if (descField && !descField.value) {
            descField.value = product.description.substring(0, 500);
        }
    }

    // Load image if available
    if (product.image) {
        loadProductImage(product.image);
    }

    // Generate SKU after filling name
    setTimeout(() => generateSku(), 500);
}

// ═══ LOAD PRODUCT IMAGE FROM URL ═══
async function loadProductImage(imageUrl) {
    try {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('uploadPlaceholder');

        // Show the image preview
        preview.src = imageUrl;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');

        // Store URL for backend to download
        let urlInput = document.getElementById('imageUrlInput');
        if (!urlInput) {
            urlInput = document.createElement('input');
            urlInput.type = 'hidden';
            urlInput.name = 'image_url';
            urlInput.id = 'imageUrlInput';
            document.querySelector('form').appendChild(urlInput);
        }
        urlInput.value = imageUrl;

    } catch (err) {
        console.error('Image load error:', err);
    }
}

// ═══ TOAST NOTIFICATION ═══
function showBarcodeToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'barcode-toast ' + type;
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ═══ QUICK ADD CATEGORY MODAL ═══
let categoryModal = null;

document.addEventListener('DOMContentLoaded', function() {
    categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
});

// Open modal for adding category or sub-category
function openCategoryModal(type) {
    const titleEl = document.getElementById('categoryModalTitle');
    const typeEl = document.getElementById('newCategoryType');
    const parentWrapper = document.getElementById('parentCategoryWrapper');
    const parentNameEl = document.getElementById('parentCategoryName');
    const parentIdEl = document.getElementById('parentCategoryId');

    // Clear previous values
    document.getElementById('newCategoryName').value = '';
    document.getElementById('newCategoryCode').value = '';
    document.getElementById('categoryError').classList.add('d-none');

    if (type === 'subcategory') {
        // Sub-category - need parent
        const categorySelect = document.getElementById('categorySelect');
        const parentId = categorySelect.value;
        const parentName = categorySelect.options[categorySelect.selectedIndex]?.text;

        if (!parentId) {
            alert('Please select a Category first before adding a Sub-Category');
            return;
        }

        titleEl.textContent = 'Add New Sub-Category';
        typeEl.value = 'subcategory';
        parentWrapper.style.display = 'block';
        parentNameEl.value = parentName;
        parentIdEl.value = parentId;
    } else {
        // Main category
        titleEl.textContent = 'Add New Category';
        typeEl.value = 'category';
        parentWrapper.style.display = 'none';
        parentIdEl.value = '';
    }

    categoryModal.show();
}

// Auto-suggest category code from name
async function suggestCategoryCode() {
    const name = document.getElementById('newCategoryName').value.trim();
    if (!name) return;

    const res = await fetch('{{ route("categories.suggest-code") }}?name=' + encodeURIComponent(name));
    const data = await res.json();
    document.getElementById('newCategoryCode').value = data.code;
}

// Save new category via AJAX
async function saveQuickCategory() {
    const name = document.getElementById('newCategoryName').value.trim();
    const code = document.getElementById('newCategoryCode').value.trim().toUpperCase();
    const type = document.getElementById('newCategoryType').value;
    const parentId = document.getElementById('parentCategoryId').value;
    const errorEl = document.getElementById('categoryError');

    // Validate
    if (!name || !code) {
        errorEl.textContent = 'Please enter name and code';
        errorEl.classList.remove('d-none');
        return;
    }

    if (code.length < 2 || code.length > 10) {
        errorEl.textContent = 'Code must be 2-10 characters';
        errorEl.classList.remove('d-none');
        return;
    }

    try {
        const res = await fetch('{{ route("categories.quick") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name,
                code: code,
                parent_id: parentId || null
            })
        });

        const data = await res.json();

        if (data.success) {
            // Add to appropriate dropdown
            if (type === 'subcategory') {
                // Add to sub-category dropdown
                const subSelect = document.getElementById('subCategorySelect');
                const option = new Option(data.category.name, data.category.id);
                option.dataset.code = data.category.code;
                subSelect.add(option);
                subSelect.value = data.category.id;
            } else {
                // Add to main category dropdown
                const catSelect = document.getElementById('categorySelect');
                const option = new Option(data.category.name, data.category.id);
                option.dataset.code = data.category.code;
                catSelect.add(option);
                catSelect.value = data.category.id;

                // Trigger change to load sub-categories (will be empty for new category)
                loadSubCategories(data.category.id);
            }

            // Update SKU
            generateSku();

            // Close modal
            categoryModal.hide();

            // Show success message
            if (typeof CeylonPOS !== 'undefined') {
                CeylonPOS.showToast('Category "' + name + '" added!', 'success');
            }
        } else {
            errorEl.textContent = data.message || 'Failed to add category';
            errorEl.classList.remove('d-none');
        }
    } catch (err) {
        errorEl.textContent = 'Error: ' + err.message;
        errorEl.classList.remove('d-none');
    }
}

// Auto-suggest code when typing name
document.getElementById('newCategoryName').addEventListener('input', function() {
    // Auto-generate code after short delay
    clearTimeout(this._timeout);
    this._timeout = setTimeout(() => {
        if (this.value.length >= 2 && !document.getElementById('newCategoryCode').value) {
            suggestCategoryCode();
        }
    }, 300);
});
</script>
@endpush
