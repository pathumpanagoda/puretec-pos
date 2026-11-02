<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SyncLog;
use App\Services\POSService;
use App\Services\InventoryService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    protected POSService $posService;
    protected InventoryService $inventoryService;
    protected AuditLogService $auditLog;

    public function __construct(POSService $posService, InventoryService $inventoryService, AuditLogService $auditLog)
    {
        $this->posService = $posService;
        $this->inventoryService = $inventoryService;
        $this->auditLog = $auditLog;
    }

    /**
     * Push local transactions to the server.
     *
     * POST /api/v1/sync/push
     *
     * Uses DB-level unique constraints for idempotency.
     * Stock is tracked via movements (not simple overwrite).
     */
    public function push(Request $request)
    {
        $request->validate([
            'device_id'                    => 'required|string|max:64',
            'transactions'                 => 'required|array|min:1|max:50',
            'transactions.*.uuid'          => 'required|uuid',
            'transactions.*.entity_type'   => 'required|in:order,customer,inventory_movement',
            'transactions.*.operation'     => 'required|in:create,update',
            'transactions.*.payload'       => 'required|array',
        ]);

        $user = $request->user();
        $deviceId = $request->device_id;
        $results = [];
        $startTime = microtime(true);

        foreach ($request->transactions as $transaction) {
            $uuid = $transaction['uuid'];
            $entityType = $transaction['entity_type'];
            $operation = $transaction['operation'];
            $payload = $transaction['payload'];

            try {
                $result = match ($entityType) {
                    'order'              => $this->processOrderSync($uuid, $payload, $user, $deviceId),
                    'customer'           => $this->processCustomerSync($uuid, $operation, $payload, $user),
                    'inventory_movement' => $this->processInventorySync($uuid, $payload, $user),
                    default              => ['status' => 'failed', 'message' => 'Unknown entity type'],
                };

                // Log sync
                SyncLog::create([
                    'tenant_id'       => $user->tenant_id,
                    'store_id'        => $user->store_id,
                    'user_id'         => $user->id,
                    'device_id'       => $deviceId,
                    'direction'       => 'push',
                    'entity_type'     => $entityType,
                    'entity_uuid'     => $uuid,
                    'entity_id'       => $result['entity_id'] ?? null,
                    'status'          => $result['status'],
                    'error_message'   => $result['message'] ?? null,
                    'payload_summary' => ['uuid' => $uuid, 'type' => $entityType],
                    'batch_size'      => 1,
                    'duration_ms'     => intval((microtime(true) - $startTime) * 1000),
                ]);

                $results[] = array_merge(['uuid' => $uuid], $result);

            } catch (\Throwable $e) {
                Log::error("Sync push failed for UUID {$uuid}: " . $e->getMessage(), [
                    'uuid' => $uuid, 'entity_type' => $entityType, 'trace' => $e->getTraceAsString()
                ]);

                SyncLog::create([
                    'tenant_id'     => $user->tenant_id,
                    'store_id'      => $user->store_id,
                    'user_id'       => $user->id,
                    'device_id'     => $deviceId,
                    'direction'     => 'push',
                    'entity_type'   => $entityType,
                    'entity_uuid'   => $uuid,
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $results[] = [
                    'uuid'    => $uuid,
                    'status'  => 'failed',
                    'message' => 'Server error processing transaction.',
                ];
            }
        }

        $durationMs = intval((microtime(true) - $startTime) * 1000);

        return response()->json([
            'success'     => true,
            'data'        => [
                'results'     => $results,
                'processed'   => count($results),
                'duration_ms' => $durationMs,
            ],
        ]);
    }

    /**
     * Pull cloud changes for a device (incremental sync).
     *
     * GET /api/v1/sync/pull?since=...&types=products,customers,categories&cursor=123
     */
    public function pull(Request $request)
    {
        $request->validate([
            'since'  => 'nullable|date',
            'types'  => 'nullable|string',
            'cursor' => 'nullable|integer',
        ]);

        $user = $request->user();
        $storeId = $user->store_id;
        $since = $request->since ? \Carbon\Carbon::parse($request->since) : now()->subDays(7);
        $types = $request->types ? explode(',', $request->types) : ['products', 'customers', 'categories'];

        $data = [];
        $maxSyncVersion = 0;
        $limit = 500;

        // Products
        if (in_array('products', $types)) {
            $query = Product::where('store_id', $storeId)
                ->where('is_active', true)
                ->where('updated_at', '>', $since);

            if ($request->cursor) {
                $query->where('sync_version', '>', $request->cursor);
            }

            $data['products'] = $query->select([
                'id', 'uuid', 'category_id', 'name', 'sku', 'barcode',
                'selling_price', 'cost_price', 'stock_quantity', 'min_stock',
                'unit', 'track_stock', 'is_taxable', 'image', 'is_active',
                'sync_version', 'updated_at'
            ])->limit($limit)->get();
        }

        // Customers
        if (in_array('customers', $types)) {
            $query = Customer::where('store_id', $storeId)
                ->where('is_active', true)
                ->where('updated_at', '>', $since);

            $data['customers'] = $query->select([
                'id', 'uuid', 'name', 'email', 'phone', 'address', 'city',
                'loyalty_points', 'loyalty_tier', 'is_active', 'updated_at'
            ])->limit($limit)->get();
        }

        // Categories
        if (in_array('categories', $types)) {
            $data['categories'] = \App\Models\Category::where('store_id', $storeId)
                ->where('is_active', true)
                ->where('updated_at', '>', $since)
                ->select(['id', 'uuid', 'parent_id', 'name', 'code', 'color', 'icon', 'is_active', 'updated_at'])
                ->get();
        }

        // Log the pull
        SyncLog::create([
            'tenant_id'       => $user->tenant_id,
            'store_id'        => $storeId,
            'user_id'         => $user->id,
            'device_id'       => $request->header('X-Device-ID'),
            'direction'       => 'pull',
            'entity_type'     => implode(',', $types),
            'status'          => 'success',
            'payload_summary' => ['since' => $since->toIso8601String(), 'counts' => array_map('count', $data)],
        ]);

        return response()->json([
            'success'     => true,
            'data'        => $data,
            'synced_at'   => now()->toIso8601String(),
            'sync_cursor' => $maxSyncVersion,
        ]);
    }

    /**
     * Get sync status for a device.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $deviceId = $request->header('X-Device-ID');

        $recentLogs = SyncLog::where('user_id', $user->id)
            ->when($deviceId, fn($q) => $q->where('device_id', $deviceId))
            ->latest()
            ->limit(20)
            ->get(['direction', 'entity_type', 'status', 'error_message', 'created_at']);

        $failedCount = SyncLog::where('user_id', $user->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $pendingOrders = Order::where('store_id', $user->store_id)
            ->where('sync_status', 'local')
            ->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'recent_syncs'    => $recentLogs,
                'failed_today'    => $failedCount,
                'pending_orders'  => $pendingOrders,
                'server_time'     => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Process a single order sync (idempotent via UUID + DB constraint).
     *
     * Stock is deducted via individual stock_movement UUIDs — never double-deducted.
     */
    protected function processOrderSync(string $uuid, array $payload, $user, string $deviceId): array
    {
        // Idempotency check (DB unique constraint is the final safety net)
        $existing = Order::where('uuid', $uuid)->first();
        if ($existing) {
            return [
                'status'    => 'duplicate',
                'message'   => 'Order already exists.',
                'entity_id' => $existing->id,
            ];
        }

        return DB::transaction(function () use ($uuid, $payload, $user, $deviceId) {
            $storeId = $user->store_id;
            $items = $payload['items'] ?? [];
            $payments = $payload['payments'] ?? [];

            // Calculate totals from items
            $subtotal = 0;
            $taxAmount = 0;
            $profit = 0;
            $discountAmount = 0;

            $itemsData = [];
            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $storeId)
                    ->first();

                if (!$product) continue;

                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $itemDisc = (float) ($item['discount_amount'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $itemSubtotal = ($qty * $unitPrice) - $itemDisc;
                $itemTax = ($taxRate > 0) ? ($itemSubtotal * $taxRate / 100) : (float)($item['tax_amount'] ?? 0);
                $costPrice = (float) ($item['cost_price'] ?? $product->cost_price);

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
                $profit += ($unitPrice - $costPrice) * $qty - $itemDisc;

                $itemsData[] = [
                    'product'          => $product,
                    'product_id'       => $product->id,
                    'product_name'     => $product->name,
                    'sku'              => $product->sku,
                    'barcode'          => $product->barcode,
                    'quantity'         => $qty,
                    'unit'             => $product->unit,
                    'unit_price'       => $unitPrice,
                    'cost_price'       => $costPrice,
                    'discount_amount'  => $itemDisc,
                    'tax_rate'         => $taxRate,
                    'tax_amount'       => $itemTax,
                    'subtotal'         => $itemSubtotal,
                    'total'            => $itemSubtotal + $itemTax,
                    'profit'           => ($unitPrice - $costPrice) * $qty - $itemDisc,
                    'stock_movement_uuid' => $item['stock_movement_uuid'] ?? Str::uuid()->toString(),
                ];
            }

            // Order-level discount
            $discountType = $payload['discount_type'] ?? null;
            $discountValue = (float) ($payload['discount_value'] ?? 0);
            if ($discountType === 'percentage' && $discountValue > 0) {
                $discountAmount = ($subtotal * $discountValue) / 100;
            } elseif ($discountValue > 0) {
                $discountAmount = $discountValue;
            }

            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            $paidAmount = 0;
            foreach ($payments as $p) {
                $paidAmount += (float) ($p['amount'] ?? 0);
            }

            $status = ($payload['status'] ?? 'completed');
            $isPending = $status === 'pending' || $status === 'pending_payment';

            // Create order
            $order = Order::create([
                'uuid'            => $uuid,
                'store_id'        => $storeId,
                'customer_id'     => $payload['customer_id'] ?? null,
                'user_id'         => $user->id,
                'status'          => $isPending ? 'pending_payment' : 'completed',
                'order_type'      => $payload['order_type'] ?? 'sale',
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => max(0, $paidAmount - $totalAmount),
                'due_amount'      => max(0, $totalAmount - $paidAmount),
                'profit'          => $profit - $discountAmount,
                'discount_type'   => $discountType,
                'discount_value'  => $discountValue,
                'notes'           => $payload['notes'] ?? null,
                'table_number'    => $payload['table_number'] ?? null,
                'device_id'       => $deviceId,
                'synced_at'       => now(),
                'sync_status'     => 'synced',
                'source'          => $payload['source'] ?? 'pwa',
                'completed_at'    => $isPending ? null : now(),
            ]);

            // Create order items & deduct stock via movements
            foreach ($itemsData as $itemData) {
                $product = $itemData['product'];
                unset($itemData['product']);

                OrderItem::create(array_merge(
                    array_diff_key($itemData, ['stock_movement_uuid' => 1]),
                    ['order_id' => $order->id, 'uuid' => Str::uuid()->toString()]
                ));

                // Stock movement with its own UUID (idempotent)
                if ($product->track_stock) {
                    $movementUuid = $itemData['stock_movement_uuid'];

                    // Check if this movement already exists (idempotent)
                    $existingMovement = InventoryMovement::where('uuid', $movementUuid)->first();
                    if (!$existingMovement) {
                        $before = $product->stock_quantity;
                        $product->decrement('stock_quantity', $itemData['quantity']);

                        $this->inventoryService->logMovement([
                            'uuid'            => $movementUuid,
                            'store_id'        => $storeId,
                            'product_id'      => $product->id,
                            'user_id'         => $user->id,
                            'type'            => 'sale',
                            'reference_type'  => 'order',
                            'reference_id'    => $order->id,
                            'reference_uuid'  => $uuid,
                            'quantity'        => -$itemData['quantity'],
                            'quantity_before'  => $before,
                            'quantity_after'   => $before - $itemData['quantity'],
                            'unit_cost'       => $itemData['cost_price'],
                            'total_cost'      => $itemData['cost_price'] * $itemData['quantity'],
                        ]);
                    }
                }
            }

            // Process payments
            foreach ($payments as $paymentData) {
                $paymentUuid = $paymentData['uuid'] ?? Str::uuid()->toString();
                $existingPayment = Payment::where('uuid', $paymentUuid)->first();
                if (!$existingPayment) {
                    Payment::create([
                        'uuid'             => $paymentUuid,
                        'order_id'         => $order->id,
                        'user_id'          => $user->id,
                        'method'           => $paymentData['method'] ?? 'cash',
                        'amount'           => $paymentData['amount'],
                        'currency'         => 'LKR',
                        'status'           => 'completed',
                    ]);
                }
            }

            // Update customer stats
            if ($order->customer_id && !$isPending) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->increment('total_purchases', $totalAmount);
                    $customer->increment('total_orders');
                }
            }

            return [
                'status'    => 'success',
                'message'   => 'Order synced successfully.',
                'entity_id' => $order->id,
            ];
        });
    }

    /**
     * Process a customer sync.
     */
    protected function processCustomerSync(string $uuid, string $operation, array $payload, $user): array
    {
        $existing = Customer::where('uuid', $uuid)->first();

        if ($operation === 'create') {
            if ($existing) {
                return ['status' => 'duplicate', 'message' => 'Customer already exists.', 'entity_id' => $existing->id];
            }
            $customer = Customer::create(array_merge($payload, [
                'uuid'     => $uuid,
                'store_id' => $user->store_id,
            ]));
            return ['status' => 'success', 'message' => 'Customer created.', 'entity_id' => $customer->id];
        }

        if ($operation === 'update' && $existing) {
            $existing->update($payload);
            return ['status' => 'success', 'message' => 'Customer updated.', 'entity_id' => $existing->id];
        }

        return ['status' => 'failed', 'message' => 'Customer not found for update.'];
    }

    /**
     * Process an inventory movement sync (idempotent via UUID).
     *
     * CRITICAL: Stock is updated via movements, never raw quantity overwrite.
     */
    protected function processInventorySync(string $uuid, array $payload, $user): array
    {
        $existing = InventoryMovement::where('uuid', $uuid)->first();
        if ($existing) {
            return ['status' => 'duplicate', 'message' => 'Movement already exists.', 'entity_id' => $existing->id];
        }

        return DB::transaction(function () use ($uuid, $payload, $user) {
            $product = Product::where('store_id', $user->store_id)
                ->where('id', $payload['product_id'])
                ->lockForUpdate()  // Pessimistic lock to prevent race conditions
                ->first();

            if (!$product) {
                return ['status' => 'failed', 'message' => 'Product not found.'];
            }

            $quantity = (float) $payload['quantity'];
            $type = $payload['type'] ?? 'adjustment';
            $before = $product->stock_quantity;
            $after = max(0, $before + $quantity);

            $movement = $this->inventoryService->logMovement([
                'uuid'            => $uuid,
                'store_id'        => $user->store_id,
                'product_id'      => $product->id,
                'user_id'         => $user->id,
                'type'            => $type,
                'reference_type'  => $payload['reference_type'] ?? null,
                'reference_id'    => $payload['reference_id'] ?? null,
                'reference_uuid'  => $payload['reference_uuid'] ?? null,
                'quantity'        => $quantity,
                'quantity_before'  => $before,
                'quantity_after'   => $after,
                'unit_cost'       => $payload['unit_cost'] ?? $product->cost_price,
                'total_cost'      => ($payload['unit_cost'] ?? $product->cost_price) * abs($quantity),
                'notes'           => $payload['notes'] ?? null,
            ]);

            // Update product stock
            if ($product->track_stock) {
                $product->update(['stock_quantity' => $after]);
            }

            return ['status' => 'success', 'message' => 'Movement synced.', 'entity_id' => $movement->id];
        });
    }
}
