<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    private const ALLOWED_SORTS = ['created_at', 'name', 'price', 'stock', 'views_count'];

    /**
     * Get all products with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['seller:id,first_name,last_name', 'category'])
            ->withCount('reviews');

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', (int) $request->seller_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy    = in_array($request->sort_by, self::ALLOWED_SORTS) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        $perPage  = min((int) $request->get('per_page', 20), 100);
        $products = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->response()->getData(true),
        ]);
    }

    /**
     * Update a product (Admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'description'    => 'sometimes|string',
            'price'          => 'sometimes|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'category_id'    => 'sometimes|exists:categories,id',
            'weight'         => 'sometimes|numeric|min:0',
            'origin_country' => 'sometimes|string|max:100',
            'certification'  => 'sometimes|string|max:255',
            'is_featured'    => 'sometimes|boolean',
            'is_active'      => 'sometimes|boolean',
            'promotion_status' => 'sometimes|string|in:none,pending,approved,rejected',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج بنجاح!',
            'data'    => new ProductResource($product->load(['seller', 'category'])),
        ]);
    }

    /**
     * Delete a product (Admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $hasActiveAuctions = Auction::where('product_id', $id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveAuctions) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المنتج لأنه مرتبط بمزادات نشطة.',
            ], 422);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المنتج بنجاح!']);
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'message' => $product->is_active ? 'تم تفعيل المنتج!' : 'تم إلغاء تفعيل المنتج!',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Approve promotion request.
     */
    public function approvePromotion(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        if ($product->promotion_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'المنتج ليس لديه طلب ترويج معلق حالياً.',
            ], 400);
        }

        $product->update([
            'is_featured' => true,
            'promotion_status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على طلب الترويج بنجاح وتفعيل الإعلان للمنتج!',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Reject promotion request (Refunds the fee to seller's wallet).
     */
    public function rejectPromotion(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        if ($product->promotion_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'المنتج ليس لديه طلب ترويج معلق حالياً.',
            ], 400);
        }

        $product->update([
            'promotion_status' => 'rejected',
        ]);

        // Refund the seller's wallet balance
        $seller = $product->seller;
        if ($seller) {
            $seller->increment('wallet_balance', 50.00);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب الترويج وإعادة مبلغ 50 ر.س لمحفظة البائع بنجاح.',
            'data'    => new ProductResource($product),
        ]);
    }
}
