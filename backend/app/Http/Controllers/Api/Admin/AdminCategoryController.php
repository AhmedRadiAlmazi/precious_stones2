<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * Get all categories with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Support both paginated and full list (for dropdowns)
        if ($request->get('paginate') === 'false') {
            $categories = $query->get();
        } else {
            $perPage    = min((int) $request->get('per_page', 20), 100);
            $categories = $query->paginate($perPage);
        }

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($categories)->response()->getData(true),
        ]);
    }

    /**
     * Create a new category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'slug'        => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
            'is_active'   => 'boolean',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفئة بنجاح!',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    /**
     * Update a category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'slug'        => 'sometimes|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
            'is_active'   => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الفئة بنجاح!',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * Delete a category (only if it has no products).
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفئة لأنها تحتوي على منتجات.',
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الفئة بنجاح!']);
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => $category->is_active ? 'تم تفعيل الفئة!' : 'تم إلغاء تفعيل الفئة!',
            'data'    => new CategoryResource($category),
        ]);
    }
}
