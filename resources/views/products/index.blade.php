@extends('layouts.app')
@section('title', 'Products')
@section('breadcrumb')
<li class="breadcrumb-item active">Products</li>
@endsection
@section('content')
<div class="page-header">
    <div><h2 class="page-title">Products</h2><p class="page-subtitle">{{ $products->total() }} products {{ $lowStock > 0 ? "• {$lowStock} low stock" : '' }}</p></div>
    <div class="page-actions">
        <!-- Bulk Delete Button - Hidden by default -->
        <button type="button" class="btn btn-danger btn-cpos me-2 d-none" id="bulkDeleteBtn" onclick="bulkDeleteProducts()">
            <i class="bi bi-trash me-1"></i><span id="bulkDeleteCount">0</span> Selected - Delete
        </button>
        @if($lowStock > 0)
        <a href="{{ route('products.index', ['stock' => 'low']) }}" class="btn btn-warning btn-cpos me-2">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $lowStock }} Low Stock
        </a>
        @endif
        <button type="button" class="btn btn-outline-primary btn-cpos me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload me-1"></i>Import
        </button>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-cpos">
            <i class="bi bi-plus-lg me-2"></i>Add Product
        </a>
    </div>
</div>

<!-- Search & Filters -->
<div class="card cpos-card mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="productSearch" class="form-control" placeholder="Search by name, SKU, barcode, price, category..." value="{{ request('search') }}" autofocus>
                    <button type="button" class="btn btn-outline-secondary" id="clearSearch" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <small class="text-muted">Type to search instantly</small>
            </div>
            <div class="col-md-2">
                <select name="category" id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock" id="stockFilter" class="form-select">
                    <option value="">All Stock</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" title="Reset filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card cpos-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-cpos mb-0" id="productsTable">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAllProducts" title="Select All">
                        </th>
                        <th>Product</th>
                        <th>Item Code</th>
                        <th>Category</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsBody">
                    @forelse($products as $product)
                    <tr data-product-id="{{ $product->id }}">
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}" data-name="{{ $product->name }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="product-thumb">
                                    @if($product->image)
                                        <img src="{{ asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) }}" alt="{{ $product->name }}" class="thumb-img">
                                    @else
                                        <div class="thumb-placeholder"><i class="bi bi-box-seam"></i></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-600">{{ $product->name }}</div>
                                    <div class="text-muted small">{{ $product->unit }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-600 text-mono" style="color: var(--cp-primary);">{{ $product->sku ?? '-' }}</div>
                            @if($product->barcode)
                            <div class="text-muted text-mono small">{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>Rs. {{ number_format($product->cost_price, 2) }}</td>
                        <td class="fw-600">Rs. {{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            <div class="stock-indicator {{ $product->isOutOfStock() ? 'stock-out' : ($product->isLowStock() ? 'stock-low' : 'stock-ok') }}">
                                <span class="stock-dot"></span>
                                {{ number_format($product->stock_quantity, 0) }} {{ $product->unit }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn action-btn-quick" title="Quick Edit" onclick="openQuickEdit({{ $product->id }})"><i class="bi bi-lightning"></i></button>
                                <a href="{{ route('products.edit', $product) }}" class="action-btn" title="Full Edit"><i class="bi bi-pencil"></i></a>
                                <button class="action-btn" title="Adjust Stock" onclick="openStockAdjust({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock_quantity }})"><i class="bi bi-arrow-down-up"></i></button>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-danger" title="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="noProductsRow"><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" id="paginationContainer">{{ $products->links() }}</div>
    </div>
</div>

<!-- Stock Adjust Modal -->
<div class="modal fade" id="stockAdjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockAdjustForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><strong id="adjustProductName"></strong><br><small class="text-muted">Current Stock: <span id="adjustCurrentStock"></span></small></div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment (+/-)</label>
                        <input type="number" name="quantity" class="form-control" placeholder="+10 or -5" required step="0.001">
                        <div class="form-text">Use + to add stock, - to remove stock.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="adjustment">Manual Adjustment</option>
                            <option value="damage">Damage/Loss</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Reason for adjustment">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Products</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('products.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Upload a CSV or Excel file with product details.
                        <a href="{{ route('products.import.template') }}" class="alert-link">Download template</a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <div class="form-text">Supported formats: CSV, Excel (xlsx, xls). Max size: 10MB</div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body py-2 px-3">
                            <small class="fw-600 d-block mb-1">Required Columns:</small>
                            <small class="text-muted">name (or product_name), selling_price (or price)</small>
                            <br>
                            <small class="fw-600 d-block mb-1 mt-2">Optional Columns:</small>
                            <small class="text-muted">sku, barcode, category, cost_price, stock, unit, description</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Edit Modal - Balanced 2-Column Layout -->
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-cpos">
            <div class="modal-header modal-cpos-header py-2">
                <h5 class="modal-title"><i class="bi bi-lightning me-2"></i>Quick Edit Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickEditForm" enctype="multipart/form-data">
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <!-- Left Column: Main Form -->
                        <div class="col-md-8">
                            <!-- Product Name - Full Width -->
                            <div class="mb-3">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="qe_name" class="form-control" required autocomplete="off">
                                <div id="qeCategorySuggestion" class="d-none">
                                    <small><i class="bi bi-lightbulb text-warning me-1"></i>Suggested: <span id="qeSuggestedCategoryName"></span>
                                    <a href="#" onclick="applyQESuggestedCategory(); return false;" class="ms-2">Apply</a></small>
                                </div>
                            </div>

                            <!-- SKU & Barcode Row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">SKU (Item Code)</label>
                                    <div class="input-group">
                                        <input type="text" name="sku" id="qe_sku" class="form-control text-mono">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateSkuQE()" title="Regenerate SKU"><i class="bi bi-arrow-clockwise"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Barcode <small class="text-muted">(scan to auto-fill)</small></label>
                                    <div class="input-group">
                                        <input type="text" name="barcode" id="qe_barcode" class="form-control text-mono" placeholder="Scan or enter barcode">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateBarcodeQE()" title="Generate"><i class="bi bi-upc"></i></button>
                                        <button type="button" class="btn btn-outline-primary" onclick="lookupBarcodeAPI()" title="Lookup API"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" name="cost_price" id="qe_cost_price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" name="selling_price" id="qe_selling_price" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wholesale</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                        <input type="number" name="wholesale_price" id="qe_wholesale_price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock_quantity" id="qe_stock_quantity" class="form-control" step="0.001" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unit</label>
                                    <select name="unit" id="qe_unit" class="form-select">
                                        <option value="pcs">PCS</option>
                                        <option value="kg">KG</option>
                                        <option value="g">G</option>
                                        <option value="litre">LITRE</option>
                                        <option value="ml">ML</option>
                                        <option value="m">M</option>
                                        <option value="box">BOX</option>
                                        <option value="pack">PACK</option>
                                        <option value="dozen">DOZEN</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Min Stock</label>
                                    <input type="number" name="min_stock" id="qe_min_stock" class="form-control" step="0.001" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Reorder</label>
                                    <input type="number" name="reorder_level" id="qe_reorder_level" class="form-control" step="0.001" min="0">
                                </div>
                            </div>

                            <!-- Category & Supplier Row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" id="qe_category_id" class="form-select">
                                        <option value="">-- None --</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" id="qe_supplier_id" class="form-select">
                                        <option value="">-- None --</option>
                                        @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax</label>
                                    <select name="tax_id" id="qe_tax_id" class="form-select">
                                        <option value="">No Tax</option>
                                        @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="qe_description" class="form-control" rows="2" placeholder="Product description..."></textarea>
                            </div>

                            <!-- Options Row -->
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" id="qe_is_active" class="form-check-input" value="1">
                                    <label class="form-check-label" for="qe_is_active">Active</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="track_stock" id="qe_track_stock" class="form-check-input" value="1">
                                    <label class="form-check-label" for="qe_track_stock">Track Stock</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_featured" id="qe_is_featured" class="form-check-input" value="1">
                                    <label class="form-check-label" for="qe_is_featured">Featured</label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Image + Stats -->
                        <div class="col-md-4">
                            <!-- Image Upload -->
                            <div class="qe-image-box mb-3">
                                <div class="qe-image-zone" id="qeImageZone" onclick="document.getElementById('qe_image_input').click()">
                                    <div class="qe-image-placeholder" id="qeImagePlaceholder">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span>Click to upload</span>
                                    </div>
                                    <img id="qeImagePreview" class="qe-image-preview d-none" src="" alt="Preview">
                                </div>
                                <input type="file" name="image" id="qe_image_input" accept="image/*" class="d-none" onchange="previewQEImage(this)">
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted" id="qeImageName">No image</small>
                                    <label class="form-check mb-0 d-none" id="qeRemoveImageWrap">
                                        <input type="checkbox" name="remove_image" id="qe_remove_image" value="1" class="form-check-input form-check-input-sm">
                                        <span class="text-danger small">Remove</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Profit Calculator -->
                            <div class="qe-stats-box">
                                <div class="qe-stat">
                                    <span class="qe-stat-label">Margin</span>
                                    <span class="qe-stat-value text-success" id="qe_profit_margin">0%</span>
                                </div>
                                <div class="qe-stat">
                                    <span class="qe-stat-label">Profit</span>
                                    <span class="qe-stat-value" id="qe_profit_amount">Rs. 0</span>
                                </div>
                                <div class="qe-stat">
                                    <span class="qe-stat-label">Stock Val</span>
                                    <span class="qe-stat-value text-primary" id="qe_stock_value">Rs. 0</span>
                                </div>
                            </div>

                            <!-- Barcode Lookup Status -->
                            <div id="barcodeLookupStatus" class="alert alert-info py-2 px-3 mb-0 mt-3 d-none">
                                <small><i class="bi bi-search me-1"></i><span id="barcodeLookupMessage">Searching...</span></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 justify-content-between">
                    <a href="#" id="qe_full_edit_link" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Full Editor
                    </a>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="qe_submit_btn">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════════════
   FIX: Hide any stray large icons that shouldn't appear
   ═══════════════════════════════════════════════════════════════════════════ */
.page-content > .bi,
.card > .bi:not(.action-btn .bi):not(.btn .bi),
.main-content > .bi {
    display: none !important;
}

/* ═══════════════════════════════════════════════════════════════════════════
   BULK SELECT CHECKBOXES - Override dark theme styles
   ═══════════════════════════════════════════════════════════════════════════ */
#productsTable input[type="checkbox"].form-check-input,
#productsTable input.product-checkbox,
#productsTable #selectAllProducts {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
    cursor: pointer !important;
    border: 2px solid #4a5568 !important;
    background-color: #ffffff !important;
    border-radius: 3px !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    position: relative !important;
    vertical-align: middle !important;
}
#productsTable input[type="checkbox"].form-check-input:checked,
#productsTable input.product-checkbox:checked,
#productsTable #selectAllProducts:checked {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e") !important;
    background-size: 12px !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
}
#productsTable input[type="checkbox"]:hover {
    border-color: #3b82f6 !important;
}
#selectAllProducts:indeterminate {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M5 10h10'/%3e%3c/svg%3e") !important;
    background-size: 12px !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
}
/* Checkbox column width */
#productsTable th:first-child,
#productsTable td:first-child {
    width: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    text-align: center !important;
    padding: 8px 4px !important;
}
#bulkDeleteBtn {
    animation: pulse-attention 2s infinite;
}
@keyframes pulse-attention {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
}

