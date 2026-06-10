<aside class="nexfloit-sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <i class="bi bi-lightning-charge-fill brand-icon"></i>
            <span class="brand-text">Nexfloit</span>
        </div>
        <button class="btn btn-link sidebar-close d-lg-none">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('nexfloit.dashboard') ? 'active' : '' }}"
                   href="{{ route('nexfloit.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-section">Tenant Management</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('nexfloit.tenants.*') ? 'active' : '' }}"
                   href="{{ route('nexfloit.tenants.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Tenants</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('nexfloit.subscriptions.*') ? 'active' : '' }}"
                   href="{{ route('nexfloit.subscriptions.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Subscriptions</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('nexfloit.payments.*') ? 'active' : '' }}"
                   href="{{ route('nexfloit.payments.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li class="nav-section">Quick Actions</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('nexfloit.tenants.create') }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Tenant</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('nexfloit.tenants.index', ['status' => 'locked']) }}">
                    <i class="bi bi-lock"></i>
                    <span>Locked Tenants</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="system-info">
            <small class="text-muted">Ceyloan POS Platform</small>
            <small class="text-muted d-block">v1.0.0</small>
        </div>
    </div>
</aside>
