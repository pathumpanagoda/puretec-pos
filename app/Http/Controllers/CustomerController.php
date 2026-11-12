<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $query   = Customer::where('store_id', $storeId)->with('group');
        if ($request->filled('search')) $query->search($request->search);
        if ($request->filled('group'))  $query->where('customer_group_id', $request->group);
        $customers = $query->latest()->paginate(20)->withQueryString();
        $groups    = CustomerGroup::where('store_id', $storeId)->get();
        return view('customers.index', compact('customers','groups'));
    }

    public function create()
    {
        $groups = CustomerGroup::where('store_id', Auth::user()->store_id)->where('is_active', true)->get();
        return view('customers.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'customer_group_id' => [
                'nullable',
                Rule::exists('customer_groups', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'nic'               => 'nullable|string|max:20',
            'date_of_birth'     => 'nullable|date',
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'credit_limit'      => 'nullable|numeric|min:0',
            'current_balance'   => 'nullable|numeric',
            'loyalty_points'    => 'nullable|integer|min:0',
            'loyalty_tier'      => 'nullable|string|max:50',
            'is_active'         => 'nullable',
        ]);

        $data = $request->only([
            'name',
            'customer_group_id',
            'phone',
            'email',
            'nic',
            'date_of_birth',
            'address',
            'city',
            'notes',
            'credit_limit',
            'current_balance',
            'loyalty_points',
            'loyalty_tier',
            'is_active',
        ]);

        unset($data['store_id'], $data['total_purchases'], $data['total_orders']);

        $data['store_id'] = Auth::user()->store_id;

        if (array_key_exists('customer_group_id', $data) && ($data['customer_group_id'] === '' || $data['customer_group_id'] === null)) {
            $data['customer_group_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully!');
    }

    public function edit(Customer $customer)
    {
        if ((int) $customer->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $groups = CustomerGroup::where('store_id', Auth::user()->store_id)->where('is_active', true)->get();
        return view('customers.edit', compact('customer','groups'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ((int) $customer->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $request->validate([
            'name'              => 'required|string|max:100',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'customer_group_id' => [
                'nullable',
                Rule::exists('customer_groups', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'nic'               => 'nullable|string|max:20',
            'date_of_birth'     => 'nullable|date',
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'credit_limit'      => 'nullable|numeric|min:0',
            'loyalty_tier'      => 'nullable|string|max:50',
            'is_active'         => 'nullable',
        ]);

        // Explicit allowed-field whitelist excluding protected financial and store fields
        $data = $request->only([
            'name',
            'customer_group_id',
            'phone',
            'email',
            'nic',
            'date_of_birth',
            'address',
            'city',
            'notes',
            'credit_limit',
            'loyalty_tier',
            'is_active',
        ]);

        unset(
            $data['store_id'],
            $data['current_balance'],
            $data['total_purchases'],
            $data['total_orders'],
            $data['loyalty_points']
        );

        if (array_key_exists('customer_group_id', $data) && ($data['customer_group_id'] === '' || $data['customer_group_id'] === null)) {
            $data['customer_group_id'] = null;
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    public function show(Customer $customer)
    {
        if ((int) $customer->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $orders = $customer->orders()->with('items')->latest()->limit(10)->get();
        return view('customers.show', compact('customer','orders'));
    }

    public function destroy(Customer $customer)
    {
        if ((int) $customer->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $customer->delete();
        return back()->with('success', 'Customer deleted successfully!');
    }
}
