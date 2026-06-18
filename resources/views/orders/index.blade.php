@extends('layouts.app')

@section('title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">View and manage all sales orders</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('pos.index') }}" class="btn-cpos btn-primary">
            <i class="bi bi-cart-plus"></i> New Sale
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ $orders->total() }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value">{{ $orders->where('status', 'completed')->count() }}</div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-value">{{ $orders->where('status', 'pending')->count() }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div class="stat-value">{{ $orders->where('status', 'refunded')->count() }}</div>
            <div class="stat-label">Refunded</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="cpos-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-3 align-items-end cpos-filter-form">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search Order</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search order number..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">From Date</label>
                <input type="date" name="from" class="form-control" placeholder="From" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">To Date</label>
                <input type="date" name="to" class="form-control" placeholder="To" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn-cpos btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted d-none d-md-block">&nbsp;</label>
                <a href="{{ route('orders.index') }}" class="btn-cpos btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Actions Toolbar (Super Admin Only) --}}
@if(auth()->user()->role === 'super_admin')
<div class="cpos-card mb-3 bulk-actions-bar" style="display: none;">
    <div class="card-body py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-600"><span id="selectedCount">0</span> orders selected</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                <i class="bi bi-x-lg me-1"></i>Clear Selection
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkDeleteOrders()">
                <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
        </div>
    </div>
</div>
@endif

{{-- Orders Table --}}
<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-receipt"></i> All Orders</h5>
        <span class="text-muted">{{ $orders->total() }} orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    @if(auth()->user()->role === 'super_admin')
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="selectAllOrders" onchange="toggleSelectAll(this)">
                    </th>
                    @endif
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="order-row" data-order-id="{{ $order->id }}">
                    @if(auth()->user()->role === 'super_admin')
                    <td>
                        <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}" onchange="updateBulkActions()">
                    </td>
                    @endif
                    <td>
                        <span class="fw-600 text-mono" style="color: var(--cp-primary);">
                            {{ $order->order_number }}
                        </span>
                    </td>
                    <td>
                        <div>{{ $order->created_at->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        @if($order->customer)
                            <div class="fw-600">{{ $order->customer->name }}</div>
                            @if($order->customer->phone)
                                <small class="text-muted">{{ $order->customer->phone }}</small>
                            @endif
                        @else
                            <span class="text-muted">Walk-in Customer</span>
                        @endif
                    </td>
                    <td>{{ $order->user?->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $order->items_count ?? $order->items()->count() }} items</span>
                    </td>
                    <td>
                        <span class="fw-600">Rs. {{ number_format($order->total_amount, 2) }}</span>
                    </td>
                    <td>
                        @php
                            $statusClass = match($order->status) {
                                'completed' => 'status-completed',
                                'pending' => 'status-pending',
                                'cancelled' => 'status-cancelled',
                                'refunded' => 'status-refunded',
                                default => 'status-pending'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('orders.show', $order) }}" class="action-btn" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('orders.edit', $order) }}" class="action-btn" title="Edit Order" style="color: #f59e0b;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <div class="dropdown d-inline">
                                <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Print">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('orders.receipt', $order) }}" target="_blank"><i class="bi bi-receipt me-2"></i>Thermal Receipt</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a4']) }}" target="_blank"><i class="bi bi-file-text me-2"></i>A4 Invoice</a></li>
                                    <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a5']) }}" target="_blank"><i class="bi bi-file-earmark me-2"></i>A5 Invoice</a></li>
                                    <li><a class="dropdown-item" href="{{ route('orders.invoice', [$order, 'size' => 'a3']) }}" target="_blank"><i class="bi bi-file-richtext me-2"></i>A3 Invoice</a></li>
                                </ul>
                            </div>
                            @if($order->status !== 'completed' && $order->status !== 'refunded')
                            <form action="{{ route('orders.destroy', $order) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                        data-confirm="Are you sure you want to delete this order?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'super_admin' ? 9 : 8 }}" class="text-center py-5">
                        <div class="empty-state-sm">
                            <i class="bi bi-receipt"></i>
                            <p>No orders found</p>
                            <a href="{{ route('pos.index') }}" class="btn-cpos btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i> Create First Sale
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="card-body">
        {{ $orders->links() }}
    </div>
    @endif
</div>

{{-- Bulk Delete Confirmation Modal --}}
@if(auth()->user()->role === 'super_admin')
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Bulk Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning!</strong> You are about to delete <span id="deleteCount" class="fw-bold">0</span> orders.
                    This action cannot be undone!
                </div>
                <p>All related data (order items, payments) will also be deleted.</p>
                <div class="form-group mb-3">
                    <label class="form-label">Type <strong>DELETE</strong> to confirm:</label>
                    <input type="text" class="form-control" id="confirmDeleteText" placeholder="Type DELETE">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete" disabled>
                    <i class="bi bi-trash me-1"></i>Delete Orders
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
@if(auth()->user()->role === 'super_admin')
<script>
let selectedOrders = [];

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    selectedOrders = Array.from(checkboxes).map(cb => cb.value);

    const bulkBar = document.querySelector('.bulk-actions-bar');
    const countSpan = document.getElementById('selectedCount');

    if (selectedOrders.length > 0) {
        bulkBar.style.display = 'block';
        countSpan.textContent = selectedOrders.length;
    } else {
        bulkBar.style.display = 'none';
    }

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.order-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllOrders');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllOrders').checked = false;
    selectedOrders = [];
    document.querySelector('.bulk-actions-bar').style.display = 'none';
}

function bulkDeleteOrders() {
    if (selectedOrders.length === 0) return;

    document.getElementById('deleteCount').textContent = selectedOrders.length;
    document.getElementById('confirmDeleteText').value = '';
    document.getElementById('confirmBulkDelete').disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteText')?.addEventListener('input', function() {
    const confirmBtn = document.getElementById('confirmBulkDelete');
    confirmBtn.disabled = this.value !== 'DELETE';
});

document.getElementById('confirmBulkDelete')?.addEventListener('click', function() {
    if (selectedOrders.length === 0) return;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

    fetch('{{ route("orders.bulk-delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order_ids: selectedOrders })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete orders');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Orders';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting orders');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Orders';
    });
});
</script>
@endif
@endpush
@endsection
