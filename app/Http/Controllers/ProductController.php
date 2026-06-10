<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Tax;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $storeId    = Auth::user()->store_id;
        $query      = Product::where('store_id', $storeId)->with('category','supplier');

        if ($request->filled('search'))     $query->search($request->search);
        if ($request->filled('category'))   $query->where('category_id', $request->category);
        if ($request->filled('status'))     $query->where('is_active', $request->status == 'active');
        if ($request->filled('stock'))      $query->when($request->stock === 'low', fn($q) => $q->lowStock());

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('store_id', $storeId)->where('is_active', true)->get();
        $suppliers  = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $taxes      = Tax::where('store_id', $storeId)->where('is_active', true)->get();
        $lowStock   = Product::where('store_id', $storeId)->lowStock()->count();

        return view('products.index', compact('products', 'categories', 'suppliers', 'taxes', 'lowStock'));
    }

    public function create()
    {
        $storeId    = Auth::user()->store_id;
        // Only get parent categories (where parent_id is null)
        $categories = Category::where('store_id', $storeId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $suppliers  = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $taxes      = Tax::where('store_id', $storeId)->where('is_active', true)->get();
        $nextSku    = $this->generateNextSku($storeId);
        return view('products.create', compact('categories','suppliers','taxes','nextSku'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'stock_quantity'=> 'nullable|numeric|min:0',
            'category_id'   => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
        ]);

        $storeId = Auth::user()->store_id;
        $data    = $request->except('_token','image','gallery','sub_category_id');
        $data['store_id'] = $storeId;

        // Use sub_category_id if provided, otherwise use category_id
        if ($request->filled('sub_category_id')) {
            $data['category_id'] = $request->sub_category_id;
        }

        // Auto-generate SKU if not provided
        if (empty($data['sku'])) {
            $categoryId = $request->category_id;
            $subCategoryId = $request->sub_category_id;
            $data['sku'] = $this->generateNextSku($storeId, $categoryId, $subCategoryId, $request->name);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            // Download image from external URL (barcode lookup)
            try {
                $imageUrl = $request->image_url;

                // Use cURL for better HTTPS support
                $ch = curl_init($imageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                $imageContents = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($imageContents && $httpCode == 200) {
                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $extension = 'jpg';
                    }
                    $filename = 'products/' . uniqid('prod_') . '.' . $extension;
                    \Storage::disk('public')->put($filename, $imageContents);
                    $data['image'] = $filename;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to download product image: ' . $e->getMessage());
            }
        }

        $product = Product::create($data);

        // Log opening stock
        if (($data['stock_quantity'] ?? 0) > 0) {
            $this->inventoryService->logMovement([
                'store_id'       => $storeId,
                'product_id'     => $product->id,
                'user_id'        => Auth::id(),
                'type'           => 'opening',
                'quantity'       => $product->stock_quantity,
                'quantity_before'=> 0,
                'quantity_after' => $product->stock_quantity,
                'unit_cost'      => $product->cost_price,
                'total_cost'     => $product->cost_price * $product->stock_quantity,
                'notes'          => 'Opening stock',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $storeId    = Auth::user()->store_id;
        $categories = Category::where('store_id', $storeId)->where('is_active', true)->get();
        $suppliers  = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $taxes      = Tax::where('store_id', $storeId)->where('is_active', true)->get();
        return view('products.edit', compact('product','categories','suppliers','taxes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('_token','_method','image','remove_image');

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            // Delete old image file
            $oldImage = str_replace('storage/', '', $product->image);
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $data['image'] = null;
        }
        // Handle new image upload
        elseif ($request->hasFile('image')) {
            // Delete old image file
            $oldImage = str_replace('storage/', '', $product->image);
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Delete product image from storage
        if ($product->image) {
            $imagePath = str_replace('storage/', '', $product->image);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $product->delete();
        return back()->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk delete multiple products.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $ids = $request->input('ids');
        $storeId = Auth::user()->store_id;

        // Get products that belong to this store
        $products = Product::where('store_id', $storeId)
            ->whereIn('id', $ids)
            ->get();

        $deletedCount = 0;

        foreach ($products as $product) {
            // Delete product image from storage
            if ($product->image) {
                $imagePath = str_replace('storage/', '', $product->image);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $product->delete();
            $deletedCount++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $deletedCount,
            'message' => "{$deletedCount} product(s) deleted successfully.",
        ]);
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'type'     => 'required|in:adjustment,damage,expired',
            'notes'    => 'nullable|string',
        ]);

        $this->inventoryService->adjustStock(
            $product,
            (float) $request->quantity,
            $request->type,
            $request->notes ?? '',
            Auth::id()
        );

        return response()->json(['success' => true, 'new_stock' => $product->fresh()->stock_quantity]);
    }

    /**
     * Quick update product details via AJAX - Full featured with image upload
     */
    public function quickUpdate(Request $request, Product $product)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'nullable|string|max:100',
            'barcode'       => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'wholesale_price'=> 'nullable|numeric|min:0',
            'stock_quantity'=> 'nullable|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'unit'          => 'nullable|string|max:20',
            'category_id'   => 'nullable|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'tax_id'        => 'nullable|exists:taxes,id',
            'is_active'     => 'nullable',
            'track_stock'   => 'nullable',
            'is_featured'   => 'nullable',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image'  => 'nullable',
        ]);

        // All updatable fields
        $data = $request->only([
            'name', 'sku', 'barcode', 'description',
            'selling_price', 'cost_price', 'wholesale_price',
            'min_stock', 'reorder_level', 'unit',
            'category_id', 'supplier_id', 'tax_id',
            'is_active', 'track_stock', 'is_featured'
        ]);

        // Convert checkbox values to boolean
        $data['is_active'] = $request->input('is_active') == '1' || $request->input('is_active') === true;
        $data['track_stock'] = $request->input('track_stock') == '1' || $request->input('track_stock') === true;
        $data['is_featured'] = $request->input('is_featured') == '1' || $request->input('is_featured') === true;

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            $oldImage = str_replace('storage/', '', $product->image);
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $data['image'] = null;
        }
        // Handle new image upload
        elseif ($request->hasFile('image')) {
            // Delete old image file
            $oldImage = str_replace('storage/', '', $product->image);
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle stock adjustment if provided
        $newStock = $request->input('stock_quantity');
        if ($newStock !== null && (float)$newStock != (float)$product->stock_quantity) {
            $adjustment = (float)$newStock - (float)$product->stock_quantity;
            $this->inventoryService->adjustStock(
                $product,
                $adjustment,
                'adjustment',
                'Quick edit stock change',
                Auth::id()
            );
        }

        // Clean up null/empty values for optional fields
        foreach (['category_id', 'supplier_id', 'tax_id', 'wholesale_price'] as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        $product->update($data);
        $product->refresh();
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category' => $product->category?->name ?? '—',
                'category_id' => $product->category_id,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'stock_quantity' => $product->stock_quantity,
                'unit' => $product->unit,
                'is_active' => $product->is_active,
                'is_low_stock' => $product->isLowStock(),
                'is_out_of_stock' => $product->isOutOfStock(),
                'image' => $product->image ? asset(\Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) : null,
            ]
        ]);
    }

    /**
     * Toggle product active status via AJAX
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => $product->is_active ? 'Product activated!' : 'Product deactivated!'
        ]);
    }

    /**
     * Get product details for quick edit modal - All fields
     */
    public function getProduct($id)
    {
        try {
            $storeId = Auth::user()->store_id;
            $product = Product::where('store_id', $storeId)
                ->where('id', $id)
                ->with(['category', 'supplier', 'tax'])
                ->firstOrFail();

            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'description' => $product->description,
                'category_id' => $product->category_id,
                'category' => $product->category?->name ?? '—',
                'supplier_id' => $product->supplier_id,
                'supplier' => $product->supplier?->name ?? '—',
                'tax_id' => $product->tax_id,
                'tax' => $product->tax?->name ?? '—',
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'wholesale_price' => $product->wholesale_price,
                'stock_quantity' => $product->stock_quantity,
                'min_stock' => $product->min_stock,
                'reorder_level' => $product->reorder_level,
                'unit' => $product->unit,
                'is_active' => $product->is_active,
                'track_stock' => $product->track_stock,
                'is_featured' => $product->is_featured,
                'image' => $product->image ? asset(\Str::startsWith($product->image, 'storage/') ? $product->image : 'storage/'.$product->image) : null,
                'image_name' => $product->image ? basename($product->image) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Product not found', 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Generate barcode - EAN-13 format
     */
    public function generateBarcode(Request $request)
    {
        // Generate EAN-13 compatible barcode
        // Format: 2 + 10 random digits + check digit
        $prefix = '20'; // Local use prefix
        $random = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $code = $prefix . $random;

        // Calculate check digit (EAN-13)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        $barcode = $code . $checkDigit;
        return response()->json(['barcode' => $barcode]);
    }

    /**
     * Generate smart SKU based on category, sub-category, and product name
     * Format: CAT-SUB-PROD-001 (e.g., ELEC-PHN-IPHN-001)
     */
    private function generateNextSku(int $storeId, ?int $categoryId = null, ?int $subCategoryId = null, ?string $productName = null): string
    {
        $parts = [];

        // Get category code
        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category && $category->code) {
                $parts[] = $category->code;
            }
        }

        // Get sub-category code
        if ($subCategoryId) {
            $subCategory = Category::find($subCategoryId);
            if ($subCategory && $subCategory->code) {
                $parts[] = $subCategory->code;
            }
        }

        // Get product name abbreviation (first 4 letters, uppercase)
        if ($productName) {
            $abbr = $this->generateProductAbbreviation($productName);
            if ($abbr) {
                $parts[] = $abbr;
            }
        }

        // If no parts, use default
        if (empty($parts)) {
            $parts[] = 'ITEM';
        }

        $prefix = implode('-', $parts);

        // Find the next number for this prefix
        $lastProduct = Product::where('store_id', $storeId)
            ->where('sku', 'like', $prefix . '-%')
            ->get()
            ->filter(function($p) use ($prefix) {
                // Only match exact prefix (e.g., ELEC-PHN- not ELEC-PHN-ABC-)
                return preg_match('/^' . preg_quote($prefix, '/') . '-\d+$/', $p->sku);
            })
            ->sortByDesc(function($p) use ($prefix) {
                preg_match('/-(\d+)$/', $p->sku, $m);
                return (int)($m[1] ?? 0);
            })
            ->first();

        if ($lastProduct && preg_match('/-(\d+)$/', $lastProduct->sku, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate product name abbreviation (4 chars max)
     * Examples: "iPhone 15 Pro" → "IPHN", "Coca-Cola" → "COCA", "Samsung Galaxy" → "SMSG"
     */
    private function generateProductAbbreviation(string $name): string
    {
        // Clean the name - remove special characters
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $name = trim(strtoupper($name));

        if (empty($name)) {
            return '';
        }

        $words = preg_split('/\s+/', $name);

        // If single word, take first 4 characters
        if (count($words) == 1) {
            return substr($words[0], 0, 4);
        }

        // If 2 words, take 2 chars from each
        if (count($words) == 2) {
            return substr($words[0], 0, 2) . substr($words[1], 0, 2);
        }

        // If 3+ words, take 1-2 chars from first 3 words
        $abbr = substr($words[0], 0, 2);
        $abbr .= substr($words[1], 0, 1);
        $abbr .= substr($words[2], 0, 1);

        return $abbr;
    }

    /**
     * API: Get next SKU (based on category, sub-category, and product name)
     */
    public function getNextSku(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $categoryId = $request->get('category_id');
        $subCategoryId = $request->get('sub_category_id');
        $productName = $request->get('product_name');

        $sku = $this->generateNextSku(
            $storeId,
            $categoryId ? (int)$categoryId : null,
            $subCategoryId ? (int)$subCategoryId : null,
            $productName
        );

        return response()->json(['sku' => $sku]);
    }

    /**
     * API: Get sub-categories for a parent category
     */
    public function getSubCategories(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $parentId = $request->get('parent_id');

        $subCategories = Category::where('store_id', $storeId)
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json(['sub_categories' => $subCategories]);
    }

    /**
     * API: Suggest category based on product name keywords
     */
    public function suggestCategory(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $name = strtolower($request->get('name', ''));

        if (strlen($name) < 2) {
            return response()->json(['category_id' => null]);
        }

        // Simple keyword matching for category suggestion
        $keywords = [
            'Electronics' => ['phone', 'mobile', 'laptop', 'computer', 'tablet', 'tv', 'television', 'camera', 'headphone', 'earphone', 'charger', 'cable', 'usb', 'keyboard', 'mouse', 'monitor', 'speaker', 'battery', 'power bank', 'iphone', 'samsung', 'nokia', 'huawei', 'xiaomi', 'redmi', 'oppo', 'vivo', 'realme'],
            'Clothing' => ['shirt', 'trouser', 'pant', 'dress', 'skirt', 'jacket', 'coat', 'sweater', 'hoodie', 't-shirt', 'tshirt', 'jeans', 'shorts', 'underwear', 'sock', 'shoe', 'sandal', 'slipper', 'cap', 'hat', 'belt', 'tie', 'scarf'],
            'Food & Beverages' => ['rice', 'flour', 'sugar', 'salt', 'oil', 'milk', 'tea', 'coffee', 'juice', 'water', 'soda', 'cola', 'pepsi', 'coca', 'biscuit', 'chocolate', 'candy', 'snack', 'chip', 'noodle', 'pasta', 'bread', 'butter', 'cheese', 'egg', 'meat', 'chicken', 'fish', 'vegetable', 'fruit', 'apple', 'banana', 'orange', 'mango'],
            'Health & Beauty' => ['soap', 'shampoo', 'conditioner', 'lotion', 'cream', 'perfume', 'deodorant', 'toothpaste', 'toothbrush', 'razor', 'medicine', 'tablet', 'capsule', 'syrup', 'vitamin', 'panadol', 'bandage', 'cotton', 'mask', 'sanitizer', 'makeup', 'lipstick', 'foundation'],
            'Home & Garden' => ['furniture', 'chair', 'table', 'bed', 'sofa', 'cupboard', 'shelf', 'lamp', 'light', 'bulb', 'fan', 'curtain', 'carpet', 'rug', 'pot', 'plant', 'flower', 'garden', 'tool', 'hammer', 'screwdriver', 'nail', 'paint', 'brush'],
            'Sports' => ['ball', 'football', 'cricket', 'bat', 'racket', 'tennis', 'badminton', 'gym', 'dumbbell', 'yoga', 'mat', 'bicycle', 'cycle', 'helmet', 'glove', 'jersey', 'sports', 'fitness', 'running', 'swimming'],
            'Books & Stationery' => ['book', 'notebook', 'pen', 'pencil', 'eraser', 'ruler', 'sharpener', 'paper', 'envelope', 'file', 'folder', 'stapler', 'tape', 'glue', 'scissor', 'marker', 'highlighter', 'calculator', 'diary', 'calendar'],
            'Toys & Games' => ['toy', 'game', 'puzzle', 'doll', 'car', 'truck', 'robot', 'lego', 'block', 'ball', 'teddy', 'bear', 'action figure', 'board game', 'card', 'playing']
        ];

        // Find matching category
        $categories = Category::where('store_id', $storeId)->where('is_active', true)->get();

        foreach ($categories as $category) {
            if (isset($keywords[$category->name])) {
                foreach ($keywords[$category->name] as $keyword) {
                    if (strpos($name, $keyword) !== false) {
                        return response()->json([
                            'category_id' => $category->id,
                            'category_name' => $category->name
                        ]);
                    }
                }
            }
        }

        return response()->json(['category_id' => null]);
    }

    /**
     * API: Search product by barcode (for scanner)
     */
    public function searchByBarcode(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $barcode = $request->get('barcode');

        // First check if product exists in our database
        $product = Product::where('store_id', $storeId)
            ->where('barcode', $barcode)
            ->first();

        if ($product) {
            return response()->json([
                'found' => true,
                'exists_in_store' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'selling_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'category_id' => $product->category_id,
                    'stock_quantity' => $product->stock_quantity,
                ]
            ]);
        }

        // Product not found - barcode is new
        return response()->json([
            'found' => false,
            'barcode' => $barcode
        ]);
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('products.import');
    }

    /**
     * Import products from CSV/Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $storeId = Auth::user()->store_id;

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            if ($extension === 'csv' || $extension === 'txt') {
                $result = $this->importFromCsv($file, $storeId);
            } else {
                $result = $this->importFromExcel($file, $storeId);
            }

            $imported = $result['imported'];
            $skipped = $result['skipped'];
            $errors = $result['errors'];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        $message = "Import complete! {$imported} products imported, {$skipped} skipped.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' and ' . (count($errors) - 5) . ' more.';
            }
        }

        return back()->with($imported > 0 ? 'success' : 'warning', $message);
    }

    /**
     * Import from CSV file
     */
    private function importFromCsv($file, $storeId): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $handle = fopen($file->getRealPath(), 'r');
        $header = null;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            // First row is header
            if ($header === null) {
                $header = array_map(fn($h) => strtolower(trim($h)), $row);
                continue;
            }

            // Map row to associative array
            $data = [];
            foreach ($header as $index => $column) {
                $data[$column] = $row[$index] ?? null;
            }

            $result = $this->importProduct($data, $storeId);
            if ($result === true) {
                $imported++;
            } elseif ($result === false) {
                $skipped++;
            } else {
                $errors[] = $result;
                $skipped++;
            }
        }

        fclose($handle);
        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Import from Excel file
     */
    private function importFromExcel($file, $storeId): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Use PhpSpreadsheet if available, otherwise try simple xlsx parsing
        if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $header = null;
            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue;

                if ($header === null) {
                    $header = array_map(fn($h) => strtolower(trim($h ?? '')), $row);
                    continue;
                }

                $data = [];
                foreach ($header as $index => $column) {
                    if (!empty($column)) {
                        $data[$column] = $row[$index] ?? null;
                    }
                }

                $result = $this->importProduct($data, $storeId);
                if ($result === true) {
                    $imported++;
                } elseif ($result === false) {
                    $skipped++;
                } else {
                    $errors[] = $result;
                    $skipped++;
                }
            }
        } else {
            // Fallback: Try to read as CSV (some xlsx can be parsed this way)
            $errors[] = 'Excel support requires PhpSpreadsheet. Please use CSV format.';
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Import a single product from row data
     */
    private function importProduct(array $data, int $storeId)
    {
        // Map common column names
        $name = $data['name'] ?? $data['product_name'] ?? $data['item_name'] ?? $data['product'] ?? null;
        $sku = $data['sku'] ?? $data['item_code'] ?? $data['code'] ?? $data['product_code'] ?? null;
        $barcode = $data['barcode'] ?? $data['upc'] ?? $data['ean'] ?? null;
        $sellingPrice = $data['selling_price'] ?? $data['price'] ?? $data['retail_price'] ?? $data['sell_price'] ?? 0;
        $costPrice = $data['cost_price'] ?? $data['cost'] ?? $data['purchase_price'] ?? $data['buy_price'] ?? 0;
        $stock = $data['stock'] ?? $data['stock_quantity'] ?? $data['quantity'] ?? $data['qty'] ?? 0;
        $category = $data['category'] ?? $data['category_name'] ?? null;
        $unit = $data['unit'] ?? 'pcs';
        $description = $data['description'] ?? $data['desc'] ?? null;

        // Validate required fields
        if (empty($name)) {
            return 'Missing product name';
        }

        // Clean numeric values
        $sellingPrice = (float) preg_replace('/[^0-9.]/', '', $sellingPrice);
        $costPrice = (float) preg_replace('/[^0-9.]/', '', $costPrice);
        $stock = (float) preg_replace('/[^0-9.]/', '', $stock);

        // Check for duplicates (by SKU or barcode)
        $existingQuery = Product::where('store_id', $storeId);
        if (!empty($sku)) {
            $existingQuery->where('sku', $sku);
        } elseif (!empty($barcode)) {
            $existingQuery->orWhere('barcode', $barcode);
        }

        if (!empty($sku) || !empty($barcode)) {
            $existing = Product::where('store_id', $storeId)
                ->where(function($q) use ($sku, $barcode) {
                    if (!empty($sku)) $q->where('sku', $sku);
                    if (!empty($barcode)) $q->orWhere('barcode', $barcode);
                })
                ->first();

            if ($existing) {
                return false; // Skip duplicate
            }
        }

        // Find or create category
        $categoryId = null;
        if (!empty($category)) {
            $cat = Category::where('store_id', $storeId)
                ->where('name', 'like', $category)
                ->first();

            if (!$cat) {
                // Create category
                $cat = Category::create([
                    'store_id' => $storeId,
                    'name' => trim($category),
                    'code' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $category), 0, 4)),
                    'is_active' => true,
                ]);
            }
            $categoryId = $cat->id;
        }

        // Generate SKU if not provided
        if (empty($sku)) {
            $sku = $this->generateNextSku($storeId, $categoryId, null, $name);
        }

        // Create product
        Product::create([
            'store_id' => $storeId,
            'name' => trim($name),
            'sku' => $sku,
            'barcode' => $barcode ?: null,
            'category_id' => $categoryId,
            'selling_price' => $sellingPrice,
            'cost_price' => $costPrice,
            'stock_quantity' => $stock,
            'unit' => $unit,
            'description' => $description,
            'is_active' => true,
            'track_stock' => true,
        ]);

        return true;
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ];

        $columns = ['name', 'sku', 'barcode', 'category', 'selling_price', 'cost_price', 'stock', 'unit', 'description'];

        $callback = function() use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            // Sample rows
            fputcsv($handle, ['iPhone 15 Pro', 'ELEC-PHN-001', '8801234567890', 'Electronics', '249999', '189999', '50', 'pcs', 'Apple iPhone 15 Pro 256GB']);
            fputcsv($handle, ['Coca-Cola 500ml', 'FOOD-BEV-001', '5449000000996', 'Beverages', '150', '120', '200', 'pcs', 'Coca-Cola Original 500ml Bottle']);
            fputcsv($handle, ['Rice 5kg Samba', 'FOOD-GRC-001', '', 'Groceries', '1850', '1650', '100', 'pcs', 'Premium Samba Rice 5kg Pack']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Search products for auto-filter
     */
    public function searchProducts(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $search = $request->get('q', '');

        $query = Product::where('store_id', $storeId)->with('category');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('selling_price', 'like', "%{$search}%")
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->get('stock') === 'low') {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->limit(50)->get();

        return response()->json([
            'products' => $products->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'category' => $p->category?->name ?? '—',
                    'cost_price' => $p->cost_price,
                    'selling_price' => $p->selling_price,
                    'stock_quantity' => $p->stock_quantity,
                    'unit' => $p->unit,
                    'is_active' => $p->is_active,
                    'is_low_stock' => $p->isLowStock(),
                    'is_out_of_stock' => $p->isOutOfStock(),
                    'image' => $p->image ? asset(\Str::startsWith($p->image, 'storage/') ? $p->image : 'storage/'.$p->image) : null,
                    'edit_url' => route('products.edit', $p),
                    'delete_url' => route('products.destroy', $p),
                ];
            }),
            'count' => $products->count(),
        ]);
    }
}
