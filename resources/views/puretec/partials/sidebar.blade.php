<aside class="puretec-sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <i class="bi bi-lightning-charge-fill brand-icon"></i>
            <span class="brand-text">Puretec</span>
        </div>
        <button class="btn btn-link sidebar-close d-lg-none">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('puretec.dashboard') ? 'active' : '' }}"
                   href="{{ route('puretec.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-section">Tenant Management</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('puretec.tenants.*') ? 'active' : '' }}"
                   href="{{ route('puretec.tenants.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Tenants</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('puretec.subscriptions.*') ? 'active' : '' }}"
                   href="{{ route('puretec.subscriptions.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Subscriptions</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('puretec.payments.*') ? 'active' : '' }}"
                   href="{{ route('puretec.payments.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li class="nav-section">Quick Actions</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('puretec.password-reset-requests.*') ? 'active' : '' }}"
                   href="{{ route('puretec.password-reset-requests.index') }}">
                    <i class="bi bi-key"></i>
                    <span>Password Resets</span>
                    @php
                        $prPendingCount = \App\Models\PasswordResetRequest::pending()->count();
                    @endphp
                    @if($prPendingCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $prPendingCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('puretec.tenants.create') }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Tenant</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('puretec.tenants.index', ['status' => 'locked']) }}">
                    <i class="bi bi-lock"></i>
                    <span>Locked Tenants</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="system-info">
            <small class="text-muted">Pure POS Platform</small>
            <small class="text-muted d-block">v1.0.0</small>
        </div>
    </div>
</aside>
