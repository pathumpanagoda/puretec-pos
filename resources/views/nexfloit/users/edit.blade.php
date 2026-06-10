@extends('nexfloit.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('nexfloit.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit User</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>User Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('nexfloit.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   name="username" value="{{ old('username', $user->username) }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="cashier" {{ old('role', $user->role) == 'cashier' ? 'selected' : '' }}>Cashier</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                       value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">User is Active</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('nexfloit.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- User Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Info</h5>
            </div>
            <div class="card-body">
                @if($user->tenant)
                    <p class="mb-1"><strong>{{ $user->tenant->business_name }}</strong></p>
                    <p class="text-muted mb-2">{{ $user->tenant->code }}</p>
                    <a href="{{ route('nexfloit.tenants.show', $user->tenant) }}" class="btn btn-sm btn-outline-primary">
                        View Business
                    </a>
                @else
                    <p class="text-muted mb-0">No tenant assigned</p>
                @endif
            </div>
        </div>

        <!-- Password Reset Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Password</h5>
            </div>
            <div class="card-body">
                @if($user->password_changed_at)
                    <p class="mb-1">
                        <small class="text-muted">Last Changed:</small><br>
                        {{ \Carbon\Carbon::parse($user->password_changed_at)->format('M d, Y H:i') }}
                    </p>
                    @if($user->password_changed_by)
                        <p class="mb-3">
                            <small class="text-muted">Changed By:</small><br>
                            <span class="badge bg-{{ $user->password_changed_by == 'nexfloit_admin' ? 'warning' : 'secondary' }}">
                                {{ $user->password_changed_by == 'nexfloit_admin' ? 'Nexfloit Admin' : 'User' }}
                            </span>
                        </p>
                    @endif
                @else
                    <p class="text-muted mb-3">Password has never been changed</p>
                @endif

                <button type="button" class="btn btn-warning w-100 mb-2"
                        data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                    <i class="bi bi-key me-1"></i>Reset Password
                </button>

                <form action="{{ route('nexfloit.users.generate-password', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-shuffle me-1"></i>Generate Random
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('nexfloit.users.impersonate', $user) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login as User
                    </button>
                </form>

                <form action="{{ route('nexfloit.users.toggle-active', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-{{ $user->is_active ? 'danger' : 'success' }} w-100">
                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }} me-1"></i>
                        {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('nexfloit.users.reset-password', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
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
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