/* Fix action buttons - override all responsive styles */
#productsTable .action-btns {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    flex-wrap: nowrap !important;
}
#productsTable .action-btn {
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    max-width: 30px !important;
    min-height: 30px !important;
    max-height: 30px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 13px !important;
    border-radius: 6px !important;
    flex-shrink: 0 !important;
    flex-grow: 0 !important;
}
.action-btn-quick {
    color: var(--cp-warning) !important;
    background: var(--cp-warning-light) !important;
}
.action-btn-quick:hover {
    background: var(--cp-warning) !important;
    color: white !important;
}

/* Fix pagination alignment and sizing */
#paginationContainer {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 16px !important;
    border-top: 1px solid var(--cp-border-light);
}
#paginationContainer nav {
    display: flex !important;
    justify-content: center !important;
    width: 100%;
}
#paginationContainer .pagination {
    margin: 0 !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    gap: 4px;
}
#paginationContainer .page-item .page-link {
    padding: 8px 14px !important;
    font-size: 14px !important;
    min-width: auto !important;
    min-height: auto !important;
}
/* Fix pagination icons - make them small */
#paginationContainer .page-link svg,
#paginationContainer .page-link i,
#paginationContainer svg,
.pagination svg {
    width: 14px !important;
    height: 14px !important;
    font-size: 14px !important;
}
/* Hide any stray large SVGs/icons near pagination */
.card ~ svg,
.card + svg,
#paginationContainer ~ svg,
.page-content > svg {
    display: none !important;
}

