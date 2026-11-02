<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * List customers for sync/mobile.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Customer::where('store_id', $user->store_id)->where('is_active', true);

        if ($request->since) {
            $query->where('updated_at', '>', $request->since);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $customers = $query->select([
            'id', 'uuid', 'name', 'email', 'phone', 'address', 'city',
            'loyalty_points', 'loyalty_tier', 'current_balance', 'is_active', 'updated_at'
        ])->paginate($request->per_page ?? 100);

        return response()->json(['success' => true, 'data' => $customers]);
    }

    /**
     * Get a single customer by UUID.
     */
    public function show(Request $request, string $uuid)
    {
        $customer = Customer::where('uuid', $uuid)
            ->where('store_id', $request->user()->store_id)
            ->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $customer]);
    }

    /**
     * Create a customer (from mobile/offline sync).
     */
    public function store(Request $request)
    {
        $request->validate([
            'uuid'  => 'nullable|uuid',
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        $uuid = $request->uuid ?? Str::uuid()->toString();

        // Idempotency
        $existing = Customer::where('uuid', $uuid)->first();
        if ($existing) {
            return response()->json(['success' => true, 'data' => $existing, 'duplicate' => true]);
        }

        $customer = Customer::create([
            'uuid'      => $uuid,
            'store_id'  => $request->user()->store_id,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'address'   => $request->address,
            'city'      => $request->city,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $customer], 201);
    }
}
