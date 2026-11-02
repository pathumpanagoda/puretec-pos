<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\POSService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    protected POSService $posService;
    protected AuditLogService $auditLog;

    public function __construct(POSService $posService, AuditLogService $auditLog)
    {
        $this->posService = $posService;
        $this->auditLog = $auditLog;
    }

    /**
     * List sales/orders.
     *
     * GET /api/v1/sales?status=completed&date=2026-09-26&page=1
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::where('store_id', $user->store_id)
            ->with(['customer:id,name,phone', 'user:id,name', 'payments:id,order_id,method,amount,status']);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->since) {
            $query->where('updated_at', '>', $request->since);
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Get a single sale/order.
     *
     * GET /api/v1/sales/{id}
     */
    public function show(Request $request, $id)
    {
        $order = Order::where('store_id', $request->user()->store_id)
            ->with(['items', 'payments', 'customer', 'user'])
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * Create a sale via API.
     *
     * POST /api/v1/sales
     */
    public function store(Request $request)
    {
        $request->validate([
            'uuid'                  => 'nullable|uuid',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'payments'              => 'required|array|min:1',
            'payments.*.method'     => 'required|string',
            'payments.*.amount'     => 'required|numeric|min:0',
            'customer_id'           => 'nullable|integer',
        ]);

        $user = $request->user();

        // Idempotency check
        if ($request->uuid) {
            $existing = Order::where('uuid', $request->uuid)->first();
            if ($existing) {
                return response()->json([
                    'success'   => true,
                    'data'      => $existing->load(['items', 'payments', 'customer']),
                    'duplicate' => true,
                ]);
            }
        }

        try {
            $data = $request->all();
            $data['paid_amount'] = collect($data['payments'])->sum('amount');

            $order = $this->posService->processSale($data);

            if ($request->uuid) {
                $order->update([
                    'uuid'        => $request->uuid,
                    'source'      => $request->source ?? 'api',
                    'device_id'   => $request->header('X-Device-ID'),
                    'synced_at'   => now(),
                    'sync_status' => 'synced',
                ]);
            }

            $this->auditLog->logCreated('Order', $order, 'Sale created via API');

            return response()->json([
                'success' => true,
                'data'    => $order->load(['items', 'payments', 'customer']),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage(),
            ], 422);
        }
    }
}
