<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List categories for sync/mobile.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Category::where('store_id', $user->store_id)->where('is_active', true);

        if ($request->since) {
            $query->where('updated_at', '>', $request->since);
        }

        $categories = $query->select([
            'id', 'uuid', 'parent_id', 'name', 'slug', 'code', 'color', 'icon',
            'sort_order', 'is_active', 'updated_at'
        ])->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }
}
