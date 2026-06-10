<?php

namespace App\Http\Controllers\Nexfloit;

use App\Http\Controllers\Controller;
use App\Models\TenantPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * List all payments with filters.
     */
    public function index(Request $request)
    {
        $query = TenantPayment::with(['tenant', 'subscription', 'receivedByUser']);

        // Filter by date range
        if ($from = $request->input('from')) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        // Filter by payment method
        if ($method = $request->input('method')) {
            $query->where('payment_method', $method);
        }

        // Search by tenant
        if ($search = $request->input('search')) {
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('payment_date')->paginate(20);

        // Summary stats
        $totalAmount = $query->sum('amount');
        $paymentMethods = TenantPayment::paymentMethods();

        return view('nexfloit.payments.index', compact('payments', 'totalAmount', 'paymentMethods'));
    }

    /**
     * Show payment details.
     */
    public function show(TenantPayment $payment)
    {
        $payment->load(['tenant', 'subscription', 'receivedByUser']);

        return view('nexfloit.payments.show', compact('payment'));
    }
}
