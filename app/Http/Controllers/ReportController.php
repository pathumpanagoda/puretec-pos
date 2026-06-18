<?php
namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\InventoryMovement;
use App\Models\Register;
use App\Models\Store;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    /**
     * Reports Dashboard / Index
     */
    public function index()
    {
        $storeId = Auth::user()->store_id;
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        // Quick stats for dashboard
        $todaySales = Order::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthSales = Order::where('store_id', $storeId)
            ->whereDate('created_at', '>=', $monthStart)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthExpenses = Expense::where('store_id', $storeId)
            ->whereDate('expense_date', '>=', $monthStart)
            ->sum('amount');

        $lowStockCount = Product::where('store_id', $storeId)
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();

        return view('reports.index', compact('todaySales', 'monthSales', 'monthExpenses', 'lowStockCount'));
    }

    /**
     * Daily Sales Report - POS transactions for a specific day
     */
    public function daily(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $date = $request->get('date', now()->toDateString());

        $orders = Order::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->with(['customer', 'user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'refunded' => $orders->where('status', 'refunded')->count(),
            'total_sales' => $orders->where('status', 'completed')->sum('total_amount'),
            'total_discount' => $orders->where('status', 'completed')->sum('discount_amount'),
            'total_tax' => $orders->where('status', 'completed')->sum('tax_amount'),
            'cash_sales' => 0,
            'card_sales' => 0,
            'other_sales' => 0,
        ];

        // Calculate payment method totals
        foreach ($orders->where('status', 'completed') as $order) {
            foreach ($order->payments as $payment) {
                if ($payment->method === 'cash') {
                    $summary['cash_sales'] += $payment->amount;
                } elseif ($payment->method === 'card') {
                    $summary['card_sales'] += $payment->amount;
                } else {
                    $summary['other_sales'] += $payment->amount;
                }
            }
        }

        // Hourly breakdown
        $hourlyData = $orders->where('status', 'completed')
            ->groupBy(fn($order) => $order->created_at->format('H'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('total_amount')
            ]);

        return view('reports.daily', compact('orders', 'summary', 'hourlyData', 'date'));
    }

    /**
     * Sales Report - Overall sales analysis
     */
    public function sales(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $summary = $this->reportService->getSalesSummary($storeId, $startDate, $endDate);
        $topProducts = $this->reportService->getTopProducts($storeId, $startDate, $endDate);
        $dailyChart = $this->reportService->getDailySalesChart($storeId, 30);

        // Sales by category
        $salesByCategory = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('categories.name as category', DB::raw('SUM(order_items.total) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        // Sales by user/cashier
        $salesByUser = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(fn($orders) => [
                'user' => $orders->first()->user?->name ?? 'Unknown',
                'orders' => $orders->count(),
                'total' => $orders->sum('total_amount')
            ])
            ->sortByDesc('total')
            ->values();

        return view('reports.sales', compact('summary', 'topProducts', 'dailyChart', 'startDate', 'endDate', 'salesByCategory', 'salesByUser'));
    }

    /**
     * POS Report - Register and transaction details
     */
    public function pos(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Get all orders with payment details
        $orders = Order::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['payments', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Payment method summary
        $paymentSummary = [
            'cash' => 0,
            'card' => 0,
            'bank_transfer' => 0,
            'mobile' => 0,
            'other' => 0
        ];

        foreach ($orders->where('status', 'completed') as $order) {
            foreach ($order->payments as $payment) {
                $method = $payment->method ?? 'other';
                if (isset($paymentSummary[$method])) {
                    $paymentSummary[$method] += $payment->amount;
                } else {
                    $paymentSummary['other'] += $payment->amount;
                }
            }
        }

        // Order status summary
        $statusSummary = [
            'completed' => $orders->where('status', 'completed')->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'refunded' => $orders->where('status', 'refunded')->count(),
        ];

        $totalSales = $orders->where('status', 'completed')->sum('total_amount');
        $totalRefunds = $orders->where('status', 'refunded')->sum('total_amount');

        return view('reports.pos', compact('orders', 'paymentSummary', 'statusSummary', 'totalSales', 'totalRefunds', 'startDate', 'endDate'));
    }

    /**
     * Inventory Report - Stock levels and valuation
     */
    public function inventory(Request $request)
    {
        $storeId  = Auth::user()->store_id;
        $products = Product::where('store_id', $storeId)->with(['category', 'supplier'])->orderBy('name')->get();

        $lowStock = $products->where('track_stock', true)->filter(fn($p) => $p->isLowStock());
        $outOfStock = $products->where('track_stock', true)->filter(fn($p) => $p->isOutOfStock());

        $totalValue = $products->sum(fn($p) => $p->selling_price * $p->stock_quantity);
        $totalCost  = $products->sum(fn($p) => $p->cost_price * $p->stock_quantity);
        $potentialProfit = $totalValue - $totalCost;

        // Stock by category
        $stockByCategory = $products->groupBy('category.name')
            ->map(fn($items) => [
                'count' => $items->count(),
                'quantity' => $items->sum('stock_quantity'),
                'value' => $items->sum(fn($p) => $p->selling_price * $p->stock_quantity)
            ]);

        // Top selling products (need to calculate from orders)
        $topSelling = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->select('order_items.product_id', 'order_items.product_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('reports.inventory', compact('products', 'lowStock', 'outOfStock', 'totalValue', 'totalCost', 'potentialProfit', 'stockByCategory', 'topSelling'));
    }

    /**
     * Stock Movements Report - All inventory adjustments
     */
    public function stockMovements(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->subDays(30)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());
        $type      = $request->get('type', '');
        $productId = $request->get('product_id', '');

        $query = InventoryMovement::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['product', 'user']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Summary stats
        $summary = [
            'total_movements' => InventoryMovement::where('store_id', $storeId)
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count(),
            'stock_in' => InventoryMovement::where('store_id', $storeId)
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->whereIn('type', ['purchase', 'opening', 'adjustment'])
                ->where('quantity', '>', 0)->sum('quantity'),
            'stock_out' => abs(InventoryMovement::where('store_id', $storeId)
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->whereIn('type', ['sale', 'damage', 'expired', 'adjustment'])
                ->where('quantity', '<', 0)->sum('quantity')),
            'total_value' => InventoryMovement::where('store_id', $storeId)
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->sum('total_cost'),
        ];

        // Movement types for filter
        $movementTypes = InventoryMovement::where('store_id', $storeId)
            ->select('type')->distinct()->pluck('type');

        // Products for filter
        $products = Product::where('store_id', $storeId)->orderBy('name')->get(['id', 'name']);

        return view('reports.stock-movements', compact('movements', 'summary', 'movementTypes', 'products', 'startDate', 'endDate', 'type', 'productId'));
    }

    /**
     * Profit Report - Revenue, costs, and profit analysis
     */
    public function profit(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Sales revenue
        $salesData = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->get();

        $totalRevenue = $salesData->sum('total_amount');
        $totalDiscount = $salesData->sum('discount_amount');

        // Calculate COGS (Cost of Goods Sold)
        $cogs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        // Expenses
        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $expensesByCategory = $expenses->groupBy('category.name')
            ->map(fn($e) => $e->sum('amount'));

        // Purchases
        $purchases = PurchaseOrder::where('store_id', $storeId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        // Calculate profits
        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Daily profit chart data
        $dailyProfitData = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.profit', compact(
            'totalRevenue', 'totalDiscount', 'cogs', 'totalExpenses', 'purchases',
            'grossProfit', 'netProfit', 'grossMargin', 'netMargin',
            'expensesByCategory', 'dailyProfitData', 'startDate', 'endDate'
        ));
    }

    /**
     * Cash Flow Report - Money in and out
     */
    public function cashflow(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Cash Inflows
        $cashSales = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->where('payments.method', 'cash')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('payments.amount');

        $cardSales = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->where('payments.method', 'card')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('payments.amount');

        $otherInflows = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereNotIn('payments.method', ['cash', 'card'])
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('payments.amount');

        // Other Income (salary, investments, etc.)
        $otherIncome = Income::where('store_id', $storeId)
            ->whereBetween('income_date', [$startDate, $endDate])
            ->sum('amount');

        $totalInflows = $cashSales + $cardSales + $otherInflows + $otherIncome;

        // Cash Outflows
        $expensesByCash = Expense::where('store_id', $storeId)
            ->where('payment_method', 'cash')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $expensesByBank = Expense::where('store_id', $storeId)
            ->whereIn('payment_method', ['bank_transfer', 'card'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $purchasePayments = PurchaseOrder::where('store_id', $storeId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['received', 'partial'])
            ->sum('total_amount');

        $totalOutflows = $expensesByCash + $expensesByBank + $purchasePayments;
        $netCashFlow = $totalInflows - $totalOutflows;

        // Daily cash flow
        $dailyCashFlow = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($currentDate <= $end) {
            $date = $currentDate->toDateString();

            $dayInflow = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $dayOutflow = Expense::where('store_id', $storeId)
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $dailyCashFlow[] = [
                'date' => $date,
                'inflow' => $dayInflow,
                'outflow' => $dayOutflow,
                'net' => $dayInflow - $dayOutflow
            ];

            $currentDate->addDay();
        }

        return view('reports.cashflow', compact(
            'cashSales', 'cardSales', 'otherInflows', 'otherIncome', 'totalInflows',
            'expensesByCash', 'expensesByBank', 'purchasePayments', 'totalOutflows',
            'netCashFlow', 'dailyCashFlow', 'startDate', 'endDate'
        ));
    }

    /**
     * Customer Report - Customer analysis
     */
    public function customers(Request $request)
    {
        $storeId  = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Top customers by revenue
        $topCustomers = Customer::where('store_id', $storeId)
            ->withCount(['orders' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])])
            ->withSum(['orders' => fn($q) => $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit(20)
            ->get();

        // Customer stats
        $totalCustomers = Customer::where('store_id', $storeId)->count();
        $newCustomers = Customer::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();

        // Customers with credit balance
        $customersWithCredit = Customer::where('store_id', $storeId)
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->get();

        $totalCredit = $customersWithCredit->sum('current_balance');

        // Customer loyalty stats
        $loyaltyStats = Customer::where('store_id', $storeId)
            ->whereNotNull('loyalty_tier')
            ->get()
            ->groupBy('loyalty_tier')
            ->map(fn($group) => $group->count());

        return view('reports.customers', compact(
            'topCustomers', 'totalCustomers', 'newCustomers',
            'customersWithCredit', 'totalCredit', 'loyaltyStats',
            'startDate', 'endDate'
        ));
    }

    /**
     * Expense Report - Expense analysis
     */
    public function expenses(Request $request)
    {
        $storeId  = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // By category
        $byCategory = $expenses->groupBy(fn($e) => $e->category?->name ?? 'Uncategorized')
            ->map(fn($group) => [
                'amount' => $group->sum('amount'),
                'count' => $group->count()
            ])
            ->sortByDesc('amount');

        // By payment method
        $byPaymentMethod = $expenses->groupBy('payment_method')
            ->map(fn($group) => $group->sum('amount'));

        // Daily expenses
        $dailyExpenses = $expenses->groupBy(fn($e) => $e->expense_date->format('Y-m-d'))
            ->map(fn($group) => $group->sum('amount'));

        // Monthly comparison (last 6 months)
        $monthlyComparison = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthExpenses = Expense::where('store_id', $storeId)
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');
            $monthlyComparison[$month->format('M Y')] = $monthExpenses;
        }

        return view('reports.expenses', compact(
            'expenses', 'totalExpenses', 'byCategory', 'byPaymentMethod',
            'dailyExpenses', 'monthlyComparison', 'startDate', 'endDate'
        ));
    }

    /**
     * Cash Balance Sheet Report - Complete cash position tracking
     */
    public function balanceSheet(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Get opening balance from register sessions at start date
        $openingRegister = Register::where('store_id', $storeId)
            ->whereDate('opened_at', '<', $startDate)
            ->where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->first();

        $openingBalance = $openingRegister ? ($openingRegister->closing_balance ?? 0) : 0;

        // If no previous register, check if there's a setting for initial cash
        if ($openingBalance == 0) {
            $firstRegister = Register::where('store_id', $storeId)
                ->whereDate('opened_at', '>=', $startDate)
                ->orderBy('opened_at', 'asc')
                ->first();
            $openingBalance = $firstRegister ? ($firstRegister->opening_balance ?? 0) : 0;
        }

        // Daily transactions with running balance
        $dailyTransactions = [];
        $runningBalance = $openingBalance;
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Totals
        $totalCashIn = 0;
        $totalCardIn = 0;
        $totalOtherIn = 0;
        $totalIncomeIn = 0;
        $totalCashOut = 0;
        $totalBankOut = 0;
        $totalPurchaseOut = 0;

        while ($currentDate <= $end) {
            $date = $currentDate->toDateString();

            // Cash Inflows for the day
            $dayCashSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->where('payments.method', 'cash')
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $dayCardSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->where('payments.method', 'card')
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $dayOtherSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->whereNotIn('payments.method', ['cash', 'card'])
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            // Cash Outflows for the day
            $dayCashExpenses = Expense::where('store_id', $storeId)
                ->where('payment_method', 'cash')
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $dayBankExpenses = Expense::where('store_id', $storeId)
                ->whereIn('payment_method', ['bank_transfer', 'card'])
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $dayPurchases = PurchaseOrder::where('store_id', $storeId)
                ->whereDate('order_date', $date)
                ->whereIn('status', ['received', 'partial'])
                ->sum('total_amount');

            // Other Income for the day
            $dayIncome = Income::where('store_id', $storeId)
                ->whereDate('income_date', $date)
                ->sum('amount');

            // Calculate day totals
            $dayTotalIn = $dayCashSales + $dayCardSales + $dayOtherSales + $dayIncome;
            $dayTotalOut = $dayCashExpenses + $dayBankExpenses + $dayPurchases;
            $dayNet = $dayTotalIn - $dayTotalOut;
            $runningBalance += $dayNet;

            // Only include days with transactions
            if ($dayTotalIn > 0 || $dayTotalOut > 0) {
                $dailyTransactions[] = [
                    'date' => $date,
                    'cash_sales' => $dayCashSales,
                    'card_sales' => $dayCardSales,
                    'other_sales' => $dayOtherSales,
                    'other_income' => $dayIncome,
                    'total_in' => $dayTotalIn,
                    'cash_expenses' => $dayCashExpenses,
                    'bank_expenses' => $dayBankExpenses,
                    'purchases' => $dayPurchases,
                    'total_out' => $dayTotalOut,
                    'net' => $dayNet,
                    'balance' => $runningBalance,
                ];
            }

            // Accumulate totals
            $totalCashIn += $dayCashSales;
            $totalCardIn += $dayCardSales;
            $totalOtherIn += $dayOtherSales;
            $totalIncomeIn += $dayIncome;
            $totalCashOut += $dayCashExpenses;
            $totalBankOut += $dayBankExpenses;
            $totalPurchaseOut += $dayPurchases;

            $currentDate->addDay();
        }

        $closingBalance = $runningBalance;
        $totalInflows = $totalCashIn + $totalCardIn + $totalOtherIn + $totalIncomeIn;
        $totalOutflows = $totalCashOut + $totalBankOut + $totalPurchaseOut;
        $netChange = $totalInflows - $totalOutflows;

        // Summary by category (for visual breakdown)
        $inflowBreakdown = [
            ['name' => 'Cash Sales', 'amount' => $totalCashIn, 'icon' => 'bi-cash', 'color' => 'success'],
            ['name' => 'Card Payments', 'amount' => $totalCardIn, 'icon' => 'bi-credit-card', 'color' => 'primary'],
            ['name' => 'Other Sales', 'amount' => $totalOtherIn, 'icon' => 'bi-wallet2', 'color' => 'info'],
            ['name' => 'Other Income', 'amount' => $totalIncomeIn, 'icon' => 'bi-cash-stack', 'color' => 'teal'],
        ];

        $outflowBreakdown = [
            ['name' => 'Cash Expenses', 'amount' => $totalCashOut, 'icon' => 'bi-cash-stack', 'color' => 'danger'],
            ['name' => 'Bank/Card Expenses', 'amount' => $totalBankOut, 'icon' => 'bi-bank', 'color' => 'warning'],
            ['name' => 'Supplier Payments', 'amount' => $totalPurchaseOut, 'icon' => 'bi-box-seam', 'color' => 'purple'],
        ];

        // Get recent register sessions for reconciliation
        $recentSessions = Register::where('store_id', $storeId)
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'closed')
            ->with('user')
            ->orderBy('closed_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate cash discrepancies
        $totalExpected = $recentSessions->sum('expected_balance');
        $totalActual = $recentSessions->sum('closing_balance');
        $totalDiscrepancy = $totalActual - $totalExpected;

        return view('reports.balance-sheet', compact(
            'openingBalance', 'closingBalance', 'netChange',
            'totalInflows', 'totalOutflows',
            'totalCashIn', 'totalCardIn', 'totalOtherIn', 'totalIncomeIn',
            'totalCashOut', 'totalBankOut', 'totalPurchaseOut',
            'dailyTransactions', 'inflowBreakdown', 'outflowBreakdown',
            'recentSessions', 'totalExpected', 'totalActual', 'totalDiscrepancy',
            'startDate', 'endDate'
        ));
    }

    /**
     * Cash Book Report - Line-by-line transaction ledger (printable format)
     */
    public function cashBook(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $store     = Store::find($storeId);
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('to', now()->toDateString());

        // Get B/F (Brought Forward) balance
        $bfBalance = 0;

        // Calculate B/F from all transactions before start date
        $priorSales = DB::table('orders')
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('created_at', '<', $startDate)
            ->sum('total_amount');

        $priorExpenses = DB::table('expenses')
            ->where('store_id', $storeId)
            ->whereDate('expense_date', '<', $startDate)
            ->sum('amount');

        $priorIncomes = DB::table('incomes')
            ->where('store_id', $storeId)
            ->whereDate('income_date', '<', $startDate)
            ->sum('amount');

        $bfBalance = $priorSales + $priorIncomes - $priorExpenses;

        // Get all transactions in date range
        $transactions = collect();

        // 1. Get Orders (Credit - money in)
        $orders = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['customer', 'payments'])
            ->get();

        foreach ($orders as $order) {
            $paymentMethod = $order->payments->pluck('method')->map(fn($m) => ucfirst(str_replace('_', ' ', $m)))->implode(', ');
            $transactions->push([
                'date' => $order->created_at,
                'invoice_no' => $order->order_number,
                'voucher_no' => '',
                'description' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                'debit' => 0,
                'credit' => $order->total_amount,
                'remarks' => $paymentMethod ?: 'Cash',
                'type' => 'sale',
            ]);
        }

        // 2. Get Expenses (Debit - money out)
        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        foreach ($expenses as $expense) {
            $transactions->push([
                'date' => Carbon::parse($expense->expense_date)->setTimeFromTimeString($expense->created_at->format('H:i:s')),
                'invoice_no' => '',
                'voucher_no' => 'EXP-' . $expense->id,
                'description' => $expense->description ?: ($expense->category->name ?? 'Expense'),
                'debit' => $expense->amount,
                'credit' => 0,
                'remarks' => ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'Cash')),
                'type' => 'expense',
            ]);
        }

        // 3. Get Incomes (Credit - money in)
        $incomes = Income::where('store_id', $storeId)
            ->whereBetween('income_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        foreach ($incomes as $income) {
            $transactions->push([
                'date' => Carbon::parse($income->income_date)->setTimeFromTimeString($income->created_at->format('H:i:s')),
                'invoice_no' => '',
                'voucher_no' => 'INC-' . $income->id,
                'description' => $income->description ?: ($income->category->name ?? 'Income'),
                'debit' => 0,
                'credit' => $income->amount,
                'remarks' => ucfirst(str_replace('_', ' ', $income->payment_method ?? 'Cash')),
                'type' => 'income',
            ]);
        }

        // Sort by date
        $transactions = $transactions->sortBy('date')->values();

        // Calculate running balance
        $runningBalance = $bfBalance;
        $transactions = $transactions->map(function ($t) use (&$runningBalance) {
            $runningBalance += $t['credit'] - $t['debit'];
            $t['balance'] = $runningBalance;
            return $t;
        });

        // Calculate totals
        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $closingBalance = $runningBalance;

        return view('reports.cash-book', compact(
            'store', 'transactions', 'bfBalance', 'closingBalance',
            'totalDebit', 'totalCredit', 'startDate', 'endDate'
        ));
    }

    /**
     * Cashier Sessions Report - Monitor register sessions
     */
    public function sessions(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $startDate = $request->get('from', now()->subDays(30)->toDateString());
        $endDate   = $request->get('to', now()->toDateString());
        $userId    = $request->get('user_id', '');
        $status    = $request->get('status', '');

        $query = Register::where('store_id', $storeId)
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $sessions = $query->orderBy('opened_at', 'desc')->paginate(20)->withQueryString();

        // Summary stats
        $allSessions = Register::where('store_id', $storeId)
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $summary = [
            'total_sessions' => $allSessions->count(),
            'open_sessions' => Register::where('store_id', $storeId)->where('status', 'open')->count(),
            'total_sales' => $allSessions->clone()->where('status', 'closed')->sum('total_sales'),
            'total_cash' => $allSessions->clone()->where('status', 'closed')->sum('cash_in'),
            'avg_session_sales' => $allSessions->clone()->where('status', 'closed')->avg('total_sales') ?? 0,
        ];

        // Calculate cash differences (discrepancies)
        $closedSessions = Register::where('store_id', $storeId)
            ->where('status', 'closed')
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $totalDifference = $closedSessions->sum(function($s) {
            return ($s->closing_balance ?? 0) - ($s->expected_balance ?? 0);
        });

        $summary['total_difference'] = $totalDifference;
        $summary['sessions_with_shortage'] = $closedSessions->filter(function($s) {
            return (($s->closing_balance ?? 0) - ($s->expected_balance ?? 0)) < -1; // Allow Rs.1 tolerance
        })->count();

        // Users for filter
        $users = \App\Models\User::where('store_id', $storeId)->get(['id', 'name']);

        return view('reports.sessions', compact('sessions', 'summary', 'users', 'startDate', 'endDate', 'userId', 'status'));
    }

    /**
     * Export reports to Excel/CSV
     */
    public function export(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $report = $request->get('report', 'sales');
        $format = $request->get('format', 'excel');
        $startDate = $request->get('from', now()->startOfMonth()->toDateString());
        $endDate = $request->get('to', now()->toDateString());
        $date = $request->get('date', now()->toDateString());

        $filename = "pure_pos_{$report}_" . now()->format('Y-m-d_His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($report, $storeId, $startDate, $endDate, $date) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            switch ($report) {
                case 'sales':
                    $this->exportSales($file, $storeId, $startDate, $endDate);
                    break;
                case 'daily':
                    $this->exportDaily($file, $storeId, $date);
                    break;
                case 'inventory':
                    $this->exportInventory($file, $storeId);
                    break;
                case 'expenses':
                    $this->exportExpenses($file, $storeId, $startDate, $endDate);
                    break;
                case 'customers':
                    $this->exportCustomers($file, $storeId, $startDate, $endDate);
                    break;
                case 'pos':
                    $this->exportPOS($file, $storeId, $startDate, $endDate);
                    break;
                case 'profit':
                    $this->exportProfit($file, $storeId, $startDate, $endDate);
                    break;
                case 'cashflow':
                    $this->exportCashflow($file, $storeId, $startDate, $endDate);
                    break;
                case 'stock-movements':
                    $this->exportStockMovements($file, $storeId, $startDate, $endDate);
                    break;
                case 'sessions':
                    $this->exportSessions($file, $storeId, $startDate, $endDate);
                    break;
                case 'balance-sheet':
                    $this->exportBalanceSheet($file, $storeId, $startDate, $endDate);
                    break;
                default:
                    $this->exportSummary($file, $storeId);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSales($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['SALES REPORT', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        // Get orders with items
        $orders = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['items', 'customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        fputcsv($file, ['Order ID', 'Date', 'Customer', 'Cashier', 'Items', 'Subtotal', 'Discount', 'Tax', 'Total']);

        foreach ($orders as $order) {
            fputcsv($file, [
                $order->order_number ?? $order->id,
                $order->created_at->format('Y-m-d H:i'),
                $order->customer?->name ?? 'Walk-in',
                $order->user?->name ?? '-',
                $order->items->count(),
                number_format($order->subtotal, 2),
                number_format($order->discount_amount, 2),
                number_format($order->tax_amount, 2),
                number_format($order->total_amount, 2),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL SALES', '', '', '', '', '', '', '', number_format($orders->sum('total_amount'), 2)]);
    }

    private function exportDaily($file, $storeId, $date)
    {
        fputcsv($file, ['DAILY SALES REPORT', '', '', '']);
        fputcsv($file, ['Date:', $date]);
        fputcsv($file, []);

        $orders = Order::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->with(['items', 'customer', 'user', 'payments'])
            ->orderBy('created_at', 'asc')
            ->get();

        fputcsv($file, ['Time', 'Order #', 'Customer', 'Payment', 'Status', 'Total']);

        foreach ($orders as $order) {
            $paymentMethod = $order->payments->first()?->method ?? 'N/A';
            fputcsv($file, [
                $order->created_at->format('H:i:s'),
                $order->order_number ?? $order->id,
                $order->customer?->name ?? 'Walk-in',
                ucfirst($paymentMethod),
                ucfirst($order->status),
                number_format($order->total_amount, 2),
            ]);
        }

        fputcsv($file, []);
        $completed = $orders->where('status', 'completed');
        fputcsv($file, ['SUMMARY', '', '', '', '', '']);
        fputcsv($file, ['Total Orders:', $orders->count()]);
        fputcsv($file, ['Completed:', $completed->count()]);
        fputcsv($file, ['Total Sales:', '', '', '', '', number_format($completed->sum('total_amount'), 2)]);
    }

    private function exportInventory($file, $storeId)
    {
        fputcsv($file, ['INVENTORY REPORT', '', '', '', '', '', '', '', '', '', '']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, []);

        $products = Product::where('store_id', $storeId)
            ->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();

        fputcsv($file, ['SKU', 'Barcode', 'Product Name', 'Category', 'Supplier', 'Unit', 'Stock', 'Min Stock', 'Cost Price', 'Sell Price', 'Stock Value', 'Status']);

        foreach ($products as $product) {
            $status = 'In Stock';
            if (!$product->track_stock) $status = 'Untracked';
            elseif ($product->isOutOfStock()) $status = 'Out of Stock';
            elseif ($product->isLowStock()) $status = 'Low Stock';

            fputcsv($file, [
                $product->sku ?? '-',
                $product->barcode ?? '-',
                $product->name,
                $product->category?->name ?? '-',
                $product->supplier?->name ?? '-',
                strtoupper($product->unit ?? 'PCS'),
                number_format($product->stock_quantity, 2),
                number_format($product->min_stock ?? 0, 2),
                number_format($product->cost_price, 2),
                number_format($product->selling_price, 2),
                number_format($product->selling_price * $product->stock_quantity, 2),
                $status,
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL PRODUCTS:', $products->count()]);
        fputcsv($file, ['TOTAL STOCK VALUE:', '', '', '', '', '', '', '', '', '', number_format($products->sum(fn($p) => $p->selling_price * $p->stock_quantity), 2)]);
    }

    private function exportExpenses($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['EXPENSE REPORT', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->orderBy('expense_date', 'desc')
            ->get();

        fputcsv($file, ['Date', 'Title', 'Category', 'Payment Method', 'Amount', 'Notes']);

        foreach ($expenses as $expense) {
            fputcsv($file, [
                $expense->expense_date?->format('Y-m-d'),
                $expense->title,
                $expense->category?->name ?? '-',
                ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'cash')),
                number_format($expense->amount, 2),
                $expense->notes ?? '',
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL EXPENSES:', '', '', '', number_format($expenses->sum('amount'), 2)]);
    }

    private function exportCustomers($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['CUSTOMER REPORT', '', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $customers = Customer::where('store_id', $storeId)
            ->withCount(['orders' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])])
            ->withSum(['orders' => fn($q) => $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->get();

        fputcsv($file, ['Customer Name', 'Phone', 'Email', 'Orders', 'Total Spent', 'Credit Balance', 'Loyalty Tier']);

        foreach ($customers as $customer) {
            fputcsv($file, [
                $customer->name,
                $customer->phone ?? '-',
                $customer->email ?? '-',
                $customer->orders_count,
                number_format($customer->orders_sum_total_amount ?? 0, 2),
                number_format($customer->current_balance ?? 0, 2),
                ucfirst($customer->loyalty_tier ?? '-'),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL CUSTOMERS:', $customers->count()]);
    }

    private function exportPOS($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['POS REPORT', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $orders = Order::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['payments', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        fputcsv($file, ['Date', 'Order #', 'Cashier', 'Payment Method', 'Status', 'Amount']);

        foreach ($orders as $order) {
            $paymentMethod = $order->payments->first()?->method ?? 'N/A';
            fputcsv($file, [
                $order->created_at->format('Y-m-d H:i'),
                $order->order_number ?? $order->id,
                $order->user?->name ?? '-',
                ucfirst($paymentMethod),
                ucfirst($order->status),
                number_format($order->total_amount, 2),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL COMPLETED:', '', '', '', '', number_format($orders->where('status', 'completed')->sum('total_amount'), 2)]);
    }

    private function exportProfit($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['PROFIT & LOSS REPORT', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $salesData = Order::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->get();

        $totalRevenue = $salesData->sum('total_amount');

        $cogs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        fputcsv($file, ['REVENUE', '', number_format($totalRevenue, 2)]);
        fputcsv($file, ['Cost of Goods Sold', '', number_format($cogs, 2)]);
        fputcsv($file, ['GROSS PROFIT', '', number_format($grossProfit, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['Operating Expenses', '', number_format($expenses, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['NET PROFIT', '', number_format($netProfit, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['Gross Margin %', '', number_format($totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0, 2) . '%']);
        fputcsv($file, ['Net Margin %', '', number_format($totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0, 2) . '%']);
    }

    private function exportCashflow($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['CASH FLOW REPORT', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $cashSales = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->where('payments.method', 'cash')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('payments.amount');

        $cardSales = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->where('payments.method', 'card')
            ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('payments.amount');

        $expensesCash = Expense::where('store_id', $storeId)
            ->where('payment_method', 'cash')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $expensesBank = Expense::where('store_id', $storeId)
            ->whereIn('payment_method', ['bank_transfer', 'card'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $totalInflows = $cashSales + $cardSales;
        $totalOutflows = $expensesCash + $expensesBank;

        fputcsv($file, ['CASH INFLOWS', '', '']);
        fputcsv($file, ['Cash Sales', '', number_format($cashSales, 2)]);
        fputcsv($file, ['Card Sales', '', number_format($cardSales, 2)]);
        fputcsv($file, ['Total Inflows', '', number_format($totalInflows, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['CASH OUTFLOWS', '', '']);
        fputcsv($file, ['Cash Expenses', '', number_format($expensesCash, 2)]);
        fputcsv($file, ['Bank/Card Expenses', '', number_format($expensesBank, 2)]);
        fputcsv($file, ['Total Outflows', '', number_format($totalOutflows, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['NET CASH FLOW', '', number_format($totalInflows - $totalOutflows, 2)]);
    }

    private function exportStockMovements($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['STOCK MOVEMENTS REPORT', '', '', '', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $movements = InventoryMovement::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        fputcsv($file, ['Date', 'Time', 'Product', 'SKU', 'Type', 'Qty Before', 'Change', 'Qty After', 'Unit Cost', 'Total', 'User', 'Notes']);

        foreach ($movements as $m) {
            fputcsv($file, [
                $m->created_at->format('Y-m-d'),
                $m->created_at->format('H:i:s'),
                $m->product?->name ?? 'Deleted Product',
                $m->product?->sku ?? '-',
                ucfirst($m->type),
                number_format($m->quantity_before, 2),
                ($m->quantity >= 0 ? '+' : '') . number_format($m->quantity, 2),
                number_format($m->quantity_after, 2),
                number_format($m->unit_cost ?? 0, 2),
                number_format($m->total_cost ?? 0, 2),
                $m->user?->name ?? '-',
                $m->notes ?? '',
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL MOVEMENTS:', $movements->count()]);
        fputcsv($file, ['TOTAL VALUE:', '', '', '', '', '', '', '', '', number_format($movements->sum('total_cost'), 2)]);
    }

    private function exportSummary($file, $storeId)
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        fputcsv($file, ['REPORTS SUMMARY', '', '']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, []);

        $todaySales = Order::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthSales = Order::where('store_id', $storeId)
            ->whereDate('created_at', '>=', $monthStart)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthExpenses = Expense::where('store_id', $storeId)
            ->whereDate('expense_date', '>=', $monthStart)
            ->sum('amount');

        $lowStockCount = Product::where('store_id', $storeId)
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();

        fputcsv($file, ["Today's Sales", number_format($todaySales, 2)]);
        fputcsv($file, ["This Month Sales", number_format($monthSales, 2)]);
        fputcsv($file, ["This Month Expenses", number_format($monthExpenses, 2)]);
        fputcsv($file, ["Low Stock Items", $lowStockCount]);
    }

    private function exportSessions($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['CASHIER SESSIONS REPORT', '', '', '', '', '', '', '', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        $sessions = Register::where('store_id', $storeId)
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('user')
            ->orderBy('opened_at', 'desc')
            ->get();

        fputcsv($file, ['Session ID', 'Cashier', 'Email', 'Opened At', 'Closed At', 'Duration', 'Opening Balance', 'Total Sales', 'Cash In', 'Expected Cash', 'Actual Cash', 'Difference', 'Status']);

        foreach ($sessions as $s) {
            $duration = '-';
            if ($s->status === 'closed' && $s->opened_at && $s->closed_at) {
                $duration = $s->opened_at->diffForHumans($s->closed_at, true);
            } elseif ($s->status === 'open' && $s->opened_at) {
                $duration = $s->opened_at->diffForHumans(now(), true) . ' (ongoing)';
            }

            $difference = ($s->closing_balance ?? 0) - ($s->expected_balance ?? 0);

            fputcsv($file, [
                $s->id,
                $s->user?->name ?? 'Unknown',
                $s->user?->email ?? '-',
                $s->opened_at?->format('Y-m-d H:i:s'),
                $s->closed_at?->format('Y-m-d H:i:s') ?? '-',
                $duration,
                number_format($s->opening_balance, 2),
                number_format($s->total_sales ?? 0, 2),
                number_format($s->cash_in ?? 0, 2),
                number_format($s->expected_balance ?? 0, 2),
                $s->status === 'closed' ? number_format($s->closing_balance ?? 0, 2) : '-',
                $s->status === 'closed' ? number_format($difference, 2) : '-',
                ucfirst($s->status),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTAL SESSIONS:', $sessions->count()]);
        fputcsv($file, ['TOTAL SALES:', '', '', '', '', '', '', number_format($sessions->where('status', 'closed')->sum('total_sales'), 2)]);
        fputcsv($file, ['TOTAL CASH COLLECTED:', '', '', '', '', '', '', '', number_format($sessions->where('status', 'closed')->sum('cash_in'), 2)]);
    }

    private function exportBalanceSheet($file, $storeId, $startDate, $endDate)
    {
        fputcsv($file, ['CASH BALANCE SHEET', '', '', '', '', '', '', '', '', '']);
        fputcsv($file, ['Period:', $startDate, 'to', $endDate]);
        fputcsv($file, []);

        // Get opening balance
        $openingRegister = Register::where('store_id', $storeId)
            ->whereDate('opened_at', '<', $startDate)
            ->where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->first();
        $openingBalance = $openingRegister ? ($openingRegister->closing_balance ?? 0) : 0;

        if ($openingBalance == 0) {
            $firstRegister = Register::where('store_id', $storeId)
                ->whereDate('opened_at', '>=', $startDate)
                ->orderBy('opened_at', 'asc')
                ->first();
            $openingBalance = $firstRegister ? ($firstRegister->opening_balance ?? 0) : 0;
        }

        fputcsv($file, ['OPENING BALANCE:', '', '', '', '', '', '', '', '', number_format($openingBalance, 2)]);
        fputcsv($file, []);

        // Header row
        fputcsv($file, ['Date', 'Cash Sales', 'Card Sales', 'Other Sales', 'Total In', 'Cash Exp', 'Bank Exp', 'Purchases', 'Total Out', 'Net', 'Balance']);

        // Daily transactions
        $runningBalance = $openingBalance;
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $totalIn = 0;
        $totalOut = 0;

        while ($currentDate <= $end) {
            $date = $currentDate->toDateString();

            $cashSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->where('payments.method', 'cash')
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $cardSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->where('payments.method', 'card')
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $otherSales = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.store_id', $storeId)
                ->where('orders.status', 'completed')
                ->whereNotIn('payments.method', ['cash', 'card'])
                ->whereDate('orders.created_at', $date)
                ->sum('payments.amount');

            $cashExp = Expense::where('store_id', $storeId)
                ->where('payment_method', 'cash')
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $bankExp = Expense::where('store_id', $storeId)
                ->whereIn('payment_method', ['bank_transfer', 'card'])
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $purchases = PurchaseOrder::where('store_id', $storeId)
                ->whereDate('order_date', $date)
                ->whereIn('status', ['received', 'partial'])
                ->sum('total_amount');

            $dayIn = $cashSales + $cardSales + $otherSales;
            $dayOut = $cashExp + $bankExp + $purchases;
            $dayNet = $dayIn - $dayOut;
            $runningBalance += $dayNet;

            if ($dayIn > 0 || $dayOut > 0) {
                fputcsv($file, [
                    $date,
                    number_format($cashSales, 2),
                    number_format($cardSales, 2),
                    number_format($otherSales, 2),
                    number_format($dayIn, 2),
                    number_format($cashExp, 2),
                    number_format($bankExp, 2),
                    number_format($purchases, 2),
                    number_format($dayOut, 2),
                    ($dayNet >= 0 ? '+' : '') . number_format($dayNet, 2),
                    number_format($runningBalance, 2),
                ]);

                $totalIn += $dayIn;
                $totalOut += $dayOut;
            }

            $currentDate->addDay();
        }

        fputcsv($file, []);
        fputcsv($file, ['TOTALS:', '', '', '', number_format($totalIn, 2), '', '', '', number_format($totalOut, 2), ($totalIn - $totalOut >= 0 ? '+' : '') . number_format($totalIn - $totalOut, 2), number_format($runningBalance, 2)]);
        fputcsv($file, []);
        fputcsv($file, ['CLOSING BALANCE:', '', '', '', '', '', '', '', '', number_format($runningBalance, 2)]);
        fputcsv($file, ['NET CHANGE:', '', '', '', '', '', '', '', '', ($totalIn - $totalOut >= 0 ? '+' : '') . number_format($totalIn - $totalOut, 2)]);
    }
}
