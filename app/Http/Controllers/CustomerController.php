<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate(['name' => 'required|string|max:100', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email']);
        $data             = $request->except('_token');
        $data['store_id'] = Auth::user()->store_id;
        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully!');
    }

    public function edit(Customer $customer)
    {
        $groups = CustomerGroup::where('store_id', Auth::user()->store_id)->where('is_active', true)->get();
        return view('customers.edit', compact('customer','groups'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $customer->update($request->except('_token','_method'));
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    public function show(Customer $customer)
    {
        $orders = $customer->orders()->with('items')->latest()->limit(10)->get();
        return view('customers.show', compact('customer','orders'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', 'Customer deleted successfully!');
    }
}
