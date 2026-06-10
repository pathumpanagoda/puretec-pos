<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#0d6e8a">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> — Ceyloan POS</title>
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('icons/icon-192.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('icons/icon-192.png')); ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/ceylon-pos.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="<?php echo $__env->yieldContent('body-class', 'app-body'); ?>">


<?php if(session('nexfloit_impersonating')): ?>
<div class="impersonation-bar">
    <div class="impersonation-content">
        <i class="bi bi-person-badge me-2"></i>
        <span>You are logged in as <strong><?php echo e(auth()->user()->name); ?></strong> (<?php echo e(auth()->user()->tenant?->business_name ?? 'No Tenant'); ?>)</span>
        <form action="<?php echo e(route('nexfloit.stop-impersonate')); ?>" method="POST" class="d-inline ms-3">
            <?php echo csrf_field(); ?>
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
<?php endif; ?>

<?php if(auth()->guard()->check()): ?>
<!-- Sidebar -->
<nav id="sidebar" class="cpos-sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-shop-window"></i></div>
            <div class="brand-text">
                <span class="brand-name">Ceyloan POS</span>
                <span class="brand-tagline">by Nexfloit</span>
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
            <div class="store-name"><?php echo e(auth()->user()->store?->name ?? 'Ceyloan POS'); ?></div>
            <div class="store-role"><?php echo e(ucfirst(str_replace('_', ' ', auth()->user()->role))); ?></div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <?php if(auth()->user()->canAccess('dashboard')): ?>
        <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard*') ? 'active' : ''); ?>">
            <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('pos')): ?>
        <a href="<?php echo e(route('pos.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pos*') ? 'active' : ''); ?>">
            <i class="bi bi-cart3"></i><span>Point of Sale</span>
            <span class="nav-badge">POS</span>
        </a>
        <?php endif; ?>

        <?php if(auth()->user()->canAccess('products') || auth()->user()->canAccess('categories') || auth()->user()->canAccess('inventory')): ?>
        <div class="nav-section-label mt-3">Catalogue</div>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('products')): ?>
        <a href="<?php echo e(route('products.index')); ?>" class="nav-link <?php echo e(request()->routeIs('products*') ? 'active' : ''); ?>">
            <i class="bi bi-box-seam"></i><span>Products</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('categories')): ?>
        <a href="<?php echo e(route('categories.index')); ?>" class="nav-link <?php echo e(request()->routeIs('categories*') ? 'active' : ''); ?>">
            <i class="bi bi-tags"></i><span>Categories</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('inventory')): ?>
        <a href="<?php echo e(route('inventory.index')); ?>" class="nav-link <?php echo e(request()->routeIs('inventory*') ? 'active' : ''); ?>">
            <i class="bi bi-archive"></i><span>Inventory</span>
        </a>
        <?php endif; ?>

        <?php if(auth()->user()->canAccess('customers') || auth()->user()->canAccess('suppliers')): ?>
        <div class="nav-section-label mt-3">People</div>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('customers')): ?>
        <a href="<?php echo e(route('customers.index')); ?>" class="nav-link <?php echo e(request()->routeIs('customers*') ? 'active' : ''); ?>">
            <i class="bi bi-people"></i><span>Customers</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('suppliers')): ?>
        <a href="<?php echo e(route('suppliers.index')); ?>" class="nav-link <?php echo e(request()->routeIs('suppliers*') ? 'active' : ''); ?>">
            <i class="bi bi-truck"></i><span>Suppliers</span>
        </a>
        <?php endif; ?>

        <?php if(auth()->user()->canAccess('orders') || auth()->user()->canAccess('purchases') || auth()->user()->canAccess('expenses')): ?>
        <div class="nav-section-label mt-3">Finance</div>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('orders')): ?>
        <a href="<?php echo e(route('orders.index')); ?>" class="nav-link <?php echo e(request()->routeIs('orders*') ? 'active' : ''); ?>">
            <i class="bi bi-receipt"></i><span>Orders</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('purchases')): ?>
        <a href="<?php echo e(route('purchases.index')); ?>" class="nav-link <?php echo e(request()->routeIs('purchases*') ? 'active' : ''); ?>">
            <i class="bi bi-bag-check"></i><span>Purchases</span>
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->canAccess('expenses')): ?>
        <a href="<?php echo e(route('expenses.index')); ?>" class="nav-link <?php echo e(request()->routeIs('expenses*') ? 'active' : ''); ?>">
            <i class="bi bi-wallet2"></i><span>Expenses</span>
        </a>
        <?php endif; ?>
        <a href="<?php echo e(route('incomes.index')); ?>" class="nav-link <?php echo e(request()->routeIs('incomes*') ? 'active' : ''); ?>">
            <i class="bi bi-cash-stack"></i><span>Income</span>
        </a>

        <?php if(auth()->user()->canAccess('reports')): ?>
        <div class="nav-section-label mt-3">Analytics</div>
        <a href="<?php echo e(route('reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('reports*') ? 'active' : ''); ?>">
            <i class="bi bi-bar-chart-line"></i><span>Reports</span>
        </a>
        <?php endif; ?>

        <?php if(auth()->user()->isAdmin()): ?>
        <div class="nav-section-label mt-3">System</div>
        <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('users*') ? 'active' : ''); ?>">
            <i class="bi bi-person-gear"></i><span>Users</span>
        </a>
        <?php if(auth()->user()->canAccess('settings')): ?>
        <a href="<?php echo e(route('settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('settings*') ? 'active' : ''); ?>">
            <i class="bi bi-gear"></i><span>Settings</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="<?php echo e(route('profile')); ?>" class="sidebar-user">
            <div class="user-avatar"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo e(auth()->user()->name); ?></div>
                <div class="user-email"><?php echo e(auth()->user()->email); ?></div>
            </div>
        </a>
        <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
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
                    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Home</a></li>
                    <?php echo $__env->yieldContent('breadcrumb'); ?>
                </ol>
            </nav>
        </div>
        <div class="topbar-center">
            <?php echo $__env->yieldContent('topbar-center'); ?>
        </div>
        <div class="topbar-right">
            <div class="topbar-clock" id="topbarClock"></div>
            <a href="<?php echo e(route('pos.index')); ?>" class="btn-pos-quick" title="Open POS">
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
                    <?php $lowStockCount = \App\Models\Product::where('store_id', auth()->user()->store_id)->where('track_stock',true)->whereColumn('stock_quantity','<=','min_stock')->count(); ?>
                    <?php if($lowStockCount > 0): ?>
                        <span class="notif-badge"><?php echo e($lowStockCount); ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-header">Notifications</div>
                    <?php if($lowStockCount > 0): ?>
                        <a href="<?php echo e(route('reports.inventory')); ?>" class="notif-item">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <div>
                                <div class="notif-title">Low Stock Alert</div>
                                <div class="notif-text"><?php echo e($lowStockCount); ?> product(s) need reordering</div>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="notif-empty"><i class="bi bi-check-circle text-success"></i> All good!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible alert-floating fade show">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible alert-floating fade show">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>


<?php echo $__env->make('components.payment-reminder-popup', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<?php if(auth()->guard()->guest()): ?>
    <?php echo $__env->yieldContent('content'); ?>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(asset('js/ceylon-pos.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>

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
const savedTheme  = localStorage.getItem('ceylon-pos-theme') || 'light';
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
<?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/layouts/app.blade.php ENDPATH**/ ?>