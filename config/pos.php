<?php
/*
|--------------------------------------------------------------------------
| Ceyloan POS – Configuration
|--------------------------------------------------------------------------
*/
return [
    'name'             => env('POS_BUSINESS_NAME', 'Ceyloan POS'),
    'version'          => '1.0.0',
    'currency'         => env('POS_CURRENCY', 'LKR'),
    'currency_symbol'  => env('POS_CURRENCY_SYMBOL', 'Rs.'),
    'tax_rate'         => env('POS_TAX_RATE', 0),
    'receipt_footer'   => env('POS_RECEIPT_FOOTER', 'Thank you for your business!'),
    'timezone'         => env('POS_TIMEZONE', 'Asia/Colombo'),
    'date_format'      => 'd/m/Y',
    'time_format'      => 'h:i A',
    'decimal_places'   => 2,
    'thousand_sep'     => ',',
    'decimal_sep'      => '.',
    'low_stock_threshold' => 5,
    'loyalty' => [
        'enabled'          => true,
        'points_per_rupee' => 0.01,
        'value_per_point'  => 0.10,
        'tiers' => [
            'bronze'   => ['min' => 0,      'discount' => 0],
            'silver'   => ['min' => 5000,   'discount' => 2],
            'gold'     => ['min' => 15000,  'discount' => 5],
            'platinum' => ['min' => 50000,  'discount' => 10],
        ],
    ],
    'payment_methods' => [
        'cash'           => ['label' => 'Cash',           'icon' => 'bi-cash-stack'],
        'card'           => ['label' => 'Card',           'icon' => 'bi-credit-card'],
        'mobile_payment' => ['label' => 'Mobile Payment', 'icon' => 'bi-phone'],
        'bank_transfer'  => ['label' => 'Bank Transfer',  'icon' => 'bi-bank'],
        'cheque'         => ['label' => 'Cheque',         'icon' => 'bi-file-text'],
        'credit'         => ['label' => 'Credit',         'icon' => 'bi-clock-history'],
        'gift_card'      => ['label' => 'Gift Card',      'icon' => 'bi-gift'],
        'loyalty_points' => ['label' => 'Loyalty Points', 'icon' => 'bi-star'],
    ],
    'order_statuses' => [
        'pending'      => ['label' => 'Pending',       'class' => 'warning'],
        'processing'   => ['label' => 'Processing',    'class' => 'info'],
        'completed'    => ['label' => 'Completed',     'class' => 'success'],
        'cancelled'    => ['label' => 'Cancelled',     'class' => 'danger'],
        'refunded'     => ['label' => 'Refunded',      'class' => 'secondary'],
        'on_hold'      => ['label' => 'On Hold',       'class' => 'dark'],
    ],
];
