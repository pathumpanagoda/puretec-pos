<?php
/**
 * Pure POS — Global Helper Functions
 */

if (!function_exists('purepos_currency')) {
    function purepos_currency(float $amount, string $symbol = null): string {
        $sym = $symbol ?? config('purepos.currency_symbol', 'Rs.');
        return $sym . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('purepos_format_date')) {
    function purepos_format_date($date, string $format = 'd M Y'): string {
        if (!$date) return '—';
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('purepos_status_color')) {
    function purepos_status_color(string $status): string {
        return match($status) {
            'completed', 'received', 'active', 'open' => 'success',
            'pending', 'ordered', 'partial'            => 'warning',
            'cancelled', 'expired', 'inactive'        => 'danger',
            'on_hold', 'draft'                         => 'info',
            default                                    => 'secondary',
        };
    }
}

if (!function_exists('purepos_stock_status')) {
    function purepos_stock_status(float $stock, float $minStock): string {
        if ($stock <= 0)         return 'out';
        if ($stock <= $minStock) return 'low';
        return 'ok';
    }
}
