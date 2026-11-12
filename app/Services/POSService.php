<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Discount;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSService
{
    /**
     * Process a complete sale transaction.
     *
     * @param array $data
     * @return Order
     * @throws \Exception
     */
    public function processSale(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user     = Auth::user();
            $storeId  = $user->store_id;
            $customer = $this->resolveCustomer($data, $storeId);

            if (empty($data['items']) || !is_array($data['items'])) {
                throw new \Exception('Sale must include at least one item.');
            }

            // Aggregate requested quantities per product to prevent multi-line cart bypass
            $productQuantities = [];
            foreach ($data['items'] as $item) {
                if (empty($item['product_id'])) {
                    throw new \Exception('Each item must specify a valid product_id.');
                }
                $pId = (int) $item['product_id'];
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new \Exception('Item quantity must be greater than zero.');
                }
                $productQuantities[$pId] = ($productQuantities[$pId] ?? 0) + $qty;
            }

            $store = Auth::user()->store ?? \App\Models\Store::find($storeId);
            $storeAllowNegative = (bool) ($store?->settings['pos']['allow_negative_stock'] ?? $store?->settings['inventory']['allow_negative_stock'] ?? false);

            // Lock products for update and verify stock
            $lockedProducts = [];
            foreach ($productQuantities as $pId => $totalRequestedQty) {
                $product = Product::where('id', $pId)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $allowNegative = (bool) ($product->allow_negative_stock || $storeAllowNegative);

                if ($product->track_stock && !$allowNegative) {
                    if ($totalRequestedQty > (float) $product->stock_quantity) {
                        throw new \Exception("Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$totalRequestedQty}.");
                    }
                }

                $lockedProducts[$pId] = $product;
            }

            // Calculate totals
            $subtotal        = 0;
            $taxAmount       = 0;
            $discountAmount  = $data['discount_amount'] ?? 0;
            $profit          = 0;
            $itemsData       = [];

            foreach ($data['items'] as $item) {
                $product      = $lockedProducts[(int) $item['product_id']];
                $qty          = (float) $item['quantity'];
                $unitPrice    = (float) $item['unit_price'];
                $itemDisc     = (float) ($item['discount_amount'] ?? 0);
                $taxRate      = (float) ($item['tax_rate'] ?? 0);
                $itemSubtotal = ($qty * $unitPrice) - $itemDisc;
                $itemTax      = ($taxRate > 0) ? ($itemSubtotal * $taxRate / 100) : 0;
                $itemTotal    = $itemSubtotal + $itemTax;
                $itemProfit   = ($unitPrice - $product->cost_price) * $qty - $itemDisc;

                $subtotal   += $itemSubtotal;
                $taxAmount  += $itemTax;
                $profit     += $itemProfit;

                $itemsData[] = [
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'sku'             => $product->sku,
                    'barcode'         => $product->barcode,
                    'quantity'        => $qty,
                    'unit'            => $product->unit,
                    'unit_price'      => $unitPrice,
                    'cost_price'      => $product->cost_price,
                    'discount_amount' => $itemDisc,
                    'tax_rate'        => $taxRate,
                    'tax_amount'      => $itemTax,
                    'subtotal'        => $itemSubtotal,
                    'total'           => $itemTotal,
                    'profit'          => $itemProfit,
                ];
            }

            $totalAmount  = $subtotal + $taxAmount - $discountAmount + ($data['shipping_amount'] ?? 0);
            if (isset($data['paid_amount']) && is_numeric($data['paid_amount'])) {
                $paidAmount = (float) $data['paid_amount'];
            } elseif (!empty($data['payments']) && is_array($data['payments'])) {
                $paidAmount = (float) collect($data['payments'])->sum(function ($p) {
                    return is_numeric($p['amount'] ?? null) ? (float) $p['amount'] : 0;
                });
            } else {
                $paidAmount = 0.0;
            }
            $changeAmount = max(0, $paidAmount - $totalAmount);
            $dueAmount    = max(0, $totalAmount - $paidAmount);

            // Create order
            $order = Order::create([
                'store_id'        => $storeId,
                'customer_id'     => $customer?->id ?? null,
                'user_id'         => $user->id,
                'status'          => 'completed',
                'order_type'      => 'sale',
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'due_amount'      => $dueAmount,
                'profit'          => $profit,
                'discount_type'   => $data['discount_type'] ?? null,
                'discount_value'  => $data['discount_value'] ?? 0,
                'coupon_code'     => $data['coupon_code'] ?? null,
                'table_number'    => $data['table_number'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'completed_at'    => now(),
                'loyalty_points_earned' => floor($totalAmount / 100),
            ]);

            // Create order items & update stock
            foreach ($itemsData as $itemData) {
                $orderItem = OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));

                // Update product stock
                $product = $lockedProducts[$itemData['product_id']];
                if ($product && $product->track_stock) {
                    $before = (float) $product->stock_quantity;
                    $product->decrement('stock_quantity', $itemData['quantity']);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $storeId,
                        'product_id'     => $product->id,
                        'user_id'        => $user->id,
                        'type'           => 'sale',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'reference_uuid' => $orderItem->uuid,
                        'quantity'       => -$itemData['quantity'],
                        'quantity_before'=> $before,
                        'quantity_after' => $before - $itemData['quantity'],
                        'unit_cost'      => $itemData['cost_price'],
                        'total_cost'     => $itemData['cost_price'] * $itemData['quantity'],
                    ]);
                }
            }

            // Process gift card deductions inside the transaction
            $this->processGiftCardPayments($data['payments'] ?? [], $storeId);

            // Process payments
            foreach ($data['payments'] ?? [] as $paymentData) {
                Payment::create([
                    'order_id'         => $order->id,
                    'user_id'          => $user->id,
                    'method'           => $paymentData['method'],
                    'payment_provider' => $paymentData['provider'] ?? null,
                    'reference_number' => $paymentData['reference'] ?? ($paymentData['reference_number'] ?? null),
                    'amount'           => $paymentData['amount'],
                    'currency'         => 'LKR',
                    'status'           => 'completed',
                ]);
            }

            // Update customer stats
            if ($customer) {
                $customer->increment('total_purchases', $totalAmount);
                $customer->increment('total_orders');
                $customer->increment('loyalty_points', $order->loyalty_points_earned);
            }

            return $order->load(['items', 'payments', 'customer', 'user']);
        });
    }

    /**
     * Process an order refund/return.
     *
     * @param Order $order
     * @param array $refundItems
     * @param string $reason
     * @return Order
     * @throws \Exception
     */
    public function processRefund(Order $order, array $refundItems, string $reason = ''): Order
    {
        return DB::transaction(function () use ($order, $refundItems, $reason) {
            // Lock order for update
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'refunded') {
                throw new \Exception('This order has already been fully refunded.');
            }

            if ($order->status !== 'completed') {
                throw new \Exception("Only completed orders can be refunded (current status: {$order->status}).");
            }

            if (empty($refundItems) || !is_array($refundItems)) {
                throw new \Exception('No items specified for refund.');
            }

            // Aggregate refund quantities by order_item_id to handle duplicates safely
            $aggregatedRefunds = [];
            foreach ($refundItems as $refundItem) {
                if (!isset($refundItem['order_item_id'])) {
                    throw new \Exception('Invalid refund request: missing order_item_id.');
                }
                $itemId = (int) $refundItem['order_item_id'];
                $qty = (float) ($refundItem['quantity'] ?? 0);

                if ($qty <= 0) {
                    throw new \Exception("Invalid refund quantity for item ID {$itemId}. Quantity must be greater than zero.");
                }

                $aggregatedRefunds[$itemId] = ($aggregatedRefunds[$itemId] ?? 0) + $qty;
            }

            $refundTotal = 0;
            $user = Auth::user();
            $userId = $user?->id ?? $order->user_id;

            foreach ($aggregatedRefunds as $itemId => $qty) {
                $orderItem = OrderItem::where('order_id', $order->id)->lockForUpdate()->find($itemId);
                if (!$orderItem) {
                    throw new \Exception("Invalid order item ID {$itemId} for this order.");
                }

                // Determine already refunded quantity using the single consistent method
                $alreadyRefunded = $this->getItemRefundedQuantity($orderItem, $order);

                $remainingRefundable = (float) $orderItem->quantity - $alreadyRefunded;
                if ($qty > $remainingRefundable) {
                    throw new \Exception("Refund quantity {$qty} exceeds refundable quantity {$remainingRefundable} for item '{$orderItem->product_name}'.");
                }

                $amount = (float) $orderItem->unit_price * $qty;
                $refundTotal += $amount;

                // Restore stock
                $product = Product::where('id', $orderItem->product_id)
                    ->where('store_id', $order->store_id)
                    ->lockForUpdate()
                    ->first();

                if ($product && $product->track_stock) {
                    $before = (float) $product->stock_quantity;
                    $product->increment('stock_quantity', $qty);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $order->store_id,
                        'product_id'     => $product->id,
                        'user_id'        => $userId,
                        'type'           => 'return',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'reference_uuid' => $orderItem->uuid,
                        'quantity'       => $qty,
                        'quantity_before'=> $before,
                        'quantity_after' => $before + $qty,
                        'unit_cost'      => $orderItem->cost_price,
                        'total_cost'     => $orderItem->cost_price * $qty,
                        'notes'          => 'Refund processed' . ($reason ? ": {$reason}" : ''),
                    ]);
                }

                // Update cumulative refunded quantity on orderItem while preserving existing notes
                $notesData = [];
                if (!empty($orderItem->notes)) {
                    $decoded = json_decode($orderItem->notes, true);
                    if (is_array($decoded)) {
                        $notesData = $decoded;
                    } else {
                        $notesData['original_notes'] = $orderItem->notes;
                    }
                }
                $notesData['refunded_qty'] = $alreadyRefunded + $qty;
                $orderItem->notes = json_encode($notesData);
                $orderItem->save();
            }

            // Check if all items in this order are now fully refunded using the exact same calculation logic
            $allItems = OrderItem::where('order_id', $order->id)->get();
            $allFullyRefunded = true;
            foreach ($allItems as $it) {
                $itRefunded = $this->getItemRefundedQuantity($it, $order);
                if ($itRefunded < (float) $it->quantity) {
                    $allFullyRefunded = false;
                    break;
                }
            }

            if ($allFullyRefunded) {
                $order->status = 'refunded';
            }

            $refundLog = "Refund: " . ($reason ?: 'No reason specified') . " (Amount: " . number_format($refundTotal, 2) . ")";
            $order->internal_notes = trim(($order->internal_notes ?? '') . "\n" . $refundLog);
            $order->save();

            return $order->load(['items', 'payments', 'customer', 'user']);
        });
    }

    /**
     * Get the cumulative refunded quantity for an order item.
     * Uses order_items.notes (refunded_qty) as authoritative source if present.
     * Falls back to legacy InventoryMovement records when notes do not contain refunded_qty.
     * Prevents double-counting when both notes and movements exist.
     *
     * @param OrderItem $orderItem
     * @param Order|null $order
     * @return float
     */
    public function getItemRefundedQuantity(OrderItem $orderItem, ?Order $order = null): float
    {
        // 1. Check notes for modern refunded_qty JSON value
        if (!empty($orderItem->notes)) {
            $decoded = json_decode($orderItem->notes, true);
            if (is_array($decoded) && isset($decoded['refunded_qty']) && is_numeric($decoded['refunded_qty'])) {
                return max(0.0, (float) $decoded['refunded_qty']);
            }
        }

        // 2. Fall back to legacy InventoryMovement records
        $orderId = $order?->id ?? $orderItem->order_id;
        $query = InventoryMovement::where('reference_type', 'order')
            ->where('reference_id', $orderId)
            ->where('product_id', $orderItem->product_id)
            ->where('type', 'return');

        if (!empty($orderItem->uuid)) {
            $uuidSum = (float) (clone $query)->where('reference_uuid', $orderItem->uuid)->sum('quantity');
            if ($uuidSum > 0) {
                return $uuidSum;
            }
        }

        $movRefunded = (float) $query->sum('quantity');
        return max(0.0, $movRefunded);
    }

    /**
     * Cancel a pending_payment order and restore its stock.
     *
     * @param Order $order
     * @param string $reason
     * @return Order
     * @throws \Exception
     */
    public function cancelPendingBill(Order $order, string $reason = ''): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            // Lock order for update to prevent concurrent cancellations
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'completed') {
                throw new \Exception('Cannot cancel a completed order. Use refund instead.');
            }

            if ($order->status === 'refunded') {
                throw new \Exception('Cannot cancel an already refunded order.');
            }

            if ($order->status === 'cancelled') {
                throw new \Exception('Order is already cancelled.');
            }

            if ($order->status !== 'pending_payment') {
                throw new \Exception("Only orders with pending_payment status can be cancelled (current status: {$order->status}).");
            }

            // Restore stock for each item
            $user = Auth::user();
            $userId = $user?->id ?? $order->user_id;

            foreach ($order->items as $orderItem) {
                $product = Product::where('id', $orderItem->product_id)
                    ->where('store_id', $order->store_id)
                    ->lockForUpdate()
                    ->first();

                if ($product && $product->track_stock) {
                    $before = (float) $product->stock_quantity;
                    $qty = (float) $orderItem->quantity;
                    $product->increment('stock_quantity', $qty);

                    app(InventoryService::class)->logMovement([
                        'store_id'       => $order->store_id,
                        'product_id'     => $product->id,
                        'user_id'        => $userId,
                        'type'           => 'adjustment',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'reference_uuid' => $orderItem->uuid,
                        'quantity'       => $qty,
                        'quantity_before'=> $before,
                        'quantity_after' => $before + $qty,
                        'unit_cost'      => $orderItem->cost_price,
                        'total_cost'     => $orderItem->cost_price * $qty,
                        'notes'          => 'Bill Cancelled' . ($reason ? ": {$reason}" : ''),
                    ]);
                }
            }

            $order->update([
                'status'         => 'cancelled',
                'internal_notes' => trim(($order->internal_notes ?? '') . "\nBill Cancelled: " . ($reason ?: 'No reason provided')),
            ]);

            return $order->load(['items', 'customer', 'user', 'billedBy']);
        });
    }

    /**
     * Hold an order (save for later).
     *
     * @param array $data
     * @return Order
     */
    public function holdOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user     = Auth::user();
            $customer = $this->resolveCustomer($data, $user->store_id);

            // Create the held order
            $order = Order::create([
                'store_id'        => $user->store_id,
                'customer_id'     => $customer?->id ?? null,
                'user_id'         => $user->id,
                'status'          => 'on_hold',
                'order_type'      => 'sale',
                'subtotal'        => $data['subtotal'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'discount_type'   => $data['discount_type'] ?? null,
                'discount_value'  => $data['discount_value'] ?? 0,
                'tax_amount'      => $data['tax_amount'] ?? 0,
                'total_amount'    => $data['total_amount'] ?? 0,
                'notes'           => $data['notes'] ?? null,
            ]);

            // Save the cart items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    OrderItem::create([
                        'order_id'        => $order->id,
                        'product_id'      => $item['product_id'],
                        'product_name'    => $item['product_name'] ?? $product?->name ?? 'Unknown',
                        'sku'             => $item['sku'] ?? $product?->sku,
                        'barcode'         => $item['barcode'] ?? $product?->barcode,
                        'quantity'        => $item['quantity'],
                        'unit'            => $item['unit'] ?? $product?->unit ?? 'pcs',
                        'unit_price'      => $item['unit_price'],
                        'cost_price'      => $item['cost_price'] ?? $product?->cost_price ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'tax_rate'        => $item['tax_rate'] ?? 0,
                        'tax_amount'      => 0,
                        'subtotal'        => $item['quantity'] * $item['unit_price'],
                        'total'           => $item['quantity'] * $item['unit_price'],
                    ]);
                }
            }

            return $order->load(['items', 'customer']);
        });
    }

    /**
     * Create a bill without payment (bill-only mode).
     * Stock is deducted but no payment is recorded.
     *
     * @param array $data
     * @return Order
     * @throws \Exception
     */
    public function createBill(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user     = Auth::user();
            $storeId  = $user->store_id;
            $customer = $this->resolveCustomer($data, $storeId);

            if (empty($data['items']) || !is_array($data['items'])) {
                throw new \Exception('Bill must include at least one item.');
            }

            // Aggregate requested quantities per product to prevent multi-line cart bypass
            $productQuantities = [];
            foreach ($data['items'] as $item) {
                if (empty($item['product_id'])) {
                    throw new \Exception('Each item must specify a valid product_id.');
                }
                $pId = (int) $item['product_id'];
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new \Exception('Item quantity must be greater than zero.');
                }
                $productQuantities[$pId] = ($productQuantities[$pId] ?? 0) + $qty;
            }

            $store = Auth::user()->store ?? \App\Models\Store::find($storeId);
            $storeAllowNegative = (bool) ($store?->settings['pos']['allow_negative_stock'] ?? $store?->settings['inventory']['allow_negative_stock'] ?? false);

            // Lock products for update and verify stock
            $lockedProducts = [];
            foreach ($productQuantities as $pId => $totalRequestedQty) {
                $product = Product::where('id', $pId)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $allowNegative = (bool) ($product->allow_negative_stock || $storeAllowNegative);

                if ($product->track_stock && !$allowNegative) {
                    if ($totalRequestedQty > (float) $product->stock_quantity) {
                        throw new \Exception("Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$totalRequestedQty}.");
                    }
                }

                $lockedProducts[$pId] = $product;
            }

            // Calculate totals
            $subtotal        = 0;
            $taxAmount       = 0;
            $discountAmount  = $data['discount_amount'] ?? 0;
            $profit          = 0;
            $itemsData       = [];

            foreach ($data['items'] as $item) {
                $product      = $lockedProducts[(int) $item['product_id']];
                $qty          = (float) $item['quantity'];
                $unitPrice    = (float) $item['unit_price'];
                $itemDisc     = (float) ($item['discount_amount'] ?? 0);
                $taxRate      = (float) ($item['tax_rate'] ?? 0);
                $itemSubtotal = ($qty * $unitPrice) - $itemDisc;
                $itemTax      = ($taxRate > 0) ? ($itemSubtotal * $taxRate / 100) : 0;
                $itemTotal    = $itemSubtotal + $itemTax;
                $itemProfit   = ($unitPrice - $product->cost_price) * $qty - $itemDisc;

                $subtotal   += $itemSubtotal;
                $taxAmount  += $itemTax;
                $profit     += $itemProfit;

                $itemsData[] = [
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'sku'             => $product->sku,
                    'barcode'         => $product->barcode,
                    'quantity'        => $qty,
                    'unit'            => $product->unit,
                    'unit_price'      => $unitPrice,
                    'cost_price'      => $product->cost_price,
                    'discount_amount' => $itemDisc,
                    'tax_rate'        => $taxRate,
                    'tax_amount'      => $itemTax,
                    'subtotal'        => $itemSubtotal,
                    'total'           => $itemTotal,
                    'profit'          => $itemProfit,
                ];
            }

            $totalAmount  = $subtotal + $taxAmount - $discountAmount + ($data['shipping_amount'] ?? 0);

            // Create order with pending_payment status
            $order = Order::create([
                'store_id'        => $storeId,
                'customer_id'     => $customer?->id ?? null,
                'user_id'         => $user->id,
                'billed_by'       => $user->id,
                'status'          => 'pending_payment',
                'order_type'      => 'sale',
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'total_amount'    => $totalAmount,
                'paid_amount'     => 0,
                'change_amount'   => 0,
                'due_amount'      => $totalAmount,
                'profit'          => $profit,
                'discount_type'   => $data['discount_type'] ?? null,
                'discount_value'  => $data['discount_value'] ?? 0,
                'coupon_code'     => $data['coupon_code'] ?? null,
                'table_number'    => $data['table_number'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'billed_at'       => now(),
                'loyalty_points_earned' => floor($totalAmount / 100),
            ]);

            // Create order items & update stock (stock is deducted on bill creation)
            foreach ($itemsData as $itemData) {
                $orderItem = OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));

                // Update product stock
                $product = $lockedProducts[$itemData['product_id']];
                if ($product && $product->track_stock) {
                    $before = (float) $product->stock_quantity;
                    $product->decrement('stock_quantity', $itemData['quantity']);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $storeId,
                        'product_id'     => $product->id,
                        'user_id'        => $user->id,
                        'type'           => 'sale',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'reference_uuid' => $orderItem->uuid,
                        'quantity'       => -$itemData['quantity'],
                        'quantity_before'=> $before,
                        'quantity_after' => $before - $itemData['quantity'],
                        'unit_cost'      => $itemData['cost_price'],
                        'total_cost'     => $itemData['cost_price'] * $itemData['quantity'],
                    ]);
                }
            }

            return $order->load(['items', 'customer', 'user', 'billedBy']);
        });
    }

    /**
     * Approve payment for a pending_payment order.
     *
     * @param Order $order
     * @param array $paymentData
     * @return Order
     * @throws \Exception
     */
    public function approvePayment(Order $order, array $paymentData): Order
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $user = Auth::user();

            // Lock order for update to prevent concurrent payment approvals
            $order = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Verify order is pending_payment
            if ($order->status !== 'pending_payment') {
                throw new \Exception('Order is not pending payment.');
            }

            $paidAmount   = (float) ($paymentData['paid_amount'] ?? $order->total_amount);
            $changeAmount = max(0, $paidAmount - $order->total_amount);
            $dueAmount    = max(0, $order->total_amount - $paidAmount);

            // Process gift card deductions inside the transaction
            $this->processGiftCardPayments($paymentData['payments'] ?? [], $order->store_id);

            // Update order
            $order->update([
                'status'               => 'completed',
                'payment_processed_by' => $user->id,
                'paid_amount'          => $paidAmount,
                'change_amount'        => $changeAmount,
                'due_amount'           => $dueAmount,
                'completed_at'         => now(),
                'payment_approved_at'  => now(),
            ]);

            // Process payments
            foreach ($paymentData['payments'] as $payment) {
                Payment::create([
                    'order_id'         => $order->id,
                    'user_id'          => $user->id,
                    'method'           => $payment['method'],
                    'payment_provider' => $payment['provider'] ?? null,
                    'reference_number' => $payment['reference'] ?? ($payment['reference_number'] ?? null),
                    'amount'           => $payment['amount'],
                    'currency'         => 'LKR',
                    'status'           => 'completed',
                ]);
            }

            // Update customer stats
            if ($order->customer) {
                $order->customer->increment('total_purchases', $order->total_amount);
                $order->customer->increment('total_orders');
                $order->customer->increment('loyalty_points', $order->loyalty_points_earned);
            }

            return $order->load(['items', 'payments', 'customer', 'user', 'billedBy', 'paymentProcessedBy']);
        });
    }

    /**
     * Validate and apply a coupon code.
     *
     * @param string $code
     * @param float $subtotal
     * @param int $storeId
     * @return array
     */
    public function applyCoupon(string $code, float $subtotal, int $storeId): array
    {
        $discount = Discount::where('store_id', $storeId)
            ->where('code', strtoupper($code))
            ->first();

        if (!$discount || !$discount->isValid()) {
            return ['success' => false, 'message' => 'Invalid or expired coupon code.'];
        }
        if ($subtotal < $discount->min_purchase) {
            return ['success' => false, 'message' => "Minimum purchase of Rs. {$discount->min_purchase} required."];
        }

        $discountAmount = ($discount->type === 'percentage')
            ? ($subtotal * $discount->value / 100)
            : $discount->value;

        if ($discount->max_discount && $discountAmount > $discount->max_discount) {
            $discountAmount = $discount->max_discount;
        }

        return [
            'success'         => true,
            'discount_amount' => round($discountAmount, 2),
            'discount_type'   => $discount->type,
            'discount_value'  => $discount->value,
            'message'         => "Coupon applied! Discount: Rs. " . number_format($discountAmount, 2),
        ];
    }

    /**
     * Validate and deduct gift card payments inside the active database transaction.
     * Enforces store isolation, active/unexpired status, sufficient balance,
     * positive amounts, pessimistic row locking, and status transition to 'used'.
     *
     * @param array $payments
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function processGiftCardPayments(array $payments, int $storeId): void
    {
        $giftCardTotals = [];

        foreach ($payments as $payment) {
            if (($payment['method'] ?? '') === 'gift_card') {
                $code = trim((string) ($payment['reference'] ?? $payment['reference_number'] ?? ''));
                $amount = (float) ($payment['amount'] ?? 0);

                if (empty($code)) {
                    throw new \Exception('Gift card code is required for gift card payments.');
                }

                if ($amount <= 0) {
                    throw new \Exception("Invalid payment amount for gift card '{$code}'. Amount must be greater than zero.");
                }

                $giftCardTotals[$code] = ($giftCardTotals[$code] ?? 0) + $amount;
            }
        }

        if (empty($giftCardTotals)) {
            return;
        }

        // Lock and validate all gift cards with pessimistic row locks
        $lockedCards = [];
        foreach ($giftCardTotals as $code => $totalRequested) {
            $card = GiftCard::where('store_id', $storeId)
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$card) {
                throw new \Exception("Gift card '{$code}' not found or does not belong to this store.");
            }

            if (!$card->isActive()) {
                $statusMsg = $card->isExpired() ? 'expired' : $card->status;
                throw new \Exception("Gift card '{$code}' cannot be used because it is {$statusMsg}.");
            }

            $currentBalance = (float) $card->current_balance;
            if ($currentBalance < $totalRequested) {
                throw new \Exception("Insufficient balance on gift card '{$code}'. Available: " . number_format($currentBalance, 2) . ", Requested: " . number_format($totalRequested, 2) . ".");
            }

            $lockedCards[$code] = $card;
        }

        // Deduct balance and update status if fully redeemed
        foreach ($giftCardTotals as $code => $totalRequested) {
            $card = $lockedCards[$code];
            $newBalance = max(0.0, round((float) $card->current_balance - $totalRequested, 2));

            $updateData = [
                'current_balance' => $newBalance,
            ];

            if ($newBalance <= 0.0001) {
                $updateData['status'] = 'used';
                $updateData['current_balance'] = 0.00;
            }

            $card->update($updateData);
        }
    }

    /**
     * Safely resolve or create a customer record from request data.
     * Prevents invalid or temporary client-generated IDs (e.g. timestamps from Date.now())
     * from being passed to foreign key columns, while properly supporting UUID-based mapping.
     *
     * @param array $data
     * @param int $storeId
     * @return Customer|null
     */
    protected function resolveCustomer(array $data, int $storeId): ?Customer
    {
        $rawId = $data['customer_id'] ?? null;
        $uuid  = $data['customer_uuid'] ?? null;

        // If customer details were passed as a nested object
        if (isset($data['customer']) && is_array($data['customer'])) {
            $rawId = $data['customer']['id'] ?? $rawId;
            $uuid  = $data['customer']['uuid'] ?? $uuid;
            $data['customer_name']    = $data['customer']['name'] ?? ($data['customer_name'] ?? null);
            $data['customer_phone']   = $data['customer']['phone'] ?? ($data['customer_phone'] ?? null);
            $data['customer_email']   = $data['customer']['email'] ?? ($data['customer_email'] ?? null);
            $data['customer_address'] = $data['customer']['address'] ?? ($data['customer_address'] ?? null);
            $data['customer_city']    = $data['customer']['city'] ?? ($data['customer_city'] ?? null);
        }

        // If rawId is a UUID string passed in customer_id field
        if (is_string($rawId) && strlen($rawId) === 36 && empty($uuid)) {
            $uuid = $rawId;
            $rawId = null;
        }

        // 1. Try finding by database integer ID (only if numeric and in a valid integer range)
        if (!empty($rawId) && is_numeric($rawId) && $rawId > 0 && $rawId < 10000000000) {
            $customer = Customer::where('store_id', $storeId)->where('id', (int) $rawId)->first();
            if ($customer) {
                return $customer;
            }
        }

        // 2. Try finding by UUID
        if (!empty($uuid) && is_string($uuid) && strlen($uuid) === 36) {
            $customer = Customer::where('store_id', $storeId)->where('uuid', $uuid)->first();
            if ($customer) {
                return $customer;
            }

            // 3. If customer was created offline with UUID and has a name, safely persist in DB
            $name = trim($data['customer_name'] ?? '');
            if (!empty($name) && strcasecmp($name, 'Walk-in Customer') !== 0) {
                return Customer::firstOrCreate(
                    [
                        'uuid' => $uuid,
                    ],
                    [
                        'store_id'  => $storeId,
                        'name'      => $name,
                        'phone'     => $data['customer_phone'] ?? null,
                        'email'     => $data['customer_email'] ?? null,
                        'address'   => $data['customer_address'] ?? null,
                        'city'      => $data['customer_city'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        return null;
    }
}
