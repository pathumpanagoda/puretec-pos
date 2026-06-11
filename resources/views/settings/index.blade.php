@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure your POS system</p>
    </div>
</div>

{{-- Settings Navigation --}}
<div class="settings-container">
    <div class="settings-nav">
        <a href="#general" class="settings-nav-item active" data-tab="general">
            <i class="bi bi-shop"></i>
            <span>General</span>
        </a>
        <a href="#receipt" class="settings-nav-item" data-tab="receipt">
            <i class="bi bi-receipt"></i>
            <span>Receipt & Invoice</span>
        </a>
        <a href="#devices" class="settings-nav-item" data-tab="devices">
            <i class="bi bi-printer"></i>
            <span>Printers & Devices</span>
        </a>
        <a href="#pos" class="settings-nav-item" data-tab="pos">
            <i class="bi bi-cart3"></i>
            <span>POS Settings</span>
        </a>
        <a href="#tax" class="settings-nav-item" data-tab="tax">
            <i class="bi bi-percent"></i>
            <span>Tax Settings</span>
        </a>
        <a href="#inventory" class="settings-nav-item" data-tab="inventory">
            <i class="bi bi-box-seam"></i>
            <span>Inventory</span>
        </a>
        <a href="#notifications" class="settings-nav-item" data-tab="notifications">
            <i class="bi bi-bell"></i>
            <span>Notifications</span>
        </a>
        <a href="#backup" class="settings-nav-item" data-tab="backup">
            <i class="bi bi-cloud-arrow-down"></i>
            <span>Backup & Restore</span>
        </a>
    </div>

    <div class="settings-content">
        {{-- General Settings --}}
        <div class="settings-panel active" id="general">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-shop"></i> Business Information</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $store->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Code</label>
                                <input type="text" name="code" class="form-control" value="{{ $store->code }}" placeholder="BRN/Registration">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $store->address }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ $store->city }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ $store->country }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $store->phone }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $store->email }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                @if($store->logo)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo" style="max-height: 60px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <small class="text-danger" style="display:none;">Logo not found. Please re-upload.</small>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="LKR" {{ $store->currency == 'LKR' ? 'selected' : '' }}>Sri Lankan Rupee (LKR)</option>
                                    <option value="USD" {{ $store->currency == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                    <option value="EUR" {{ $store->currency == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                    <option value="GBP" {{ $store->currency == 'GBP' ? 'selected' : '' }}>British Pound (GBP)</option>
                                    <option value="INR" {{ $store->currency == 'INR' ? 'selected' : '' }}>Indian Rupee (INR)</option>
                                    <option value="AUD" {{ $store->currency == 'AUD' ? 'selected' : '' }}>Australian Dollar (AUD)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" name="currency_symbol" class="form-control" value="{{ $store->currency_symbol ?? 'Rs.' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Tax Rate (%)</label>
                                <input type="number" name="tax_rate" class="form-control" value="{{ $store->tax_rate ?? 0 }}" step="0.01">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Receipt Settings --}}
        <div class="settings-panel" id="receipt">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-receipt"></i> Receipt & Invoice Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.receipt') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Receipt Header</label>
                                <textarea name="receipt_header" class="form-control" rows="3" placeholder="Text shown at top of receipt">{{ $store->receipt_header }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Receipt Footer</label>
                                <textarea name="receipt_footer" class="form-control" rows="3" placeholder="Text shown at bottom of receipt">{{ $store->receipt_footer }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-printer-fill text-primary"></i> Quick Print Default</label>
                                <select name="default_print_format" class="form-select">
                                    <optgroup label="Thermal Receipt">
                                        <option value="thermal-58" {{ ($store->settings['default_print_format'] ?? '') == 'thermal-58' ? 'selected' : '' }}>58mm Thermal (Small)</option>
                                        <option value="thermal-80" {{ ($store->settings['default_print_format'] ?? 'thermal-80') == 'thermal-80' ? 'selected' : '' }}>80mm Thermal (Standard)</option>
                                    </optgroup>
                                    <optgroup label="Invoice (with Logo)">
                                        <option value="a5" {{ ($store->settings['default_print_format'] ?? '') == 'a5' ? 'selected' : '' }}>A5 Invoice (Half Page)</option>
                                        <option value="a4" {{ ($store->settings['default_print_format'] ?? '') == 'a4' ? 'selected' : '' }}>A4 Invoice (Full Page)</option>
                                        <option value="a3" {{ ($store->settings['default_print_format'] ?? '') == 'a3' ? 'selected' : '' }}>A3 Invoice (Large)</option>
                                    </optgroup>
                                </select>
                                <small class="text-muted"><i class="bi bi-info-circle"></i> Opens when you click "Quick Print" in POS</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Thermal Paper Size</label>
                                <select name="receipt_paper_size" class="form-select">
                                    <option value="80mm" {{ ($store->settings['receipt_paper_size'] ?? '80mm') == '80mm' ? 'selected' : '' }}>80mm Thermal</option>
                                    <option value="58mm" {{ ($store->settings['receipt_paper_size'] ?? '') == '58mm' ? 'selected' : '' }}>58mm Thermal</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Receipt Template</label>
                                <select name="receipt_template" class="form-select">
                                    <option value="standard" {{ ($store->settings['receipt_template'] ?? 'standard') == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="compact" {{ ($store->settings['receipt_template'] ?? '') == 'compact' ? 'selected' : '' }}>Compact</option>
                                    <option value="detailed" {{ ($store->settings['receipt_template'] ?? '') == 'detailed' ? 'selected' : '' }}>Detailed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Invoice Prefix</label>
                                <input type="text" name="invoice_prefix" class="form-control" value="{{ $store->settings['invoice_prefix'] ?? 'INV-' }}" placeholder="INV-">
                            </div>

                            {{-- Print Layout Settings --}}
                            <div class="col-12 mt-4">
                                <h6 class="text-primary mb-3"><i class="bi bi-rulers me-2"></i>Print Layout Settings</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Top Margin (mm)</label>
                                <input type="number" name="print_margin_top" class="form-control" value="{{ $store->settings['print_margin_top'] ?? 3 }}" min="0" max="50" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bottom Margin (mm)</label>
                                <input type="number" name="print_margin_bottom" class="form-control" value="{{ $store->settings['print_margin_bottom'] ?? 3 }}" min="0" max="50" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Left Margin (mm)</label>
                                <input type="number" name="print_margin_left" class="form-control" value="{{ $store->settings['print_margin_left'] ?? 3 }}" min="0" max="50" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Right Margin (mm)</label>
                                <input type="number" name="print_margin_right" class="form-control" value="{{ $store->settings['print_margin_right'] ?? 3 }}" min="0" max="50" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Store Name Font Size</label>
                                <select name="print_font_store_name" class="form-select">
                                    <option value="14" {{ ($store->settings['print_font_store_name'] ?? '16') == '14' ? 'selected' : '' }}>Small (14px)</option>
                                    <option value="16" {{ ($store->settings['print_font_store_name'] ?? '16') == '16' ? 'selected' : '' }}>Medium (16px)</option>
                                    <option value="18" {{ ($store->settings['print_font_store_name'] ?? '') == '18' ? 'selected' : '' }}>Large (18px)</option>
                                    <option value="20" {{ ($store->settings['print_font_store_name'] ?? '') == '20' ? 'selected' : '' }}>Extra Large (20px)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Item Font Size</label>
                                <select name="print_font_items" class="form-select">
                                    <option value="10" {{ ($store->settings['print_font_items'] ?? '12') == '10' ? 'selected' : '' }}>Small (10px)</option>
                                    <option value="12" {{ ($store->settings['print_font_items'] ?? '12') == '12' ? 'selected' : '' }}>Medium (12px)</option>
                                    <option value="14" {{ ($store->settings['print_font_items'] ?? '') == '14' ? 'selected' : '' }}>Large (14px)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total Font Size</label>
                                <select name="print_font_total" class="form-select">
                                    <option value="14" {{ ($store->settings['print_font_total'] ?? '16') == '14' ? 'selected' : '' }}>Small (14px)</option>
                                    <option value="16" {{ ($store->settings['print_font_total'] ?? '16') == '16' ? 'selected' : '' }}>Medium (16px)</option>
                                    <option value="18" {{ ($store->settings['print_font_total'] ?? '') == '18' ? 'selected' : '' }}>Large (18px)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Line Spacing</label>
                                <select name="print_line_spacing" class="form-select">
                                    <option value="1.2" {{ ($store->settings['print_line_spacing'] ?? '1.4') == '1.2' ? 'selected' : '' }}>Compact (1.2)</option>
                                    <option value="1.4" {{ ($store->settings['print_line_spacing'] ?? '1.4') == '1.4' ? 'selected' : '' }}>Normal (1.4)</option>
                                    <option value="1.6" {{ ($store->settings['print_line_spacing'] ?? '') == '1.6' ? 'selected' : '' }}>Relaxed (1.6)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Receipt Width</label>
                                <select name="print_receipt_width" class="form-select">
                                    <option value="auto" {{ ($store->settings['print_receipt_width'] ?? 'auto') == 'auto' ? 'selected' : '' }}>Auto (fit paper)</option>
                                    <option value="58mm" {{ ($store->settings['print_receipt_width'] ?? '') == '58mm' ? 'selected' : '' }}>58mm</option>
                                    <option value="72mm" {{ ($store->settings['print_receipt_width'] ?? '') == '72mm' ? 'selected' : '' }}>72mm (80mm paper)</option>
                                    <option value="100%" {{ ($store->settings['print_receipt_width'] ?? '') == '100%' ? 'selected' : '' }}>Full Width</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Divider Style</label>
                                <select name="print_divider_style" class="form-select">
                                    <option value="dashed" {{ ($store->settings['print_divider_style'] ?? 'dashed') == 'dashed' ? 'selected' : '' }}>Dashed Line</option>
                                    <option value="solid" {{ ($store->settings['print_divider_style'] ?? '') == 'solid' ? 'selected' : '' }}>Solid Line</option>
                                    <option value="dotted" {{ ($store->settings['print_divider_style'] ?? '') == 'dotted' ? 'selected' : '' }}>Dotted Line</option>
                                    <option value="none" {{ ($store->settings['print_divider_style'] ?? '') == 'none' ? 'selected' : '' }}>No Divider</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Print Colors</label>
                                <select name="print_colors" class="form-select">
                                    <option value="color" {{ ($store->settings['print_colors'] ?? 'color') == 'color' ? 'selected' : '' }}>Color (Blue accents)</option>
                                    <option value="bw" {{ ($store->settings['print_colors'] ?? '') == 'bw' ? 'selected' : '' }}>Black & White</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="show_logo_on_receipt" class="form-check-input" id="showLogoReceipt" {{ ($store->settings['show_logo_on_receipt'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showLogoReceipt">Show logo on receipt</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="show_tax_breakdown" class="form-check-input" id="showTaxBreakdown" {{ ($store->settings['show_tax_breakdown'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showTaxBreakdown">Show tax breakdown on receipt</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="auto_print_receipt" class="form-check-input" id="autoPrintReceipt" {{ ($store->settings['auto_print_receipt'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoPrintReceipt">Auto-print receipt after sale</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="print_duplicate" class="form-check-input" id="printDuplicate" {{ ($store->settings['print_duplicate'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="printDuplicate">Print duplicate copy</label>
                                </div>
                            </div>

                            {{-- Live Receipt Preview --}}
                            <div class="col-md-4">
                                <div class="receipt-preview-box">
                                    <div class="receipt-preview-header">
                                        <i class="bi bi-receipt me-2"></i>Live Preview
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-auto" onclick="printPreviewReceipt()">
                                            <i class="bi bi-printer"></i> Test Print
                                        </button>
                                    </div>
                                    <div id="receiptPreview" class="receipt-preview-content">
                                        <div class="receipt-paper" id="previewPaper">
                                            <div class="receipt-header">
                                                <h3 class="store-name" id="previewStoreName">{{ $store->name }}</h3>
                                                @if($store->address)<p class="store-detail">{{ $store->address }}</p>@endif
                                                @if($store->phone)<p class="store-detail">Tel: {{ $store->phone }}</p>@endif
                                            </div>
                                            <hr class="receipt-divider" id="previewDivider1">
                                            <div class="receipt-meta">
                                                <div><span class="meta-label">Order:</span> ORD-20240101-001</div>
                                                <div><span class="meta-label">Date:</span> {{ date('d/m/Y, H:i:s') }}</div>
                                                <div><span class="meta-label">Cashier:</span> {{ auth()->user()->name }}</div>
                                            </div>
                                            <hr class="receipt-divider">
                                            <div class="receipt-items" id="previewItems">
                                                <div class="receipt-item">
                                                    <div class="ri-name">Sample Product A</div>
                                                    <div class="ri-line"><span class="ri-qty">2 x Rs.500.00</span><span class="ri-total">Rs.1,000.00</span></div>
                                                </div>
                                                <div class="receipt-item">
                                                    <div class="ri-name">Sample Product B</div>
                                                    <div class="ri-line"><span class="ri-qty">1 x Rs.750.00</span><span class="ri-total">Rs.750.00</span></div>
                                                </div>
                                            </div>
                                            <hr class="receipt-divider">
                                            <div class="receipt-totals" id="previewTotals">
                                                <div class="rt-row"><span>Subtotal</span><span>Rs. 1,750.00</span></div>
                                                <div class="rt-row rt-total"><span>TOTAL</span><span>Rs. 1,750.00</span></div>
                                                <div class="rt-row"><span>Paid (Cash)</span><span>Rs. 2,000.00</span></div>
                                                <div class="rt-row rt-change"><span>Change</span><span>Rs. 250.00</span></div>
                                            </div>
                                            <hr class="receipt-divider">
                                            <div class="receipt-footer">
                                                <p class="footer-thanks">{{ $store->receipt_footer ?? 'Thank you for your business!' }}</p>
                                                <p class="receipt-powered">Pure POS</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save Receipt Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Devices Settings --}}
        <div class="settings-panel" id="devices">
            <div class="row g-4">
                {{-- Printer Settings --}}
                <div class="col-12">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-printer"></i> Printer Configuration</h5>
                            <button type="button" class="btn-cpos btn-sm btn-primary" onclick="addPrinter()">
                                <i class="bi bi-plus-lg"></i> Add Printer
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                Configure your printers below. Pure POS supports USB, Network/IP, Bluetooth, and WiFi printers.
                            </div>

                            <form action="{{ route('settings.devices') }}" method="POST" id="printerForm">
                                @csrf
                                <div id="printerList">
                                    @php $printers = $store->settings['printers'] ?? []; @endphp
                                    @forelse($printers as $index => $printer)
                                    <div class="device-card" data-printer="{{ $index }}">
                                        <div class="device-header">
                                            <div class="device-info">
                                                <i class="bi bi-printer device-icon"></i>
                                                <div>
                                                    <input type="text" name="printers[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $printer['name'] }}" placeholder="Printer Name">
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePrinter({{ $index }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="device-body">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small">Connection Type</label>
                                                    <select name="printers[{{ $index }}][type]" class="form-select form-select-sm" onchange="updatePrinterFields(this, {{ $index }})">
                                                        <option value="usb" {{ ($printer['type'] ?? '') == 'usb' ? 'selected' : '' }}>USB</option>
                                                        <option value="network" {{ ($printer['type'] ?? '') == 'network' ? 'selected' : '' }}>Network/IP</option>
                                                        <option value="bluetooth" {{ ($printer['type'] ?? '') == 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                                                        <option value="wifi" {{ ($printer['type'] ?? '') == 'wifi' ? 'selected' : '' }}>WiFi Direct</option>
                                                        <option value="cloud" {{ ($printer['type'] ?? '') == 'cloud' ? 'selected' : '' }}>Cloud Print</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Paper Size</label>
                                                    <select name="printers[{{ $index }}][paper_size]" class="form-select form-select-sm">
                                                        <option value="80mm" {{ ($printer['paper_size'] ?? '') == '80mm' ? 'selected' : '' }}>80mm Thermal</option>
                                                        <option value="58mm" {{ ($printer['paper_size'] ?? '') == '58mm' ? 'selected' : '' }}>58mm Thermal</option>
                                                        <option value="A4" {{ ($printer['paper_size'] ?? '') == 'A4' ? 'selected' : '' }}>A4</option>
                                                        <option value="A5" {{ ($printer['paper_size'] ?? '') == 'A5' ? 'selected' : '' }}>A5</option>
                                                        <option value="A3" {{ ($printer['paper_size'] ?? '') == 'A3' ? 'selected' : '' }}>A3</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 printer-ip-field" style="{{ ($printer['type'] ?? '') == 'network' ? '' : 'display:none' }}">
                                                    <label class="form-label small">IP Address</label>
                                                    <input type="text" name="printers[{{ $index }}][ip_address]" class="form-control form-control-sm" value="{{ $printer['ip_address'] ?? '' }}" placeholder="192.168.1.100">
                                                </div>
                                                <div class="col-md-3 printer-port-field" style="{{ ($printer['type'] ?? '') == 'network' ? '' : 'display:none' }}">
                                                    <label class="form-label small">Port</label>
                                                    <input type="number" name="printers[{{ $index }}][port]" class="form-control form-control-sm" value="{{ $printer['port'] ?? '9100' }}" placeholder="9100">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Usage</label>
                                                    <select name="printers[{{ $index }}][usage]" class="form-select form-select-sm">
                                                        <option value="receipt" {{ ($printer['usage'] ?? '') == 'receipt' ? 'selected' : '' }}>Receipt Printer</option>
                                                        <option value="invoice" {{ ($printer['usage'] ?? '') == 'invoice' ? 'selected' : '' }}>Invoice Printer</option>
                                                        <option value="kitchen" {{ ($printer['usage'] ?? '') == 'kitchen' ? 'selected' : '' }}>Kitchen Display</option>
                                                        <option value="label" {{ ($printer['usage'] ?? '') == 'label' ? 'selected' : '' }}>Label Printer</option>
                                                        <option value="report" {{ ($printer['usage'] ?? '') == 'report' ? 'selected' : '' }}>Report Printer</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Status</label>
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" name="printers[{{ $index }}][enabled]" class="form-check-input" {{ ($printer['enabled'] ?? true) ? 'checked' : '' }}>
                                                        <label class="form-check-label">Enabled</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">&nbsp;</label>
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="testPrinter({{ $index }})">
                                                        <i class="bi bi-printer"></i> Test Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-5" id="noPrintersMsg">
                                        <i class="bi bi-printer fs-1"></i>
                                        <p class="mt-2">No printers configured. Click "Add Printer" to get started.</p>
                                    </div>
                                    @endforelse
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn-cpos btn-primary">
                                        <i class="bi bi-check-lg"></i> Save Printer Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Barcode Scanner --}}
                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-upc-scan"></i> Barcode Scanner</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.scanner') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Scanner Type</label>
                                    <select name="scanner_type" class="form-select">
                                        <option value="usb">USB Scanner (Keyboard Mode)</option>
                                        <option value="bluetooth">Bluetooth Scanner</option>
                                        <option value="camera">Camera Scanner (Mobile)</option>
                                        <option value="serial">Serial Port Scanner</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Barcode Format</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="ean13" class="form-check-input" checked>
                                                <label class="form-check-label">EAN-13</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="ean8" class="form-check-input" checked>
                                                <label class="form-check-label">EAN-8</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="upca" class="form-check-input" checked>
                                                <label class="form-check-label">UPC-A</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="code128" class="form-check-input" checked>
                                                <label class="form-check-label">Code 128</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="code39" class="form-check-input" checked>
                                                <label class="form-check-label">Code 39</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="barcode_formats[]" value="qr" class="form-check-input" checked>
                                                <label class="form-check-label">QR Code</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="scanner_beep" class="form-check-input" id="scannerBeep" checked>
                                        <label class="form-check-label" for="scannerBeep">Play beep sound on scan</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="scanner_auto_add" class="form-check-input" id="scannerAutoAdd" checked>
                                        <label class="form-check-label" for="scannerAutoAdd">Auto-add product to cart on scan</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn-cpos btn-primary w-100">
                                    <i class="bi bi-check-lg"></i> Save Scanner Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Card Reader --}}
                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-credit-card"></i> Card Reader / Payment Terminal</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.cardreader') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Terminal Type</label>
                                    <select name="terminal_type" class="form-select">
                                        <option value="none">No Card Terminal</option>
                                        <option value="manual">Manual Entry</option>
                                        <option value="usb">USB Card Reader</option>
                                        <option value="bluetooth">Bluetooth Terminal</option>
                                        <option value="network">Network Terminal (IP)</option>
                                        <option value="integrated">Integrated POS Terminal</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Terminal IP (if network)</label>
                                    <input type="text" name="terminal_ip" class="form-control" placeholder="192.168.1.50">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Merchant ID</label>
                                    <input type="text" name="merchant_id" class="form-control" placeholder="Your merchant ID">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Accepted Cards</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="accepted_cards[]" value="visa" class="form-check-input" checked>
                                                <label class="form-check-label">Visa</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="accepted_cards[]" value="mastercard" class="form-check-input" checked>
                                                <label class="form-check-label">Mastercard</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="accepted_cards[]" value="amex" class="form-check-input">
                                                <label class="form-check-label">American Express</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="accepted_cards[]" value="unionpay" class="form-check-input">
                                                <label class="form-check-label">UnionPay</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn-cpos btn-primary w-100">
                                    <i class="bi bi-check-lg"></i> Save Card Reader Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Cash Drawer --}}
                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-safe"></i> Cash Drawer</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.cashdrawer') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Connection Type</label>
                                    <select name="drawer_type" class="form-select">
                                        <option value="none">No Cash Drawer</option>
                                        <option value="printer">Connected to Printer (RJ11)</option>
                                        <option value="usb">USB Cash Drawer</option>
                                        <option value="serial">Serial Port</option>
                                        <option value="network">Network Cash Drawer</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="auto_open_drawer" class="form-check-input" id="autoOpenDrawer" checked>
                                        <label class="form-check-label" for="autoOpenDrawer">Auto-open on cash payment</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="open_drawer_card" class="form-check-input" id="openDrawerCard">
                                        <label class="form-check-label" for="openDrawerCard">Open on card payment too</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn-cpos btn-primary w-100">
                                    <i class="bi bi-check-lg"></i> Save Cash Drawer Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Customer Display --}}
                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-display"></i> Customer Display</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.display') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Display Type</label>
                                    <select name="display_type" class="form-select">
                                        <option value="none">No Customer Display</option>
                                        <option value="lcd">LCD Pole Display (Serial)</option>
                                        <option value="vfd">VFD Display</option>
                                        <option value="secondary">Secondary Monitor</option>
                                        <option value="tablet">Tablet Display</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">COM Port (if serial)</label>
                                    <select name="display_port" class="form-select">
                                        <option value="COM1">COM1</option>
                                        <option value="COM2">COM2</option>
                                        <option value="COM3">COM3</option>
                                        <option value="COM4">COM4</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="show_item_price" class="form-check-input" id="showItemPrice" checked>
                                        <label class="form-check-label" for="showItemPrice">Show item price</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="show_total" class="form-check-input" id="showTotal" checked>
                                        <label class="form-check-label" for="showTotal">Show running total</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn-cpos btn-primary w-100">
                                    <i class="bi bi-check-lg"></i> Save Display Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- POS Settings --}}
        <div class="settings-panel" id="pos">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-cart3"></i> Point of Sale Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.pos') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Decimal Places</label>
                                <select name="decimal_places" class="form-select">
                                    <option value="0">0 (1000)</option>
                                    <option value="2" selected>2 (1000.00)</option>
                                    <option value="3">3 (1000.000)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Payment Method</label>
                                <select name="default_payment" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="mobile">Mobile Payment</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Order Number Format</label>
                                <input type="text" name="order_format" class="form-control" value="ORD-{YYYYMMDD}-{####}" placeholder="ORD-{YYYYMMDD}-{####}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Customer</label>
                                <select name="default_customer" class="form-select">
                                    <option value="">Walk-in Customer</option>
                                    @foreach(\App\Models\Customer::where('store_id', auth()->user()->store_id)->limit(50)->get() as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quick Amount Buttons</label>
                                <input type="text" name="quick_amounts" class="form-control" value="50,100,500,1000,5000" placeholder="50,100,500,1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Barcode Search Mode</label>
                                <select name="barcode_mode" class="form-select">
                                    <option value="exact">Exact Match</option>
                                    <option value="contains">Contains</option>
                                    <option value="starts">Starts With</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <h6 class="mt-3 mb-3">POS Behavior</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        @php $posSettings = $store->settings['pos'] ?? []; @endphp
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="allow_negative_stock" class="form-check-input" id="allowNegStock" {{ ($posSettings['allow_negative_stock'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allowNegStock">Allow negative stock sales</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="require_customer" class="form-check-input" id="requireCustomer" {{ ($posSettings['require_customer'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="requireCustomer">Require customer for each sale</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="allow_discounts" class="form-check-input" id="allowDiscounts" {{ ($posSettings['allow_discounts'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allowDiscounts">Allow discounts at POS</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="allow_price_edit" class="form-check-input" id="allowPriceEdit" {{ ($posSettings['allow_price_edit'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="allowPriceEdit">Allow price editing at POS</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="show_stock" class="form-check-input" id="showStock" {{ ($posSettings['show_stock'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="showStock">Show stock quantity on POS</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="confirm_checkout" class="form-check-input" id="confirmCheckout" {{ ($posSettings['confirm_checkout'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="confirmCheckout">Confirm before checkout</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="sound_effects" class="form-check-input" id="soundEffects" {{ ($posSettings['sound_effects'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="soundEffects">Enable sound effects</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="hold_orders" class="form-check-input" id="holdOrders" {{ ($posSettings['hold_orders'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="holdOrders">Allow hold orders</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save POS Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Season Mode / Bill-Only Settings --}}
            @if(auth()->user()->isManager())
            <div class="cpos-card mt-4">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-calendar-event"></i> Season Mode (Bill-Only)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Season Mode</h6>
                            <p class="text-muted mb-0">
                                When enabled, <strong>all cashiers</strong> can process payments directly.
                                When disabled, cashiers set to "Bill Only" mode will create bills without payment - a main cashier must approve and collect payment.
                            </p>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Use this during busy seasons (e.g., Avurudu) to allow faster checkout at all counters.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="form-check form-switch form-switch-lg d-inline-block">
                                <input type="checkbox" class="form-check-input" id="seasonModeToggle"
                                       {{ ($store->settings['season_mode'] ?? false) ? 'checked' : '' }}
                                       onchange="toggleSeasonMode(this.checked)"
                                       style="width: 3rem; height: 1.5rem;">
                                <label class="form-check-label ms-2" for="seasonModeToggle" id="seasonModeLabel">
                                    {{ ($store->settings['season_mode'] ?? false) ? 'ON' : 'OFF' }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="alert {{ ($store->settings['season_mode'] ?? false) ? 'alert-success' : 'alert-warning' }} mt-3 mb-0" id="seasonModeAlert">
                        @if($store->settings['season_mode'] ?? false)
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Season Mode Active:</strong> All cashiers can process payments directly.
                        @else
                            <i class="bi bi-hourglass-split me-2"></i>
                            <strong>Normal Mode:</strong> Bill-only cashiers create bills without payment. Main cashier approves payments.
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Tax Settings --}}
        <div class="settings-panel" id="tax">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-percent"></i> Tax Configuration</h5>
                    <button type="button" class="btn-cpos btn-sm btn-primary" onclick="addTaxRow()">
                        <i class="bi bi-plus-lg"></i> Add Tax
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.tax') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-cpos" id="taxTable">
                                <thead>
                                    <tr>
                                        <th>Tax Name</th>
                                        <th>Rate (%)</th>
                                        <th>Type</th>
                                        <th>Apply To</th>
                                        <th>Status</th>
                                        <th width="60"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taxes as $tax)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="taxes[{{ $tax->id }}][id]" value="{{ $tax->id }}">
                                            <input type="text" name="taxes[{{ $tax->id }}][name]" class="form-control form-control-sm" value="{{ $tax->name }}">
                                        </td>
                                        <td>
                                            <input type="number" name="taxes[{{ $tax->id }}][rate]" class="form-control form-control-sm" value="{{ $tax->rate }}" step="0.01">
                                        </td>
                                        <td>
                                            <select name="taxes[{{ $tax->id }}][type]" class="form-select form-select-sm">
                                                <option value="percentage" {{ $tax->type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                <option value="fixed" {{ $tax->type == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="taxes[{{ $tax->id }}][apply_to]" class="form-select form-select-sm">
                                                <option value="all" {{ ($tax->apply_to ?? 'all') == 'all' ? 'selected' : '' }}>All Products</option>
                                                <option value="taxable" {{ ($tax->apply_to ?? '') == 'taxable' ? 'selected' : '' }}>Taxable Only</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="taxes[{{ $tax->id }}][is_active]" class="form-check-input" {{ $tax->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="noTaxRow">
                                        <td colspan="6" class="text-center text-muted py-4">No taxes configured</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save Tax Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Inventory Settings --}}
        <div class="settings-panel" id="inventory">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-box-seam"></i> Inventory Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.inventory') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Default Low Stock Alert Level</label>
                                <input type="number" name="low_stock_level" class="form-control" value="10" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" value="5" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Count Method</label>
                                <select name="stock_method" class="form-select">
                                    <option value="fifo">FIFO (First In, First Out)</option>
                                    <option value="lifo">LIFO (Last In, First Out)</option>
                                    <option value="average">Weighted Average</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Unit</label>
                                <select name="default_unit" class="form-select">
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="kg">Kilograms (kg)</option>
                                    <option value="g">Grams (g)</option>
                                    <option value="l">Liters (l)</option>
                                    <option value="ml">Milliliters (ml)</option>
                                    <option value="m">Meters (m)</option>
                                    <option value="box">Box</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU Prefix</label>
                                <input type="text" name="sku_prefix" class="form-control" value="SKU-" placeholder="SKU-">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Auto-generate SKU</label>
                                <select name="auto_sku" class="form-select">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <h6 class="mt-3 mb-3">Inventory Behavior</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="track_stock_default" class="form-check-input" id="trackStockDefault" checked>
                                            <label class="form-check-label" for="trackStockDefault">Track stock by default for new products</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="allow_negative_stock" class="form-check-input" id="allowNegativeStock">
                                            <label class="form-check-label" for="allowNegativeStock">Allow negative stock</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="auto_deduct_stock" class="form-check-input" id="autoDeductStock" checked>
                                            <label class="form-check-label" for="autoDeductStock">Auto-deduct stock on sale</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="stock_alert_email" class="form-check-input" id="stockAlertEmail" checked>
                                            <label class="form-check-label" for="stockAlertEmail">Email alerts for low stock</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="track_expiry" class="form-check-input" id="trackExpiry">
                                            <label class="form-check-label" for="trackExpiry">Track product expiry dates</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input type="checkbox" name="batch_tracking" class="form-check-input" id="batchTracking">
                                            <label class="form-check-label" for="batchTracking">Enable batch/lot tracking</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save Inventory Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Notification Settings --}}
        <div class="settings-panel" id="notifications">
            <div class="cpos-card">
                <div class="cpos-card-header">
                    <h5 class="card-title"><i class="bi bi-bell"></i> Notification Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.notifications') }}" method="POST">
                        @csrf
                        <h6 class="mb-3">Email Notifications</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="email_daily_report" class="form-check-input" id="emailDailyReport">
                                    <label class="form-check-label" for="emailDailyReport">Daily sales summary email</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="email_low_stock" class="form-check-input" id="emailLowStock" checked>
                                    <label class="form-check-label" for="emailLowStock">Low stock alerts</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="email_new_order" class="form-check-input" id="emailNewOrder">
                                    <label class="form-check-label" for="emailNewOrder">New order notifications</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notification Email</label>
                                <input type="email" name="notification_email" class="form-control" value="{{ $store->email }}" placeholder="admin@yourstore.com">
                            </div>
                        </div>

                        <h6 class="mb-3">System Alerts</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="alert_low_stock" class="form-check-input" id="alertLowStock" checked>
                                    <label class="form-check-label" for="alertLowStock">Show low stock alerts in dashboard</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="alert_expiring" class="form-check-input" id="alertExpiring" checked>
                                    <label class="form-check-label" for="alertExpiring">Show expiring product alerts</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="alert_pending_orders" class="form-check-input" id="alertPendingOrders" checked>
                                    <label class="form-check-label" for="alertPendingOrders">Show pending order alerts</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="sound_notifications" class="form-check-input" id="soundNotifications" checked>
                                    <label class="form-check-label" for="soundNotifications">Play sound for notifications</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="browser_notifications" class="form-check-input" id="browserNotifications">
                                    <label class="form-check-label" for="browserNotifications">Enable browser notifications</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-cpos btn-primary">
                                <i class="bi bi-check-lg"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Backup Settings --}}
        <div class="settings-panel" id="backup">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-cloud-arrow-up"></i> Backup Data</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">Create a backup of your data. This includes products, customers, orders, and settings.</p>
                            <div class="mb-3">
                                <label class="form-label">Backup Type</label>
                                <select name="backup_type" class="form-select" id="backupType">
                                    <option value="full">Full Backup (All Data)</option>
                                    <option value="products">Products Only</option>
                                    <option value="customers">Customers Only</option>
                                    <option value="orders">Orders Only</option>
                                    <option value="settings">Settings Only</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <select name="backup_format" class="form-select">
                                    <option value="json">JSON</option>
                                    <option value="csv">CSV (Excel Compatible)</option>
                                    <option value="sql">SQL Database Dump</option>
                                </select>
                            </div>
                            <a href="{{ route('settings.backup.download') }}" class="btn-cpos btn-primary w-100">
                                <i class="bi bi-download"></i> Download Backup
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-cloud-arrow-down"></i> Restore Data</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">Restore data from a previous backup file. This will overwrite existing data.</p>
                            <form action="{{ route('settings.backup.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Backup File</label>
                                    <input type="file" name="backup_file" class="form-control" accept=".json,.csv,.sql">
                                </div>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> Restoring will replace existing data. Make sure to backup first!
                                </div>
                                <button type="submit" class="btn-cpos btn-warning w-100">
                                    <i class="bi bi-upload"></i> Restore Backup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="cpos-card">
                        <div class="cpos-card-header">
                            <h5 class="card-title"><i class="bi bi-clock-history"></i> Auto Backup</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.backup.schedule') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Backup Schedule</label>
                                    <select name="backup_schedule" class="form-select">
                                        <option value="disabled">Disabled</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Backup Time</label>
                                    <input type="time" name="backup_time" class="form-control" value="02:00">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keep Last</label>
                                    <select name="backup_retention" class="form-select">
                                        <option value="7">7 Backups</option>
                                        <option value="14">14 Backups</option>
                                        <option value="30">30 Backups</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-cpos btn-primary w-100">
                                    <i class="bi bi-check-lg"></i> Save Schedule
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->role === 'super_admin')
                <div class="col-md-6">
                    <div class="cpos-card border-danger">
                        <div class="cpos-card-header bg-danger text-white">
                            <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>System Reset</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This will permanently delete data. Use only to clear test data before going live.
                            </div>
                            <form action="{{ route('settings.reset') }}" method="POST" id="resetForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Data to Clear</label>
                                    <select name="type" class="form-select" id="resetType" required>
                                        <option value="">-- Select Reset Type --</option>
                                        <option value="orders">🧾 Clear All Orders & Sales</option>
                                        <option value="expenses">💸 Clear All Expenses</option>
                                        <option value="incomes">💰 Clear All Incomes</option>
                                        <option value="inventory">📦 Reset Stock to Zero</option>
                                        <option value="customers">👥 Clear All Customers</option>
                                        <option value="full_reset">⚠️ FULL SYSTEM RESET (Everything)</option>
                                    </select>
                                </div>
                                <div class="mb-3 d-none" id="confirmTextBox">
                                    <label class="form-label text-danger fw-bold">Type "RESET ALL DATA" to confirm:</label>
                                    <input type="text" name="confirm_text" class="form-control border-danger" id="confirmText" placeholder="RESET ALL DATA">
                                </div>
                                <button type="submit" class="btn btn-danger w-100" id="resetBtn" disabled>
                                    <i class="bi bi-trash"></i> Reset Selected Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Receipt Preview Box */
.receipt-preview-box {
    border: 1px solid var(--cp-border);
    border-radius: var(--cp-radius-md);
    overflow: hidden;
    background: #f5f5f5;
}
.receipt-preview-header {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background: var(--cp-primary);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
}
.receipt-preview-content {
    max-height: 400px;
    overflow-y: auto;
    background: #fff;
    border: 8px solid #f5f5f5;
}
.receipt-preview-content .receipt-paper {
    padding: 15px 12px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.4;
}
.receipt-preview-content .store-name {
    font-size: 14px;
    color: #1565c0;
    margin-bottom: 5px;
}
.receipt-preview-content .store-detail {
    font-size: 10px;
    margin: 2px 0;
}
.receipt-preview-content .receipt-divider {
    border: none;
    border-top: 1px dashed #ccc;
    margin: 10px 0;
}
.receipt-preview-content .receipt-meta div,
.receipt-preview-content .ri-name,
.receipt-preview-content .ri-line,
.receipt-preview-content .rt-row {
    font-size: 10px;
}
.receipt-preview-content .rt-row.rt-total {
    font-size: 12px;
    font-weight: 700;
    color: #1565c0;
}
.receipt-preview-content .footer-thanks {
    font-size: 10px;
    font-style: italic;
}
.receipt-preview-content .receipt-powered {
    font-size: 9px;
    color: #1976d2;
}

.settings-container {
    display: flex;
    gap: 24px;
    min-height: 70vh;
}
.settings-nav {
    width: 240px;
    flex-shrink: 0;
    background: var(--cp-surface);
    border-radius: var(--cp-radius-lg);
    padding: 8px;
    height: fit-content;
    position: sticky;
    top: 100px;
}
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: var(--cp-radius-md);
    color: var(--cp-text);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}
.settings-nav-item:hover {
    background: var(--cp-bg-alt);
    color: var(--cp-primary);
}
.settings-nav-item.active {
    background: var(--cp-primary);
    color: #fff;
}
.settings-nav-item i {
    font-size: 18px;
    width: 24px;
    text-align: center;
}
.settings-content {
    flex: 1;
    min-width: 0;
}
.settings-panel {
    display: none;
}
.settings-panel.active {
    display: block;
}
.device-card {
    background: var(--cp-bg);
    border: 1px solid var(--cp-border-light);
    border-radius: var(--cp-radius-md);
    margin-bottom: 16px;
}
.device-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--cp-border-light);
}
.device-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.device-icon {
    font-size: 24px;
    color: var(--cp-primary);
}
.device-body {
    padding: 16px;
}

@media (max-width: 991px) {
    .settings-container {
        flex-direction: column;
    }
    .settings-nav {
        width: 100%;
        display: flex;
        overflow-x: auto;
        position: static;
        gap: 8px;
    }
    .settings-nav-item {
        white-space: nowrap;
        padding: 10px 14px;
    }
    .settings-nav-item span {
        display: none;
    }
    .settings-nav-item i {
        margin: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Season Mode Toggle
async function toggleSeasonMode(enabled) {
    const toggle = document.getElementById('seasonModeToggle');
    const label = document.getElementById('seasonModeLabel');
    const alert = document.getElementById('seasonModeAlert');

    try {
        const res = await fetch('{{ route("settings.season-mode") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ season_mode: enabled })
        });

        const data = await res.json();

        if (data.success) {
            label.textContent = data.season_mode ? 'ON' : 'OFF';
            alert.className = data.season_mode ? 'alert alert-success mt-3 mb-0' : 'alert alert-warning mt-3 mb-0';
            alert.innerHTML = data.season_mode
                ? '<i class="bi bi-check-circle me-2"></i><strong>Season Mode Active:</strong> All cashiers can process payments directly.'
                : '<i class="bi bi-hourglass-split me-2"></i><strong>Normal Mode:</strong> Bill-only cashiers create bills without payment. Main cashier approves payments.';

            // Show toast
            showToast(data.message, 'success');
        } else {
            toggle.checked = !enabled; // Revert
            showToast(data.message || 'Failed to update season mode', 'danger');
        }
    } catch (e) {
        toggle.checked = !enabled; // Revert
        showToast('Network error. Please try again.', 'danger');
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':type==='danger'?'x-circle':'info-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// Tab navigation
document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const tab = this.dataset.tab;

        // Update nav
        document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');

        // Update panel
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(tab).classList.add('active');

        // Update URL
        history.replaceState(null, null, '#' + tab);
    });
});

// Load tab from URL hash
if (window.location.hash) {
    const tab = window.location.hash.substring(1);
    const navItem = document.querySelector(`[data-tab="${tab}"]`);
    if (navItem) navItem.click();
}

// Add printer
let printerIndex = {{ count($store->settings['printers'] ?? []) }};
function addPrinter() {
    document.getElementById('noPrintersMsg')?.remove();
    const html = `
        <div class="device-card" data-printer="${printerIndex}">
            <div class="device-header">
                <div class="device-info">
                    <i class="bi bi-printer device-icon"></i>
                    <input type="text" name="printers[${printerIndex}][name]" class="form-control form-control-sm" placeholder="Printer Name" style="width:200px">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePrinter(${printerIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="device-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Connection Type</label>
                        <select name="printers[${printerIndex}][type]" class="form-select form-select-sm" onchange="updatePrinterFields(this, ${printerIndex})">
                            <option value="usb">USB</option>
                            <option value="network">Network/IP</option>
                            <option value="bluetooth">Bluetooth</option>
                            <option value="wifi">WiFi Direct</option>
                            <option value="cloud">Cloud Print</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Paper Size</label>
                        <select name="printers[${printerIndex}][paper_size]" class="form-select form-select-sm">
                            <option value="80mm">80mm Thermal</option>
                            <option value="58mm">58mm Thermal</option>
                            <option value="A4">A4</option>
                            <option value="A5">A5</option>
                            <option value="A3">A3</option>
                        </select>
                    </div>
                    <div class="col-md-3 printer-ip-field" style="display:none">
                        <label class="form-label small">IP Address</label>
                        <input type="text" name="printers[${printerIndex}][ip_address]" class="form-control form-control-sm" placeholder="192.168.1.100">
                    </div>
                    <div class="col-md-3 printer-port-field" style="display:none">
                        <label class="form-label small">Port</label>
                        <input type="number" name="printers[${printerIndex}][port]" class="form-control form-control-sm" value="9100" placeholder="9100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Usage</label>
                        <select name="printers[${printerIndex}][usage]" class="form-select form-select-sm">
                            <option value="receipt">Receipt Printer</option>
                            <option value="invoice">Invoice Printer</option>
                            <option value="kitchen">Kitchen Display</option>
                            <option value="label">Label Printer</option>
                            <option value="report">Report Printer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="printers[${printerIndex}][enabled]" class="form-check-input" checked>
                            <label class="form-check-label">Enabled</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="testPrinter(${printerIndex})">
                            <i class="bi bi-printer"></i> Test Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('printerList').insertAdjacentHTML('beforeend', html);
    printerIndex++;
}

function removePrinter(index) {
    document.querySelector(`[data-printer="${index}"]`)?.remove();
}

function updatePrinterFields(select, index) {
    const card = document.querySelector(`[data-printer="${index}"]`);
    const isNetwork = select.value === 'network' || select.value === 'wifi';
    card.querySelectorAll('.printer-ip-field, .printer-port-field').forEach(el => {
        el.style.display = isNetwork ? '' : 'none';
    });
}

function testPrinter(index) {
    alert('Test print sent to printer ' + index);
}

// Add tax row
let taxIndex = {{ $taxes->count() }};
function addTaxRow() {
    document.getElementById('noTaxRow')?.remove();
    const html = `
        <tr>
            <td><input type="text" name="taxes[new${taxIndex}][name]" class="form-control form-control-sm" placeholder="Tax Name"></td>
            <td><input type="number" name="taxes[new${taxIndex}][rate]" class="form-control form-control-sm" value="0" step="0.01"></td>
            <td>
                <select name="taxes[new${taxIndex}][type]" class="form-select form-select-sm">
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed Amount</option>
                </select>
            </td>
            <td>
                <select name="taxes[new${taxIndex}][apply_to]" class="form-select form-select-sm">
                    <option value="all">All Products</option>
                    <option value="taxable">Taxable Only</option>
                </select>
            </td>
            <td>
                <div class="form-check form-switch">
                    <input type="checkbox" name="taxes[new${taxIndex}][is_active]" class="form-check-input" checked>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    document.querySelector('#taxTable tbody').insertAdjacentHTML('beforeend', html);
    taxIndex++;
}

// System Reset Logic
document.getElementById('resetType')?.addEventListener('change', function() {
    const type = this.value;
    const confirmBox = document.getElementById('confirmTextBox');
    const confirmText = document.getElementById('confirmText');
    const resetBtn = document.getElementById('resetBtn');

    if (type === 'full_reset') {
        confirmBox.classList.remove('d-none');
        confirmText.required = true;
        resetBtn.disabled = true;
    } else {
        confirmBox.classList.add('d-none');
        confirmText.required = false;
        confirmText.value = '';
        resetBtn.disabled = !type;
    }
});

document.getElementById('confirmText')?.addEventListener('input', function() {
    const resetBtn = document.getElementById('resetBtn');
    resetBtn.disabled = this.value !== 'RESET ALL DATA';
});

document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const type = document.getElementById('resetType').value;
    const typeLabels = {
        'orders': 'ALL ORDERS & SALES',
        'expenses': 'ALL EXPENSES',
        'incomes': 'ALL INCOMES',
        'inventory': 'INVENTORY (reset to zero)',
        'customers': 'ALL CUSTOMERS',
        'full_reset': 'ENTIRE SYSTEM'
    };

    if (!confirm(`⚠️ WARNING!\n\nYou are about to delete ${typeLabels[type]}.\n\nThis action CANNOT be undone!\n\nClick OK to proceed or Cancel to abort.`)) {
        e.preventDefault();
        return false;
    }

    if (type === 'full_reset') {
        if (!confirm('🚨 FINAL WARNING!\n\nThis will permanently delete ALL:\n- Orders & Sales\n- Expenses\n- Incomes\n- Stock Movements\n- Purchases\n- Register Sessions\n\nAre you ABSOLUTELY sure?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Live Receipt Preview Updates
function updateReceiptPreview() {
    const preview = document.getElementById('receiptPreview');
    if (!preview) return;

    const paper = preview.querySelector('.receipt-paper');
    const dividers = preview.querySelectorAll('.receipt-divider');
    const storeName = preview.querySelector('.store-name');
    const items = preview.querySelectorAll('.ri-name, .ri-line, .receipt-meta div');
    const totals = preview.querySelector('.rt-row.rt-total');

    // Get current settings values
    const fontStoreName = document.querySelector('[name="print_font_store_name"]')?.value || '16';
    const fontItems = document.querySelector('[name="print_font_items"]')?.value || '12';
    const fontTotal = document.querySelector('[name="print_font_total"]')?.value || '16';
    const lineSpacing = document.querySelector('[name="print_line_spacing"]')?.value || '1.4';
    const dividerStyle = document.querySelector('[name="print_divider_style"]')?.value || 'dashed';
    const printColors = document.querySelector('[name="print_colors"]')?.value || 'color';

    // Apply to preview (scaled down for preview)
    const scale = 0.85;
    if (paper) paper.style.lineHeight = lineSpacing;
    if (storeName) {
        storeName.style.fontSize = (parseInt(fontStoreName) * scale) + 'px';
        storeName.style.color = printColors === 'bw' ? '#000' : '#1565c0';
    }
    items.forEach(el => el.style.fontSize = (parseInt(fontItems) * scale) + 'px');
    if (totals) {
        totals.style.fontSize = (parseInt(fontTotal) * scale) + 'px';
        totals.style.color = printColors === 'bw' ? '#000' : '#1565c0';
    }
    dividers.forEach(d => {
        if (dividerStyle === 'none') {
            d.style.borderTop = 'none';
            d.style.margin = '8px 0';
        } else {
            d.style.borderTop = '1px ' + dividerStyle + ' #ccc';
            d.style.margin = '10px 0';
        }
    });
}

// Print preview receipt
function printPreviewReceipt() {
    const preview = document.getElementById('receiptPreview');
    if (!preview) return;

    const printWindow = window.open('', '_blank', 'width=400,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Receipt Preview</title>
            <style>
                body { margin: 0; padding: 10mm; font-family: 'Courier New', monospace; }
                .receipt-paper { max-width: 72mm; margin: 0 auto; }
                .receipt-header { text-align: center; }
                .store-name { font-size: ${document.querySelector('[name="print_font_store_name"]')?.value || '16'}px; font-weight: 700; margin-bottom: 5px; color: ${document.querySelector('[name="print_colors"]')?.value === 'bw' ? '#000' : '#1565c0'}; }
                .store-detail { font-size: 12px; margin: 2px 0; color: #666; }
                .receipt-divider { border: none; border-top: 1px ${document.querySelector('[name="print_divider_style"]')?.value || 'dashed'} #ccc; margin: 12px 0; }
                .receipt-meta { font-size: ${document.querySelector('[name="print_font_items"]')?.value || '12'}px; }
                .meta-label { font-weight: 700; }
                .receipt-item { margin-bottom: 8px; }
                .ri-name { font-weight: 700; font-size: ${document.querySelector('[name="print_font_items"]')?.value || '12'}px; }
                .ri-line { display: flex; justify-content: space-between; font-size: ${document.querySelector('[name="print_font_items"]')?.value || '12'}px; color: #666; }
                .receipt-totals .rt-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: ${document.querySelector('[name="print_font_items"]')?.value || '12'}px; }
                .rt-row.rt-total { font-size: ${document.querySelector('[name="print_font_total"]')?.value || '16'}px; font-weight: 700; color: ${document.querySelector('[name="print_colors"]')?.value === 'bw' ? '#000' : '#1565c0'}; padding-top: 6px; margin-top: 4px; }
                .rt-row.rt-change { color: #2e7d32; }
                .receipt-footer { text-align: center; margin-top: 10px; }
                .footer-thanks { font-size: 12px; font-style: italic; color: #666; }
                .receipt-powered { font-size: 11px; color: #1976d2; }
            </style>
        </head>
        <body>
            ${preview.innerHTML}
            <script>window.onload = function() { window.print(); }<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Attach live preview updates to form inputs
document.addEventListener('DOMContentLoaded', function() {
    const inputs = [
        'print_font_store_name', 'print_font_items', 'print_font_total',
        'print_line_spacing', 'print_divider_style', 'print_colors'
    ];
    inputs.forEach(name => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.addEventListener('change', updateReceiptPreview);
    });
    // Initial update
    updateReceiptPreview();
});
</script>
@endpush
@endsection
