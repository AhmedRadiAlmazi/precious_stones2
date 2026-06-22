<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAuctionRequest;
use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /** Allowed columns for sorting */
    private const ALLOWED_SORTS = ['end_time', 'start_time', 'current_price', 'total_bids', 'created_at'];

    /**
     * Display a listing of auctions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auction::with(['product.category', 'seller:id,first_name,last_name'])
            ->withCount('bids'); // Use withCount to avoid loading all bids (N+1 fix)

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active(); // Default: active auctions only
        }

        if ($request->boolean('ending_soon')) {
            $query->endingSoon();
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', (int) $request->category_id);
            });
        }

        // Safe sorting via whitelist
        $sortBy    = in_array($request->sort_by, self::ALLOWED_SORTS) ? $request->sort_by : 'end_time';
        $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage  = min((int) $request->get('per_page', 15), 100);
        $auctions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => AuctionResource::collection($auctions)->response()->getData(true),
        ]);
    }

    /**
     * Create a new auction (Seller only).
     */
    public function store(StoreAuctionRequest $request): JsonResponse
    {
        // Check product ownership
        $product = Product::findOrFail($request->product_id);

        if ($product->seller_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك إنشاء مزاد لمنتج لا تملكه.',
            ], 403);
        }

        // Check if product already has an active/pending auction
        $existingAuction = $product->auction()
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($existingAuction) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المنتج لديه مزاد نشط أو قيد المراجعة بالفعل.',
            ], 422);
        }

        $auction = Auction::create([
            'product_id'     => $request->product_id,
            'seller_id'      => $request->user()->id,
            'starting_price' => $request->starting_price,
            'current_price'  => $request->starting_price,
            'reserve_price'  => $request->reserve_price,
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
            'bid_increment'  => $request->bid_increment ?? 100,
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المزاد بنجاح! في انتظار موافقة الإدارة.',
            'data'    => new AuctionResource($auction->load('product')),
        ], 201);
    }

    /**
     * Display a specific auction with full details.
     */
    public function show(int $id): JsonResponse
    {
        $auction = Auction::with(['product.category', 'seller:id,first_name,last_name', 'bids.user:id,first_name,last_name', 'winningBid'])
            ->withCount('bids')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new AuctionResource($auction),
        ]);
    }

    /**
     * Update an auction (Owner only, before it starts).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        // Ownership check
        if ($auction->seller_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المزاد.',
            ], 403);
        }

        // Only pending auctions can be edited
        if ($auction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل مزاد نشط أو منتهٍ.',
            ], 422);
        }

        $validated = $request->validate([
            'starting_price' => 'sometimes|numeric|min:0',
            'reserve_price'  => 'nullable|numeric|min:0',
            'start_time'     => 'sometimes|date|after:now',
            'end_time'       => 'sometimes|date|after:start_time',
            'bid_increment'  => 'nullable|numeric|min:1',
        ]);

        $auction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المزاد بنجاح!',
            'data'    => new AuctionResource($auction->fresh(['product'])),
        ]);
    }

    /**
     * Cancel an auction (Owner or Admin).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        $isOwner = $auction->seller_id === $request->user()->id;
        $isAdmin = $request->user()->hasRole('admin');

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإلغاء هذا المزاد.',
            ], 403);
        }

        // Cannot cancel an already ended auction
        if ($auction->status === 'ended') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء مزاد منتهٍ.',
            ], 422);
        }

        $auction->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء المزاد بنجاح!',
        ]);
    }

    /**
     * Get the authenticated seller's auctions.
     */
    public function myAuctions(Request $request): JsonResponse
    {
        $auctions = Auction::where('seller_id', $request->user()->id)
            ->with(['product'])
            ->withCount('bids')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => AuctionResource::collection($auctions)->response()->getData(true),
        ]);
    }
}
