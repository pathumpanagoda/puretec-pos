@extends('nexfloit.layouts.app')

@section('title', 'All Business Users')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Business Users</h1>
        <p class="text-muted">Manage all users across all tenants</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('nexfloit.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search"
                       value="{{ request('search') }}"
                       placeholder="Name, email, username...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Business</label>
                <select class="form-select" name="tenant_id">
                    <option value="">All Businesses</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('nexfloit.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Business</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Password Changed</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->tenant)
                                    <a href="{{ route('nexfloit.tenants.show', $user->tenant) }}">
                                        {{ $user->tenant->business_name }}
                                    </a>
                                    <br><small class="text-muted">{{ $user->store?->name ?? 'No Store' }}</small>
                                @else
                                    <span class="text-muted">No Tenant</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : ($user->role == 'manager' ? 'info' : 'secondary') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at }}">
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
                                    </span>
                                    <br><small class="text-muted">{{ $user->last_login_ip }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($user->password_changed_at)
                                    <span title="{{ $user->password_changed_at }}">
                                        {{ \Carbon\Carbon::parse($user->password_changed_at)->diffForHumans() }}
                                    </span>
                                    @if($user->password_changed_by)
                                        <br><small class="text-{{ $user->password_changed_by == 'nexfloit_admin' ? 'warning' : 'muted' }}">
                                            by {{ $user->password_changed_by == 'nexfloit_admin' ? 'Nexfloit Admin' : 'User' }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}"
                                            title="Reset Password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <a href="{{ route('nexfloit.users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('nexfloit.users.impersonate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Login as this user">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('nexfloit.users.toggle-active', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Reset Password Modal -->
                                <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reset Password - {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('nexfloit.users.reset-password', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Set a new password for <strong>{{ $user->email }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">New Password</label>
                                                        <input type="password" class="form-control" name="password"
                                                               required minlength="6" placeholder="Enter new password">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Confirm Password</label>
                                                        <input type="password" class="form-control" name="password_confirmation"
                                                               required placeholder="Confirm new password">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="bi bi-key me-1"></i>Reset Password
                                                    </button>
                                                </div>
                                            </form>
                                            <div class="modal-footer border-top">
                                                <form action="{{ route('nexfloit.users.generate-password', $user) }}" method="POST" class="w-100">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary w-100">
                                                        <i class="bi bi-shuffle me-1"></i>Generate Random Password
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

<style>
.user-avatar-sm {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}
</style>
@endsection
