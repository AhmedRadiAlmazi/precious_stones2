<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get all users
     */
    public function getAllUsers(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by account type
        if ($request->has('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent disabling own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تعطيل حسابك الخاص.',
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'تم تفعيل حساب المستخدم.' : 'تم تعطيل حساب المستخدم.',
            'data' => $user,
        ]);
    }

    /**
     * Get pending sellers (not approved)
     */
    public function getPendingSellers()
    {
        $sellers = User::where('account_type', 'seller')
            ->where('is_approved', false)
            ->with('roles')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sellers,
        ]);
    }

    /**
     * Approve a seller
     */
    public function approveSeller($id)
    {
        $seller = User::findOrFail($id);

        if ($seller->account_type !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'هذا المستخدم ليس بائعاً.',
            ], 422);
        }

        if ($seller->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'هذا البائع موافق عليه بالفعل.',
            ], 422);
        }

        $seller->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم الموافقة على البائع بنجاح!',
            'data' => $seller,
        ]);
    }

    /**
     * Reject a seller
     */
    public function rejectSeller($id)
    {
        $seller = User::findOrFail($id);

        if ($seller->account_type !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'هذا المستخدم ليس بائعاً.',
            ], 422);
        }

        $seller->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض البائع.',
            'data' => $seller,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_buyers' => User::where('account_type', 'buyer')->count(),
                'total_sellers' => User::where('account_type', 'seller')->where('is_approved', true)->count(),
                'pending_sellers' => User::where('account_type', 'seller')->where('is_approved', false)->count(),
                'total_products' => \App\Models\Product::count(),
                'active_products' => \App\Models\Product::where('is_active', true)->count(),
                'total_auctions' => \App\Models\Auction::count(),
                'active_auctions' => \App\Models\Auction::active()->count(),
                'pending_auctions' => \App\Models\Auction::where('status', 'pending')->count(),
                'total_bids' => \App\Models\Bid::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الإحصائيات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending auctions
     */
    public function getPendingAuctions()
    {
        $auctions = \App\Models\Auction::where('status', 'pending')
            ->with(['product', 'seller'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $auctions,
        ]);
    }

    /**
     * Approve an auction
     */
    public function approveAuction($id)
    {
        $auction = \App\Models\Auction::findOrFail($id);

        if ($auction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا المزاد ليس في حالة انتظار.',
            ], 422);
        }

        $auction->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على المزاد بنجاح!',
            'data' => $auction,
        ]);
    }

    /**
     * Reject an auction
     */
    public function rejectAuction($id)
    {
        $auction = \App\Models\Auction::findOrFail($id);

        if ($auction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا المزاد ليس في حالة انتظار.',
            ], 422);
        }

        $auction->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض المزاد.',
            'data' => $auction,
        ]);
    }

    /**
     * Get all products with filters
     */
    public function getAllProducts(Request $request)
    {
        $query = \App\Models\Product::with(['seller', 'category'])
            ->withCount('reviews');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, $id)
    {
        $product = \App\Models\Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'weight' => 'sometimes|numeric|min:0',
            'origin_country' => 'sometimes|string|max:100',
            'certification' => 'sometimes|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المنتج بنجاح!',
            'data' => $product->load(['seller', 'category']),
        ]);
    }

    /**
     * Delete product (soft delete)
     */
    public function deleteProduct($id)
    {
        $product = \App\Models\Product::findOrFail($id);

        // Check if product has active auctions
        $hasActiveAuctions = \App\Models\Auction::where('product_id', $id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveAuctions) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المنتج لأنه مرتبط بمزادات نشطة.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح!',
        ]);
    }

    /**
     * Toggle product status
     */
    public function toggleProductStatus($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'message' => $product->is_active ? 'تم تفعيل المنتج!' : 'تم إلغاء تفعيل المنتج!',
            'data' => $product,
        ]);
    }

    /**
     * Get all auctions with filters
     */
    public function getAllAuctions(Request $request)
    {
        $query = \App\Models\Auction::with(['product', 'seller', 'winner'])
            ->withCount('bids');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        // Search
        if ($request->has('search')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $auctions = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $auctions,
        ]);
    }

    /**
     * Update auction
     */
    public function updateAuction(Request $request, $id)
    {
        $auction = \App\Models\Auction::findOrFail($id);

        $validated = $request->validate([
            'starting_price' => 'sometimes|numeric|min:0',
            'min_bid_increment' => 'sometimes|numeric|min:0',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'status' => 'sometimes|in:pending,active,ended,cancelled',
        ]);

        $auction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المزاد بنجاح!',
            'data' => $auction->load(['product', 'seller']),
        ]);
    }

    /**
     * Delete auction
     */
    public function deleteAuction($id)
    {
        $auction = \App\Models\Auction::findOrFail($id);

        // Only allow deletion of pending or cancelled auctions
        if (in_array($auction->status, ['active', 'ended'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المزادات النشطة أو المنتهية.',
            ], 422);
        }

        $auction->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المزاد بنجاح!',
        ]);
    }

    /**
     * End auction manually
     */
    public function endAuction($id)
    {
        $auction = \App\Models\Auction::findOrFail($id);

        if ($auction->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'يمكن إنهاء المزادات النشطة فقط.',
            ], 422);
        }

        $auction->update(['status' => 'ended']);

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء المزاد بنجاح!',
            'data' => $auction,
        ]);
    }

    /**
     * Get all categories
     */
    public function getAllCategories(Request $request)
    {
        $query = \App\Models\Category::withCount('products');

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $categories = $request->has('paginate') && $request->paginate === 'false'
            ? $query->get()
            : $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Create category
     */
    public function createCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $category = \App\Models\Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفئة بنجاح!',
            'data' => $category,
        ], 201);
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الفئة بنجاح!',
            'data' => $category,
        ]);
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفئة لأنها تحتوي على منتجات.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفئة بنجاح!',
        ]);
    }

    /**
     * Toggle category status
     */
    public function toggleCategoryStatus($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => $category->is_active ? 'تم تفعيل الفئة!' : 'تم إلغاء تفعيل الفئة!',
            'data' => $category,
        ]);
    }
}
