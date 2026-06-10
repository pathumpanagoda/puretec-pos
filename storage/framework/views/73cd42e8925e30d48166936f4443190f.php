<?php $__env->startSection('title','Edit Product'); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('products.index')); ?>">Products</a></li>
<li class="breadcrumb-item active">Edit</li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div><h2 class="page-title">Edit Product</h2><p class="page-subtitle"><?php echo e($product->name); ?></p></div>
    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary btn-cpos"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
<form action="<?php echo e(route('products.update', $product)); ?>" method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Product Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $product->name)); ?>" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Code (SKU)</label>
                        <input type="text" name="sku" id="skuInput" class="form-control text-mono" value="<?php echo e(old('sku', $product->sku)); ?>">
                        <small class="text-muted">You can edit manually if needed.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <div class="input-group">
                            <input type="text" name="barcode" id="barcodeInput" class="form-control text-mono" value="<?php echo e(old('barcode', $product->barcode)); ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()" title="Generate barcode"><i class="bi bi-upc"></i></button>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $product->description)); ?></textarea></div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Pricing</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label required">Selling Price</label><input type="number" name="selling_price" class="form-control" value="<?php echo e(old('selling_price', $product->selling_price)); ?>" step="0.01" min="0" required></div>
                    <div class="col-md-6"><label class="form-label">Cost Price</label><input type="number" name="cost_price" class="form-control" value="<?php echo e(old('cost_price', $product->cost_price)); ?>" step="0.01" min="0"></div>
                    <div class="col-md-6"><label class="form-label">Wholesale Price</label><input type="number" name="wholesale_price" class="form-control" value="<?php echo e(old('wholesale_price', $product->wholesale_price)); ?>" step="0.01" min="0"></div>
                </div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Stock</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Current Stock</label><input type="number" name="stock_quantity" class="form-control" value="<?php echo e(old('stock_quantity', $product->stock_quantity)); ?>" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Min Stock</label><input type="number" name="min_stock" class="form-control" value="<?php echo e(old('min_stock', $product->min_stock)); ?>" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Reorder Level</label><input type="number" name="reorder_level" class="form-control" value="<?php echo e(old('reorder_level', $product->reorder_level)); ?>" step="0.001" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Unit</label><select name="unit" class="form-select"><?php $__currentLoopData = ['pcs','kg','g','litre','ml','m','cm','box','pack','dozen','pair','set']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u); ?>" <?php echo e($product->unit === $u ? 'selected' : ''); ?>><?php echo e(strtoupper($u)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Categorisation</h5></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">— No Category —</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cat->id); ?>" <?php echo e($product->category_id == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="mb-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">— No Supplier —</option><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sup->id); ?>" <?php echo e($product->supplier_id == $sup->id ? 'selected' : ''); ?>><?php echo e($sup->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="mb-3"><label class="form-label">Tax</label><select name="tax_id" class="form-select"><option value="">No Tax</option><?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tax->id); ?>" <?php echo e($product->tax_id == $tax->id ? 'selected' : ''); ?>><?php echo e($tax->name); ?> (<?php echo e($tax->rate); ?>%)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Product Image</h5></div>
            <div class="card-body">
                <div class="image-upload-zone" id="imageZone" onclick="document.getElementById('productImage').click()">
                    <div class="upload-placeholder" id="uploadPlaceholder" <?php if($product->image): ?> style="display:none" <?php endif; ?>>
                        <i class="bi bi-image fs-3 text-muted"></i>
                        <p class="text-muted mb-0">Click to upload image</p>
                        <small class="text-muted">JPG, PNG, WebP • Max 2MB</small>
                    </div>
                    <img id="imagePreview" class="img-preview <?php if(!$product->image): ?> d-none <?php endif; ?>" src="<?php echo e($product->image ? asset(Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) : ''); ?>">
                </div>
                <input type="file" name="image" id="productImage" accept="image/*" class="d-none" onchange="previewImage(this)">
                <?php if($product->image): ?>
                <div class="mt-2 d-flex align-items-center gap-2">
                    <small class="text-muted">Current: <?php echo e(basename($product->image)); ?></small>
                    <label class="form-check mb-0">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input form-check-input-sm">
                        <span class="text-danger small">Remove image</span>
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card cpos-card mb-4">
            <div class="card-header cpos-card-header"><h5 class="card-title mb-0">Options</h5></div>
            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" <?php echo e($product->is_active ? 'checked' : ''); ?>>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="track_stock" value="0">
                    <input type="checkbox" name="track_stock" class="form-check-input" value="1" <?php echo e($product->track_stock ? 'checked' : ''); ?>>
                    <label class="form-check-label">Track Stock</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" class="form-check-input" value="1" <?php echo e($product->is_featured ? 'checked' : ''); ?>>
                    <label class="form-check-label">Featured</label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-cpos w-100 btn-lg"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
    </div>
</div>
</form>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
async function generateBarcode() {
    const res  = await fetch('<?php echo e(route("products.barcode")); ?>');
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home3/motobeatceyloanp/public_html/resources/views/products/edit.blade.php ENDPATH**/ ?>