<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display - {{ $store?->name ?? 'Ceyloan POS' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0d47a1 0%, #1565c0 25%, #1976d2 50%, #2196f3 75%, #42a5f5 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 4px;
            text-transform: uppercase;
            background: rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }

        .main {
            flex: 1;
            display: flex;
            padding: 20px;
            gap: 20px;
            position: relative;
        }

        .left {
            flex: 1;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 25px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 4px;
            opacity: 0.8;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .items {
            flex: 1;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .items::-webkit-scrollbar { width: 6px; }
        .items::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 3px; }
        .items::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 18px;
            transition: all 0.3s;
        }

        .item.new-item {
            animation: slideIn 0.4s ease-out;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 15px;
            margin: 0 -10px;
        }

        @keyframes slideIn {
            from { transform: translateX(-30px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .item-name { flex: 1; font-weight: 500; }
        .item-qty { min-width: 60px; text-align: center; opacity: 0.8; }
        .item-price { min-width: 120px; text-align: right; font-weight: 600; }

        .totals {
            margin-top: auto;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 18px;
            opacity: 0.9;
        }

        .row.grand {
            font-size: 32px;
            font-weight: 800;
            padding-top: 15px;
            opacity: 1;
        }

        .method {
            font-size: 18px;
            background: rgba(255,255,255,0.2);
            padding: 12px 35px;
            border-radius: 40px;
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
        }

        .label {
            font-size: 20px;
            opacity: 0.8;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .amount {
            font-size: 72px;
            font-weight: 800;
            margin-bottom: 30px;
            text-shadow: 0 4px 30px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }

        .status {
            font-size: 28px;
            font-weight: 600;
            padding: 18px 50px;
            border-radius: 50px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            transition: all 0.3s;
        }

        .status.green { background: rgba(76,175,80,0.5); color: #b9f6ca; }
        .status.red { background: rgba(244,67,54,0.5); color: #ffcdd2; }
        .status.orange { background: rgba(255,152,0,0.5); color: #ffe0b2; }
        .status.pulse { animation: pulse 1.5s ease-in-out infinite; }
        .status.pulse-slow { animation: pulse 3s ease-in-out infinite; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Overlay States */
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .overlay.success { background: linear-gradient(135deg, rgba(76,175,80,0.95), rgba(56,142,60,0.95)); }
        .overlay.hold { background: linear-gradient(135deg, rgba(255,152,0,0.95), rgba(245,124,0,0.95)); }
        .overlay.cancel { background: linear-gradient(135deg, rgba(244,67,54,0.95), rgba(211,47,47,0.95)); }
        .overlay.ready-screen { background: linear-gradient(135deg, #0d47a1 0%, #1976d2 50%, #42a5f5 100%); }

        .overlay-icon {
            font-size: 140px;
            margin-bottom: 35px;
            animation: bounceIn 0.6s ease-out;
        }

        .overlay-icon .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .overlay-icon .icon-circle.success { color: #4caf50; }
        .overlay-icon .icon-circle.cancel { color: #f44336; }

        .overlay-text {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .overlay-sub {
            font-size: 28px;
            opacity: 0.9;
        }

        .ready-icon {
            font-size: 120px;
            margin-bottom: 25px;
            animation: float 3s ease-in-out infinite;
        }

        .ready-text {
            font-size: 64px;
            font-weight: 300;
            letter-spacing: 10px;
            margin-bottom: 20px;
        }

        .ready-sub {
            font-size: 28px;
            opacity: 0.8;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .left { display: none; }
            .amount { font-size: 56px; }
        }

        @media (max-height: 600px) {
            .header { padding: 12px; font-size: 20px; }
            .amount { font-size: 48px; }
            .status { font-size: 22px; padding: 14px 35px; }
            .overlay-text { font-size: 40px; }
        }

        /* Connection indicator */
        .connection-status {
            position: fixed;
            bottom: 15px;
            right: 15px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .connection-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
            animation: blink 2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .connection-dot.disconnected {
            background: #f44336;
            animation: none;
        }
    </style>
</head>
<body>
    <div class="header">{{ $store?->name ?? 'Ceyloan POS' }}</div>

    <div class="main" id="mainContent">
        <!-- Content will be dynamically updated -->
    </div>

    <div class="connection-status">
        <div class="connection-dot" id="connectionDot"></div>
        <span id="connectionText">Connected</span>
    </div>

    <script>
        const storeName = @json($store?->name ?? 'Ceyloan POS');
        const STORAGE_KEY = 'ceylon_pos_customer_display';
        let lastUpdate = 0;
        let currentState = 'ready';

        // Request fullscreen on load
        document.addEventListener('DOMContentLoaded', () => {
            // Try to go fullscreen
            setTimeout(() => {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(() => {});
                }
            }, 500);

            // Start polling for updates
            pollForUpdates();
            setInterval(pollForUpdates, 200); // Check every 200ms for real-time feel
        });

        // Poll localStorage for updates from POS
        function pollForUpdates() {
            try {
                const data = localStorage.getItem(STORAGE_KEY);
                if (data) {
                    const parsed = JSON.parse(data);
                    if (parsed.timestamp > lastUpdate) {
                        lastUpdate = parsed.timestamp;
                        updateDisplay(parsed);
                        setConnected(true);
                    }
                }
            } catch (e) {
                console.error('Error polling:', e);
            }
        }

        // Update display based on POS data
        function updateDisplay(data) {
            const main = document.getElementById('mainContent');
            currentState = data.state || 'normal';

            // Build HTML based on state
            let html = '';

            if (currentState === 'sale_complete') {
                html = `
                    <div class="overlay success">
                        <div class="overlay-icon">
                            <div class="icon-circle success"><i class="bi bi-check-lg" style="font-size:80px"></i></div>
                        </div>
                        <div class="overlay-text">Payment Successful!</div>
                        <div class="overlay-sub">Thank you for shopping with us</div>
                    </div>
                `;
            } else if (currentState === 'sale_hold') {
                html = `
                    <div class="overlay hold">
                        <div class="overlay-icon"><i class="bi bi-pause-circle" style="font-size:120px"></i></div>
                        <div class="overlay-text">Order Saved</div>
                        <div class="overlay-sub">Your order has been placed on hold</div>
                    </div>
                `;
            } else if (currentState === 'sale_cancel') {
                html = `
                    <div class="overlay cancel">
                        <div class="overlay-icon">
                            <div class="icon-circle cancel"><i class="bi bi-x-lg" style="font-size:80px"></i></div>
                        </div>
                        <div class="overlay-text">Order Cancelled</div>
                    </div>
                `;
            } else if (currentState === 'ready' || !data.cart || data.cart.length === 0) {
                html = `
                    <div class="overlay ready-screen">
                        <div class="ready-icon"><i class="bi bi-cart3"></i></div>
                        <div class="ready-text">Welcome!</div>
                        <div class="ready-sub">Ready for your order</div>
                    </div>
                `;
            } else {
                // Normal cart view
                const items = data.cart || [];
                const totals = data.totals || { subtotal: 0, grandTotal: 0, discountAmount: 0 };
                const paymentAmount = data.paymentAmount || 0;
                const paymentMethod = data.paymentMethod || 'cash';
                const lastItemIndex = items.length - 1;

                // Calculate status
                let statusText = 'Waiting...';
                let statusClass = '';
                const change = Math.max(0, paymentAmount - totals.grandTotal);
                const due = Math.max(0, totals.grandTotal - paymentAmount);

                if (paymentAmount > 0) {
                    if (change > 0) {
                        statusText = 'Change: Rs. ' + change.toFixed(2);
                        statusClass = 'green';
                    } else if (due > 0) {
                        statusText = 'Due: Rs. ' + due.toFixed(2);
                        statusClass = 'red';
                    } else {
                        statusText = 'Exact Amount';
                        statusClass = 'green';
                    }
                }

                const methodNames = {
                    cash: 'Cash', card: 'Card', mobile_payment: 'Mobile',
                    bank_transfer: 'Bank', credit: 'Credit', gift_card: 'Gift Card'
                };

                let itemsHtml = items.map((item, i) => `
                    <div class="item ${data.state === 'item_added' && i === lastItemIndex ? 'new-item' : ''}">
                        <span class="item-name">${item.product_name}</span>
                        <span class="item-qty">x${item.quantity}</span>
                        <span class="item-price">Rs. ${(item.quantity * item.unit_price).toFixed(2)}</span>
                    </div>
                `).join('');

                html = `
                    <div class="left">
                        <div class="title">Order Summary</div>
                        <div class="items" id="itemsList">${itemsHtml}</div>
                        <div class="totals">
                            <div class="row"><span>Subtotal</span><span>Rs. ${totals.subtotal.toFixed(2)}</span></div>
                            ${totals.discountAmount > 0 ? `<div class="row"><span>Discount</span><span style="color:#69f0ae">- Rs. ${totals.discountAmount.toFixed(2)}</span></div>` : ''}
                            <div class="row grand"><span>TOTAL</span><span>Rs. ${totals.grandTotal.toFixed(2)}</span></div>
                        </div>
                    </div>
                    <div class="right">
                        <div class="method">${methodNames[paymentMethod] || 'Cash'} Payment</div>
                        <div class="label">Total Amount</div>
                        <div class="amount">Rs. ${totals.grandTotal.toFixed(2)}</div>
                        ${paymentAmount > 0 ? `<div class="label">Received: Rs. ${paymentAmount.toFixed(2)}</div>` : ''}
                        <div class="status ${statusClass}">${statusText}</div>
                    </div>
                `;
            }

            main.innerHTML = html;

            // Auto-scroll items
            if (currentState === 'item_added') {
                setTimeout(() => {
                    const itemsList = document.getElementById('itemsList');
                    if (itemsList) itemsList.scrollTop = itemsList.scrollHeight;
                }, 100);
            }
        }

        // Connection status
        function setConnected(connected) {
            const dot = document.getElementById('connectionDot');
            const text = document.getElementById('connectionText');
            if (connected) {
                dot.classList.remove('disconnected');
                text.textContent = 'Connected';
            } else {
                dot.classList.add('disconnected');
                text.textContent = 'Disconnected';
            }
        }

        // Check if POS is active
        setInterval(() => {
            const data = localStorage.getItem(STORAGE_KEY);
            if (data) {
                const parsed = JSON.parse(data);
                // If no update in 30 seconds, show as disconnected
                if (Date.now() - parsed.timestamp > 30000) {
                    setConnected(false);
                }
            }
        }, 5000);

        // Handle fullscreen toggle with F11
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F11') {
                e.preventDefault();
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    document.documentElement.requestFullscreen();
                }
            }
        });
    </script>
</body>
</html>