/* Fix table - prevent horizontal scroll issues */
.card-body.p-0 {
    overflow: hidden;
}
#productsTable {
    width: 100%;
    table-layout: auto;
}
#productsTable th,
#productsTable td {
    padding: 12px 10px !important;
    vertical-align: middle !important;
}
#productsTable td:last-child {
    width: 150px;
    min-width: 150px;
}

/* Quick Edit Modal Styles */
#quickEditModal .form-label {
    font-weight: 600;
    color: var(--cp-text);
    font-size: 13px;
    margin-bottom: 4px;
}
#quickEditModal .form-control,
#quickEditModal .form-select {
    font-size: 14px;
}
#quickEditModal .input-group-text {
    font-size: 13px;
    padding: 6px 10px;
}

/* Image Box */
.qe-image-box {
    background: var(--cp-bg-alt, #f8f9fa);
    border: 1px solid var(--cp-border-light, #e9ecef);
    border-radius: 10px;
    padding: 12px;
}
.qe-image-zone {
    width: 100%;
    height: 150px;
    border: 2px dashed var(--cp-border, #dee2e6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
    background: var(--cp-bg, #fff);
}
.qe-image-zone:hover {
    border-color: var(--cp-primary, #0d6efd);
    background: var(--cp-blue-50, #e7f1ff);
}
.qe-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: var(--cp-text-muted, #6c757d);
}
.qe-image-placeholder i {
    font-size: 32px;
}
.qe-image-placeholder span {
    font-size: 12px;
    font-weight: 500;
}
.qe-image-preview {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* ═══════════════════════════════════════════════════════════════════════════
   STATS BOX - MARGIN, PROFIT, STOCK VAL - Bright colors on blue
   ═══════════════════════════════════════════════════════════════════════════ */
.qe-stats-box {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    border-radius: 12px !important;
    padding: 16px !important;
    display: flex !important;
    justify-content: space-between !important;
    gap: 8px !important;
}
.qe-stat {
    text-align: center !important;
    flex: 1 !important;
}
#quickEditModal .qe-stat-label,
.qe-stats-box .qe-stat-label,
.qe-stat .qe-stat-label,
span.qe-stat-label {
    display: block !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    color: #fef08a !important; /* Bright yellow for labels */
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    margin-bottom: 4px !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}
#quickEditModal .qe-stat-value,
.qe-stats-box .qe-stat-value,
.qe-stat .qe-stat-value,
span.qe-stat-value {
    display: block !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
}
/* Bright colors for stat values - VERY VISIBLE on blue */
#quickEditModal .qe-stat-value.text-success,
.qe-stats-box .qe-stat-value.text-success,
.qe-stat-value.text-success,
#qe_profit_margin {
    color: #FFFF00 !important; /* Bright Yellow - maximum visibility on blue */
    text-shadow: 0 1px 3px rgba(0,0,0,0.5) !important;
    font-weight: 800 !important;
}
#quickEditModal .qe-stat-value.text-warning,
.qe-stat-value.text-warning { color: #FF9500 !important; } /* Orange */
#quickEditModal .qe-stat-value.text-danger,
.qe-stat-value.text-danger { color: #FF3B30 !important; } /* Bright red */
#quickEditModal .qe-stat-value.text-primary,
.qe-stat-value.text-primary { color: #ffffff !important; } /* White */

/* ═══════════════════════════════════════════════════════════════════════════
   QUICK EDIT MODAL - Toggle Switches (Active, Track Stock, Featured)
   ═══════════════════════════════════════════════════════════════════════════ */
/* Container for toggles */
#quickEditModal .d-flex.flex-wrap.gap-4 {
    gap: 12px !important;
    margin-top: 8px !important;
}
/* Toggle wrapper cards */
#quickEditModal .form-check.form-switch {
    padding: 10px 14px !important;
    background: #f1f5f9 !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 10px !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}
#quickEditModal .form-check.form-switch:hover {
    background: #e2e8f0 !important;
    border-color: #94a3b8 !important;
}
/* Switch toggle - smaller */
#quickEditModal .form-switch .form-check-input {
    width: 38px !important;
    height: 20px !important;
    margin: 0 !important;
    background-color: #94a3b8 !important;
    border: none !important;
    border-radius: 20px !important;
    cursor: pointer !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
    background-position: left center !important;
    background-size: contain !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
}
#quickEditModal .form-switch .form-check-input:checked {
    background-color: #22c55e !important;
    background-position: right center !important;
}
/* Labels - DARK text, visible */
#quickEditModal .form-switch .form-check-label,
#quickEditModal .form-check-label {
    color: #1e293b !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Category suggestion */
#qeCategorySuggestion {
    background: var(--cp-warning-light, #fff3cd);
    border: 1px solid var(--cp-warning, #ffc107);
    border-radius: 6px;
    padding: 6px 10px;
    margin-top: 6px;
}
#qeCategorySuggestion a {
    color: var(--cp-primary);
    font-weight: 600;
}
.toast-notification {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: var(--cp-success);
    color: white;
    padding: 12px 20px;
    border-radius: var(--cp-radius-md);
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 9999;
}
.toast-notification.show {
    transform: translateY(0);
    opacity: 1;
}
.toast-notification.toast-error {
    background: var(--cp-danger);
}
.toast-notification i {
    font-size: 18px;
}
</style>
@endpush

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentQuickEditId = null;
const quickEditModal = new bootstrap.Modal(document.getElementById('quickEditModal'));

// Route URLs from Laravel
const ROUTES = {
    getProduct: (id) => `{{ url('products/quick-get') }}/${id}`,
    quickUpdate: (id) => `{{ url('products') }}/${id}/quick-update`,
    adjustStock: (id) => `{{ url('products') }}/${id}/adjust-stock`,
    editProduct: (id) => `{{ url('products') }}/${id}/edit`,
    barcodeSearch: `{{ url('products/search-barcode') }}`,
    barcodeAPI: 'https://world.openfoodfacts.org/api/v0/product/'
};

// Stock Adjust
function openStockAdjust(id, name, stock) {
    document.getElementById('adjustProductName').textContent = name;
    document.getElementById('adjustCurrentStock').textContent = stock;
    document.getElementById('stockAdjustForm').action = ROUTES.adjustStock(id);
    new bootstrap.Modal(document.getElementById('stockAdjustModal')).show();
}

document.getElementById('stockAdjustForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form));
    const res = await fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
        body: JSON.stringify(data)
    });
    const json = await res.json();
    if (json.success) { location.reload(); } else { alert(json.message); }
});

// Quick Edit Functions
async function openQuickEdit(productId) {
    currentQuickEditId = productId;

    // Show loading state
    document.getElementById('qe_name').value = 'Loading...';
    clearQuickEditForm();

    try {
        const url = ROUTES.getProduct(productId);
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const product = await res.json();

        if (product.error) {
            throw new Error(product.message || product.error);
        }

        if (!product || !product.name) {
            throw new Error('Invalid product data received');
        }

        // Populate ALL form fields
        document.getElementById('qe_name').value = product.name || '';
        document.getElementById('qe_sku').value = product.sku || '';
        document.getElementById('qe_barcode').value = product.barcode || '';
        document.getElementById('qe_description').value = product.description || '';
        document.getElementById('qe_cost_price').value = product.cost_price || 0;
        document.getElementById('qe_selling_price').value = product.selling_price || 0;
        document.getElementById('qe_wholesale_price').value = product.wholesale_price || '';
        document.getElementById('qe_stock_quantity').value = product.stock_quantity || 0;
        document.getElementById('qe_min_stock').value = product.min_stock || 0;
        document.getElementById('qe_reorder_level').value = product.reorder_level || 0;
        document.getElementById('qe_unit').value = product.unit || 'pcs';
        document.getElementById('qe_category_id').value = product.category_id || '';
        document.getElementById('qe_supplier_id').value = product.supplier_id || '';
        document.getElementById('qe_tax_id').value = product.tax_id || '';
        document.getElementById('qe_is_active').checked = product.is_active;
        document.getElementById('qe_track_stock').checked = product.track_stock;
        document.getElementById('qe_is_featured').checked = product.is_featured;
        document.getElementById('qe_full_edit_link').href = ROUTES.editProduct(productId);

        // Set image
        const imgPreview = document.getElementById('qeImagePreview');
        const imgPlaceholder = document.getElementById('qeImagePlaceholder');
        const imgName = document.getElementById('qeImageName');
        const removeWrap = document.getElementById('qeRemoveImageWrap');

        if (product.image) {
            imgPreview.src = product.image;
            imgPreview.classList.remove('d-none');
            imgPlaceholder.classList.add('d-none');
            imgName.textContent = product.image_name || 'Current image';
            removeWrap.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPlaceholder.classList.remove('d-none');
            imgName.textContent = 'No image';
            removeWrap.classList.add('d-none');
        }
        document.getElementById('qe_remove_image').checked = false;
        document.getElementById('qe_image_input').value = '';

        // Calculate profit
        updateProfitDisplay();

        quickEditModal.show();
    } catch (err) {
        console.error('Failed to load product:', err);
        showToast('Failed to load product details: ' + err.message, 'error');
    }
}

function clearQuickEditForm() {
    const fields = ['qe_name', 'qe_sku', 'qe_barcode', 'qe_description', 'qe_cost_price',
        'qe_selling_price', 'qe_wholesale_price', 'qe_stock_quantity', 'qe_min_stock', 'qe_reorder_level'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Reset image
    document.getElementById('qeImagePreview').classList.add('d-none');
    document.getElementById('qeImagePlaceholder').classList.remove('d-none');
    document.getElementById('qeImageName').textContent = 'No image';
    document.getElementById('qeRemoveImageWrap').classList.add('d-none');
    document.getElementById('qe_remove_image').checked = false;
    document.getElementById('qe_image_input').value = '';
}

// Preview image in Quick Edit
function previewQEImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('qeImagePreview').src = e.target.result;
            document.getElementById('qeImagePreview').classList.remove('d-none');
            document.getElementById('qeImagePlaceholder').classList.add('d-none');
            document.getElementById('qeImageName').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Generate barcode for Quick Edit
async function generateBarcodeQE() {
    try {
        const res = await fetch(`{{ url('products/generate-barcode') }}`);
        const data = await res.json();
        document.getElementById('qe_barcode').value = data.barcode;
        showToast('Barcode generated!', 'success');
    } catch (err) {
        showToast('Failed to generate barcode', 'error');
    }
}

// Lookup barcode from external APIs
async function lookupBarcodeAPI() {
    const barcode = document.getElementById('qe_barcode').value.trim();
    if (!barcode) {
        showToast('Please enter a barcode first', 'error');
        return;
    }

    const statusDiv = document.getElementById('barcodeLookupStatus');
    const statusMsg = document.getElementById('barcodeLookupMessage');
    statusDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
    statusDiv.classList.add('alert-info');
    statusMsg.textContent = 'Searching global product databases...';

    try {
        // Try Open Food Facts first
        const offRes = await fetch(`https://world.openfoodfacts.org/api/v0/product/${barcode}.json`);
        const offData = await offRes.json();

        if (offData.status === 1 && offData.product) {
            const p = offData.product;

            // Fill form with data
            if (p.product_name) document.getElementById('qe_name').value = p.product_name;
            if (p.brands) document.getElementById('qe_description').value = `Brand: ${p.brands}`;

            // Set image from API
            if (p.image_url) {
                document.getElementById('qeImagePreview').src = p.image_url;
                document.getElementById('qeImagePreview').classList.remove('d-none');
                document.getElementById('qeImagePlaceholder').classList.add('d-none');
                document.getElementById('qeImageName').textContent = 'From API';
            }

            statusDiv.classList.remove('alert-info');
            statusDiv.classList.add('alert-success');
            statusMsg.innerHTML = `<i class="bi bi-check-circle me-2"></i>Found: <strong>${p.product_name || 'Unknown'}</strong> (${p.brands || 'Unknown brand'})`;
            showToast('Product found in Open Food Facts!', 'success');
            return;
        }

        // Try UPC Item DB as fallback
        try {
            const upcRes = await fetch(`https://api.upcitemdb.com/prod/trial/lookup?upc=${barcode}`);
            const upcData = await upcRes.json();

            if (upcData.items && upcData.items.length > 0) {
                const item = upcData.items[0];
                if (item.title) document.getElementById('qe_name').value = item.title;
                if (item.description) document.getElementById('qe_description').value = item.description;
                if (item.brand) {
                    const desc = document.getElementById('qe_description');
                    desc.value = `Brand: ${item.brand}` + (desc.value ? '\n' + desc.value : '');
                }
                if (item.images && item.images.length > 0) {
                    document.getElementById('qeImagePreview').src = item.images[0];
                    document.getElementById('qeImagePreview').classList.remove('d-none');
                    document.getElementById('qeImagePlaceholder').classList.add('d-none');
                    document.getElementById('qeImageName').textContent = 'From UPC DB';
                }

                statusDiv.classList.remove('alert-info');
                statusDiv.classList.add('alert-success');
                statusMsg.innerHTML = `<i class="bi bi-check-circle me-2"></i>Found: <strong>${item.title || 'Unknown'}</strong>`;
                showToast('Product found in UPC Database!', 'success');
                return;
            }
        } catch (e) {
            console.log('UPC API failed, continuing...');
        }

        // Not found in any database
        statusDiv.classList.remove('alert-info');
        statusDiv.classList.add('alert-warning');
        statusMsg.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>Product not found in global databases. You can enter details manually.`;

    } catch (err) {
        console.error('Barcode lookup error:', err);
        statusDiv.classList.remove('alert-info');
        statusDiv.classList.add('alert-danger');
        statusMsg.innerHTML = `<i class="bi bi-x-circle me-2"></i>Lookup failed. Please enter details manually.`;
    }
}

// Auto-lookup when barcode changes (debounced)
let barcodeTimeout = null;
document.getElementById('qe_barcode').addEventListener('input', (e) => {
    const barcode = e.target.value.trim();
    clearTimeout(barcodeTimeout);

    // Auto-lookup if barcode is 8+ digits (EAN-8, EAN-13, UPC-A)
    if (barcode.length >= 8 && /^\d+$/.test(barcode)) {
        barcodeTimeout = setTimeout(() => {
            lookupBarcodeAPI();
        }, 500);
    }
});

// ═══ GENERATE SKU - Smart Format CAT-SUB-PROD-001 ═══
async function generateSkuQE() {
    const categoryId = document.getElementById('qe_category_id').value;
    const productName = document.getElementById('qe_name').value.trim();

    let params = new URLSearchParams();
    if (categoryId) params.append('category_id', categoryId);
    if (productName) params.append('product_name', productName);

    try {
        const res = await fetch(`{{ url('products/generate-sku') }}?` + params.toString());
        const data = await res.json();
        document.getElementById('qe_sku').value = data.sku;
        showToast('SKU generated!', 'success');
    } catch (err) {
        showToast('Failed to generate SKU', 'error');
    }
}

// ═══ CATEGORY SUGGESTION - Suggest category from product name ═══
let qeSuggestedCategoryId = null;
let nameTimeout = null;

document.getElementById('qe_name').addEventListener('input', function() {
    const name = this.value.trim();
    if (nameTimeout) clearTimeout(nameTimeout);

    if (name.length >= 3) {
        nameTimeout = setTimeout(async () => {
            try {
                // Suggest category
                const res = await fetch(`{{ url('products/suggest-category') }}?name=` + encodeURIComponent(name));
                const data = await res.json();

                const suggestionEl = document.getElementById('qeCategorySuggestion');
                if (data.category_id && !document.getElementById('qe_category_id').value) {
                    qeSuggestedCategoryId = data.category_id;
                    document.getElementById('qeSuggestedCategoryName').textContent = data.category_name;
                    suggestionEl.classList.remove('d-none');
                } else {
                    suggestionEl.classList.add('d-none');
                }
            } catch (e) {
                console.log('Category suggestion failed:', e);
            }
        }, 500);
    } else {
        document.getElementById('qeCategorySuggestion').classList.add('d-none');
    }
});

// Apply suggested category
function applyQESuggestedCategory() {
    if (qeSuggestedCategoryId) {
        document.getElementById('qe_category_id').value = qeSuggestedCategoryId;
        document.getElementById('qeCategorySuggestion').classList.add('d-none');
        // Auto-generate SKU after applying category
        generateSkuQE();
        showToast('Category applied!', 'success');
    }
}

// Auto-regenerate SKU when category changes
document.getElementById('qe_category_id').addEventListener('change', function() {
    const name = document.getElementById('qe_name').value.trim();
    if (name.length >= 2) {
        generateSkuQE();
    }
});

// Enhanced barcode lookup - also trigger SKU generation after auto-fill
const originalLookupBarcodeAPI = lookupBarcodeAPI;
lookupBarcodeAPI = async function() {
    await originalLookupBarcodeAPI();
    // After auto-filling name, generate SKU
    const name = document.getElementById('qe_name').value.trim();
    if (name.length >= 2) {
        setTimeout(() => generateSkuQE(), 300);
    }
};

function updateProfitDisplay() {
    const cost = parseFloat(document.getElementById('qe_cost_price').value) || 0;
    const sell = parseFloat(document.getElementById('qe_selling_price').value) || 0;
    const stock = parseFloat(document.getElementById('qe_stock_quantity').value) || 0;

    const profit = sell - cost;
    const margin = sell > 0 ? ((profit / sell) * 100) : 0;
    const stockValue = sell * stock;

    document.getElementById('qe_profit_margin').textContent = margin.toFixed(1) + '%';
    document.getElementById('qe_profit_margin').className = 'fw-bold ' + (margin >= 20 ? 'text-success' : (margin >= 10 ? 'text-warning' : 'text-danger'));
    document.getElementById('qe_profit_amount').textContent = 'Rs. ' + profit.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('qe_stock_value').textContent = 'Rs. ' + stockValue.toLocaleString('en-US', {minimumFractionDigits: 0});
}

// Listen for price/stock changes
document.getElementById('qe_cost_price').addEventListener('input', updateProfitDisplay);
document.getElementById('qe_selling_price').addEventListener('input', updateProfitDisplay);
document.getElementById('qe_stock_quantity').addEventListener('input', updateProfitDisplay);

// Quick Edit Form Submit
document.getElementById('quickEditForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = document.getElementById('qe_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    // Use FormData to support file uploads
    const formData = new FormData();
    formData.append('name', document.getElementById('qe_name').value);
    formData.append('sku', document.getElementById('qe_sku').value || '');
    formData.append('barcode', document.getElementById('qe_barcode').value || '');
    formData.append('description', document.getElementById('qe_description').value || '');
    formData.append('cost_price', document.getElementById('qe_cost_price').value || 0);
    formData.append('selling_price', document.getElementById('qe_selling_price').value || 0);
    formData.append('wholesale_price', document.getElementById('qe_wholesale_price').value || '');
    formData.append('stock_quantity', document.getElementById('qe_stock_quantity').value || 0);
    formData.append('min_stock', document.getElementById('qe_min_stock').value || 0);
    formData.append('reorder_level', document.getElementById('qe_reorder_level').value || 0);
    formData.append('unit', document.getElementById('qe_unit').value || 'pcs');
    formData.append('category_id', document.getElementById('qe_category_id').value || '');
    formData.append('supplier_id', document.getElementById('qe_supplier_id').value || '');
    formData.append('tax_id', document.getElementById('qe_tax_id').value || '');
    formData.append('is_active', document.getElementById('qe_is_active').checked ? 1 : 0);
    formData.append('track_stock', document.getElementById('qe_track_stock').checked ? 1 : 0);
    formData.append('is_featured', document.getElementById('qe_is_featured').checked ? 1 : 0);

    // Add image if selected
    const imageInput = document.getElementById('qe_image_input');
    if (imageInput.files && imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
    }

    // Add remove_image flag
    if (document.getElementById('qe_remove_image').checked) {
        formData.append('remove_image', 1);
    }

    try {
        const res = await fetch(ROUTES.quickUpdate(currentQuickEditId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const json = await res.json();

        if (json.success) {
            quickEditModal.hide();

            // Update the table row directly without page reload
            updateProductRow(json.product);

            // Show success toast
            showToast('Product updated successfully!', 'success');
        } else {
            alert(json.message || 'Update failed');
        }
    } catch (err) {
        console.error('Quick update failed:', err);
        alert('Failed to update product');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Changes';
    }
});

function updateProductRow(product) {
    const row = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (!row) {
        location.reload();
        return;
    }

    // Update product name
    const nameCell = row.querySelector('td:first-child .fw-600');
    if (nameCell) nameCell.textContent = product.name;

    // Update category
    const categoryCell = row.querySelector('td:nth-child(3)');
    if (categoryCell) categoryCell.textContent = product.category;

    // Update cost price
    const costCell = row.querySelector('td:nth-child(4)');
    if (costCell) costCell.textContent = 'Rs. ' + Number(product.cost_price).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Update selling price
    const priceCell = row.querySelector('td:nth-child(5)');
    if (priceCell) priceCell.textContent = 'Rs. ' + Number(product.selling_price).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Update stock
    const stockCell = row.querySelector('td:nth-child(6) .stock-indicator');
    if (stockCell) {
        const stockClass = product.is_out_of_stock ? 'stock-out' : (product.is_low_stock ? 'stock-low' : 'stock-ok');
        stockCell.className = 'stock-indicator ' + stockClass;
        stockCell.innerHTML = `<span class="stock-dot"></span>${Math.floor(product.stock_quantity)} ${product.unit}`;
    }

    // Update status badge
    const statusCell = row.querySelector('td:nth-child(7) .badge');
    if (statusCell) {
        statusCell.className = 'badge ' + (product.is_active ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger');
        statusCell.textContent = product.is_active ? 'Active' : 'Inactive';
    }
}

function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Remove after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Auto Search
let searchTimeout = null;
const searchInput = document.getElementById('productSearch');
const categoryFilter = document.getElementById('categoryFilter');
const statusFilter = document.getElementById('statusFilter');
const stockFilter = document.getElementById('stockFilter');
const clearSearchBtn = document.getElementById('clearSearch');
const productsBody = document.getElementById('productsBody');
const paginationContainer = document.getElementById('paginationContainer');

function performSearch() {
    const search = searchInput.value.trim();
    const category = categoryFilter.value;
    const status = statusFilter.value;
    const stock = stockFilter.value;

    // Show/hide clear button
    clearSearchBtn.style.display = search ? 'block' : 'none';

    // If all filters are empty and search is empty, reload page for pagination
    if (!search && !category && !status && !stock) {
        location.href = '{{ route("products.index") }}';
        return;
    }

    // Show loading
    productsBody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Searching...</td></tr>';

    // Fetch results
    const params = new URLSearchParams();
    if (search) params.append('q', search);
    if (category) params.append('category', category);
    if (status) params.append('status', status);
    if (stock) params.append('stock', stock);

    fetch('{{ route("products.search") }}?' + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data.products.length === 0) {
                productsBody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i>No products match your search</td></tr>';
                paginationContainer.innerHTML = '';
                return;
            }

            let html = '';
            data.products.forEach(p => {
                const stockClass = p.is_out_of_stock ? 'stock-out' : (p.is_low_stock ? 'stock-low' : 'stock-ok');
                const statusBadge = p.is_active
                    ? '<span class="badge bg-success-soft text-success">Active</span>'
                    : '<span class="badge bg-danger-soft text-danger">Inactive</span>';
                const image = p.image
                    ? `<img src="${p.image}" alt="${p.name}" class="thumb-img">`
                    : '<div class="thumb-placeholder"><i class="bi bi-box-seam"></i></div>';

                html += `
                <tr data-product-id="${p.id}">
                    <td>
                        <input type="checkbox" class="form-check-input product-checkbox" value="${p.id}" data-name="${p.name.replace(/"/g, '&quot;')}">
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="product-thumb">${image}</div>
                            <div>
                                <div class="fw-600">${p.name}</div>
                                <div class="text-muted small">${p.unit}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-600 text-mono" style="color: var(--cp-primary);">${p.sku || '-'}</div>
                        ${p.barcode ? `<div class="text-muted text-mono small">${p.barcode}</div>` : ''}
                    </td>
                    <td>${p.category}</td>
                    <td>Rs. ${Number(p.cost_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="fw-600">Rs. ${Number(p.selling_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>
                        <div class="stock-indicator ${stockClass}">
                            <span class="stock-dot"></span>
                            ${Math.floor(p.stock_quantity)} ${p.unit}
                        </div>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn action-btn-quick" title="Quick Edit" onclick="openQuickEdit(${p.id})"><i class="bi bi-lightning"></i></button>
                            <a href="${p.edit_url}" class="action-btn" title="Full Edit"><i class="bi bi-pencil"></i></a>
                            <button class="action-btn" title="Adjust Stock" onclick="openStockAdjust(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.stock_quantity})"><i class="bi bi-arrow-down-up"></i></button>
                            <form action="${p.delete_url}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>`;
            });

            productsBody.innerHTML = html;
            paginationContainer.innerHTML = `<small class="text-muted">${data.count} product(s) found</small>`;

            // Reset bulk selection after search
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            bulkDeleteBtn.classList.add('d-none');
        })
        .catch(err => {
            console.error('Search error:', err);
            productsBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Search failed. Please try again.</td></tr>';
        });
}

// Debounced search on input
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(performSearch, 300);
});

