@extends('layouts.app')

@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@php
    $seasonMode = auth()->user()->store->settings['season_mode'] ?? false;
@endphp

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage user accounts and permissions</p>
    </div>
    <div class="page-actions d-flex align-items-center gap-2">
        @if($seasonMode)
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-sun-fill me-1"></i> Season Mode ON
            </span>
        @endif
        <a href="{{ route('users.create') }}" class="btn-cpos btn-primary">
            <i class="bi bi-plus-lg"></i> Add User
        </a>
    </div>
</div>

<div class="cpos-card">
    <div class="cpos-card-header">
        <h5 class="card-title"><i class="bi bi-people"></i> All Users</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-cpos mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Payment Mode</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->username }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="status-badge status-{{ $user->role === 'admin' ? 'completed' : ($user->role === 'super_admin' ? 'refunded' : 'pending') }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td>
                        @if($user->isSuperAdmin() || $user->isAdmin() || $user->isManager())
                            <span class="badge bg-success-soft text-success">
                                <i class="bi bi-star-fill me-1"></i>Main Cashier
                            </span>
                        @elseif($user->payment_mode === 'bill_only')
                            <span class="badge bg-warning-soft text-warning">
                                <i class="bi bi-receipt me-1"></i>Bill Only
                            </span>
                            @if($seasonMode)
                                <br><small class="text-success"><i class="bi bi-check-circle"></i> Full access (Season)</small>
                            @endif
                        @else
                            <span class="badge bg-primary-soft text-primary">
                                <i class="bi bi-cash-stack me-1"></i>Full Access
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="status-badge status-completed">Active</span>
                        @else
                            <span class="status-badge status-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($user->last_login_at)
                            {{ $user->last_login_at->diffForHumans() }}
                        @else
                            <span class="text-muted">Never</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns justify-content-end">
                            @if(!$user->isSuperAdmin())
                                <a href="{{ route('users.edit', $user) }}" class="action-btn" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete"
                                                data-confirm="Are you sure you want to delete this user?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        No users found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $users->links() }}
</div>
@endif
@endsection
