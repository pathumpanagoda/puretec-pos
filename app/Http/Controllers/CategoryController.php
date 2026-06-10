<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $storeId    = Auth::user()->store_id;
        $categories = Category::where('store_id', $storeId)->with('parent','children')->withCount('products')->whereNull('parent_id')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::where('store_id', Auth::user()->store_id)->whereNull('parent_id')->get();
        return view('categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $data             = $request->except('_token');
        $data['store_id'] = Auth::user()->store_id;
        $data['slug']     = Str::slug($data['name']);
        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Category created!');
    }

    public function edit(Category $category)
    {
        $parents = Category::where('store_id', Auth::user()->store_id)->whereNull('parent_id')->where('id','!=',$category->id)->get();
        return view('categories.edit', compact('category','parents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $data = $request->except('_token','_method');
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted!');
    }

    /**
     * Quick create category via AJAX (from product form)
     */
    public function storeQuick(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $storeId = Auth::user()->store_id;

        // Create category
        $category = Category::create([
            'store_id'  => $storeId,
            'parent_id' => $request->parent_id ?: null,
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'slug'      => Str::slug($request->name),
            'color'     => $request->color ?? '#3b82f6',
            'icon'      => $request->icon ?? 'bi-tag',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'category' => [
                'id'   => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
            ]
        ]);
    }

    /**
     * Generate category code suggestion from name
     */
    public function suggestCode(Request $request)
    {
        $name = strtoupper(preg_replace('/[^a-zA-Z]/', '', $request->name));
        $code = substr($name, 0, 4);

        // Make sure code is unique
        $storeId = Auth::user()->store_id;
        $existing = Category::where('store_id', $storeId)->where('code', $code)->exists();

        if ($existing) {
            // Add number suffix
            $i = 1;
            while (Category::where('store_id', $storeId)->where('code', $code . $i)->exists()) {
                $i++;
            }
            $code = $code . $i;
        }

        return response()->json(['code' => $code]);
    }
}
