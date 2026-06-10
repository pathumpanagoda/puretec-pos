<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get sales summary for a date range.
     */
    public function getSalesSummary(int $storeId, string $startDate, string $endDate): array
    {
        $orders = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_sales,
                SUM(profit) as total_profit,
                SUM(discount_amount) as total_discounts,
                SUM(tax_amount) as total_tax,
                AVG(total_amount) as avg_order_value
            ')
            ->first();

        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $payments = Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId)->where('status', 'completed')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']))
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        return [
            'total_orders'     => $orders->total_orders ?? 0,
            'total_sales'      => $orders->total_sales ?? 0,
            'total_profit'     => ($orders->total_profit ?? 0) - $expenses,
            'gross_profit'     => $orders->total_profit ?? 0,
            'total_expenses'   => $expenses,
            'total_discounts'  => $orders->total_discounts ?? 0,
            'total_tax'        => $orders->total_tax ?? 0,
            'avg_order_value'  => $orders->avg_order_value ?? 0,
            'payments_by_method'=> $payments,
        ];
    }

    /**
     * Get top selling products.
     */
    public function getTopProducts(int $storeId, string $startDate, string $endDate, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return OrderItem::select('product_id', 'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(profit) as total_profit'))
            ->whereHas('order', fn($q) => $q->where('store_id', $storeId)->where('status', 'completed')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily sales chart data.
     */
    public function getDailySalesChart(int $storeId, int $days = 30): array
    {
        return Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as sales, SUM(profit) as profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}