// Immediate filter on select change
categoryFilter.addEventListener('change', performSearch);
statusFilter.addEventListener('change', performSearch);
stockFilter.addEventListener('change', performSearch);

// Clear search
clearSearchBtn.addEventListener('click', () => {
    searchInput.value = '';
    performSearch();
});

// Reset all filters
document.getElementById('resetFilters').addEventListener('click', () => {
    searchInput.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
    stockFilter.value = '';
    location.href = '{{ route("products.index") }}';
});

// Show clear button if search has value on page load
if (searchInput.value) {
    clearSearchBtn.style.display = 'block';
}

// ═══════════════════════════════════════════════════════════════════════════
// BULK DELETE FUNCTIONALITY
// ═══════════════════════════════════════════════════════════════════════════

const selectAllCheckbox = document.getElementById('selectAllProducts');
const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
const bulkDeleteCount = document.getElementById('bulkDeleteCount');

// Handle Select All checkbox
selectAllCheckbox.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkDeleteButton();
});

// Handle individual checkbox changes (using event delegation)
document.getElementById('productsBody').addEventListener('change', function(e) {
    if (e.target.classList.contains('product-checkbox')) {
        updateBulkDeleteButton();
        // Update "Select All" checkbox state
        const allCheckboxes = document.querySelectorAll('.product-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    }
});

function updateBulkDeleteButton() {
    const selectedCount = document.querySelectorAll('.product-checkbox:checked').length;
    bulkDeleteCount.textContent = selectedCount;

    if (selectedCount > 0) {
        bulkDeleteBtn.classList.remove('d-none');
    } else {
        bulkDeleteBtn.classList.add('d-none');
    }
}

async function bulkDeleteProducts() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    const selectedNames = Array.from(selectedCheckboxes).map(cb => cb.dataset.name).slice(0, 5);

    if (selectedIds.length === 0) {
        alert('Please select products to delete');
        return;
    }

    // Confirm deletion
    const namesPreview = selectedNames.join(', ') + (selectedIds.length > 5 ? ` and ${selectedIds.length - 5} more...` : '');
    if (!confirm(`Are you sure you want to delete ${selectedIds.length} product(s)?\n\n${namesPreview}\n\nThis action cannot be undone. Product images will also be deleted.`)) {
        return;
    }

    // Show loading state
    bulkDeleteBtn.disabled = true;
    bulkDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

    try {
        const response = await fetch('{{ route("products.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedIds })
        });

        const result = await response.json();

        if (result.success) {
            // Remove deleted rows from table
            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-product-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => row.remove(), 300);
                }
            });

            // Reset checkboxes
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            bulkDeleteBtn.classList.add('d-none');

            showToast(`${result.deleted} product(s) deleted successfully!`, 'success');

            // Reload page after a short delay to update counts
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(result.message || 'Failed to delete products');
        }
    } catch (err) {
        console.error('Bulk delete error:', err);
        alert('Failed to delete products. Please try again.');
    } finally {
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.innerHTML = '<i class="bi bi-trash me-1"></i><span id="bulkDeleteCount">0</span> Selected - Delete';
    }
}
</script>
@endpush
