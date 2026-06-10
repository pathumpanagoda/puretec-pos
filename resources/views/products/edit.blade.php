@extends('layouts.app')
@section('title','Edit Product')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header">
    <div><h2 class="page-title">Edit Product</h2><p class="page-subtitle">{{ $product->name }}</p></div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-cpos"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
<form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Product Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Code (SKU)</label>
                        <input type="text" name="sku" id="skuInput" class="form-control text-mono" value="{{ old('sku', $product->sku) }}">
                        <small class="text-muted">You can edit manually if needed.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <div class="input-group">
                            <input type="text" name="barcode" id="barcodeInput" class="form-control text-mono" value="{{ old('barcode', $product->barcode) }}">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()" title="Generate barcode"><i class="bi bi-upc"></i></button>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea></div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Pricing</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label required">Selling Price</label><input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0" required></div>
                    <div class="col-md-6"><label class="form-label">Cost Price</label><input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"></div>
                    <div class="col-md-6"><label class="form-label">Wholesale Price</label><input type="number" name="wholesale_price" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price) }}" step="0.01" min="0"></div>
                </div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Stock</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Current Stock</label><input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity) }}" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Min Stock</label><input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Reorder Level</label><input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', $product->reorder_level) }}" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Unit</label><select name="unit" class="form-select">@foreach(['pcs','kg','g','litre','ml','m','cm','box','pack','dozen','pair','set'] as $u)<option value="{{ $u }}" {{ $product->unit === $u ? 'selected' : '' }}>{{ strtoupper($u) }}</option>@endforeach</select></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Categorisation</h5></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">— No Category —</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">— No Supplier —</option>@foreach($suppliers as $sup)<option value="{{ $sup->id }}" {{ $product->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Tax</label><select name="tax_id" class="form-select"><option value="">No Tax</option>@foreach($taxes as $tax)<option value="{{ $tax->id }}" {{ $product->tax_id == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>@endforeach</select></div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Product Image</h5></div>
            <div class="card-body">
                <div class="image-upload-zone" id="imageZone" onclick="document.getElementById('productImage').click()">
                    <div class="upload-placeholder" id="uploadPlaceholder" @if($product->image) style="display:none" @endif>
                        <i class="bi bi-image fs-3 text-muted"></i>
                        <p class="text-muted mb-0">Click to upload image</p>
                        <small class="text-muted">JPG, PNG, WebP • Max 2MB</small>
                    </div>
                    <img id="imagePreview" class="img-preview @if(!$product->image) d-none @endif" src="{{ $product->image ? asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) : '' }}">
                </div>
                <input type="file" name="image" id="productImage" accept="image/*" class="d-none" onchange="previewImage(this)">
                @if($product->image)
                <div class="mt-2 d-flex align-items-center gap-2">
                    <small class="text-muted">Current: {{ basename($product->image) }}</small>
                    <label class="form-check mb-0">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input form-check-input-sm">
                        <span class="text-danger small">Remove image</span>
                    </label>
                </div>
                @endif
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Options</h5></div>
            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $product->is_active ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="track_stock" value="0">
                    <input type="checkbox" name="track_stock" class="form-check-input" value="1" {{ $product->track_stock ? 'checked' : '' }}>
                    <label class="form-check-label">Track Stock</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" class="form-check-input" value="1" {{ $product->is_featured ? 'checked' : '' }}>
                    <label class="form-check-label">Featured</label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
async function generateBarcode() {
    const res  = await fetch('{{ route("products.barcode") }}');
    const data = await res.json();
    document.getElementById('barcodeInput').value = data.barcode;
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('d-none');
            document.getElementById('uploadPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
