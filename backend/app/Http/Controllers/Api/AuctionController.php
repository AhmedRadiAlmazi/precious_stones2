<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuctionController extends Controller
{
    /**
     * Display a listing of auctions
     */
    public function index(Request $request)
    {
        $query = Auction::with(['product.category', 'seller', 'bids']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active(); // Default to active auctions
        }

        if ($request->has('ending_soon')) {
            $query->endingSoon();
        }

        if ($request->has('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'end_time');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $auctions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $auctions,
        ]);
    }

    /**
     * Create a new auction (Seller only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'starting_price' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'bid_increment' => 'nullable|numeric|min:1',
        ]);

        // Check product ownership
        $product = Product::findOrFail($request->product_id);
        if ($product->seller_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك إنشاء مزاد لمنتج لا تملكه.',
            ], 403);
        }

        // Check if product already has an auction
        if ($product->auction()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المنتج لديه مزاد بالفعل.',
            ], 422);
        }

        $auction = Auction::create([
            'product_id' => $request->product_id,
            'seller_id' => $request->user()->id,
            'starting_price' => $request->starting_price,
            'current_price' => $request->starting_price,
            'reserve_price' => $request->reserve_price,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'bid_increment' => $request->bid_increment ?? 100,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المزاد بنجاح!',
            'data' => $auction->load('product'),
        ], 201);
    }

    /**
     * Display a specific auction
     */
    public function show($id)
    {
        $auction = Auction::with(['product.category', 'seller', 'bids.user', 'winningBid'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $auction,
        ]);
    }

    /**
     * Update an auction (Owner only, before it starts)
     */
    public function update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        // Check ownership
        if ($auction->seller_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المزاد.',
            ], 403);
        }

        // Can't edit active or ended auctions
        if ($auction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل مزاد نشط أو منتهي.',
            ], 422);
        }

        $request->validate([
            'starting_price' => 'sometimes|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'start_time' => 'sometimes|date|after:now',
            'end_time' => 'sometimes|date|after:start_time',
            'bid_increment' => 'nullable|numeric|min:1',
        ]);

        $auction->update($request->only([
            'starting_price', 'reserve_price', 'start_time', 'end_time', 'bid_increment'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المزاد بنجاح!',
            'data' => $auction->fresh(['product']),
        ]);
    }

    /**
     * Cancel an auction (Owner only)
     */
    public function destroy(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        // Check ownership
        if ($auction->seller_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإلغاء هذا المزاد.',
            ], 403);
        }

        $auction->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء المزاد بنجاح!',
        ]);
    }

    /**
     * Get seller's auctions
     */
    public function myAuctions(Request $request)
    {
        $auctions = Auction::where('seller_id', $request->user()->id)
            ->with(['product', 'bids'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $auctions,
        ]);
    }
}
