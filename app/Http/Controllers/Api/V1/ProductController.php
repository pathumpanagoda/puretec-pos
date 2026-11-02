<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products for sync/mobile.
     *
     * GET /api/v1/products?since=...&page=1&per_page=100
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Product::where('store_id', $user->store_id)
            ->where('is_active', true)
            ->with('category:id,uuid,name,code,color,icon');

        if ($request->since) {
            $query->where('updated_at', '>', $request->since);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', $search);
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->select([
            'id', 'uuid', 'category_id', 'name', 'sku', 'barcode',
            'selling_price', 'cost_price', 'wholesale_price', 'min_selling_price',
            'stock_quantity', 'min_stock', 'unit', 'track_stock',
            'is_taxable', 'image', 'is_active', 'updated_at'
        ])->paginate($request->per_page ?? 100);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Get a single product by UUID.
     *
     * GET /api/v1/products/{uuid}
     */
    public function show(Request $request, string $uuid)
    {
        $product = Product::where('uuid', $uuid)
            ->where('store_id', $request->user()->store_id)
            ->with('category:id,uuid,name,code,color,icon')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }

    /**
     * Find product by barcode.
     *
     * GET /api/v1/products/barcode/{barcode}
     */
    public function findByBarcode(Request $request, string $barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->where('store_id', $request->user()->store_id)
            ->where('is_active', true)
            ->with('category:id,uuid,name,code,color,icon')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }
}
