<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\DeviceRegistration;
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
     * Enforces idempotency via database unique constraints and UUID checks.
     * Stock tracked strictly via inventory movements with conflict detection.
     */
    public function push(Request $request)
    {
        $request->validate([
            'device_id'                    => 'required|string|max:64',
            'transactions'                 => 'required|array|min:1|max:50',
            'transactions.*.uuid'          => 'required|uuid',
            'transactions.*.entity_type'   => 'required|in:order,customer,inventory_movement,register',
            'transactions.*.operation'     => 'required|in:create,update',
            'transactions.*.payload'       => 'required|array',
        ]);

        $user = $request->user();
        $deviceId = $request->device_id;

        // Device authorization check
        $device = DeviceRegistration::where('device_id', $deviceId)->first();
        if ($device && !$device->is_active) {
            return response()->json([
                'success'    => false,
                'message'    => 'This device has been deactivated or revoked.',
                'error_code' => 'DEVICE_DEACTIVATED',
            ], 403);
        }

        if ($device) {
            $device->update(['last_seen_at' => now(), 'last_sync_at' => now()]);
        }

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
                    'inventory_movement' => $this->processInventorySync($uuid, $payload, $user, $deviceId),
                    'register'           => $this->processRegisterSync($uuid, $operation, $payload, $user, $deviceId),
                    default              => ['status' => 'failed', 'message' => 'Unknown entity type'],
                };

                // Record sync log for every transaction
                SyncLog::create([
                    'tenant_id'       => $user->tenant_id,
                    'store_id'        => $user->store_id,
                    'user_id'         => $user->id,
                    'device_id'       => $deviceId,
                    'direction'       => 'push',
                    'entity_type'     => $entityType,
                    'entity_uuid'     => $uuid,
                    'entity_id'       => $result['entity_id'] ?? null,
                    'status'          => $result['status'], // success, duplicate, conflict, failed
                    'error_message'   => $result['message'] ?? null,
                    'payload_summary' => ['uuid' => $uuid, 'type' => $entityType, 'operation' => $operation],
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
                    'message' => 'Server error processing transaction: ' . $e->getMessage(),
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
     * Pull cloud changes for a device (incremental sync using cursors).
     *
     * GET /api/v1/sync/pull?since=...&types=products,customers,categories&cursor=1730663456000
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

        // Device authorization check if device header/param present
        $deviceId = $request->header('X-Device-ID') ?? $request->query('device_id');
        if ($deviceId) {
            $device = DeviceRegistration::where('device_id', $deviceId)->first();
            if ($device && !$device->is_active) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'This device has been deactivated or revoked.',
                    'error_code' => 'DEVICE_DEACTIVATED',
                ], 403);
            }
            if ($device) {
                $device->update(['last_seen_at' => now()]);
            }
        }

        $since = $request->since ? \Carbon\Carbon::parse($request->since) : now()->subDays(7);
        $types = $request->types ? explode(',', $request->types) : ['products', 'customers', 'categories'];
        $cursor = (int) ($request->cursor ?? 0);

        $data = [];
        $limit = 500;

        // 1. Products (includes allow_negative_stock for offline decision-making)
        if (in_array('products', $types)) {
            $query = Product::where('store_id', $storeId)->where('is_active', true);
            if ($cursor > 0) {
                $query->where('sync_version', '>', $cursor);
            } elseif ($request->filled('since')) {
                $query->where('updated_at', '>=', $since);
            }

            $data['products'] = $query->select([
                'id', 'uuid', 'category_id', 'name', 'sku', 'barcode',
                'selling_price', 'cost_price', 'stock_quantity', 'min_stock',
                'unit', 'track_stock', 'allow_negative_stock', 'is_taxable', 'image', 'is_active',
                'sync_version', 'updated_at'
            ])->orderBy('sync_version', 'asc')->limit($limit)->get();
        }

        // 2. Customers
        if (in_array('customers', $types)) {
            $query = Customer::where('store_id', $storeId)->where('is_active', true);
            if ($cursor > 0) {
                $query->where('sync_version', '>', $cursor);
            } elseif ($request->filled('since')) {
                $query->where('updated_at', '>=', $since);
            }

            $data['customers'] = $query->select([
                'id', 'uuid', 'name', 'email', 'phone', 'address', 'city',
                'loyalty_points', 'loyalty_tier', 'is_active', 'sync_version', 'updated_at'
            ])->orderBy('sync_version', 'asc')->limit($limit)->get();
        }

        // 3. Categories
        if (in_array('categories', $types)) {
            $query = Category::where('store_id', $storeId)->where('is_active', true);
            if ($cursor > 0) {
                $query->where('sync_version', '>', $cursor);
            } elseif ($request->filled('since')) {
                $query->where('updated_at', '>=', $since);
            }

            $data['categories'] = $query->select([
                'id', 'uuid', 'parent_id', 'name', 'code', 'color', 'icon', 'is_active', 'sync_version', 'updated_at'
            ])->orderBy('sync_version', 'asc')->limit($limit)->get();
        }

        // Calculate maximum sync version across returned items
        $pMax = isset($data['products']) && $data['products']->count() > 0 ? (int) $data['products']->max('sync_version') : 0;
        $cMax = isset($data['customers']) && $data['customers']->count() > 0 ? (int) $data['customers']->max('sync_version') : 0;
        $catMax = isset($data['categories']) && $data['categories']->count() > 0 ? (int) $data['categories']->max('sync_version') : 0;
        $maxSyncVersion = max($cursor, $pMax, $cMax, $catMax);

        // Update sync_versions tracking table for audit & cursor status
        if ($maxSyncVersion > 0) {
            foreach ($types as $t) {
                if (isset($data[$t]) && $data[$t]->count() > 0) {
                    DB::table('sync_versions')->updateOrInsert(
                        ['store_id' => $storeId, 'entity_type' => $t],
                        ['last_version' => (int) $data[$t]->max('sync_version'), 'last_sync_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // Log the pull event
        SyncLog::create([
            'tenant_id'       => $user->tenant_id,
            'store_id'        => $storeId,
            'user_id'         => $user->id,
            'device_id'       => $request->header('X-Device-ID'),
            'direction'       => 'pull',
            'entity_type'     => implode(',', $types),
            'status'          => 'success',
            'payload_summary' => ['since' => $since->toIso8601String(), 'cursor' => $cursor, 'counts' => array_map('count', $data)],
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

        $conflictCount = SyncLog::where('user_id', $user->id)
            ->where('status', 'conflict')
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
                'conflicts_today' => $conflictCount,
                'pending_orders'  => $pendingOrders,
                'server_time'     => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Process a single order sync (idempotent via UUID + DB constraint).
     *
     * Employs pessimistic locking on inventory, stock movement UUIDs, and multi-device conflict detection.
     */
    protected function processOrderSync(string $uuid, array $payload, $user, string $deviceId): array
    {
        // 1. Idempotency check: Order level
        $existing = Order::where('uuid', $uuid)->first();
        if ($existing) {
            return [
                'status'    => 'duplicate',
                'message'   => 'Order already exists on server.',
                'entity_id' => $existing->id,
            ];
        }

        return DB::transaction(function () use ($uuid, $payload, $user, $deviceId) {
            $storeId = $user->store_id;
            $items = $payload['items'] ?? [];
            $payments = $payload['payments'] ?? [];

            if (empty($items)) {
                return [
                    'status'  => 'failed',
                    'message' => 'Cannot sync an order without items.',
                ];
            }

            // Calculate totals from items
            $subtotal = 0;
            $taxAmount = 0;
            $profit = 0;
            $discountAmount = 0;
            $itemsData = [];

            foreach ($items as $item) {
                // Lock product row to prevent race conditions during concurrent syncs
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    return [
                        'status'  => 'failed',
                        'message' => "Product ID {$item['product_id']} not found in store.",
                    ];
                }

                $rawVariantId = $item['variant_id'] ?? $item['product_variant_id'] ?? null;
                $productVariantId = null;

                if ($rawVariantId !== null && $rawVariantId !== '' && is_numeric($rawVariantId)) {
                    $candidateVariantId = (int) $rawVariantId;

                    if ($candidateVariantId > 0) {
                        $variantExists = $product->variants()
                            ->where('id', $candidateVariantId)
                            ->exists();

                        if ($variantExists) {
                            $productVariantId = $candidateVariantId;
                        }
                    }
                }

                $qty = (float) $item['quantity'];
                $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? $product->selling_price);
                $itemDisc = (float) ($item['discount_amount'] ?? $item['discount'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $itemSubtotal = ($qty * $unitPrice) - $itemDisc;
                $itemTax = ($taxRate > 0) ? ($itemSubtotal * $taxRate / 100) : (float)($item['tax_amount'] ?? 0);
                $costPrice = (float) ($item['cost_price'] ?? $product->cost_price);

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
                $profit += ($unitPrice - $costPrice) * $qty - $itemDisc;

                $itemsData[] = [
                    'product'             => $product,
                    'product_id'          => $product->id,
                    'product_variant_id'  => $productVariantId,
                    'product_name'        => $product->name,
                    'sku'                 => $product->sku,
                    'barcode'             => $product->barcode,
                    'quantity'            => $qty,
                    'unit'                => $product->unit ?? 'pcs',
                    'unit_price'          => $unitPrice,
                    'cost_price'          => $costPrice,
                    'discount_amount'     => $itemDisc,
                    'tax_rate'            => $taxRate,
                    'tax_amount'          => $itemTax,
                    'subtotal'            => $itemSubtotal,
                    'total'               => $itemSubtotal + $itemTax,
                    'profit'              => ($unitPrice - $costPrice) * $qty - $itemDisc,
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

            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);
            $paidAmount = 0;
            foreach ($payments as $p) {
                $paidAmount += (float) ($p['amount'] ?? 0);
            }

            $status = ($payload['status'] ?? 'completed');
            $isPending = $status === 'pending' || $status === 'pending_payment';
            $orderType = $payload['order_type'] ?? 'sale';

            // Conflict detection tracking
            $hasStockConflict = false;
            $conflictNotes = [];

            // Resolve customer safely for offline order sync (prevent temporary timestamp IDs from triggering FK violations)
            $customer = $this->resolveCustomer($payload, $storeId);
            $syncCustomerId = $customer?->id;

            // 2. Create order record
            $order = Order::create([
                'uuid'            => $uuid,
                'store_id'        => $storeId,
                'customer_id'     => $syncCustomerId,
                'user_id'         => $user->id,
                'status'          => $isPending ? 'pending_payment' : 'completed',
                'order_type'      => $orderType,
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
                'source'          => $payload['source'] ?? 'offline',
                'completed_at'    => $isPending ? null : now(),
            ]);

            // 3. Create order items & deduct stock via idempotent movements
            foreach ($itemsData as $itemData) {
                $product = $itemData['product'];
                unset($itemData['product']);

                OrderItem::create(array_merge(
                    array_diff_key($itemData, ['stock_movement_uuid' => 1]),
                    ['order_id' => $order->id, 'uuid' => Str::uuid()->toString()]
                ));

                // Stock movement (idempotent per stock_movement_uuid)
                if ($product->track_stock && !$isPending) {
                    $movementUuid = $itemData['stock_movement_uuid'];
                    $existingMovement = InventoryMovement::where('uuid', $movementUuid)->first();

                    if (!$existingMovement) {
                        $before = (float) $product->stock_quantity;
                        $quantityChange = $orderType === 'return' ? $itemData['quantity'] : -$itemData['quantity'];
                        $rawAfter = $before + $quantityChange;

                        // Multi-device stock conflict detection
                        $itemConflict = false;
                        if ($rawAfter < 0 && !$product->allow_negative_stock) {
                            $hasStockConflict = true;
                            $itemConflict = true;
                            $conflictMsg = "Oversold item '{$product->name}' (Stock before: {$before}, Change: {$quantityChange}, Balance: {$rawAfter})";
                            $conflictNotes[] = $conflictMsg;
                            $after = $product->allow_negative_stock ? $rawAfter : max(0, $rawAfter);
                        } else {
                            $after = $rawAfter;
                        }

                        // Updating via Eloquent model updates sync_version via model saving event
                        $product->update(['stock_quantity' => $after]);

                        $this->inventoryService->logMovement([
                            'uuid'            => $movementUuid,
                            'store_id'        => $storeId,
                            'product_id'      => $product->id,
                            'user_id'         => $user->id,
                            'device_id'       => $deviceId,
                            'sync_status'     => $itemConflict ? 'conflict' : 'synced',
                            'type'            => $orderType === 'return' ? 'return' : 'sale',
                            'reference_type'  => 'order',
                            'reference_id'    => $order->id,
                            'reference_uuid'  => $uuid,
                            'quantity'        => $quantityChange,
                            'quantity_before' => $before,
                            'quantity_after'  => $after,
                            'unit_cost'       => $itemData['cost_price'],
                            'total_cost'      => $itemData['cost_price'] * abs($quantityChange),
                            'notes'           => $itemConflict ? ($conflictMsg ?? 'Conflict: oversold by offline transaction') : null,
                        ]);
                    }
                }
            }

            // 4. Process payments idempotently
            foreach ($payments as $paymentData) {
                $paymentUuid = $paymentData['uuid'] ?? Str::uuid()->toString();
                $existingPayment = Payment::where('uuid', $paymentUuid)->first();

                if (!$existingPayment) {
                    Payment::create([
                        'uuid'             => $paymentUuid,
                        'order_id'         => $order->id,
                        'user_id'          => $user->id,
                        'method'           => $paymentData['method'] ?? 'cash',
                        'amount'           => $paymentData['amount'] ?? $totalAmount,
                        'currency'         => 'LKR',
                        'status'           => 'completed',
                    ]);
                }
            }

            // 5. Update customer stats
            if ($order->customer_id && !$isPending) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->increment('total_purchases', $totalAmount);
                    $customer->increment('total_orders');
                }
            }

            // If a multi-device stock conflict was detected, flag order for administrative resolution
            if ($hasStockConflict) {
                $order->update([
                    'sync_status' => 'conflict',
                    'sync_error'  => implode('; ', $conflictNotes),
                ]);

                return [
                    'status'    => 'conflict',
                    'message'   => 'Order synced with inventory conflict: ' . implode('; ', $conflictNotes),
                    'entity_id' => $order->id,
                ];
            }

            return [
                'status'    => 'success',
                'message'   => 'Order synced successfully.',
                'entity_id' => $order->id,
            ];
        });
    }

    /**
     * Resolve or safely create a customer for offline order synchronization.
     *
     * @param array $payload
     * @param int $storeId
     * @return Customer|null
     */
    protected function resolveCustomer(array $payload, int $storeId): ?Customer
    {
        $rawId = $payload['customer_id'] ?? null;
        $uuid  = $payload['customer_uuid'] ?? null;

        // If customer details were passed as a nested object
        if (isset($payload['customer']) && is_array($payload['customer'])) {
            $rawId = $payload['customer']['id'] ?? $rawId;
            $uuid  = $payload['customer']['uuid'] ?? $uuid;
            $payload['customer_name']  = $payload['customer']['name'] ?? ($payload['customer_name'] ?? null);
            $payload['customer_phone'] = $payload['customer']['phone'] ?? ($payload['customer_phone'] ?? null);
            $payload['customer_email'] = $payload['customer']['email'] ?? ($payload['customer_email'] ?? null);
        }

        // If rawId is a UUID string passed in customer_id field
        if (is_string($rawId) && strlen($rawId) === 36 && empty($uuid)) {
            $uuid  = $rawId;
            $rawId = null;
        }

        $customer = null;

        // 1. Try finding by database integer ID (valid range, strictly within user's store)
        if (!empty($rawId) && is_numeric($rawId) && $rawId > 0 && $rawId < 10000000000) {
            $customer = Customer::where('store_id', $storeId)->where('id', (int) $rawId)->first();
        }

        // 2. Try finding by UUID with store_id validation
        if (!$customer && !empty($uuid) && is_string($uuid)) {
            $customer = Customer::where('store_id', $storeId)->where('uuid', $uuid)->first();
        }

        $name  = trim($payload['customer_name'] ?? '');
        $phone = !empty($payload['customer_phone']) ? trim($payload['customer_phone']) : null;
        $email = !empty($payload['customer_email']) ? trim($payload['customer_email']) : null;

        // 3. If customer is not found, check whether customer_uuid and a valid customer_name are available
        // Do not create a customer for Walk-in Customer
        if (!$customer && !empty($uuid) && is_string($uuid) && strlen($uuid) === 36) {
            if (!empty($name) && strcasecmp($name, 'Walk-in Customer') !== 0) {
                $customer = Customer::firstOrCreate(
                    [
                        'uuid' => $uuid,
                    ],
                    [
                        'store_id'  => $storeId,
                        'name'      => $name,
                        'phone'     => $phone,
                        'email'     => $email,
                        'is_active' => true,
                    ]
                );

                // Ensure customer belongs to this store
                if ($customer->store_id !== $storeId) {
                    return null;
                }
            }
        }

        // 4. Ensure an existing customer's non-empty phone/email are not overwritten with empty values
        if ($customer) {
            $updates = [];
            if (empty($customer->phone) && !empty($phone)) {
                $updates['phone'] = $phone;
            }
            if (empty($customer->email) && !empty($email)) {
                $updates['email'] = $email;
            }
            if (!empty($updates)) {
                $customer->update($updates);
            }
        }

        return $customer;
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
     * Enforces pessimistic locking, conflict detection, before/after audit, and sync_version bumping.
     */
    protected function processInventorySync(string $uuid, array $payload, $user, string $deviceId): array
    {
        $existing = InventoryMovement::where('uuid', $uuid)->first();
        if ($existing) {
            return ['status' => 'duplicate', 'message' => 'Movement already exists on server.', 'entity_id' => $existing->id];
        }

        return DB::transaction(function () use ($uuid, $payload, $user, $deviceId) {
            $productQuery = Product::where('store_id', $user->store_id)->lockForUpdate();
            if (!empty($payload['product_id'])) {
                $productQuery->where('id', $payload['product_id']);
            } elseif (!empty($payload['product_uuid'])) {
                $productQuery->where('uuid', $payload['product_uuid']);
            } else {
                return ['status' => 'failed', 'message' => 'Product identifier (id or uuid) required.'];
            }

            $product = $productQuery->first();
            if (!$product) {
                return ['status' => 'failed', 'message' => 'Product not found in store.'];
            }

            $quantity = (float) ($payload['quantity'] ?? 0);
            if ($quantity == 0) {
                return ['status' => 'failed', 'message' => 'Movement quantity cannot be zero.'];
            }

            $type = $payload['type'] ?? 'adjustment';
            $before = (float) $product->stock_quantity;
            $rawAfter = $before + $quantity;
            $hasConflict = false;
            $conflictMsg = null;

            // Conflict detection: negative stock when not allowed
            if ($rawAfter < 0 && !$product->allow_negative_stock) {
                $hasConflict = true;
                $conflictMsg = "Offline stock movement caused negative balance on '{$product->name}' (Stock before: {$before}, Change: {$quantity}, Result: {$rawAfter})";
                $after = $product->allow_negative_stock ? $rawAfter : max(0, $rawAfter);
            } else {
                $after = $rawAfter;
            }

            // Note if client's observed offline before-quantity differed from actual server stock
            $notes = $payload['notes'] ?? '';
            if (isset($payload['quantity_before'])) {
                $clientBefore = (float) $payload['quantity_before'];
                if (abs($clientBefore - $before) > 0.001) {
                    $divergenceNote = "Prior stock divergence detected (client observed: {$clientBefore}, cloud actual: {$before})";
                    $notes = $notes ? ($notes . ' | ' . $divergenceNote) : $divergenceNote;
                }
            }

            if ($hasConflict && $conflictMsg) {
                $notes = $notes ? ($notes . ' | ' . $conflictMsg) : $conflictMsg;
            }

            // Log movement strictly via InventoryService
            $movement = $this->inventoryService->logMovement([
                'uuid'            => $uuid,
                'store_id'        => $user->store_id,
                'product_id'      => $product->id,
                'user_id'         => $user->id,
                'device_id'       => $deviceId,
                'sync_status'     => $hasConflict ? 'conflict' : 'synced',
                'type'            => $type,
                'reference_type'  => $payload['reference_type'] ?? 'offline_sync',
                'reference_id'    => $payload['reference_id'] ?? null,
                'reference_uuid'  => $payload['reference_uuid'] ?? null,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'unit_cost'       => $payload['unit_cost'] ?? $product->cost_price,
                'total_cost'      => abs(($payload['unit_cost'] ?? $product->cost_price) * $quantity),
                'notes'           => $notes ?: null,
            ]);

            // Update product stock quantity and trigger sync_version refresh
            if ($product->track_stock) {
                $product->update(['stock_quantity' => $after]);
            }

            if ($hasConflict) {
                return [
                    'status'    => 'conflict',
                    'message'   => $conflictMsg,
                    'entity_id' => $movement->id,
                ];
            }

            return ['status' => 'success', 'message' => 'Movement synced.', 'entity_id' => $movement->id];
        });
    }

    /**
     * Process cash register session sync (open/close).
     */
    protected function processRegisterSync(string $uuid, string $operation, array $payload, $user, string $deviceId): array
    {
        $action = $payload['action'] ?? ($operation === 'create' ? 'open' : 'close');

        if ($action === 'open') {
            $existing = \App\Models\Register::where('store_id', $user->store_id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                return ['status' => 'duplicate', 'message' => 'Active open register already exists on server.', 'entity_id' => $existing->id];
            }

            $register = \App\Models\Register::create([
                'store_id'        => $user->store_id,
                'user_id'         => $user->id,
                'name'            => 'Register ' . ($deviceId ?: '1'),
                'opening_balance' => (float) ($payload['opening_balance'] ?? 0),
                'status'          => 'open',
                'opened_at'       => $payload['opened_at'] ?? now(),
                'notes'           => $payload['notes'] ?? 'Opened offline',
            ]);

            return ['status' => 'success', 'message' => 'Register opened.', 'entity_id' => $register->id];
        }

        if ($action === 'close') {
            $register = \App\Models\Register::where('store_id', $user->store_id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('opened_at')
                ->first();

            if (!$register) {
                $latest = \App\Models\Register::where('store_id', $user->store_id)->where('user_id', $user->id)->latest()->first();
                return ['status' => 'duplicate', 'message' => 'Register session already closed.', 'entity_id' => $latest?->id];
            }

            $register->update([
                'closing_balance'  => (float) ($payload['closing_balance'] ?? 0),
                'expected_balance' => (float) ($payload['expected_balance'] ?? 0),
                'total_sales'      => (float) ($payload['total_sales'] ?? 0),
                'total_refunds'    => (float) ($payload['total_refunds'] ?? 0),
                'status'           => 'closed',
                'closed_at'        => $payload['closed_at'] ?? now(),
                'notes'            => $payload['notes'] ?? null,
            ]);

            return ['status' => 'success', 'message' => 'Register closed.', 'entity_id' => $register->id];
        }

        return ['status' => 'failed', 'message' => 'Unknown register action.'];
    }
}
