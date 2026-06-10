@extends('layouts.app')

@section('title', 'Edit Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Category</h1>
        <p class="page-subtitle">Update category: {{ $category->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('categories.index') }}" class="btn-cpos btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ route('categories.update', $category) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Main Info Card --}}
        <div class="col-lg-8">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Category Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Category Name --}}
                        <div class="col-md-6">
                            <label class="form-label required">Category Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $category->name) }}" placeholder="e.g. Electronics" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Parent Category --}}
                        <div class="col-md-6">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: Select if this is a subcategory</small>
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Brief description of this category">{{ old('description', $category->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Settings Card --}}
        <div class="col-lg-4">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-gear"></i> Settings</h5>
                </div>
                <div class="card-body p-4">
                    {{-- Color --}}
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" class="form-control form-control-color"
                                   value="{{ old('color', $category->color ?? '#3b82f6') }}" style="width: 50px; height: 40px;">
                            <input type="text" class="form-control" value="{{ $category->color ?? '#3b82f6' }}" id="colorText"
                                   style="width: 100px;" readonly>
                        </div>
                        <small class="text-muted">Used for display in POS</small>
                    </div>

                    {{-- Icon --}}
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <option value="bi-tag" {{ old('icon', $category->icon) == 'bi-tag' ? 'selected' : '' }}>Tag (Default)</option>
                            <option value="bi-box" {{ old('icon', $category->icon) == 'bi-box' ? 'selected' : '' }}>Box</option>
                            <option value="bi-cart" {{ old('icon', $category->icon) == 'bi-cart' ? 'selected' : '' }}>Cart</option>
                            <option value="bi-cup-hot" {{ old('icon', $category->icon) == 'bi-cup-hot' ? 'selected' : '' }}>Cup (Beverages)</option>
                            <option value="bi-egg-fried" {{ old('icon', $category->icon) == 'bi-egg-fried' ? 'selected' : '' }}>Food</option>
                            <option value="bi-phone" {{ old('icon', $category->icon) == 'bi-phone' ? 'selected' : '' }}>Phone</option>
                            <option value="bi-laptop" {{ old('icon', $category->icon) == 'bi-laptop' ? 'selected' : '' }}>Laptop</option>
                            <option value="bi-shirt" {{ old('icon', $category->icon) == 'bi-shirt' ? 'selected' : '' }}>Clothing</option>
                            <option value="bi-house" {{ old('icon', $category->icon) == 'bi-house' ? 'selected' : '' }}>Home</option>
                            <option value="bi-gift" {{ old('icon', $category->icon) == 'bi-gift' ? 'selected' : '' }}>Gift</option>
                            <option value="bi-tools" {{ old('icon', $category->icon) == 'bi-tools' ? 'selected' : '' }}>Tools</option>
                            <option value="bi-book" {{ old('icon', $category->icon) == 'bi-book' ? 'selected' : '' }}>Books</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }} id="statusSwitch">
                            <label class="form-check-label" for="statusSwitch">Active</label>
                        </div>
                        <small class="text-muted">Inactive categories won't show in POS</small>
                    </div>

                    <hr>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn-cpos btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Update Category
                    </button>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="cpos-card mt-4">
                <div class="card-body p-4">
                    <h6 class="text-muted mb-3">Category Info</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Products:</span>
                        <span class="fw-600">{{ $category->products()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span>{{ $category->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Updated:</span>
                        <span>{{ $category->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
.btn-secondary {
    background: var(--cp-surface);
    border: 1px solid var(--cp-border);
    color: var(--cp-text);
}
.btn-secondary:hover {
    background: var(--cp-bg-alt);
}
</style>
@endpush

@push('scripts')
<script>
// Update color text when color picker changes
document.querySelector('[name="color"]').addEventListener('input', function() {
    document.getElementById('colorText').value = this.value;
});
</script>
@endpush
@endsection
