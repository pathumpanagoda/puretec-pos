@extends('layouts.app')

@section('title', 'Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Organize your products into categories</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('categories.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    </div>
</div>

<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-tags"></i> All Categories</h5>
        <span class="text-muted">{{ $categories->count() }} categories</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            {{-- Category color indicator --}}
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ $category->color ?? '#3b82f6' }}; display: flex; align-items: center; justify-content: center;">
                                <i class="bi {{ $category->icon ?? 'bi-tag' }}" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div>
                                <div class="fw-600">{{ $category->name }}</div>
                                @if($category->children->count() > 0)
                                    <small class="text-muted">{{ $category->children->count() }} subcategories</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-muted">{{ Str::limit($category->description, 40) ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $category->products_count > 0 ? 'completed' : 'pending' }}">
                            {{ $category->products_count }} products
                        </span>
                    </td>
                    <td>
                        @if($category->is_active)
                            <span class="status-badge status-completed">Active</span>
                        @else
                            <span class="status-badge status-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('categories.edit', $category) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this category?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Show subcategories (children) --}}
                @foreach($category->children as $child)
                <tr>
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center gap-3 ps-4">
                            <i class="bi bi-arrow-return-right text-muted"></i>
                            <div style="width: 30px; height: 30px; border-radius: 6px; background: {{ $child->color ?? '#60a5fa' }}; display: flex; align-items: center; justify-content: center;">
                                <i class="bi {{ $child->icon ?? 'bi-tag' }}" style="color: white; font-size: 12px;"></i>
                            </div>
                            <div>
                                <div class="fw-600">{{ $child->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted">{{ Str::limit($child->description, 40) ?? '-' }}</span></td>
                    <td>
                        <span class="status-badge status-{{ $child->products_count > 0 ? 'completed' : 'pending' }}">
                            {{ $child->products_count }} products
                        </span>
                    </td>
                    <td>
                        @if($child->is_active)
                            <span class="status-badge status-completed">Active</span>
                        @else
                            <span class="status-badge status-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('categories.edit', $child) }}" class="action-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('categories.destroy', $child) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Delete this subcategory?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-tags"></i>
                            <p>No categories yet</p>
                            <a href="{{ route('categories.create') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Category
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
