<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0d0d">
    <title>@yield('title', 'Dashboard') — PUREPOS</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ceylon-pos.css') }}">
    @stack('styles')
</head>
<body class="@yield('body-class', 'app-body')">

{{-- Impersonation Bar - Shows when Nexfloit admin is logged in as a user --}}
@if(session('nexfloit_impersonating'))
<div class="impersonation-bar">
    <div class="impersonation-content">
        <i class="bi bi-person-badge me-2"></i>
        <span>You are logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->tenant?->business_name ?? 'No Tenant' }})</span>
        <form action="{{ route('nexfloit.stop-impersonate') }}" method="POST" class="d-inline ms-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-light">
                <i class="bi bi-box-arrow-left me-1"></i>Exit & Return to Nexfloit
            </button>
        </form>
    </div>
</div>
<style>
.impersonation-bar {
    background: linear-gradient(135deg, #e94560, #c73e54);
    color: #fff;
    padding: 8px 20px;
    text-align: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99999;
    font-size: 14px;
}
.impersonation-bar + * .cpos-sidebar,
.impersonation-bar + * .main-content {
    margin-top: 44px;
}
body:has(.impersonation-bar) .cpos-sidebar {
    top: 44px;
    height: calc(100vh - 44px);
}
body:has(.impersonation-bar) .main-content {
    padding-top: 44px;
}
</style>
@endif

@auth
<!-- Sidebar -->
<nav id="sidebar" class="cpos-sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <img src="{{ asset('icons/logo png.png') }}" alt="Logo" class="brand-logo-img">
            </div>
            <div class="brand-text">
                <span class="brand-name"><span class="text-gold">PURE</span>POS</span>
                <span class="brand-tagline">by PURETEC</span>
            </div>
        </div>
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="sidebar-toggle d-lg-none" id="sidebarClose"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="sidebar-store">
        <div class="store-avatar"><i class="bi bi-building"></i></div>
        <div class="store-info">
            <div class="store-name">{{ auth()->user()->store?->name ?? 'PUREPOS' }}</div>
            <div class="store-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        @if(auth()->user()->canAccess('dashboard'))
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('pos'))
        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos*') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i><span>Point of Sale</span>
            <span class="nav-badge">POS</span>
        </a>
        @endif

        @if(auth()->user()->canAccess('products') || auth()->user()->canAccess('categories') || auth()->user()->canAccess('inventory'))
        <div class="nav-section-label mt-3">Catalogue</div>
        @endif
        @if(auth()->user()->canAccess('products'))
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i><span>Products</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('categories'))
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i><span>Categories</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('inventory'))
        <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory*') ? 'active' : '' }}">
            <i class="bi bi-archive"></i><span>Inventory</span>
        </a>
        @endif

        @if(auth()->user()->canAccess('customers') || auth()->user()->canAccess('suppliers'))
        <div class="nav-section-label mt-3">People</div>
        @endif
        @if(auth()->user()->canAccess('customers'))
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>Customers</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('suppliers'))
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i><span>Suppliers</span>
        </a>
        @endif

        @if(auth()->user()->canAccess('orders') || auth()->user()->canAccess('purchases') || auth()->user()->canAccess('expenses'))
        <div class="nav-section-label mt-3">Finance</div>
        @endif
        @if(auth()->user()->canAccess('orders'))
        <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i><span>Orders</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('purchases'))
        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases*') ? 'active' : '' }}">
            <i class="bi bi-bag-check"></i><span>Purchases</span>
        </a>
        @endif
        @if(auth()->user()->canAccess('expenses'))
        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i><span>Expenses</span>
        </a>
        @endif
        <a href="{{ route('incomes.index') }}" class="nav-link {{ request()->routeIs('incomes*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i><span>Income</span>
        </a>

        @if(auth()->user()->canAccess('reports'))
        <div class="nav-section-label mt-3">Analytics</div>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i><span>Reports</span>
        </a>
        @endif

        @if(auth()->user()->isAdmin())
        <div class="nav-section-label mt-3">System</div>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i><span>Users</span>
        </a>
        @if(auth()->user()->canAccess('settings'))
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i><span>Settings</span>
        </a>
        @endif
        @endif
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('profile') }}" class="sidebar-user">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn-logout" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
        </form>
    </div>
</nav>

<!-- Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<!-- Main Content -->
<div id="mainContent" class="main-content">
    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="topbar-center">
            @yield('topbar-center')
        </div>
        <div class="topbar-right">
            <div class="topbar-clock" id="topbarClock"></div>
            <a href="{{ route('pos.index') }}" class="btn-pos-quick" title="Open POS">
                <i class="bi bi-cart3"></i>
            </a>
            <div class="topbar-theme">
                <button class="btn-theme-toggle" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>
            </div>
            <div class="topbar-notif">
                <button class="btn-notif" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @php $lowStockCount = \App\Models\Product::where('store_id', auth()->user()->store_id)->where('track_stock',true)->whereColumn('stock_quantity','<=','min_stock')->count(); @endphp
                    @if($lowStockCount > 0)
                        <span class="notif-badge">{{ $lowStockCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-header">Notifications</div>
                    @if($lowStockCount > 0)
                        <a href="{{ route('reports.inventory') }}" class="notif-item">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <div>
                                <div class="notif-title">Low Stock Alert</div>
                                <div class="notif-text">{{ $lowStockCount }} product(s) need reordering</div>
                            </div>
                        </a>
                    @else
                        <div class="notif-empty"><i class="bi bi-check-circle text-success"></i> All good!</div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible alert-floating fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible alert-floating fade show">
                <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

{{-- Payment Reminder Popup for tenants with upcoming payment due --}}
@include('components.payment-reminder-popup')
@endauth

@guest
    @yield('content')
@endguest

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/ceylon-pos.js') }}"></script>
@stack('scripts')

<script>
// Live clock
function updateClock() {
    const now = new Date();
    const el = document.getElementById('topbarClock');
    if (el) el.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
}
setInterval(updateClock, 1000); updateClock();

// Theme toggle
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');
const html        = document.documentElement;
const savedTheme  = localStorage.getItem('ceylon-pos-theme') || 'dark';
html.setAttribute('data-theme', savedTheme);
if (themeIcon) themeIcon.className = savedTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        const next    = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('ceylon-pos-theme', next);
        if (themeIcon) themeIcon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    });
}
</script>
</body>
</html>
