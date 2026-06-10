<?php
/**
 * Ceyloan POS — Global Helper Functions
 */

if (!function_exists('ceylonpos_currency')) {
    function ceylonpos_currency(float $amount, string $symbol = null): string {
        $sym = $symbol ?? config('ceylonpos.currency_symbol', 'Rs.');
        return $sym . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('ceylonpos_format_date')) {
    function ceylonpos_format_date($date, string $format = 'd M Y'): string {
        if (!$date) return '—';
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('ceylonpos_status_color')) {
    function ceylonpos_status_color(string $status): string {
        return match($status) {
            'completed', 'received', 'active', 'open' => 'success',
            'pending', 'ordered', 'partial'            => 'warning',
            'cancelled', 'expired', 'inactive'        => 'danger',
            'on_hold', 'draft'                         => 'info',
            default                                    => 'secondary',
        };
    }
}

if (!function_exists('ceylonpos_stock_status')) {
    function ceylonpos_stock_status(float $stock, float $minStock): string {
        if ($stock <= 0)         return 'out';
        if ($stock <= $minStock) return 'low';
        return 'ok';
    }
}
