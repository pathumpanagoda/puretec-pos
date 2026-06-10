<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $user    = Auth::user();
        $storeId = $user->store_id;
        $today   = now()->toDateString();

        // Today's stats
        $todayStats = $this->reportService->getSalesSummary($storeId, $today, $today);

        // Monthly stats
        $monthStart = now()->startOfMonth()->toDateString();
        $monthStats = $this->reportService->getSalesSummary($storeId, $monthStart, $today);

        // Key metrics
        $totalProducts  = Product::where('store_id', $storeId)->where('is_active', true)->count();
        $totalCustomers = Customer::where('store_id', $storeId)->where('is_active', true)->count();
        $lowStockCount  = Product::where('store_id', $storeId)->where('track_stock', true)->where('is_active', true)->whereColumn('stock_quantity', '<=', 'reorder_level')->count();
        $pendingOrders  = Order::where('store_id', $storeId)->where('status', 'on_hold')->count();

        // Recent orders
        $recentOrders = Order::where('store_id', $storeId)
            ->with(['customer','user'])
            ->latest()
            ->limit(10)
            ->get();

        // Top products (this month)
        $topProducts = $this->reportService->getTopProducts($storeId, $monthStart, $today, 5);

        // Sales chart (last 7 days)
        $salesChart = $this->reportService->getDailySalesChart($storeId, 7);

        return view('dashboard.index', compact(
            'todayStats','monthStats','totalProducts','totalCustomers',
            'lowStockCount','pendingOrders','recentOrders','topProducts','salesChart'
        ));
    }
}
