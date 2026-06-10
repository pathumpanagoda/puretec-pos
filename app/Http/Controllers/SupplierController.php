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
        $request->validate(['name' => 'required|string|max:100']);
        $data             = $request->except('_token');
        $data['store_id'] = Auth::user()->store_id;
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully!');
    }

    public function edit(Supplier $supplier)    { return view('suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $supplier->update($request->except('_token','_method'));
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier) { $supplier->delete(); return back()->with('success', 'Supplier deleted!'); }
}
