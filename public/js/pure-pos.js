/* =====================================================================
   PURE POS — Core JavaScript
   by Nexfloit
   ===================================================================== */
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar Toggle (Mobile) ──────────────────────────────────────
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn  = document.getElementById('sidebarClose');

    function openSidebar()  { sidebar?.classList.add('sidebar-open');  overlay?.classList.add('active'); }
    function closeSidebar() { sidebar?.classList.remove('sidebar-open'); overlay?.classList.remove('active'); }

    // Mobile: open/close sidebar with slide
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // ── Sidebar Collapse (Desktop) ────────────────────────────────────
    const collapseBtn   = document.getElementById('sidebarCollapseBtn');
    const mainContent   = document.getElementById('mainContent');
    const STORAGE_KEY   = 'pure-pos-sidebar-collapsed';

    // Load saved preference
    function loadSidebarState() {
        const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
        if (isCollapsed && window.innerWidth >= 992) {
            sidebar?.classList.add('collapsed');
            mainContent?.classList.add('sidebar-collapsed');
            updateCollapseBtnIcon(true);
        } else {
            updateCollapseBtnIcon(false);
        }
    }

    // Update the chevron icon class on the collapse button
    function updateCollapseBtnIcon(isCollapsed) {
        const icon = collapseBtn?.querySelector('i');
        if (icon) {
            if (isCollapsed) {
                icon.className = 'bi bi-chevron-right';
            } else {
                icon.className = 'bi bi-chevron-left';
            }
        }
    }

    // Toggle collapsed state (desktop only)
    function toggleSidebarCollapse() {
        const isCollapsed = sidebar?.classList.toggle('collapsed');
        mainContent?.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem(STORAGE_KEY, isCollapsed ? 'true' : 'false');
        updateCollapseBtnIcon(isCollapsed);
    }

    // Initialize on page load
    loadSidebarState();

    // Sidebar collapse button (chevron in sidebar header)
    collapseBtn?.addEventListener('click', toggleSidebarCollapse);

    // Topbar hamburger button: mobile = slide, desktop = collapse
    toggleBtn?.addEventListener('click', () => {
        if (window.innerWidth < 992) {
            // Mobile: slide open/close
            sidebar?.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        } else {
            // Desktop: collapse/expand
            toggleSidebarCollapse();
        }
    });

    // Reset on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth < 992) {
            sidebar?.classList.remove('collapsed');
            mainContent?.classList.remove('sidebar-collapsed');
            updateCollapseBtnIcon(false);
        } else {
            loadSidebarState();
        }
    });

    // ── Auto-dismiss alerts ───────────────────────────────────────────
    document.querySelectorAll('.alert-floating').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 4000);
    });

    // ── Confirm delete buttons ────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    // ── AJAX Form Helpers ─────────────────────────────────────────────
    window.PurePOS = {
        csrf: document.querySelector('meta[name="csrf-token"]')?.content,

        async post(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify(data)
            });
            return res.json();
        },

        async get(url) {
            const res = await fetch(url);
            return res.json();
        },

        formatCurrency(amount, symbol = 'Rs.') {
            return `${symbol} ${parseFloat(amount).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        },

        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
            toast.className  = `toast align-items-center text-white bg-${type} border-0 position-fixed`;
            toast.style.cssText = 'bottom: 24px; right: 24px; z-index: 9999;';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-${icons[type] || 'info-circle-fill'} me-2"></i>${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 3500 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    };

    // ── Number formatting ─────────────────────────────────────────────
    document.querySelectorAll('[data-format="currency"]').forEach(el => {
        const num = parseFloat(el.textContent);
        if (!isNaN(num)) el.textContent = PurePOS.formatCurrency(num);
    });

    // ── Table row click nav ───────────────────────────────────────────
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', () => window.location = row.dataset.href);
    });

    // ── Pagination keep filters ───────────────────────────────────────
    document.querySelectorAll('.pagination a').forEach(link => {
        const url      = new URL(link.href);
        const form     = document.querySelector('form.filter-form');
        if (!form) return;
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (value && key !== 'page') url.searchParams.set(key, value);
        });
        link.href = url.toString();
    });

    // ── Input number arrows for price fields ─────────────────────────
    document.querySelectorAll('input[type="number"].form-control').forEach(input => {
        input.addEventListener('wheel', e => e.preventDefault());
    });
});
