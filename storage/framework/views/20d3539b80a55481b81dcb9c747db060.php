


<?php if(isset($paymentReminder) && $paymentReminder['show'] && !session('payment_reminder_dismissed')): ?>
<div class="payment-reminder-overlay" id="paymentReminderOverlay">
    <div class="payment-reminder-popup">
        <button class="popup-close" onclick="dismissPaymentReminder()">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="popup-content">
            <!-- Battery Animation -->
            <div class="battery-container">
                <div class="battery">
                    <div class="battery-head"></div>
                    <div class="battery-body">
                        <div class="battery-level">
                            <div class="battery-fill"></div>
                        </div>
                        <div class="charging-bolt">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                    </div>
                </div>
                <div class="charging-particles">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </div>

            <!-- Message -->
            <h2 class="popup-title">Payment Due Soon!</h2>
            <p class="popup-subtitle">Your subscription payment is due</p>

            <!-- Details -->
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Billing Period</span>
                    <span class="detail-value"><?php echo e($paymentReminder['billingMonth']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date</span>
                    <span class="detail-value text-warning"><?php echo e($paymentReminder['dueDate']->format('F d, Y')); ?></span>
                </div>
                <div class="detail-row amount-row">
                    <span class="detail-label">Amount Due</span>
                    <span class="detail-value amount">Rs. <?php echo e(number_format($paymentReminder['amount'], 2)); ?></span>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <p>Please contact Nexfloit to make your payment:</p>
                <p class="contact-phone">
                    <i class="bi bi-telephone-fill"></i>
                    <?php echo e($paymentReminder['phone'] ?? '+94 77 123 4567'); ?>

                </p>
                <p class="contact-email">
                    <i class="bi bi-envelope-fill"></i>
                    <?php echo e($paymentReminder['email'] ?? 'billing@nexfloit.com'); ?>

                </p>
            </div>

            <!-- Actions -->
            <button class="btn btn-primary btn-dismiss" onclick="dismissPaymentReminder()">
                I Understand
            </button>
        </div>
    </div>
</div>

<style>
.payment-reminder-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    backdrop-filter: blur(10px);
}

.payment-reminder-popup {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 20px;
    padding: 40px;
    max-width: 420px;
    width: 90%;
    position: relative;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: popupSlideIn 0.5s ease;
}

@keyframes popupSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.popup-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #fff;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}

.popup-content {
    text-align: center;
    color: #fff;
}

/* Battery Animation */
.battery-container {
    margin-bottom: 25px;
    position: relative;
}

.battery {
    display: inline-flex;
    align-items: center;
}

.battery-head {
    width: 8px;
    height: 25px;
    background: linear-gradient(to bottom, #00d9a5, #00b894);
    border-radius: 0 4px 4px 0;
    margin-left: -2px;
}

.battery-body {
    width: 80px;
    height: 45px;
    border: 3px solid #00d9a5;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
}

.battery-level {
    position: absolute;
    bottom: 3px;
    left: 3px;
    right: 3px;
    height: 0%;
    background: linear-gradient(to top, #00d9a5, #00b894);
    border-radius: 4px;
    animation: batteryCharge 2s ease-in-out infinite;
}

@keyframes batteryCharge {
    0%, 100% { height: 20%; }
    50% { height: 80%; }
}

.charging-bolt {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: #1a1a2e;
    z-index: 2;
    animation: boltPulse 1s ease-in-out infinite;
}

@keyframes boltPulse {
    0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.7; transform: translate(-50%, -50%) scale(1.1); }
}

.charging-particles {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.charging-particles span {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #00d9a5;
    border-radius: 50%;
    animation: particleFloat 2s ease-in-out infinite;
}

.charging-particles span:nth-child(1) { animation-delay: 0s; left: -40px; }
.charging-particles span:nth-child(2) { animation-delay: 0.3s; left: -30px; }
.charging-particles span:nth-child(3) { animation-delay: 0.6s; left: -20px; }
.charging-particles span:nth-child(4) { animation-delay: 0.9s; left: -10px; }
.charging-particles span:nth-child(5) { animation-delay: 1.2s; left: 0px; }

@keyframes particleFloat {
    0% { opacity: 0; transform: translateY(20px); }
    50% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-20px); }
}

.popup-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #e94560;
}

.popup-subtitle {
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 25px;
}

.payment-details {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: rgba(255, 255, 255, 0.6);
}

.detail-value {
    font-weight: 600;
}

.detail-value.text-warning {
    color: #f39c12;
}

.amount-row {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
}

.detail-value.amount {
    font-size: 1.5rem;
    color: #00d9a5;
}

.contact-info {
    margin-bottom: 25px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.contact-phone, .contact-email {
    margin: 5px 0;
    color: #fff;
}

.contact-phone i, .contact-email i {
    margin-right: 8px;
    color: #00d9a5;
}

.btn-dismiss {
    background: linear-gradient(135deg, #e94560, #c73e54);
    border: none;
    padding: 12px 40px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s;
}

.btn-dismiss:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(233, 69, 96, 0.3);
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .payment-reminder-popup {
        padding: 25px;
        margin: 15px;
    }

    .popup-title {
        font-size: 1.4rem;
    }

    .detail-value.amount {
        font-size: 1.25rem;
    }
}
</style>

<script>
function dismissPaymentReminder() {
    document.getElementById('paymentReminderOverlay').style.display = 'none';
    // Store dismissal in session via AJAX
    fetch('<?php echo e(url("/api/dismiss-payment-reminder")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).catch(() => {});
}
</script>
<?php endif; ?>
<?php /**PATH C:\Users\pathu\Desktop\puretec pos\resources\views/components/payment-reminder-popup.blade.php ENDPATH**/ ?>