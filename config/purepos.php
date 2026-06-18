<?php
/**
 * Pure POS — Application Configuration
 * by Nexfloit
 */
return [
    'name'           => env('APP_NAME', 'Pure POS'),
    'version'        => '1.0.0',
    'currency'       => env('POS_CURRENCY', 'LKR'),
    'currency_symbol'=> env('POS_CURRENCY_SYMBOL', 'Rs.'),
    'tax_rate'       => env('POS_TAX_RATE', 0),
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Thank you for your business!'),
    'timezone'       => env('POS_TIMEZONE', 'Asia/Colombo'),
    'business_name'  => env('POS_BUSINESS_NAME', 'Pure POS'),

    /*
     * Supported payment methods
     */
    'payment_methods' => [
        'cash'           => ['label' => 'Cash', 'icon' => 'bi-cash-coin'],
        'card'           => ['label' => 'Credit/Debit Card', 'icon' => 'bi-credit-card'],
        'mobile_payment' => ['label' => 'Mobile Payment', 'icon' => 'bi-phone'],
        'bank_transfer'  => ['label' => 'Bank Transfer', 'icon' => 'bi-bank'],
        'cheque'         => ['label' => 'Cheque', 'icon' => 'bi-file-earmark-text'],
        'credit'         => ['label' => 'Customer Credit', 'icon' => 'bi-person-credit-card'],
        'gift_card'      => ['label' => 'Gift Card', 'icon' => 'bi-gift'],
        'loyalty_points' => ['label' => 'Loyalty Points', 'icon' => 'bi-star'],
    ],

    /*
     * Expense categories defaults
     */
    'expense_categories' => [
        'Rent', 'Utilities', 'Salaries', 'Maintenance',
        'Marketing', 'Transport', 'Miscellaneous',
    ],

    /*
     * Low stock alert threshold
     */
    'low_stock_threshold' => 5,

    /*
     * Loyalty points config
     */
    'loyalty' => [
        'earn_rate'  => 1,      // 1 point per Rs. 100
        'redeem_rate'=> 0.10,   // Rs. 0.10 per point
    ],
];
