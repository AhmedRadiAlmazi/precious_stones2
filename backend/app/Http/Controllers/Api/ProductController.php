<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /** Allowed columns for sorting (prevents SQL injection) */
    private const ALLOWED_SORTS = ['created_at', 'price', 'name', 'views_count', 'stock'];

    /**
     * Display a listing of active products.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['seller:id,first_name,last_name', 'category'])
            ->withCount('reviews')
            ->active();

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Safe sorting via whitelist
        $sortBy    = in_array($request->sort_by, self::ALLOWED_SORTS) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage  = min((int) $request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->response()->getData(true),
        ]);
    }

    /**
     * Store a newly created product (Seller only).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path         = $image->store('products', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $product = Product::create([
            'seller_id'      => $request->user()->id,
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'description'    => $request->description,
            'price'          => $request->price,
            'stock'          => $request->stock,
            'weight'         => $request->weight,
            'origin_country' => $request->origin_country,
            'certification'  => $request->certification,
            'images'         => $imagePaths,
            'is_active'      => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المنتج بنجاح!',
            'data'    => new ProductResource($product->load('category')),
        ], 201);
    }

    /**
     * Display a specific product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['seller:id,first_name,last_name', 'category', 'reviews.user', 'auction'])
            ->findOrFail($id);

        // Increment views count atomically
        $product->increment('views_count');

        return response()->json([
            'success' => true,
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Update a product (Owner or Admin only).
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Authorization check
        if ($product->seller_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المنتج.',
            ], 403);
        }

        // Handle new images if provided
        $data = $request->only([
            'category_id', 'name', 'description', 'price', 'stock',
            'weight', 'origin_country', 'certification',
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path         = $image->store('products', 'public');
                $imagePaths[] = Storage::url($path);
            }
            $data['images'] = $imagePaths;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج بنجاح!',
            'data'    => new ProductResource($product->fresh(['category'])),
        ]);
    }

    /**
     * Delete a product (Owner or Admin only).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($product->seller_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا المنتج.',
            ], 403);
        }

        // Prevent deletion if product has active auctions
        $hasActiveAuctions = $product->auction()
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveAuctions) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المنتج لأنه مرتبط بمزادات نشطة.',
            ], 422);
        }

        $product->delete(); // SoftDelete

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح!',
        ]);
    }

    /**
     * Get the authenticated seller's products.
     */
    public function myProducts(Request $request): JsonResponse
    {
        $products = Product::where('seller_id', $request->user()->id)
            ->with(['category'])
            ->withCount('reviews')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->response()->getData(true),
        ]);
    }
}
