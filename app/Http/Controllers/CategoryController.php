<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:50',
            'code'        => 'nullable|string|max:10',
            'is_active'   => 'nullable',
        ]);

        $data = $request->only(['name', 'parent_id', 'description', 'color', 'icon', 'code', 'is_active']);
        unset($data['store_id']);

        $data['store_id']  = Auth::user()->store_id;
        $data['parent_id'] = $request->filled('parent_id') ? $request->parent_id : null;
        $data['slug']      = Str::slug($data['name']);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Category created!');
    }

    public function edit(Category $category)
    {
        if ((int) $category->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $parents = Category::where('store_id', Auth::user()->store_id)->whereNull('parent_id')->where('id','!=',$category->id)->get();
        return view('categories.edit', compact('category','parents'));
    }

    public function update(Request $request, Category $category)
    {
        if ((int) $category->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) use ($category) {
                    $query->where('store_id', Auth::user()->store_id)
                          ->where('id', '!=', $category->id);
                }),
            ],
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:50',
            'code'        => 'nullable|string|max:10',
            'is_active'   => 'nullable',
        ]);

        $data = $request->only(['name', 'parent_id', 'description', 'color', 'icon', 'code', 'is_active']);
        unset($data['store_id']);

        $data['parent_id'] = $request->filled('parent_id') ? $request->parent_id : null;
        $data['slug']      = Str::slug($data['name']);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated!');
    }

    public function destroy(Category $category)
    {
        if ((int) $category->store_id !== (int) Auth::user()->store_id) {
            abort(404);
        }

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
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                }),
            ],
        ]);

        $storeId = Auth::user()->store_id;

        // Create category
        $category = Category::create([
            'store_id'  => $storeId,
            'parent_id' => $request->filled('parent_id') ? $request->parent_id : null,
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
