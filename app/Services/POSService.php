<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Discount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSService
{
    /**
     * Process a complete sale transaction.
     *
     * @param array $data
     * @return Order
     */
    public function processSale(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user     = Auth::user();
            $storeId  = $user->store_id;
            $customer = null;

            if (!empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            }

            // Calculate totals
            $subtotal        = 0;
            $taxAmount       = 0;
            $discountAmount  = $data['discount_amount'] ?? 0;
            $profit          = 0;
            $itemsData       = [];

            foreach ($data['items'] as $item) {
                // Security: Verify product belongs to user's store
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $storeId)
                    ->firstOrFail();
                $qty        = (float) $item['quantity'];
                $unitPrice  = (float) $item['unit_price'];
                $itemDisc   = (float) ($item['discount_amount'] ?? 0);
                $taxRate    = (float) ($item['tax_rate'] ?? 0);
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
            $paidAmount   = (float) ($data['paid_amount'] ?? 0);
            $changeAmount = max(0, $paidAmount - $totalAmount);
            $dueAmount    = max(0, $totalAmount - $paidAmount);

            // Create order
            $order = Order::create([
                'store_id'        => $storeId,
                'customer_id'     => $data['customer_id'] ?? null,
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
                OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));

                // Update product stock
                $product = Product::find($itemData['product_id']);
                if ($product && $product->track_stock) {
                    $before = $product->stock_quantity;
                    $product->decrement('stock_quantity', $itemData['quantity']);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $storeId,
                        'product_id'     => $product->id,
                        'user_id'        => $user->id,
                        'type'           => 'sale',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'quantity'       => -$itemData['quantity'],
                        'quantity_before'=> $before,
                        'quantity_after' => $before - $itemData['quantity'],
                        'unit_cost'      => $itemData['cost_price'],
                        'total_cost'     => $itemData['cost_price'] * $itemData['quantity'],
                    ]);
                }
            }

            // Process payments
            foreach ($data['payments'] as $paymentData) {
                Payment::create([
                    'order_id'         => $order->id,
                    'user_id'          => $user->id,
                    'method'           => $paymentData['method'],
                    'payment_provider' => $paymentData['provider'] ?? null,
                    'reference_number' => $paymentData['reference'] ?? null,
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
     */
    public function processRefund(Order $order, array $refundItems, string $reason = ''): Order
    {
        return DB::transaction(function () use ($order, $refundItems, $reason) {
            $refundTotal = 0;

            foreach ($refundItems as $refundItem) {
                $orderItem = OrderItem::find($refundItem['order_item_id']);
                if (!$orderItem) continue;

                $qty    = (float) $refundItem['quantity'];
                $amount = $orderItem->unit_price * $qty;
                $refundTotal += $amount;

                // Restore stock
                $product = Product::find($orderItem->product_id);
                if ($product && $product->track_stock) {
                    $before = $product->stock_quantity;
                    $product->increment('stock_quantity', $qty);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $order->store_id,
                        'product_id'     => $product->id,
                        'user_id'        => auth()->id(),
                        'type'           => 'return',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'quantity'       => $qty,
                        'quantity_before'=> $before,
                        'quantity_after' => $before + $qty,
                        'unit_cost'      => $orderItem->cost_price,
                        'total_cost'     => $orderItem->cost_price * $qty,
                    ]);
                }
            }

            $order->update([
                'status'        => 'refunded',
                'internal_notes'=> trim($order->internal_notes . "\nRefund: {$reason}"),
            ]);

            return $order;
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
            $user = Auth::user();

            // Create the held order
            $order = Order::create([
                'store_id'        => $user->store_id,
                'customer_id'     => $data['customer_id'] ?? null,
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
     */
    public function createBill(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user     = Auth::user();
            $storeId  = $user->store_id;
            $customer = null;

            if (!empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            }

            // Calculate totals
            $subtotal        = 0;
            $taxAmount       = 0;
            $discountAmount  = $data['discount_amount'] ?? 0;
            $profit          = 0;
            $itemsData       = [];

            foreach ($data['items'] as $item) {
                // Security: Verify product belongs to user's store
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $storeId)
                    ->firstOrFail();
                $qty        = (float) $item['quantity'];
                $unitPrice  = (float) $item['unit_price'];
                $itemDisc   = (float) ($item['discount_amount'] ?? 0);
                $taxRate    = (float) ($item['tax_rate'] ?? 0);
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
                'customer_id'     => $data['customer_id'] ?? null,
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
                OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));

                // Update product stock
                $product = Product::find($itemData['product_id']);
                if ($product && $product->track_stock) {
                    $before = $product->stock_quantity;
                    $product->decrement('stock_quantity', $itemData['quantity']);
                    app(InventoryService::class)->logMovement([
                        'store_id'       => $storeId,
                        'product_id'     => $product->id,
                        'user_id'        => $user->id,
                        'type'           => 'sale',
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
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
     */
    public function approvePayment(Order $order, array $paymentData): Order
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $user = Auth::user();

            // Verify order is pending_payment
            if ($order->status !== 'pending_payment') {
                throw new \Exception('Order is not pending payment.');
            }

            $paidAmount   = (float) ($paymentData['paid_amount'] ?? $order->total_amount);
            $changeAmount = max(0, $paidAmount - $order->total_amount);
            $dueAmount    = max(0, $order->total_amount - $paidAmount);

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
                    'reference_number' => $payment['reference'] ?? null,
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
}
