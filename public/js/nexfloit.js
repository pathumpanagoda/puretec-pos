/**
 * Nexfloit Admin Panel JavaScript
 * Handles sidebar toggle, mobile navigation, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // Sidebar Toggle (Mobile)
    // ============================================
    const sidebar = document.querySelector('.nexfloit-sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebarClose = document.querySelector('.sidebar-close');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    function openSidebar() {
        sidebar?.classList.add('active');
        sidebarOverlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('active');
        sidebarOverlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    // Close sidebar on window resize (if desktop)
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });

    // ============================================
    // Auto-dismiss Alerts
    // ============================================
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // ============================================
    // Confirmation Dialogs
    // ============================================
    const confirmForms = document.querySelectorAll('[data-confirm]');
    confirmForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const message = form.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ============================================
    // Table Row Click
    // ============================================
    const clickableRows = document.querySelectorAll('tr[data-href]');
    clickableRows.forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            // Don't navigate if clicking on links or buttons
            if (e.target.closest('a, button, .btn')) return;
            window.location.href = row.dataset.href;
        });
    });

    // ============================================
    // Format Currency Inputs
    // ============================================
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = parseFloat(input.value) || 0;
            input.value = value.toFixed(2);
        });
    });

    // ============================================
    // Search Form Enhancement
    // ============================================
    const searchInputs = document.querySelectorAll('.search-on-enter');
    searchInputs.forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                input.closest('form')?.submit();
            }
        });
    });

    // ============================================
    // Dropdown Menu Positioning
    // ============================================
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('show.bs.dropdown', function() {
            const menu = dropdown.querySelector('.dropdown-menu');
            if (!menu) return;

            const rect = dropdown.getBoundingClientRect();
            const menuHeight = menu.offsetHeight || 200;

            if (rect.bottom + menuHeight > window.innerHeight) {
                menu.classList.add('dropdown-menu-end');
            }
        });
    });

    // ============================================
    // Copy to Clipboard
    // ============================================
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const text = button.dataset.copy;
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(function() {
                    button.innerHTML = originalText;
                }, 2000);
            });
        });
    });

    // ============================================
    // Date Range Picker Validation
    // ============================================
    const dateFromInput = document.querySelector('input[name="from"]');
    const dateToInput = document.querySelector('input[name="to"]');

    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            dateToInput.min = dateFromInput.value;
        });

        dateToInput.addEventListener('change', function() {
            dateFromInput.max = dateToInput.value;
        });
    }

    // ============================================
    // Loading State for Forms
    // ============================================
    const forms = document.querySelectorAll('form:not([data-no-loading])');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

                // Re-enable after 10 seconds (fallback)
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }
        });
    });

    // ============================================
    // Tooltip Initialization
    // ============================================
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(function(tooltip) {
        new bootstrap.Tooltip(tooltip);
    });

    // ============================================
    // Number Format Helper
    // ============================================
    window.formatNumber = function(num, decimals = 2) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    };

    // ============================================
    // AJAX Helper
    // ============================================
    window.nexfloitFetch = async function(url, options = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        };

        const response = await fetch(url, { ...defaultOptions, ...options });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    };

    // ============================================
    // Console Log for Debug
    // ============================================
    console.log('%cNexfloit Admin Panel', 'color: #e94560; font-size: 20px; font-weight: bold;');
    console.log('%cPure POS Platform Management', 'color: #8a8a9a; font-size: 12px;');

});
