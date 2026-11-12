<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $storeId  = Auth::user()->store_id;
        $products = Product::where('store_id', $storeId)->with('category')->orderBy('name')->get();
        $lowStock = $this->inventoryService->getLowStockProducts($storeId);
        return view('inventory.index', compact('products', 'lowStock'));
    }

    public function movements(Request $request, Product $product)
    {
        if ((int) $product->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $movements = InventoryMovement::where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->paginate(20);
        return view('inventory.movements', compact('product','movements'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'quantity'   => 'required|numeric',
            'type'       => 'required|in:adjustment,damage,expired,transfer',
            'notes'      => 'nullable|string',
        ]);

        $product = Product::where('store_id', Auth::user()->store_id)->findOrFail($request->product_id);
        $this->inventoryService->adjustStock($product, $request->quantity, $request->type, $request->notes ?? '', Auth::id());

        return back()->with('success', 'Stock adjusted successfully!');
    }
}
