<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>System Locked - Pure POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            height: 100vh;
            height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .locked-container {
            text-align: center;
            padding: clamp(10px, 2vh, 25px) clamp(15px, 3vw, 30px);
            width: 95%;
            max-width: 420px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: clamp(8px, 1.5vh, 20px);
        }

        /* Lock Icon */
        .lock-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: clamp(60px, 12vh, 120px);
        }

        .lock-icon {
            font-size: clamp(40px, 8vh, 80px);
            color: #e94560;
            animation: lockShake 3s ease-in-out infinite;
        }

        @keyframes lockShake {
            0%, 100% { transform: rotate(0deg); }
            5%, 15% { transform: rotate(-5deg); }
            10%, 20% { transform: rotate(5deg); }
            25%, 100% { transform: rotate(0deg); }
        }

        .lock-pulse {
            position: absolute;
            width: clamp(70px, 14vh, 130px);
            height: clamp(70px, 14vh, 130px);
            border: 2px solid #e94560;
            border-radius: 50%;
            animation: lockPulse 2s ease-out infinite;
        }

        @keyframes lockPulse {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .locked-title {
            font-size: clamp(1.4rem, 4vh, 2.2rem);
            font-weight: 700;
            color: #e94560;
            text-shadow: 0 0 30px rgba(233, 69, 96, 0.5);
            line-height: 1.1;
        }

        .locked-subtitle {
            font-size: clamp(0.8rem, 1.8vh, 1rem);
            color: rgba(255, 255, 255, 0.7);
        }

        /* Info Card */
        .locked-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(12px, 2vh, 20px);
            padding: clamp(12px, 2vh, 25px);
        }

        .tenant-info {
            padding-bottom: clamp(8px, 1.5vh, 15px);
            margin-bottom: clamp(8px, 1.5vh, 15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tenant-name {
            font-size: clamp(0.95rem, 2.2vh, 1.2rem);
            font-weight: 600;
        }

        .tenant-code {
            font-family: monospace;
            color: rgba(255, 255, 255, 0.5);
            font-size: clamp(0.7rem, 1.5vh, 0.85rem);
        }

        .amount-due {
            margin-bottom: clamp(8px, 1.5vh, 15px);
        }

        .amount-label {
            font-size: clamp(0.65rem, 1.3vh, 0.8rem);
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-value {
            font-size: clamp(1.5rem, 4vh, 2.2rem);
            font-weight: 700;
            color: #00d9a5;
            text-shadow: 0 0 20px rgba(0, 217, 165, 0.3);
        }

        .lock-reason {
            background: rgba(233, 69, 96, 0.2);
            border: 1px solid rgba(233, 69, 96, 0.3);
            border-radius: 8px;
            padding: clamp(8px, 1.5vh, 12px);
            color: #e94560;
            font-size: clamp(0.7rem, 1.5vh, 0.85rem);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .lock-reason i {
            font-size: clamp(0.9rem, 1.8vh, 1.1rem);
        }

        /* Contact Section */
        .contact-section {
            background: rgba(0, 217, 165, 0.1);
            border: 1px solid rgba(0, 217, 165, 0.2);
            border-radius: clamp(10px, 1.5vh, 15px);
            padding: clamp(10px, 2vh, 20px);
        }

        .contact-title {
            font-size: clamp(0.65rem, 1.4vh, 0.85rem);
            color: #00d9a5;
            margin-bottom: clamp(6px, 1vh, 12px);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-items {
            display: flex;
            justify-content: center;
            gap: clamp(15px, 4vw, 30px);
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: clamp(0.75rem, 1.6vh, 0.9rem);
        }

        .contact-item i {
            color: #00d9a5;
            font-size: clamp(0.9rem, 2vh, 1.1rem);
        }

        .contact-item a {
            color: #fff;
            text-decoration: none;
        }

        /* Logout Button */
        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: clamp(8px, 1.5vh, 12px) clamp(20px, 5vw, 35px);
            border-radius: 8px;
            font-size: clamp(0.8rem, 1.8vh, 0.95rem);
            cursor: pointer;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(233, 69, 96, 0.08);
        }

        .bg-shape:nth-child(1) {
            width: 40vw;
            height: 40vw;
            max-width: 300px;
            max-height: 300px;
            top: -10%;
            left: -10%;
        }

        .bg-shape:nth-child(2) {
            width: 30vw;
            height: 30vw;
            max-width: 200px;
            max-height: 200px;
            bottom: -5%;
            right: -5%;
        }

        .bg-shape:nth-child(3) {
            width: 20vw;
            height: 20vw;
            max-width: 150px;
            max-height: 150px;
            top: 40%;
            right: 5%;
        }

        /* Landscape mode adjustments */
        @media (max-height: 500px) and (orientation: landscape) {
            .locked-container {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                max-width: 100%;
                gap: 10px 20px;
            }

            .lock-icon-wrapper {
                height: 80px;
                width: 100px;
            }

            .lock-icon {
                font-size: 50px;
            }

            .lock-pulse {
                width: 70px;
                height: 70px;
            }

            .header-section {
                text-align: left;
            }

            .locked-card, .contact-section {
                flex: 1;
                min-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <div class="locked-container">
        <div class="lock-icon-wrapper">
            <div class="lock-pulse"></div>
            <i class="bi bi-lock-fill lock-icon"></i>
        </div>

        <div class="header-section">
            <h1 class="locked-title">System Locked</h1>
            <p class="locked-subtitle">Your subscription has expired</p>
        </div>

        <div class="locked-card">
            <div class="tenant-info">
                <div class="tenant-name">{{ $tenant->business_name }}</div>
                <div class="tenant-code">{{ $tenant->code }}</div>
            </div>

            <div class="amount-due">
                <div class="amount-label">Amount Due</div>
                <div class="amount-value">Rs. {{ number_format($pendingAmount, 2) }}</div>
            </div>

            @if($tenant->lock_reason)
                <div class="lock-reason">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>{{ $tenant->lock_reason }}</span>
                </div>
            @endif
        </div>

        <div class="contact-section">
            <div class="contact-title">Contact Nexfloit to Restore Access</div>
            <div class="contact-items">
                <div class="contact-item">
                    <i class="bi bi-telephone-fill"></i>
                    <a href="tel:+94771234567">+94 77 123 4567</a>
                </div>
                <div class="contact-item">
                    <i class="bi bi-whatsapp"></i>
                    <a href="https://wa.me/94771234567">WhatsApp</a>
                </div>
                <div class="contact-item">
                    <i class="bi bi-envelope-fill"></i>
                    <a href="mailto:support@nexfloit.com">support@nexfloit.com</a>
                </div>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </button>
        </form>
    </div>
</body>
</html>
