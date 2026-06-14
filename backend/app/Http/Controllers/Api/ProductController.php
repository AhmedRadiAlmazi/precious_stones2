<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'reviews'])
            ->active();

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('in_stock')) {
            $query->inStock();
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a new product (Seller only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'certification' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Allow images up to 5MB
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store in 'public/products' and get the URL
                $path = $image->store('products', 'public');
                // Create a full URL or relative path depending on frontend needs. 
                // Storage::url() usually returns /storage/products/filename.jpg
                $imagePaths[] = Storage::url($path);
            }
        }

        $product = Product::create([
            'seller_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'weight' => $request->weight,
            'origin_country' => $request->origin_country,
            'certification' => $request->certification,
            'images' => $imagePaths, // Save file paths
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المنتج بنجاح!',
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Display a specific product
     */
    public function show($id)
    {
        $product = Product::with(['seller', 'category', 'reviews.user', 'auction'])
            ->findOrFail($id);

        // Increment views
        $product->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update a product (Owner or Admin only)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Check ownership
        if ($product->seller_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المنتج.',
            ], 403);
        }

        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'certification' => 'nullable|string|max:255',
            'images' => 'nullable|array',
        ]);

        $product->update($request->only([
            'category_id', 'name', 'description', 'price', 'stock',
            'weight', 'origin_country', 'certification', 'images'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج بنجاح!',
            'data' => $product->fresh(['category']),
        ]);
    }

    /**
     * Delete a product (Owner or Admin only)
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Check ownership
        if ($product->seller_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا المنتج.',
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح!',
        ]);
    }

    /**
     * Get seller's products
     */
    public function myProducts(Request $request)
    {
        $products = Product::where('seller_id', $request->user()->id)
            ->with(['category', 'reviews'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
