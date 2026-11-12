<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $storeId   = Auth::user()->store_id;
        $query     = Supplier::where('store_id', $storeId);
        if ($request->filled('search')) $query->where('name', 'like', "%{$request->search}%");
        $suppliers = $query->latest()->paginate(20)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'company'         => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:255',
            'mobile'          => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:65535',
            'city'            => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:255',
            'tax_number'      => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|between:-9999999999999.99,9999999999999.99',
            'current_balance' => 'nullable|numeric|between:-9999999999999.99,9999999999999.99',
            'notes'           => 'nullable|string|max:65535',
            'is_active'       => 'nullable|boolean',
        ]);
        $data = $request->only([
            'name',
            'company',
            'email',
            'phone',
            'mobile',
            'address',
            'city',
            'country',
            'tax_number',
            'opening_balance',
            'current_balance',
            'notes',
            'is_active',
        ]);
        $data['store_id'] = Auth::user()->store_id;

        if (!isset($data['country']) || trim((string)$data['country']) === '') {
            $data['country'] = 'Sri Lanka';
        }
        if (!isset($data['opening_balance']) || $data['opening_balance'] === null) {
            $data['opening_balance'] = 0.00;
        }
        if (!isset($data['current_balance']) || $data['current_balance'] === null) {
            $data['current_balance'] = 0.00;
        }
        if (!isset($data['is_active']) || $data['is_active'] === null) {
            $data['is_active'] = 1;
        }

        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully!');
    }

    public function edit(Supplier $supplier)
    {
        if ((int) $supplier->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        if ((int) $supplier->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $request->validate([
            'name'            => 'required|string|max:100',
            'company'         => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:255',
            'mobile'          => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:65535',
            'city'            => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:255',
            'tax_number'      => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|between:-9999999999999.99,9999999999999.99',
            'current_balance' => 'nullable|numeric|between:-9999999999999.99,9999999999999.99',
            'notes'           => 'nullable|string|max:65535',
            'is_active'       => 'nullable|boolean',
        ]);
        $data = $request->only([
            'name',
            'company',
            'email',
            'phone',
            'mobile',
            'address',
            'city',
            'country',
            'tax_number',
            'opening_balance',
            'current_balance',
            'notes',
            'is_active',
        ]);

        if (array_key_exists('country', $data) && (empty($data['country']) || trim((string)$data['country']) === '')) {
            $data['country'] = $supplier->country;
        }
        if (array_key_exists('opening_balance', $data) && $data['opening_balance'] === null) {
            $data['opening_balance'] = $supplier->opening_balance;
        }
        if (array_key_exists('current_balance', $data) && $data['current_balance'] === null) {
            $data['current_balance'] = $supplier->current_balance;
        }
        if (array_key_exists('is_active', $data) && $data['is_active'] === null) {
            $data['is_active'] = $supplier->is_active;
        }

        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated!');
    }

    public function destroy(Supplier $supplier)
    {
        if ((int) $supplier->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $supplier->delete();
        return back()->with('success', 'Supplier deleted!');
    }
}
